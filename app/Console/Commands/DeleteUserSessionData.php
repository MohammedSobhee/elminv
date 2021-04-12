<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\UserSessionData;

class DeleteUserSessionData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:deleteusersessiondata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all data in the user_data column ofthe users_session_data table.';

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
     * This allows for forcing a logout if a new property is introduced without deleting last visited
     * wp page data
     *
     * @return int
     */
    public function handle() {
        UserSessionData::whereNotNull('user_data')->update(['user_data' => null]);
    }
}
