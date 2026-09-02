<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<rss version="2.0"
    xmlns:atom="http://www.w3.org/2005/Atom"
    xmlns:content="http://purl.org/rss/1.0/modules/content/"
>
    <channel>
        <title>{{ $feed->channelTitle() }}</title>
        <link>{{ url('/') }}</link>
        <description>{{ $feed->channelDescription() }}</description>
        <language>{{ app()->getLocale() }}</language>
        <lastBuildDate>{{ $feed->lastBuildDate($posts) }}</lastBuildDate>
        <atom:link href="{{ url('/feed') }}" rel="self" type="application/rss+xml"/>
        @foreach ($posts as $post)
        <item>
            <title>{{ $post->title }}</title>
            <link>{{ route('blog.show', $post) }}</link>
            <description>{{ $feed->entryDescription($post) }}</description>
            <content:encoded><![CDATA[{!! $feed->entryContent($post) !!}]]></content:encoded>
            <pubDate>{{ $post->published_at->format('r') }}</pubDate>
            <guid isPermaLink="true">{{ route('blog.show', $post) }}</guid>
            @if ($author = $feed->entryAuthor($post))
            <author>{{ $author }}</author>
            @endif
            @if ($enclosure = $feed->featuredImageEnclosure($post))
            <enclosure url="{{ $enclosure['url'] }}" length="{{ $enclosure['length'] }}" type="{{ $enclosure['type'] }}" />
            @endif
            @foreach ($post->tags as $tag)
            <category>{{ $tag->name }}</category>
            @endforeach
        </item>
        @endforeach
    </channel>
</rss>
