<div class="rich-video-embed not-prose my-6 overflow-hidden rounded-lg border border-neutral-200 dark:border-neutral-700">
    <div class="relative aspect-video w-full">
        <iframe
            src="{{ $embedUrl }}"
            title="{{ $title }}"
            loading="lazy"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            allowfullscreen
            referrerpolicy="strict-origin-when-cross-origin"
            class="absolute inset-0 h-full w-full border-0"
        ></iframe>
    </div>
</div>
