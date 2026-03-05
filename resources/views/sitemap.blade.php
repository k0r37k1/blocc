<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ url('/') }}</loc>
    </url>
    <url>
        <loc>{{ route('archive') }}</loc>
    </url>
    @foreach ($posts as $post)
    <url>
        <loc>{{ route('blog.show', $post) }}</loc>
        <lastmod>{{ $post->updated_at->toW3cString() }}</lastmod>
    </url>
    @endforeach
    @foreach ($pages as $page)
    <url>
        <loc>{{ route('page.show', $page) }}</loc>
        <lastmod>{{ $page->updated_at->toW3cString() }}</lastmod>
    </url>
    @endforeach
    @foreach ($categories as $category)
    <url>
        <loc>{{ route('category.show', $category) }}</loc>
    </url>
    @endforeach
    @foreach ($tags as $tag)
    <url>
        <loc>{{ route('tag.show', $tag) }}</loc>
    </url>
    @endforeach
</urlset>
