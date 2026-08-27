<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;
use Illuminate\Support\Facades\Log;

class GoogleDriveService
{
    protected $driveService = null;
    protected $folderId = null;

    public function __construct()
    {
        $authPath = storage_path('app/service-account.json');

        if (file_exists($authPath)) {
            try {
                $client = new Client();$client->setAuthConfig($authPath);$client->addScope(Drive::DRIVE);
                $this->driveService = new Drive($client);
            } catch (\Exception $e) {
                Log::error('Google Drive Auth Error: ' . $e->getMessage());
            }
        }

        $this->folderId = env('GOOGLE_DRIVE_FOLDER_ID');
    }

    /**
     * Upload berkas ke Google Drive dan return link publik
     */
    public function uploadFile($localFilePath,$filename)
    {
        if (!$this->driveService) {
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

            // Set izin akses public read
            $permission = new Permission([                 'type' => 'anyone',                 'role' => 'reader'             ]);$this->driveService->permissions->create($file->id,$permission);

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
}