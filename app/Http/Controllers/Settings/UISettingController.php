<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\UISetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UISettingController extends Controller
{
    public function index()
    {
        $settings = UISetting::first() ?? new UISetting();
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'org_name' => 'nullable|string|max:255',
            'org_address' => 'nullable|string|max:500',
            'org_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'social_links.facebook' => 'nullable|url',
            'social_links.instagram' => 'nullable|url',
            'social_links.twitter' => 'nullable|url',
            'social_links.youtube' => 'nullable|url',
            'social_links.website' => 'nullable|url',
            'theme_colors.primary' => 'required|string',
            'theme_colors.secondary' => 'required|string',
            'theme_colors.tertiary' => 'required|string',
        ]);

        $settings = UISetting::first();
        if (!$settings) {
            $settings = new UISetting();
        }

        $settings->org_name = $request->input('org_name');
        $settings->org_address = $request->input('org_address');

        if ($request->hasFile('org_logo')) {
            // Delete old logo if exists
            if ($settings->org_logo && Storage::disk('public')->exists($settings->org_logo)) {
                Storage::disk('public')->delete($settings->org_logo);
            }
            
            $path = $request->file('org_logo')->store('org_logos', 'public');
            $settings->org_logo = $path;
        }

        $settings->social_links = $request->input('social_links');
        $settings->theme_colors = $request->input('theme_colors');
        $settings->save();

        return redirect()->back()->with('toast-success', 'Settings updated successfully');
    }
}
