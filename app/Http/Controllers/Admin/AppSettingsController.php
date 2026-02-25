<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class AppSettingsController extends Controller
{
    /**
     * Show the app settings form.
     */
    public function index()
    {
        $defaults = Setting::defaultKeys();
        $existing = Setting::getAllKeys();

        $settings = [];
        foreach ($defaults as $key => $config) {
            $settings[$key] = [
                'value' => $existing[$key]['value'] ?? $config['default'],
                'type' => $config['type'],
                'group' => $config['group'],
            ];
        }

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update app settings.
     */
    public function update(Request $request)
    {
        $defaults = Setting::defaultKeys();
        $rules = [];

        foreach ($defaults as $key => $config) {
            if ($config['type'] === 'integer') {
                $rules[$key] = 'nullable|integer';
                if ($key === 'default_quiz_timer') {
                    $rules[$key] .= '|min:2|max:120';
                }
                if ($key === 'min_quiz_timer') {
                    $rules[$key] .= '|min:1|max:60';
                }
                if ($key === 'max_quiz_timer') {
                    $rules[$key] .= '|min:10|max:300';
                }
            }
            if ($config['type'] === 'string') {
                $rules[$key] = 'nullable|string|max:500';
            }
            if ($config['type'] === 'boolean') {
                $rules[$key] = 'nullable|in:0,1';
            }
        }

        $validated = $request->validate($rules);

        foreach ($defaults as $key => $config) {
            if ($config['type'] === 'boolean') {
                $value = $request->boolean($key);
            } else {
                $value = $validated[$key] ?? $config['default'] ?? '';
            }
            Setting::set($key, $value, $config['type'], $config['group']);
        }

        return redirect()->route('admin.settings.index')->with('success', 'Settings saved successfully.');
    }
}
