<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\UISetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class UISettingController extends Controller
{
    public function index(Request $request)
    {
        Log::info('UI Settings: Page accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);
        $settings = UISetting::first() ?? new UISetting();
        Log::info('UI Settings: Current settings fetched', [
            'user_id' => Auth::guard('admin')->id(),
            'settings' => json_decode($settings, true),
            'timestamp' => now(),
        ]);
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        Log::info('UI Settings: Attempting to update settings', [
            'user_id' => Auth::guard('admin')->id(),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);
        $validator = Validator::make($request->all(), [
            'org_name'          => 'nullable|string|max:255',
            'org_address'       => 'nullable|string|max:500',
            'email'             => 'nullable|email|max:255',
            'contact_number'    => 'nullable|string|max:45',
            'org_logo'          => 'nullable|image|mimes:jpeg,png,jpg|max:5012',
            'org_logo_full'     => 'nullable|image|mimes:jpeg,png,jpg|max:5012',
            'facebook'          => 'nullable|url',
            'instagram'         => 'nullable|url',
            'twitter'           => 'nullable|url',
            'youtube'           => 'nullable|url',
            'website'           => 'nullable|url',
            'primary'           => 'required|string',
            'secondary'         => 'required|string',
            'tertiary'          => 'required|string',
        ]);
        if ($validator->fails()) {
            Log::warning('UI Settings: Update failed - Validation error', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors()->toArray(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', $validator->errors()->first() ?? 'Something went wrong')->withInput();
        }
        $settings = UISetting::first();
        if (!$settings) {
            $settings = new UISetting();
        }

        $settings->org_name = $request->input('org_name');
        $settings->org_address = $request->input('org_address');
        $settings->email = $request->input('email');
        $settings->contact_number = $request->input('contact_number');

        if ($request->hasFile('org_logo')) {
            // Delete old logo if exists
            if ($settings->org_logo && Storage::disk('public')->exists($settings->org_logo)) {
                Storage::disk('public')->delete($settings->org_logo);
            }

            $path = $request->file('org_logo')->store('org_logos', 'public');
            $settings->org_logo = $path;
        }

        if ($request->hasFile('org_logo_full')) {
            // Delete old logo full if exists
            if ($settings->org_logo_full && Storage::disk('public')->exists($settings->org_logo_full)) {
                Storage::disk('public')->delete($settings->org_logo_full);
            }

            $pathFull = $request->file('org_logo_full')->store('org_logos/full', 'public');
            $settings->org_logo_full = $pathFull;
        }

        $settings->social_links = $request->only([
            'facebook',
            'instagram',
            'twitter',
            'youtube',
            'website',
        ]);
        $settings->theme_colors = $request->only([
            'primary',
            'secondary',
            'tertiary',
        ]);
        $settings->save();
        Log::info('UI Settings: Settings updated successfully', [
            'user_id' => Auth::guard('admin')->id(),
            'timestamp' => now(),
        ]);

        return redirect()->back()->with('toast-success', 'Settings updated successfully');
    }
}
