<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Page;

class PublishScheduledContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'brentwood:publish-scheduled-content';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes any Pages or Content Elements that have a publish_at in the past';

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
    public function handle()
    {
        Page::publishScheduledContent();
    }
}
