<?php

namespace App\Console\Commands;

use App\Models\Book;
use Illuminate\Console\Command;

class FixBookCoverPaths extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-book-cover-paths';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes incorrect book cover photo paths in the database by removing the /storage/ prefix.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to fix book cover paths...');

        $books = Book::all();
        $fixedCount = 0;

        foreach ($books as $book) {
            if ($book->cover_photo && str_starts_with($book->cover_photo, '/storage/')) {
                $originalPath = $book->cover_photo;
                $book->cover_photo = ltrim($book->cover_photo, '/storage/');
                $book->save();
                $fixedCount++;
                $this->comment("Fixed book ID: {$book->id} - Original: {$originalPath} -> New: {$book->cover_photo}");
            }
        }

        $this->info("Finished fixing {$fixedCount} book cover paths.");
    }
}
