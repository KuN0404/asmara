<?php

namespace App\Jobs;

use App\Models\WhatsAppLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWhatsAppNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;
    public $backoff = [10, 30, 60]; // Retry after 10s, 30s, 60s

    protected $phoneNumber;
    protected $message;
    protected $type;
    protected $trigger;
    protected $relatedId;

    public function __construct($phoneNumber, $message, $type, $trigger, $relatedId = null)
    {
        $this->phoneNumber = $phoneNumber;
        $this->message = $message;
        $this->type = $type; // 'announcement', 'office_agenda', 'my_agenda', 'test'
        $this->trigger = $trigger; // 'created', 'h_minus_1', 'h_day', 'manual'
        $this->relatedId = $relatedId;
    }

    // public function handle()
    // {
    //     $whatsappServiceUrl = env('WHATSAPP_SERVICE_URL', 'http://localhost:3030');

    //     try {
    //         Log::info("Sending WhatsApp to {$this->phoneNumber}", [
    //             'type' => $this->type,
    //             'trigger' => $this->trigger
    //         ]);

    //         $response = Http::timeout(10)->post("{$whatsappServiceUrl}/send-message", [
    //             'phone' => $this->phoneNumber,
    //             'message' => $this->message,
    //         ]);

    //         if ($response->successful()) {
    //             $this->logNotification('success', null);
    //             Log::info("WhatsApp sent successfully to {$this->phoneNumber}");
    //         } else {
    //             $errorMessage = $response->json()['error'] ?? 'Unknown error';
    //             $this->logNotification('failed', $errorMessage);
    //             Log::error("WhatsApp failed to {$this->phoneNumber}: {$errorMessage}");

    //             // Retry if it's a connection issue
    //             if ($this->attempts() < $this->tries) {
    //                 throw new \Exception($errorMessage);
    //             }
    //         }
    //     } catch (\Exception $e) {
    //         $this->logNotification('failed', $e->getMessage());
    //         Log::error("WhatsApp exception for {$this->phoneNumber}: " . $e->getMessage());

    //         // Rethrow to trigger retry
    //         if ($this->attempts() < $this->tries) {
    //             throw $e;
    //         }
    //     }
    // }


    public function handle()
{
    $whatsappServiceUrl = env('WHATSAPP_SERVICE_URL', 'http://localhost:3030');

    try {
        // FORMAT NOMOR - PENTING!
        $formattedPhone = $this->formatPhoneNumber($this->phoneNumber);

        Log::info("Sending WhatsApp to {$formattedPhone}", [
            'original' => $this->phoneNumber,
            'type' => $this->type,
            'trigger' => $this->trigger
        ]);

        $response = Http::timeout(10)->post("{$whatsappServiceUrl}/send-message", [
            'phone' => $formattedPhone,
            'message' => $this->message,
        ]);

        if ($response->successful()) {
            $this->logNotification('success', null);
            Log::info("WhatsApp sent successfully to {$formattedPhone}");
        } else {
            $errorMessage = $response->json()['error'] ?? 'Unknown error';
            $this->logNotification('failed', $errorMessage);
            Log::error("WhatsApp failed to {$formattedPhone}: {$errorMessage}");

            if ($this->attempts() < $this->tries) {
                throw new \Exception($errorMessage);
            }
        }
    } catch (\Exception $e) {
        $this->logNotification('failed', $e->getMessage());
        Log::error("WhatsApp exception for {$this->phoneNumber}: " . $e->getMessage());

        if ($this->attempts() < $this->tries) {
            throw $e;
        }
    }
}

// TAMBAHKAN METHOD INI
protected function formatPhoneNumber($phone)
{
    // Remove all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);

    // Remove leading zeros
    $phone = ltrim($phone, '0');

    // Add 62 if not present (Indonesia country code)
    if (!str_starts_with($phone, '62')) {
        $phone = '62' . $phone;
    }

    // Add WhatsApp suffix
    return $phone . '@s.whatsapp.net';
}

    protected function logNotification($status, $errorMessage = null)
    {
        WhatsAppLog::create([
            'phone_number' => $this->phoneNumber,
            'message' => $this->message,
            'type' => $this->type,
            'trigger' => $this->trigger,
            'related_id' => $this->relatedId,
            'status' => $status,
            'error_message' => $errorMessage,
            'sent_at' => now(),
        ]);
    }

    public function failed(\Throwable $exception)
    {
        Log::error("WhatsApp job failed permanently for {$this->phoneNumber}", [
            'exception' => $exception->getMessage(),
            'type' => $this->type,
            'trigger' => $this->trigger
        ]);

        $this->logNotification('failed', "Job failed after {$this->tries} attempts: " . $exception->getMessage());
    }
}