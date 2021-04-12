<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use jeremykenedy\LaravelLogger\App\Models\Activity;

class DeleteActivityLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:deleteactivitylogs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete old activity logs';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        $deleted = Activity::where('created_at', '<=', Carbon::now()->subDays(3)->toDateTimeString())->forceDelete();
        $this->info('Deleted ' . $deleted . ' activity logs');
    }
}
