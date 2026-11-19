<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'shop_name' => BusinessSetting::get('shop_name', 'Seumpama Bunga'),
            'shop_address' => BusinessSetting::get('shop_address', ''),
            'shop_phone' => BusinessSetting::get('shop_phone', ''),
            'receipt_footer' => BusinessSetting::get('receipt_footer', ''),
            'currency' => BusinessSetting::get('currency', 'IDR'),
        ];

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'shop_name' => ['required', 'string', 'max:150'],
            'shop_address' => ['nullable', 'string', 'max:500'],
            'shop_phone' => ['nullable', 'string', 'max:30'],
            'receipt_footer' => ['nullable', 'string', 'max:500'],
            'currency' => ['required', 'string', 'max:10'],
        ]);

        foreach ($data as $key => $value) {
            BusinessSetting::set($key, $value, 'string');
        }

        return redirect()
            ->route('settings.index')
            ->with('success', 'Pengaturan berhasil disimpan.');
    }
}
