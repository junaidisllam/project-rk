<?php

namespace App\Filament\Resources\Authors\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AuthorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Textarea::make('biography')
                    ->columnSpanFull(),
                FileUpload::make('photo')
                    ->image()
                    ->disk('public')
                    ->directory('authors/photos')
                    ->visibility('public'),
            ]);
    }
}
