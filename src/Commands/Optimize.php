<?php

declare(strict_types=1);

namespace Semmelsamu\Imgs\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Semmelsamu\Imgs\Imgs;

class Optimize extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'imgs:optimize
                            {--cleanup : Remove orphaned output files after processing}
                            {--rebuild : Clear output directory and reprocess all images}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize and resize all source images via Imgs';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        /** @var Imgs $imgs */
        $imgs = app(Imgs::class);

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

        return self::SUCCESS;
    }
}
