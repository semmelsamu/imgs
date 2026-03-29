<img 
    src="{{ $src }}" 
    
    width="{{ $width }}" 
    height="{{ $height }}"
    
    srcset="{{ $srcset }}"
    
    onload="this.dataset.loaded = true"
    
    loading="{{ $loading }}"
    
    {{ $attributes->merge(["class" => "bg-bg-light animate-pulse data-loaded:bg-transparent data-loaded:animate-none"]) }}
/>
