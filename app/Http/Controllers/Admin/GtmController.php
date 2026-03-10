<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class GtmController extends Controller
{
    public function index()
    {
        $gtmEnabled = Setting::get('gtm_enabled', false);
        $gtmContainerId = Setting::get('gtm_container_id', '');
        $gtmHeadScript = Setting::get('gtm_head_script', '');
        $gtmBodyScript = Setting::get('gtm_body_script', '');

        return view('admin.gtm.index', compact('gtmEnabled', 'gtmContainerId', 'gtmHeadScript', 'gtmBodyScript'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'gtm_enabled' => 'nullable|in:0,1',
            'gtm_container_id' => 'nullable|string|max:50',
            'gtm_head_script' => 'nullable|string|max:10000',
            'gtm_body_script' => 'nullable|string|max:10000',
        ]);

        Setting::set('gtm_enabled', $request->boolean('gtm_enabled'), 'boolean', 'gtm');
        Setting::set('gtm_container_id', $validated['gtm_container_id'] ?? '', 'string', 'gtm');
        Setting::set('gtm_head_script', $validated['gtm_head_script'] ?? '', 'string', 'gtm');
        Setting::set('gtm_body_script', $validated['gtm_body_script'] ?? '', 'string', 'gtm');

        return redirect()->route('admin.gtm.index')->with('success', 'Google Tag Manager settings saved.');
    }
}
