<?php

namespace App\Http\Controllers;

use App\Models\WeatherDailySummary;
use App\Models\WeatherObservation;
use App\Services\Weather\WeatherSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class WeatherController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->from) : Carbon::today()->subDays(30);
        $to = $request->filled('to') ? Carbon::parse($request->to) : Carbon::today();

        $summaries = WeatherDailySummary::whereBetween('business_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->orderByDesc('business_date')
            ->paginate(31)
            ->withQueryString();

        $stats = [
            'total' => WeatherDailySummary::whereBetween('business_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])->count(),
            'complete' => WeatherDailySummary::whereBetween('business_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])->where('source_status', 'complete')->count(),
            'partial' => WeatherDailySummary::whereBetween('business_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])->where('source_status', 'partial')->count(),
        ];

        $lastSync = WeatherObservation::max('created_at');
        $lastObservation = WeatherObservation::orderByDesc('observed_at')->first();

        return view('weather.index', compact('summaries', 'stats', 'lastSync', 'lastObservation', 'from', 'to'));
    }

    public function sync(Request $request, WeatherSyncService $service)
    {
        $data = $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:92'],
        ]);

        try {
            $r = $service->syncRecentDays((int) $data['days']);

            $hasNew = $r['new_observations'] > 0 || $r['new_summaries'] > 0;

            if (! $hasNew) {
                $message = "✓ Data sudah terbaru — tidak ada perubahan.";
            } else {
                $message = "✓ Sync selesai: {$r['new_observations']} observasi baru"
                    . ($r['updated_observations'] > 0 ? ", {$r['updated_observations']} diperbarui" : '')
                    . ". {$r['new_summaries']} hari baru"
                    . ($r['updated_summaries'] > 0 ? ", {$r['updated_summaries']} hari di-refresh" : '')
                    . ".";
            }

            return back()->with('success', $message);
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'Could not resolve host')) {
                $short = 'Tidak bisa terhubung ke internet / Open-Meteo unreachable.';
            } elseif (str_contains($msg, 'timed out')) {
                $short = 'Koneksi ke Open-Meteo timeout. Coba lagi nanti.';
            } elseif (str_contains($msg, 'HTTP 4') || str_contains($msg, 'HTTP 5')) {
                $short = 'Open-Meteo error server. Coba lagi nanti.';
            } else {
                $short = mb_strlen($msg) > 200 ? mb_substr($msg, 0, 200) . '…' : $msg;
            }
            return back()->with('error', 'Sync gagal: ' . $short);
        }
    }
}
