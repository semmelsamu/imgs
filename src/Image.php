<?php

declare(strict_types=1);

namespace Semmelsamu\Imgs;

use Closure;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;

class Image extends Component
{
    /**
     * The intrinsic width of the image.
     */
    public ?int $width;

    /**
     * The intrinsic height of the image.
     */
    public ?int $height;

    /**
     * The srcset string for the image.
     */
    public ?string $srcset;

    /**
     * Create a new component instance.
     *
     * @param  string  $src  The fully qualified path to the image
     * @param  string  $loading  The HTML image loading property. Read more here:
     *                           https://developer.mozilla.org/en-US/docs/Web/API/HTMLImageElement/loading
     * @return void
     *
     * @throws Exception
     */
    public function __construct(
        public string $src,
        public string $loading = 'lazy'
    ) {
        [$this->width, $this->height] = Cache::rememberForever('imagesize-'.$src, fn () => getimagesize($src));
        $this->srcset = self::getSrcset($src);
        $this->src = asset(substr($src, strlen(public_path())));
    }

    private function getSrcset(string $src): string
    {
        $srcsetStrings = collect(config('imgs.sizes'))
            ->filter(fn ($size) => $size <= $this->width || $size == config('imgs.sizes')[0])
            ->map(fn ($size) => image($src, $size).' '.$size.'w')
            ->toArray();

        // Convert the srcset array to a string
        return implode(', ', $srcsetStrings);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.image');
    }
}
