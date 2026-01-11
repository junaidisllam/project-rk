<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

use Inertia\Inertia;

class WishlistController extends Controller
{
    public function index()
    {
        $books = auth()->user()->wishlists()
            ->with(['book.writer', 'book.category'])
            ->latest()
            ->get()
            ->pluck('book'); // We only want the book details

        return Inertia::render('Wishlist/Index', [
            'books' => $books
        ]);
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
        ]);

        $user = auth()->user();
        $book_id = $request->book_id;

        $wishlist = Wishlist::where('user_id', $user->id)
            ->where('book_id', $book_id)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            $message = 'Removed from wishlist';
        } else {
            Wishlist::create([
                'user_id' => $user->id,
                'book_id' => $book_id,
            ]);
            $message = 'Added to wishlist';
        }

        return Redirect::back()->with('success', $message);
    }
}
