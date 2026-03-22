<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ url('/') }}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ route('archive') }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.5</priority>
    </url>
    @foreach ($posts as $post)
    <url>
        <loc>{{ route('blog.show', $post) }}</loc>
        <lastmod>{{ $post->updated_at->toW3cString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach
    @foreach ($pages as $page)
    <url>
        <loc>{{ route('page.show', $page) }}</loc>
        <lastmod>{{ $page->updated_at->toW3cString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    @endforeach
    @foreach ($categories as $category)
    <url>
        <loc>{{ route('category.show', $category) }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.5</priority>
    </url>
    @endforeach
    @foreach ($tags as $tag)
    <url>
        <loc>{{ route('tag.show', $tag) }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.4</priority>
    </url>
    @endforeach
</urlset>
