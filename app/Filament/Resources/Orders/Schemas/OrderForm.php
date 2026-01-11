<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),
                TextInput::make('invoice_no')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                TextInput::make('alt_phone')
                    ->tel()
                    ->default(null),
                TextInput::make('division_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('division_name')
                    ->required(),
                TextInput::make('district_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('district_name')
                    ->required(),
                TextInput::make('upazila_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('upazila_name')
                    ->required(),
                TextInput::make('union_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('union_name')
                    ->required(),
                Textarea::make('address_details')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric(),
                TextInput::make('shipping_charge')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total_price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Select::make('payment_method')
                    ->options([
                        'cod' => 'Cash on Delivery',
                        'online' => 'Online Payment',
                    ])
                    ->required()
                    ->default('cod'),
                Select::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                    ])
                    ->required()
                    ->default('pending'),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required()
                    ->default('pending'),
                Textarea::make('note')
                    ->default(null)
                    ->columnSpanFull(),
                Repeater::make('items')
                    ->relationship()
                    ->schema([
                        Select::make('book_id')
                            ->relationship('book', 'name')
                            ->required()
                            ->searchable()
                            ->columnSpan(2),
                        TextInput::make('quantity')
                            ->numeric()
                            ->default(1)
                            ->required(),
                        TextInput::make('price')
                            ->numeric()
                            ->required()
                            ->prefix('$'),
                        TextInput::make('subtotal')
                            ->numeric()
                            ->required()
                            ->prefix('$'),
                    ])
                    ->columns(5)
                    ->columnSpanFull(),
            ]);
    }
}
