<?php

namespace App\Console\Commands;

use App\UserSessionData;
use Illuminate\Console\Command;

class ForceLogout extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:forcelogout';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Log out all users currently logged in.';

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
        UserSessionData::whereNotNull('user_data')->update(['hash' => 0]);
    }
}
