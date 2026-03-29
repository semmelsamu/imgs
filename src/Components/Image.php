<?php

declare(strict_types=1);

namespace Semmelsamu\Imgs\Components;

use Closure;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;
use Semmelsamu\Imgs\Imgs;

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
     * @param  string  $src  Fully qualified path to the source image
     * @param  string  $loading  HTML loading attribute (lazy|eager|auto)
     *
     * @throws Exception
     */
    public function __construct(
        public string $src,
        public string $loading = 'lazy',
    ) {
        [$this->width, $this->height] = Cache::rememberForever(
            'imgs-imagesize-'.$src,
            fn () => getimagesize($src)
        );

        $this->srcset = $this->buildSrcset($src);

        // Convert the absolute path to a public URL
        $this->src = asset(substr($src, strlen(public_path())));
    }

    /**
     * Build the srcset string for this image.
     */
    private function buildSrcset(string $src): string
    {
        /** @var Imgs $imgs */
        $imgs = app(Imgs::class);

        $entries = collect(config('imgs.sizes'))
            ->filter(fn (int $size) => $size <= $this->width || $size === config('imgs.sizes')[0])
            ->map(function (int $size) use ($src, $imgs) {
                $filename = $imgs->image($src, $size);
                $url = asset(
                    substr(config('imgs.output_dir'), strlen(public_path()))
                    .'/'.$filename
                );

                return $url.' '.$size.'w';
            });

        return $entries->implode(', ');
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        // Use the namespaced view so the package view is found even if
        // the user has not published the views.
        return view('imgs::components.image');
    }
}
