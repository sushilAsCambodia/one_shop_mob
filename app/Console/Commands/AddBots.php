<?php

namespace App\Console\Commands;

use App\Jobs\AddBot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AddBots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:bots';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info("Cron is working fine!");
        AddBot::dispatch();
    }
}
