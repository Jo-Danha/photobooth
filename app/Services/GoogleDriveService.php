<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;
use Illuminate\Support\Facades\Log;
use App\Models\BoothSetting;
use Illuminate\Support\Facades\Http;

class GoogleDriveService
{
    protected $driveService = null;
    protected $folderId = null;
    public $authMethod = null; // 'oauth' | 'service_account' | null

    /**
     * Ambil OAuth client ID + secret dari .env
     */
    protected function oauthClient()
    {
        $clientId = env('GOOGLE_OAUTH_CLIENT_ID');
        $clientSecret = env('GOOGLE_OAUTH_CLIENT_SECRET');

        if (empty($clientId) || empty($clientSecret)) {
            return null;
        }

        $redirectUri = env('GOOGLE_OAUTH_REDIRECT_URI');
        // Bangun redirect URI dari request bila kosong
        if (empty($redirectUri)) {
            $redirectUri = url('/admin/gdrive/callback');
        }

        return (object) [
            'id' => $clientId,
            'secret' => $clientSecret,
            'redirect' => $redirectUri,
        ];
    }

    public function __construct()
    {
        $refreshToken = null;
        try {
            $refreshToken = BoothSetting::getActiveSettings()->google_oauth_refresh_token;
        } catch (\Exception $e) {
            $refreshToken = null;
        }

        $this->folderId = env('GOOGLE_DRIVE_FOLDER_ID');
        if (empty($this->folderId)) {
            try {
                $this->folderId = BoothSetting::getActiveSettings()->google_drive_folder_id;
            } catch (\Exception $e) {
                $this->folderId = null;
            }
        }

        if (!class_exists(Client::class)) {
            return;
        }

        // Prioritas: OAuth 2.0 (My Drive) bila refresh token & client tersedia
        if ($refreshToken && $this->oauthClient()) {
            try {
                $client = new Client();
                $client->setClientId($this->oauthClient()->id);
                $client->setClientSecret($this->oauthClient()->secret);
                $client->setAccessType('offline');
                $client->setApprovalPrompt('force');
                $client->refreshToken($refreshToken);
                $this->driveService = new Drive($client);
                $this->authMethod = 'oauth';
                return;
            } catch (\Exception $e) {
                Log::error('Google Drive OAuth init error: ' . $e->getMessage());
                $this->driveService = null;
                $this->authMethod = null;
            }
        }

        // Fallback: Service Account (shared drive)
        $authPath = storage_path('app/service-account.json');
        if (file_exists($authPath)) {
            try {
                $client = new Client();
                $client->setAuthConfig($authPath);
                $client->addScope(Drive::DRIVE);
                $this->driveService = new Drive($client);
                $this->authMethod = 'service_account';
            } catch (\Exception $e) {
                Log::error('Google Drive Service Account Error: ' . $e->getMessage());
                $this->driveService = null;
            }
        }
    }

    /**
     * Mengembalikan status koneksi terkini (untuk panel admin).
     */
    public function status()
    {
        $hasRefresh = !empty(BoothSetting::getActiveSettings()->google_oauth_refresh_token);
        $client = $this->oauthClient();
        $hasJson = file_exists(storage_path('app/service-account.json'));

        if ($hasRefresh && $client) {
            return ['ok' => true, 'method' => 'oauth', 'connected' => $this->driveService !== null];
        }
        if ($client) {
            return ['ok' => false, 'method' => 'oauth', 'connected' => false, 'need_login' => true];
        }
        if ($hasJson) {
            return ['ok' => false, 'method' => 'service_account', 'connected' => $this->driveService !== null];
        }
        return ['ok' => false, 'method' => null, 'connected' => false];
    }

