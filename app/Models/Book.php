<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'writer_id',
        'description',
        'price',
        'original_price',
        'cover_photo',
        'category_id',
        'summary',
        'specification',
        'book_preview',
        'published_date',
        'stock_quantity',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'specification' => 'array',
            'book_preview' => 'array',
            'published_date' => 'date',
            'is_active' => 'boolean',
            'price' => 'decimal:2',
            'original_price' => 'decimal:2',
        ];
    }

    // Accessor for cover_photo




    public function writer()
    {
        return $this->belongsTo(Author::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function sections()
    {
        return $this->belongsToMany(Section::class);
    }
}
