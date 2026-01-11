<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $books = Book::where('is_active', true)
            ->select('slug', 'updated_at', 'name', 'cover_photo', 'description')
            ->get();

        $categories = Category::select('slug', 'updated_at')
            ->get();

        $authors = Author::select('id', 'updated_at')
            ->get();

        return response()->view('sitemap', [
            'books' => $books,
            'categories' => $categories,
            'authors' => $authors,
        ])->header('Content-Type', 'text/xml');
    }
}
