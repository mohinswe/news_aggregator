<?php

namespace App\Console\Commands;

use App\Models\Source;
use Illuminate\Console\Command;

class AggregateArticlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:aggregate-articles-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command is used to aggregate articles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sources = Source::where('status', 1)->get();

        foreach ($sources as $source) {
            $source->type()->aggregateArticles($source);
        }
    }
}
