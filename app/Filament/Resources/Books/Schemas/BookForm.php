<?php

namespace App\Filament\Resources\Books\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set; // Correct import for Filament v4
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class BookForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true) // Updates slug when leaving the field
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', $state ? Str::slug($state) : null)),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->helperText('URL-friendly version of the book name. Auto-generated but editable.'),
                Select::make('writer_id')
                    ->relationship('writer', 'name')
                    ->required()
                    ->searchable()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required(),
                        Textarea::make('biography'),
                        FileUpload::make('photo')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('authors/photos'),
                    ]),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required()
                    ->searchable()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required()
                            ->lazy()
                            ->afterStateUpdated(fn ($set, ?string $state) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Textarea::make('description'),
                        FileUpload::make('image')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('categories/images'),
                        Toggle::make('is_active')->default(true),
                    ]),
                Textarea::make('description')
                    ->columnSpanFull(),
                Textarea::make('summary')
                    ->columnSpanFull(),
                KeyValue::make('specification')
                    ->keyLabel('বিষয়ের নাম')
                    ->valueLabel('বিস্তারিত')
                    ->addActionLabel('নতুন স্পেসিফিকেশন যুক্ত করুন')
                    ->default([
                        'আইএসবিএন' => '',
                        'সংস্করণ' => '',
                        'ভাষা' => 'বাংলা',
                        'দেশ' => 'বাংলাদেশ',
                        'পৃষ্ঠা সংখ্যা' => '',
                        'বাঁধাই' => 'হার্ডকভার',
                    ])
                    ->columnSpanFull(),
                FileUpload::make('cover_photo')
                    ->image()
                    ->disk('public')
                    ->visibility('public')
                    ->directory('books/covers')
                    ->getUploadedFileNameForStorageUsing(
                        fn (TemporaryUploadedFile $file): string =>
                            'books/covers/' . $file->hashName(),
                    ),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->minValue(0),
                TextInput::make('original_price')
                    ->numeric()
                    ->prefix('$')
                    ->minValue(0),
                TextInput::make('stock_quantity')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
                DatePicker::make('published_date'),
                Toggle::make('is_active')
                    ->required()
                    ->default(true),
            ]);
    }
}
