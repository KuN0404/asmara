<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\CheckUpcomingAgendas;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Check H-1 reminders every day at 18:00 (6 PM)
        $schedule->job(new CheckUpcomingAgendas('h_minus_1'))
                 ->dailyAt('16:00')
                 ->timezone('Asia/Jakarta');

        // Check H-Day reminders every day at 07:00 (7 AM)
        $schedule->job(new CheckUpcomingAgendas('h_day'))
                 ->dailyAt('07:00')
                 ->timezone('Asia/Jakarta');

        // Optional: Check again at 08:30 AM for H-Day
        // $schedule->job(new CheckUpcomingAgendas('h_day'))
        //          ->dailyAt('08:30')
        //          ->timezone('Asia/Jakarta');
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}