<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Utils\BengaliPhonetic;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function suggestions(Request $request)
    {
        $request->validate([
            'q' => 'nullable|string|max:100',
        ]);

        $query = $request->input('q');

        // IF NO QUERY: Return Popular/Trending Suggestions
        if (! $query) {
            $popularBooks = Book::query()
                ->where('is_active', true)
                ->with('writer')
                ->latest()
                ->limit(4)
                ->get()
                ->map(fn ($book) => [
                    'id' => 'popular-book-'.$book->id,
                    'name' => $book->name,
                    'type' => 'জনপ্রিয় বই',
                    'url' => route('book.show', $book->slug),
                    'image' => $book->cover_photo ? '/storage/'.$book->cover_photo : null,
                ]);

            return response()->json(['data' => $popularBooks]);
        }

        // --- Advanced Hybrid Tokenization & Query Expansion ---
        $tokens = explode(' ', preg_replace('/\s+/', ' ', trim($query)));
        $tokenVariants = [];

        foreach ($tokens as $token) {
            $variants = [strtolower($token)];
            if (mb_check_encoding($token, 'ASCII')) {
                // Primary Transliteration
                $variants[] = strtolower(BengaliPhonetic::translate($token));

                // Handle inherent vowel 'o' variations
                if (str_contains(strtolower($token), 'o')) {
                    $variants[] = strtolower(BengaliPhonetic::translate(str_ireplace('o', 'A', $token)));
                }
            }
            $tokenVariants[] = array_unique($variants);
        }

        // --- Helper for Broad "OR" Search (Captures everything partial) ---
        $applyBroadSearch = function ($q) use ($tokenVariants) {
            $q->where(function ($sub) use ($tokenVariants) {
                foreach ($tokenVariants as $variants) {
                    foreach ($variants as $v) {
                        $sub->orWhere('name', 'LIKE', "%{$v}%");
                    }
                }
            });
        };

        // --- Helper for Scoring & Ranking ---
        $scoreItem = function ($item) use ($tokenVariants, $query) {
            $score = 0;
            $itemNameLower = strtolower($item->name);

            // 1. Exact Phrase Bonus
            if (str_contains($itemNameLower, strtolower($query))) {
                $score += 50;
            }

            // 2. Token Matching (Weighted)
            foreach ($tokenVariants as $variants) {
                $tokenMatched = false;
                foreach ($variants as $v) {
                    if (str_contains($itemNameLower, $v)) {
                        $score += 20;
                        $tokenMatched = true;
                        break;
                    }
                }

                // 3. Fuzzy Matching (If no exact match found for this token)
                if (! $tokenMatched && strlen($query) > 3) {
                    $words = explode(' ', $itemNameLower);
                    foreach ($words as $word) {
                        foreach ($variants as $v) {
                            if (strlen($v) > 3 && strlen($word) > 3) {
                                // PHP Levenshtein for 90% accuracy
                                $dist = levenshtein($v, $word);
                                $maxLen = max(strlen($v), strlen($word));
                                if ($dist <= $maxLen * 0.2) { // Roughly 80-90% match
                                    $score += 10;
                                    $tokenMatched = true;
                                    break 2;
                                }
                            }
                        }
                    }
                }
            }

            return $score;
        };

        // 1. Search for books
        $books = Book::query()
            ->with('writer')
            ->where('is_active', true)
            ->where($applyBroadSearch)
            ->limit(15) // Fetch more to allow ranking
            ->get()
            ->map(function ($book) use ($scoreItem) {
                return [
                    'id' => 'book-'.$book->id,
                    'name' => $book->name,
                    'relevancy' => $scoreItem($book),
                    'type' => 'বই',
                    'writer' => $book->writer?->name,
                    'price' => (float) $book->price,
                    'original_price' => (float) $book->original_price,
                    'url' => route('book.show', $book->slug),
                    'image' => $book->cover_photo ? '/storage/'.$book->cover_photo : null,
                ];
            })
            ->sortByDesc('relevancy')
            ->values()
            ->take(6);

        // 2. Search for authors
        $authors = Author::query()
            ->where($applyBroadSearch)
            ->limit(10)
            ->get()
            ->map(function ($author) use ($scoreItem) {
                return [
                    'id' => 'author-'.$author->id,
                    'name' => $author->name,
                    'relevancy' => $scoreItem($author),
                    'type' => 'লেখক',
                    'url' => route('allbooks', ['authors' => [$author->id]]),
                    'image' => null,
                ];
            })
            ->sortByDesc('relevancy')
            ->values()
            ->take(4);

        // 3. Search for Categories
        $categories = Category::query()
            ->where($applyBroadSearch)
            ->limit(6)
            ->get()
            ->map(function ($cat) use ($scoreItem) {
                return [
                    'id' => 'cat-'.$cat->id,
                    'name' => $cat->name,
                    'relevancy' => $scoreItem($cat),
                    'type' => 'ক্যাটাগরি',
                    'url' => route('allbooks', ['categories' => [$cat->id]]),
                    'image' => null,
                ];
            })
            ->sortByDesc('relevancy')
            ->values()
            ->take(3);

        $results = $books->concat($authors)->concat($categories);

        return response()->json([
            'data' => $results,
            'meta' => [
                'variants' => array_merge(...$tokenVariants)
            ]
        ]);
    }
}
