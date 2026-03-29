<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class OptimizeImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:optimize-images 
                            {--cleanup : Remove orphaned output files after processing}
                            {--rebuild : Clear output directory and reprocess all images}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize all images via Imgs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $imgs = app(\App\Imgs::class);

        if ($this->option('rebuild')) {
            $this->info('Removing output directory...');

            if (File::exists($imgs->OUTPUT_DIR)) {
                File::deleteDirectory($imgs->OUTPUT_DIR);
            }
        }

        $this->info('Building images...');
        $imgs->build();

        if ($this->option('cleanup')) {
            $this->info('Cleaning up orphaned output files...');
            $imgs->cleanup();
        }

        $this->info('Done.');
    }
}
