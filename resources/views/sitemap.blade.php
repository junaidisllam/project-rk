{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">

    {{-- Static Pages with optimized priorities --}}
    <url>
        <loc>{{ url('/') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <url>
        <loc>{{ url('/allbooks') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>

    <url>
        <loc>{{ url('/catalog') }}</loc>
        <lastmod>{{ now()->startOfWeek()->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>

    {{-- Dynamic Books with image support --}}
    @foreach ($books as $book)
        <url>
            <loc>{{ url('/book/' . $book->slug) }}</loc>
            <lastmod>{{ $book->updated_at->toAtomString() }}</lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.8</priority>
            @if(isset($book->cover_photo))
            <image:image>
                <image:loc>{{ asset('storage/' . $book->cover_photo) }}</image:loc>
                <image:title>{{ $book->name }}</image:title>
                @if(isset($book->description))
                <image:caption>{{ Str::limit(strip_tags($book->description), 100) }}</image:caption>
                @endif
            </image:image>
            @endif
        </url>
    @endforeach

    {{-- Dynamic Categories with clean URLs --}}
    @foreach ($categories as $category)
        <url>
            <loc>{{ url('/allbooks?categories[0]=' . $category->slug) }}</loc>
            {{-- Alternative if using query params: url('/allbooks?category=' . $category->slug) --}}
            <lastmod>{{ $category->updated_at->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.7</priority>
        </url>
    @endforeach

    {{-- Dynamic Authors with clean URLs --}}
    @foreach ($authors as $author)
        <url>
            <loc>{{ url('allbooks?authors[0]=' . $author->id) }}</loc>
            {{-- Alternative if using query params: url('/allbooks?author=' . $author->slug) --}}
            <lastmod>{{ $author->updated_at->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.7</priority>
        </url>
    @endforeach

    {{-- Additional SEO Pages (if applicable) --}}
    @if(isset($publishers))
        @foreach ($publishers as $publisher)
            <url>
                <loc>{{ url('/publisher/' . $publisher->slug) }}</loc>
                <lastmod>{{ $publisher->updated_at->toAtomString() }}</lastmod>
                <changefreq>monthly</changefreq>
                <priority>0.6</priority>
            </url>
        @endforeach
    @endif

    {{-- Blog/News Pages (if applicable) --}}
    @if(isset($posts))
        @foreach ($posts as $post)
            <url>
                <loc>{{ url('/blog/' . $post->slug) }}</loc>
                <lastmod>{{ $post->updated_at->toAtomString() }}</lastmod>
                <changefreq>monthly</changefreq>
                <priority>0.6</priority>
            </url>
        @endforeach
    @endif
</urlset>
