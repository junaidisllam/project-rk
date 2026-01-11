<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Utils\BengaliPhonetic;
use Illuminate\Support\Str;
use Inertia\Inertia;

class BookController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $categories = cache()->remember('categories', now()->addDay(), function () {
            return Category::query()
                ->select('id', 'name', 'slug')
                ->get();
        });

        $authors = cache()->remember('authors', now()->addDay(), function () {
            return Author::query()
                ->select('id', 'name')
                ->get();
        });

        $query = Book::query()
            ->select('id', 'name', 'slug', 'writer_id', 'category_id', 'cover_photo', 'price', 'original_price')
            ->with(['writer:id,name', 'category:id,name,slug'])
            ->where('is_active', true);

        // Advanced AI Search Logic
        if ($request->filled('search')) {
            $searchQuery = $request->search;
            $tokens = explode(' ', preg_replace('/\s+/', ' ', trim($searchQuery)));
            $allVariants = [];

            foreach ($tokens as $token) {
                $allVariants[] = strtolower($token);
                if (mb_check_encoding($token, 'ASCII')) {
                    $allVariants[] = strtolower(BengaliPhonetic::translate($token));
                    // Handle inherent vowel 'o' variations
                    if (str_contains(strtolower($token), 'o')) {
                        $allVariants[] = strtolower(BengaliPhonetic::translate(str_ireplace('o', 'A', $token)));
                    }
                }
            }
            $allVariants = array_unique($allVariants);

            $query->where(function ($q) use ($allVariants) {
                foreach ($allVariants as $variant) {
                    $q->orWhere('name', 'like', '%' . $variant . '%')
                      ->orWhereHas('writer', function ($sub) use ($variant) {
                          $sub->where('name', 'like', '%' . $variant . '%');
                      });
                }
            });

            // Relevancy Bubbling: Bring exact phrase matches and multiple variant hits to the top
            $query->orderByRaw('CASE WHEN name LIKE ? THEN 0 ELSE 1 END', ['%' . $searchQuery . '%']);
            foreach (array_slice($allVariants, 0, 5) as $v) { // Top 5 variants for performance
                $query->orderByRaw('CASE WHEN name LIKE ? THEN 0 ELSE 1 END', ['%' . $v . '%']);
            }
        }

        if ($request->filled('categories')) {
            $categorySlugs = is_array($request->categories) ? $request->categories : explode(',', $request->categories);
            $query->whereHas('category', function ($q) use ($categorySlugs) {
                $q->whereIn('slug', $categorySlugs);
            });
        }

        if ($request->filled('authors')) {
            $authorIds = is_array($request->authors) ? $request->authors : explode(',', $request->authors);
            $query->whereIn('writer_id', $authorIds);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Sorting
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'popular':
                // For now, order by ID desc or some other metric
                $query->orderBy('id', 'desc');
                break;
            default:
                $query->latest();
                break;
        }

        // For super-fast direct API calls
        if ($request->wantsJson() && $request->has('api')) {
            return response()->json([
                'books' => $query->paginate(24)->withQueryString(),
                'filters' => $request->only(['search', 'categories', 'authors', 'min_price', 'max_price', 'sort']),
            ]);
        }

        // Logic for backend title
        $title = 'সকল বই';
        if ($request->filled('categories')) {
            $slugs = is_array($request->categories) ? $request->categories : explode(',', $request->categories);
            if (count($slugs) === 1) {
                $category = $categories->where('slug', $slugs[0])->first();
                if ($category) $title = "{$category->name} - সকল বই";
            }
        } elseif ($request->filled('authors')) {
            $ids = is_array($request->authors) ? $request->authors : explode(',', $request->authors);
            if (count($ids) === 1) {
                $author = $authors->where('id', $ids[0])->first();
                if ($author) $title = "{$author->name} - এর বইসমূহ";
            }
        } elseif ($request->filled('search')) {
            $title = "\"{$request->search}\" - সার্চ রেজাল্ট";
        }

        // Logic for backend description
        $description = 'এলাকার বাজার এ আমাদের সকল বই এর বিশাল সংগ্রহ দেখুন। আপনার পছন্দের বই বা লেখক খুঁজে নিন সহজে।';
        if ($request->filled('categories')) {
            $slugs = is_array($request->categories) ? $request->categories : explode(',', $request->categories);
            if (count($slugs) === 1) {
                $category = $categories->where('slug', $slugs[0])->first();
                if ($category) $description = "{$category->name} বিষয়ের সকল বই এর সংগ্রহ আমাদের এলাকার বাজার এ দেখুন।";
            }
        } elseif ($request->filled('authors')) {
            $ids = is_array($request->authors) ? $request->authors : explode(',', $request->authors);
            if (count($ids) === 1) {
                $author = $authors->where('id', $ids[0])->first();
                if ($author) $description = "{$author->name} এর লেখা সকল বই এর সংগ্রহ এলাকার বাজার এ।";
            }
        } elseif ($request->filled('search')) {
            $description = "\"{$request->search}\" সম্পর্কিত সকল বই এর সংগ্রহ দেখুন।";
        }

        return Inertia::render('Books', [
            'books' => Inertia::defer(fn () => $query->paginate(24)->withQueryString()),
            'categories' => $categories,
            'authors' => $authors,
            'filters' => $request->only(['search', 'categories', 'authors', 'min_price', 'max_price', 'sort']),
            'title' => "{$title} | এলাকার বাজার",
            'description' => $description,
        ]);
    }

    public function show(\Illuminate\Http\Request $request, Book $book)
    {
        if (auth()->check()) {
            $book->is_wishlisted = auth()->user()->wishlists()->where('book_id', $book->id)->exists();
        } else {
            $book->is_wishlisted = false;
        }

        $book->load('writer:id,name,photo,biography', 'category');

        if ($request->wantsJson()) {
            $relatedBooks = Book::query()
                ->where('category_id', $book->category_id)
                ->where('id', '!=', $book->id)
                ->where('is_active', true)
                ->with(['writer:id,name', 'category:id,name,slug'])
                ->select('id', 'name', 'slug', 'writer_id', 'category_id', 'cover_photo', 'price', 'original_price')
                ->limit(6)
                ->get();

            return response()->json([
                'book' => $book,
                'related_books' => $relatedBooks,
            ]);
        }

        $relatedBooksQuery = Book::query()
            ->where('category_id', $book->category_id)
            ->where('id', '!=', $book->id)
            ->where('is_active', true)
            ->with(['writer:id,name', 'category:id,name,slug'])
            ->select('id', 'name', 'slug', 'writer_id', 'category_id', 'cover_photo', 'price', 'original_price')
            ->limit(6);

        return Inertia::render('Book', [
            'book' => Inertia::defer(fn () => $book),
            'relatedBooks' => Inertia::defer(fn () => $relatedBooksQuery->get()),
            'title' => "{$book->name} - {$book->writer->name} | এলাকার বাজার",
            'description' => "{$book->name} - {$book->writer->name} এর লেখা এই চমৎকার বইটি এলাকার বাজার এ কিনুন। " . Str::limit(strip_tags($book->description), 150),
        ]);
    }
}
