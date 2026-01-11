<?php

namespace App\Filament\Resources\Sliders\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SliderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            FileUpload::make('image_path')
                ->label('Image')
                ->disk('public')
                ->directory('sliders')
                ->image()
                ->required(),
            FileUpload::make('mobile_image_path')
                ->label('Mobile Image')
                ->disk('public')
                ->directory('sliders')
                ->image(),
            TextInput::make('title')
                ->maxLength(255),
            TextInput::make('link')
                ->url()
                ->maxLength(255),
            Toggle::make('is_active')
                ->default(true)
                ->required(),
            TextInput::make('order_column')
                ->numeric()
                ->default(0)
                ->required(),
        ]);
    }
}