<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\WhatsAppLog;

class WhatsAppService
{
    private $nodeServiceUrl;

    public function __construct()
    {
        $this->nodeServiceUrl = env('WHATSAPP_SERVICE_URL', 'http://localhost:3000');
    }

    /**
     * Send WhatsApp message
     */
    public function sendMessage(string $phoneNumber, string $message, string $type = 'general', string $trigger = 'manual', ?int $relatedId = null)
    {
        try {
            // Format phone number (remove +, spaces, dashes)
            $formattedPhone = $this->formatPhoneNumber($phoneNumber);

            // Log to database
            $log = WhatsAppLog::create([
                'phone_number' => $formattedPhone,
                'message' => $message,
                'type' => $type,
                'trigger' => $trigger,
                'related_id' => $relatedId,
                'status' => 'pending',
            ]);

            // Send to Node.js service
            $response = Http::timeout(30)->post("{$this->nodeServiceUrl}/send-message", [
                'phone' => $formattedPhone,
                'message' => $message,
            ]);

            if ($response->successful()) {
                $log->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);

                Log::info("WhatsApp sent to {$formattedPhone}", ['type' => $type]);
                return ['success' => true, 'log_id' => $log->id];
            }

            throw new \Exception($response->body());

        } catch (\Exception $e) {
            if (isset($log)) {
                $log->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }

            Log::error("WhatsApp failed to {$phoneNumber}: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Format phone number to international format
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If starts with 0, replace with 62 (Indonesia)
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        // If doesn't start with country code, add 62
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }

        return $phone . '@s.whatsapp.net';
    }

    /**
     * Check WhatsApp service status
     */
    public function checkStatus(): array
    {
        try {
            $response = Http::timeout(5)->get("{$this->nodeServiceUrl}/status");
            return $response->json();
        } catch (\Exception $e) {
            return [
                'connected' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
