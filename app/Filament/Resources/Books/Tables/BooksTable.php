<?php

namespace App\Filament\Resources\Books\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BooksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_photo')
                    ->label('Cover')
                    ->disk('public')
                    ->square()
                    ->size(50),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->writer?->name),
                TextColumn::make('writer.name')
                    ->label('Writer')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->toggleable(),
                TextColumn::make('price')
                    ->label('Sale Price')
                    ->money()
                    ->sortable()
                    ->color('success'),
                TextColumn::make('original_price')
                    ->label('Original Price')
                    ->money()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->numeric()
                    ->sortable()
                    // ->badgeStyles(fn ($state) => [
                    //     'background' => $state > 10 ? 'rgb(34 197 94)' : ($state > 0 ? 'rgb(251 146 60)' : 'rgb(239 68 68)'),
                    // ])
                    ->badge(),
                TextColumn::make('published_date')
                    ->label('Published')
                    ->date('M d, Y')
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('writer_id')
                    ->label('Writer')
                    ->relationship('writer', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('is_active')
                    ->query(fn (Builder $query) => $query->where('is_active', true))
                    ->label('Active Only')
                    ->toggle(),
                Filter::make('low_stock')
                    ->query(fn (Builder $query) => $query->where('stock_quantity', '<', 10))
                    ->label('Low Stock'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
