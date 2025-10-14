<?php

namespace App\Console\Commands;

use App\Models\LiveShow;
use App\Models\UserQuiz;
use Illuminate\Console\Command;

class ClearLiveShowUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear-live-show-users {liveShowId}';

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
        $liveShowId = $this->argument('liveShowId');

       LiveShow::clearGameShowUsers($liveShowId);


        $this->info('All users cleared from the live show.');
        return Command::SUCCESS;
    }
}
