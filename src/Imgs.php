<?php

declare(strict_types=1);

namespace Semmelsamu\Imgs;

use Exception;
use Intervention\Image\Laravel\Facades\Image;

/**
 * Image scaling and optimizing utility.
 */
class Imgs
{
    /**
     * Create a new Imgs instance.
     *
     * @param  string  $INPUT_DIR  The fully qualified path to the input directory
     * @param  string  $OUTPUT_DIR  The fully qualified path to the output directory
     * @param  array  $OUTPUT_SIZES  The sizes in which the images will be processed
     * @param  string  $OUTPUT_FORMAT  The output format
     * @param  int  $OUTPUT_QUALITY  The output quality
     */
    public function __construct(
        public readonly string $INPUT_DIR,
        public readonly string $OUTPUT_DIR,
        public readonly array $OUTPUT_SIZES,
        public readonly string $OUTPUT_FORMAT,
        public readonly int $OUTPUT_QUALITY,
    ) {}

    /**
     * Returns weather or not the image is eligible for processing.
     *
     * @param  string  $image  Fully qualified path to the image
     * @return array The result of getimagesize
     *
     * @throws Exception if the image is not valid
     */
    public function validate(string $image): array
    {
        if (str_starts_with($image, $this->OUTPUT_DIR)) {
            throw new Exception('Images in output directory are not allowed');
        }

        $imagesize = getimagesize($image);

        if (! $imagesize) {
            throw new Exception('Image could not be read');
        }

        return $imagesize;
    }

    /**
     * Generate the filename of an optimized image for the specified size.
     *
     * @param  mixed  $image  The fully qualified path to the image
     * @param  mixed  $size  The size
     */
    public function image(string $image, int $size): string
    {
        return substr(basename($image), 0, 64).'-'.md5($image.filemtime($image)).'-'.$size.'.'.$this->OUTPUT_FORMAT;
    }

    /**
     * Conveniently get the filename of the optimized image, if possible with
     * the requested width.
     *
     * @param  mixed  $width
     */
    public function get(string $image, ?int $width): string
    {
        $originalWidth = $this->validate($image)[0];

        $availableSizes = collect($this->OUTPUT_SIZES)
            ->filter(fn ($size) => $size <= $originalWidth);

        // If a width was specified, find the smallest available size larger or equal
        if (isset($width)) {
            $width = $availableSizes
                ->filter(fn ($size) => $size >= $width)
                ->min();
        }

        // If either no width was specified or no size was found, use the largest available size
        if (! $width) {
            $width = $availableSizes->max();
        }

        return $this->image($image, $width);
    }

    /**
     * Scan the inputs for available images.
     *
     * @return array An array listing all fully qualified paths to the found images
     */
    public function scan(): array
    {
        echo 'Discovering images...'.PHP_EOL;

        $files = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $this->INPUT_DIR,
                \RecursiveDirectoryIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS
            )
        );

        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $pathname = $file->getPathname();

            try {
                $this->validate($pathname);
            } catch (Exception $e) {
                continue;
            }

            echo '    '.$file->getFilename()."\n";

            $files[] = $pathname;
        }

        return $files;
    }

    /**
     * Optimize the image in question.
     *
     * @param  string  $image  The fully qualified path to the image
     * @param  string  $output  The output path
     */
    public function process(string $image, int $width, string $output): void
    {
        $image = Image::decode($image);
        $image->scaleDown(width: $width);
        $image->save($output, quality: $this->OUTPUT_QUALITY);
    }

    /**
     * Optimize all images in all sizes.
     *
     * @param  bool  $force  If true, images that are already present at the
     *                       destination will be overwritten instead of skipped
     */
    public function build(bool $force = false): void
    {
        // Create directory if it doesn't exist
        if (! file_exists($this->OUTPUT_DIR)) {
            echo 'Creating output directory...'.PHP_EOL;
            mkdir($this->OUTPUT_DIR, 0777, true);
        }

        $images = $this->scan();

        echo 'Discovered '.count($images).' images'.PHP_EOL;

        foreach ($images as $image) {
            echo $image.PHP_EOL;

            $originalWidth = getimagesize($image)[0];

            foreach ($this->OUTPUT_SIZES as $size) {
                if ($size > $originalWidth) {
                    // Make sure that at least the smallest size is processed
                    if ($size != $this->OUTPUT_SIZES[0]) {
                        echo '    '.$size.' - Skipped - Larger than original'.PHP_EOL;

                        continue;
                    }
                }

                $output = $this->OUTPUT_DIR.'/'.$this->image($image, $size);

                if (file_exists($output) && ! $force) {
                    echo '    '.$size.' - Skipped - Already processed'.PHP_EOL;

                    continue;
                }

                try {
                    $this->process($image, $size, $output);
                    echo '    '.$size.' - OK'.PHP_EOL;
                } catch (Exception $e) {
                    echo '    '.$size.' - Error - '.$e->getMessage().PHP_EOL;
                }
            }
        }

        echo 'Done!'.PHP_EOL;
    }

    /**
     * Remove orphaned output files that no longer correspond to any source images.
     *
     * This method scans all source images, determines their expected output filenames,
     * and deletes any files in the output directory that don't match.
     */
    public function cleanup(): void
    {
        if (! file_exists($this->OUTPUT_DIR)) {
            echo 'Output directory does not exist. Nothing to clean.'.PHP_EOL;

            return;
        }

        // Get all source images and calculate expected output filenames
        $images = $this->scan();
        $expectedFiles = [];

        foreach ($images as $image) {
            $originalWidth = getimagesize($image)[0];

            foreach ($this->OUTPUT_SIZES as $size) {
                // Include files for all sizes, even those larger than original
                // (matching the logic in build() where at least the smallest size is processed)
                $filename = $this->image($image, $size);
                $expectedFiles[$filename] = true;
            }
        }

        echo 'Expected '.count($expectedFiles).' output files'.PHP_EOL;

        // Scan output directory and delete orphaned files
        $deletedCount = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $this->OUTPUT_DIR,
                \RecursiveDirectoryIterator::SKIP_DOTS
            )
        );

        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $filename = $file->getFilename();

            if (! isset($expectedFiles[$filename])) {
                echo '    Deleting: '.$filename.PHP_EOL;
                unlink($file->getPathname());
                $deletedCount++;
            }
        }

        echo 'Deleted '.$deletedCount.' orphaned files'.PHP_EOL;
        echo 'Cleanup complete!'.PHP_EOL;
    }
}
