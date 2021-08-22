<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Libraries\GameHelper;

class PlayGame extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'play:game';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return mixed
     */
    public function handle()
    {
        try {
            $game = new GameHelper;
            $game->initializeGame();
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
    }
}
