<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\CronController;
use Illuminate\Support\Facades\Log;

class RecurringTapPayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recurring:payment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process recurring payments for users';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Call the recurringPayment method from CronController
        $cronController = new CronController();
        $response = $cronController->recurringPayment();

        // Log the execution of the cron
        Log::error('Run Recurring Payment Cron ===> ');

        $this->info('Recurring payments processed successfully.');
        return 0;
    }
}

