<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppLog;
use App\Jobs\SendWhatsAppNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WhatsAppController extends Controller
{
    private $whatsappServiceUrl = 'http://localhost:3030';

    public function status()
    {
        try {
            $response = Http::timeout(5)->get("{$this->whatsappServiceUrl}/status");

            return response()->json([
                'success' => true,
                'data' => $response->json()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'WhatsApp service is not available',
                'error' => $e->getMessage()
            ], 503);
        }
    }

    public function logs(Request $request)
    {
        $logs = WhatsAppLog::query()
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->start_date, fn($q) => $q->whereDate('created_at', '>=', $request->start_date))
            ->when($request->end_date, fn($q) => $q->whereDate('created_at', '<=', $request->end_date))
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($logs);
    }

public function sendTest(Request $request)
{
    $user = auth()->user();

    // Validasi nomor WhatsApp
    if (!$user->whatsapp_number) {
        return response()->json([
            'success' => false,
            'message' => 'Nomor WhatsApp Anda belum terdaftar. Silakan update profil terlebih dahulu.'
        ], 400);
    }

    // Validasi format nomor
    $phone = preg_replace('/[^0-9]/', '', $user->whatsapp_number);
    if (strlen($phone) < 10 || strlen($phone) > 15) {
        return response()->json([
            'success' => false,
            'message' => 'Format nomor WhatsApp tidak valid.'
        ], 400);
    }

    try {
        // Check status WhatsApp service
        $statusResponse = Http::timeout(5)->get("{$this->whatsappServiceUrl}/status");

        if (!$statusResponse->successful() || !$statusResponse->json()['connected']) {
            return response()->json([
                'success' => false,
                'message' => 'WhatsApp belum terhubung. Silakan scan QR code terlebih dahulu.'
            ], 503);
        }

        $message = "🧪 *TEST NOTIFIKASI*\n\n" .
                  "Halo {$user->name}! 👋\n\n" .
                  "Ini adalah pesan test dari sistem Agenda Management.\n\n" .
                  "✅ Notifikasi WhatsApp berhasil dikonfigurasi dan siap digunakan!\n\n" .
                  "_Pesan dikirim pada: " . now()->format('d M Y H:i') . " WIB_";

        // Dispatch job - HANYA 1X
        SendWhatsAppNotification::dispatch(
            $user->whatsapp_number,
            $message,
            'test',
            'manual',
            null
        );

        return response()->json([
            'success' => true,
            'message' => 'Test notification berhasil dijadwalkan untuk dikirim ke ' . $user->whatsapp_number
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal mengirim test notification: ' . $e->getMessage()
        ], 500);
    }
}
}
