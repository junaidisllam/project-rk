<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Auth::user()->orders()->with('items.book')->latest()->get();

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
        ]);
    }
}