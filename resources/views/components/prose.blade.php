@props(['class' => ''])

<div {{ $attributes->merge(['class' => 'prose prose-neutral dark:prose-invert prose-a:text-accent prose-a:no-underline hover:prose-a:underline prose-img:rounded-lg max-w-none ' . $class]) }}>
    {{ $slot }}
</div>
