<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Category;
use Inertia\Inertia;

class CatalogController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('books')
            ->orderBy('name')
            ->get();

        $authors = Author::withCount('books')
            ->orderBy('name')
            ->get();

        return Inertia::render('Catalog', [
            'categories' => $categories,
            'authors' => $authors,
            'title' => 'বইয়ের ক্যাটালগ | এলাকাডটকম',
            'description' => 'এলাকাডটকম-এর বইয়ের বিশাল ক্যাটালগ দেখুন। আপনার পছন্দের লেখক বা বিষয়ের বই খুঁজে নিন সহজেই।',
        ]);
    }
}
