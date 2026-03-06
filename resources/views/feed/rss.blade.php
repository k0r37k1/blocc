<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ config('app.name') }}</title>
        <link>{{ url('/') }}</link>
        <description>{{ config('app.description', 'Thoughts served fresh') }}</description>
        <language>de</language>
        <lastBuildDate>{{ $posts->first()?->published_at?->format('r') }}</lastBuildDate>
        <atom:link href="{{ url('/feed') }}" rel="self" type="application/rss+xml"/>
        @foreach ($posts as $post)
        <item>
            <title>{{ $post->title }}</title>
            <link>{{ route('blog.show', $post) }}</link>
            <description><![CDATA[{!! str($post->body)->sanitizeHtml() !!}]]></description>
            <pubDate>{{ $post->published_at->format('r') }}</pubDate>
            <guid isPermaLink="true">{{ route('blog.show', $post) }}</guid>
            @foreach ($post->tags as $tag)
            <category>{{ $tag->name }}</category>
            @endforeach
        </item>
        @endforeach
    </channel>
</rss>
