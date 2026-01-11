<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('Customer')
                    ->placeholder(fn ($record) => $record->name),
                TextEntry::make('invoice_no'),
                TextEntry::make('phone'),
                TextEntry::make('division_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('division_name'),
                TextEntry::make('district_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('district_name'),
                TextEntry::make('upazila_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('upazila_name'),
                TextEntry::make('union_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('union_name'),
                TextEntry::make('address_details')
                    ->columnSpanFull(),
                TextEntry::make('subtotal')
                    ->numeric(),
                TextEntry::make('shipping_charge')
                    ->numeric(),
                TextEntry::make('total_price')
                    ->money(),
                TextEntry::make('payment_method'),
                TextEntry::make('payment_status'),
                TextEntry::make('status'),
                TextEntry::make('note')
                    ->placeholder('-')
                    ->columnSpanFull(),
                RepeatableEntry::make('items')
                    ->schema([
                        TextEntry::make('book.name')
                            ->label('Book'),
                        TextEntry::make('quantity'),
                        TextEntry::make('price')
                            ->money(),
                        TextEntry::make('subtotal')
                            ->money(),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
