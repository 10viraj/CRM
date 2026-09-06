<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->groupBy('group');
        return view('settings.index', compact('settings'));
    }

    public function store(Request $request)
    {
        $data = $request->except('_token');

        foreach ($data as $key => $value) {
            Setting::where('key', $key)->update(['value' => $value]);
        }

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully.');
    }
}