    /**
     * Bangun URL consent Google untuk login OAuth (dari panel admin).
     */
    public function connectUrl()
    {
        $client = $this->oauthClient();
        if (!$client) return null;

        try {
            $g = new Client();
            $g->setClientId($client->id);
            $g->setRedirectUri($client->redirect);
            $g->setAccessType('offline');
            $g->setApprovalPrompt('force');
            $g->setPrompt('consent');
            $g->addScope('https://www.googleapis.com/auth/drive.file');
            return $g->createAuthUrl();
        } catch (\Exception $e) {
            Log::error('Google Drive connect URL error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Pertukarkan kode otorisasi Google menjadi refresh token + simpan ke DB.
     */
    public function handleCallback($code)
    {
        $client = $this->oauthClient();
        if (!$client) {
            return ['ok' => false, 'message' => 'Konfigurasi OAuth Client belum diisi di .env.'];
        }

        try {
            $g = new Client();
            $g->setClientId($client->id);
            $g->setClientSecret($client->secret);
            $g->setRedirectUri($client->redirect);
            $token = $g->fetchAccessTokenWithAuthCode($code);

            if (isset($token['error'])) {
                return ['ok' => false, 'message' => 'OAuth error: ' . ($token['error_description'] ?? $token['error'])];
            }
            if (empty($token['refresh_token'])) {
                return ['ok' => false, 'message' => 'Tidak ada refresh_token. Coba lagi (pastikan pilih akun & setujui akses penuh).'];
            }

            BoothSetting::getActiveSettings()->update([
                'google_oauth_refresh_token' => $token['refresh_token'],
            ]);

            return ['ok' => true, 'message' => 'Berhasil terhubung ke Google Drive kamu!'];
        } catch (\Exception $e) {
            Log::error('Google Drive OAuth callback error: ' . $e->getMessage());
            return ['ok' => false, 'message' => 'Gagal: ' . $e->getMessage()];
        }
    }

    /** Hapus koneksi OAuth (logout). */
    public function disconnect()
    {
        BoothSetting::getActiveSettings()->update(['google_oauth_refresh_token' => null]);
        return true;
    }

    /**
     * Upload file ke Google Drive.
     * Prioritas OAuth (My Drive) bila refresh token & client ada, else service account.
     */
    public function uploadFile($localFilePath, $filename)
    {
        if (!$this->driveService || !file_exists($localFilePath)) {
            return null;
        }

        try {
            $fileMetadata = new DriveFile([
                'name' => $filename,
                'parents' => $this->folderId ? [$this->folderId] : []
            ]);

            $content = file_get_contents($localFilePath);

            $file = $this->driveService->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => 'image/png',
                'uploadType' => 'multipart',
                'fields' => 'id, webViewLink, webContentLink'
            ]);

            $permission = new Permission([
                'type' => 'anyone',
                'role' => 'reader'
            ]);
            $this->driveService->permissions->create($file->id, $permission);

            return [
                'file_id'       => $file->id,
                'view_link'     => $file->webViewLink,
                'download_link' => $file->webContentLink
            ];
        } catch (\Exception $e) {
            Log::error('Gagal upload Google Drive: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Uji koneksi Google Drive.
     */
    public function testConnection()
    {
        if (!class_exists(Client::class)) {
            return ['ok' => false, 'method' => $this->authMethod, 'message' => 'Library Google API Client belum terinstall (jalankan composer require google/apiclient).'];
        }

        if ($this->authMethod === 'oauth') {
            try {
                $filename = 'connection_test_' . time() . '.txt';
                $fileMetadata = new DriveFile([
                    'name'     => $filename,
                    'parents'  => $this->folderId ? [$this->folderId] : [],
                ]);
                $file = $this->driveService->files->create($fileMetadata, [
                    'data'       => 'test',
                    'mimeType'   => 'text/plain',
                    'uploadType' => 'multipart',
                    'fields'     => 'id,name,parents',
                ]);
                $this->driveService->files->delete($file->getId());
                $folderNote = $this->folderId ? ' Folder kamu valid.' : ' Tidak ada Folder ID (file masuk ke root Drive).';
                return ['ok' => true, 'method' => 'oauth', 'message' => 'Koneksi OAuth Google Drive BERHASIL!' . $folderNote];
            } catch (\Exception $e) {
                return ['ok' => false, 'method' => 'oauth', 'message' => 'Gagal terhubung OAuth: ' . $e->getMessage()];
            }
        }

        if ($this->authMethod === 'service_account') {
            if (!file_exists(storage_path('app/service-account.json'))) {
                return ['ok' => false, 'method' => 'service_account', 'message' => 'File Service Account JSON belum diunggah.'];
            }
            try {
                $filename = 'connection_test_' . time() . '.txt';
                $fileMetadata = new DriveFile([
                    'name'     => $filename,
                    'parents'  => $this->folderId ? [$this->folderId] : [],
                ]);
                $file = $this->driveService->files->create($fileMetadata, [
                    'data'       => 'test',
                    'mimeType'   => 'text/plain',
                    'uploadType' => 'multipart',
                    'fields'     => 'id,name,parents',
                ]);
                $this->driveService->files->delete($file->getId());
                $folderNote = $this->folderId ? ' Folder tujuan valid.' : ' Tidak ada Folder ID (file masuk ke root Drive).';
                return ['ok' => true, 'method' => 'service_account', 'message' => 'Koneksi Service Account BERHASIL!' . $folderNote];
            } catch (\Exception $e) {
                return ['ok' => false, 'method' => 'service_account', 'message' => 'Gagal terhubung: ' . $e->getMessage()];
            }
        }

        return ['ok' => false, 'method' => null, 'message' => 'Belum ada koneksi Google Drive. Isi OAuth Client lalu login, atau upload Service Account JSON (untuk Shared Drive).'];
    }
}
