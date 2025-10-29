<?php

namespace App\Jobs;

use App\Models\MyAgenda;
use App\Models\OfficeAgenda;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckUpcomingAgendas implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $reminderType;

    public function __construct($reminderType = 'h_minus_1')
    {
        $this->reminderType = $reminderType; // 'h_minus_1' or 'h_day'
    }

    public function handle()
    {
        Log::info("🔔 Checking {$this->reminderType} reminders...");

        if ($this->reminderType === 'h_minus_1') {
            $this->checkHMinus1Reminders();
        } elseif ($this->reminderType === 'h_day') {
            $this->checkHDayReminders();
        }
    }

    protected function checkHMinus1Reminders()
    {
        $tomorrow = Carbon::tomorrow('Asia/Jakarta');

        // H-1 Office Agendas
        $officeAgendas = OfficeAgenda::with(['userParticipants', 'room'])
            ->whereDate('start_at', $tomorrow->toDateString())
            ->whereIn('status', ['comming_soon', 'in_progress'])
            ->get();

        foreach ($officeAgendas as $agenda) {
            foreach ($agenda->userParticipants as $user) {
                if ($user->whatsapp_number) {
                    $message = "⏰ *REMINDER H-1 AGENDA KANTOR*\n\n" .
                              "📌 {$agenda->title}\n" .
                              "📅 Besok: " . Carbon::parse($agenda->start_at)->format('d M Y') . "\n" .
                              "🕐 Jam: " . Carbon::parse($agenda->start_at)->format('H:i') . " - " . Carbon::parse($agenda->until_at)->format('H:i') . " WIB\n" .
                              "📍 Lokasi: {$agenda->location}\n" .
                              ($agenda->room ? "🚪 Ruangan: {$agenda->room->name}\n" : "") .
                              ($agenda->description ? "\n📝 {$agenda->description}\n" : "") .
                              "\n_Jangan lupa hadir ya! 🙏_";

                    SendWhatsAppNotification::dispatch(
                        $user->whatsapp_number,
                        $message,
                        'office_agenda',
                        'h_minus_1',
                        $agenda->id
                    );
                }
            }
        }

        // H-1 My Agendas
        $myAgendas = MyAgenda::with('creator')
            ->whereDate('start_at', $tomorrow->toDateString())
            ->whereIn('status', ['comming_soon', 'in_progress'])
            ->get();

        foreach ($myAgendas as $agenda) {
            $user = $agenda->creator;
            if ($user && $user->whatsapp_number) {
                $message = "⏰ *REMINDER H-1 AGENDA PRIBADI*\n\n" .
                          "📌 {$agenda->title}\n" .
                          "📅 Besok: " . Carbon::parse($agenda->start_at)->format('d M Y') . "\n" .
                          "🕐 Jam: " . Carbon::parse($agenda->start_at)->format('H:i') . " - " . Carbon::parse($agenda->until_at)->format('H:i') . " WIB\n" .
                          ($agenda->description ? "\n📝 {$agenda->description}\n" : "") .
                          "\n_Persiapkan diri Anda! 💪_";

                SendWhatsAppNotification::dispatch(
                    $user->whatsapp_number,
                    $message,
                    'my_agenda',
                    'h_minus_1',
                    $agenda->id
                );
            }
        }

        Log::info("✅ H-1 reminders sent: {$officeAgendas->count()} office agendas, {$myAgendas->count()} personal agendas");
    }

    protected function checkHDayReminders()
    {
        $today = Carbon::today('Asia/Jakarta');

        // H-Day Office Agendas
        $officeAgendas = OfficeAgenda::with(['userParticipants', 'room'])
            ->whereDate('start_at', $today->toDateString())
            ->whereIn('status', ['comming_soon', 'in_progress'])
            ->get();

        foreach ($officeAgendas as $agenda) {
            foreach ($agenda->userParticipants as $user) {
                if ($user->whatsapp_number) {
                    $message = "🔔 *REMINDER HARI INI - AGENDA KANTOR*\n\n" .
                              "📌 {$agenda->title}\n" .
                              "📅 Hari ini: " . Carbon::parse($agenda->start_at)->format('d M Y') . "\n" .
                              "🕐 Jam: " . Carbon::parse($agenda->start_at)->format('H:i') . " - " . Carbon::parse($agenda->until_at)->format('H:i') . " WIB\n" .
                              "📍 Lokasi: {$agenda->location}\n" .
                              ($agenda->room ? "🚪 Ruangan: {$agenda->room->name}\n" : "") .
                              ($agenda->metting_link ? "🔗 Link: {$agenda->metting_link}\n" : "") .
                              "\n_Agenda akan segera dimulai! ⏰_";

                    SendWhatsAppNotification::dispatch(
                        $user->whatsapp_number,
                        $message,
                        'office_agenda',
                        'h_day',
                        $agenda->id
                    );
                }
            }
        }

        // H-Day My Agendas
        $myAgendas = MyAgenda::with('creator')
            ->whereDate('start_at', $today->toDateString())
            ->whereIn('status', ['comming_soon', 'in_progress'])
            ->get();

        foreach ($myAgendas as $agenda) {
            $user = $agenda->creator;
            if ($user && $user->whatsapp_number) {
                $message = "🔔 *REMINDER HARI INI - AGENDA PRIBADI*\n\n" .
                          "📌 {$agenda->title}\n" .
                          "📅 Hari ini: " . Carbon::parse($agenda->start_at)->format('d M Y') . "\n" .
                          "🕐 Jam: " . Carbon::parse($agenda->start_at)->format('H:i') . " - " . Carbon::parse($agenda->until_at)->format('H:i') . " WIB\n" .
                          ($agenda->description ? "\n📝 {$agenda->description}\n" : "") .
                          "\n_Waktunya melaksanakan agenda! 🚀_";

                SendWhatsAppNotification::dispatch(
                    $user->whatsapp_number,
                    $message,
                    'my_agenda',
                    'h_day',
                    $agenda->id
                );
            }
        }

        Log::info("✅ H-Day reminders sent: {$officeAgendas->count()} office agendas, {$myAgendas->count()} personal agendas");
    }
}
