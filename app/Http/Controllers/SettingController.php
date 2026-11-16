<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = [
            'store_name' => Setting::get('store_name', 'Rose Colection'),
            'store_tagline' => Setting::get('store_tagline', 'Boutique & Modern Fashion'),
            'store_address' => Setting::get('store_address', 'Jl. Boulevard No. 18, Panakkukang, Makassar'),
            'store_phone' => Setting::get('store_phone', '0812-3456-7890'),
            'store_email' => Setting::get('store_email', 'info@rosecolection.com'),
            'receipt_footer' => Setting::get('receipt_footer', "Terima kasih telah berbelanja di Rose Colection!\nBarang yang sudah dibeli dapat ditukar maksimal 2 hari."),
            'currency' => Setting::get('currency', 'IDR'),
            'timezone' => Setting::get('timezone', 'Asia/Makassar'),
            'min_stock_alert_threshold' => Setting::get('min_stock_alert_threshold', '5'),
        ];

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:100'],
            'store_tagline' => ['nullable', 'string', 'max:150'],
            'store_address' => ['required', 'string', 'max:255'],
            'store_phone' => ['required', 'string', 'max:50'],
            'store_email' => ['nullable', 'email', 'max:100'],
            'receipt_footer' => ['required', 'string', 'max:500'],
            'currency' => ['required', 'string', 'max:10'],
            'timezone' => ['required', 'string', 'max:50'],
            'min_stock_alert_threshold' => ['required', 'integer', 'min:1'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->route('settings.index')->with('success', 'Pengaturan toko berhasil disimpan.');
    }
}
