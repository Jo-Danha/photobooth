<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\BoothSetting;
use App\Models\CustomFrame;

class AdminController extends Controller
{
    public function loginForm()
    {
        if (session('is_admin_logged_in')) {
            return redirect()->route('admin.settings');
        }
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $setting = BoothSetting::getActiveSettings();

        // Login bisa pakai username/password ATAU PIN
        if (
            ($request->username === $setting->admin_username && $request->password === $setting->admin_password) ||
            ($request->password === $setting->admin_pin)
        ) {
            $request->session()->put('is_admin_logged_in', true);
            return redirect()->route('admin.settings')->with('success', 'Selamat datang di Panel Admin Photobooth!');
        }

        return back()->with('error', 'Username, Password, atau PIN salah.');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('is_admin_logged_in');
        return redirect()->route('admin.login')->with('success', 'Anda telah berhasil logout.');
    }

    public function settings()
    {
        $setting = BoothSetting::getActiveSettings();
        $customFrames = CustomFrame::orderBy('id', 'desc')->get();
        $hasServiceAccount = file_exists(storage_path('app/service-account.json'));
        $localIp = gethostbyname(gethostname());

        return view('admin.settings', compact('setting', 'customFrames', 'hasServiceAccount', 'localIp'));
    }

    public function updateSettings(Request $request)
    {
        $setting = BoothSetting::getActiveSettings();

        $validated = $request->validate([
            'camera_device_id'       => 'nullable|string',
            'camera_brightness'      => 'required|integer|min:20|max:200',
            'camera_contrast'        => 'required|integer|min:20|max:200',
            'camera_iso'             => 'required|integer|min:-100|max:100',
            'camera_saturation'      => 'required|integer|min:0|max:200',
            'default_brand_text'     => 'required|string|max:50',
            'default_frame_color'    => 'required|string|max:10',
            'admin_username'         => 'required|string|max:30',
            'admin_password'         => 'required|string|min:4|max:50',
            'admin_pin'              => 'required|string|min:4|max:8',
            'google_drive_folder_id' => 'nullable|string',
            'public_domain_url'      => 'nullable|url',
        ]);

        $validated['is_payment_enabled'] = $request->has('is_payment_enabled');
        $validated['is_lock_mode']        = $request->has('is_lock_mode');
        $validated['lock_brand_text']     = $request->has('lock_brand_text');
        $validated['lock_frame_color']    = $request->has('lock_frame_color');

        if ($request->hasFile('service_account_file')) {
            $request->file('service_account_file')->storeAs('', 'service-account.json', 'local');
        }

        $setting->update($validated);

        return redirect()->route('admin.settings')->with('success', 'Pengaturan berhasil diperbarui!');
    }

    public function uploadFrame(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:100',
            'category'     => 'required|string|max:50',
            'layout_type'  => 'required|string',
            'frame_image'  => 'required|image|mimes:png,webp|max:5120',
        ]);

        $file = $request->file('frame_image');
        $filename = 'frame_' . time() . '_' . Str::slug($request->name) . '.' . $file->getClientOriginalExtension();

        $destDir = public_path('frames');
        if (!file_exists($destDir)) {
            mkdir($destDir, 0777, true);
        }

        $file->move($destDir, $filename);

        CustomFrame::create([
            'name'             => $request->name,
            'category'         => $request->category,
            'layout_type'      => $request->layout_type,
            'frame_image_path' => 'frames/' . $filename,
            'is_active'        => true,
        ]);

        return redirect()->route('admin.settings')->with('success', 'Template frame "' . $request->name . '" berhasil ditambahkan!');
    }

    public function deleteFrame($id)
    {
        $frame = CustomFrame::findOrFail($id);
        $fullPath = public_path($frame->frame_image_path);
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }
        $frame->delete();

        return redirect()->route('admin.settings')->with('success', 'Template frame berhasil dihapus.');
    }
}