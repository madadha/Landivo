<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label(__('landivo.customers.name'))->required(),
                TextInput::make('phone')->label(__('landivo.customers.phone'))->tel()->required(),
                TextInput::make('email')->label(__('landivo.customers.email'))->email(),
                TextInput::make('city')->label(__('landivo.customers.city')),
                TextInput::make('country')->label(__('landivo.customers.country')),
            ]);
    }
}
