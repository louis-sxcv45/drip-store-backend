<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Store;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\StoreResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\StoreResource\RelationManagers;

class StoreResource extends Resource
{
    protected static ?string $model = Store::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name_store')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('owner_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('address'),
                TextColumn::make('phone'),
                TextColumn::make('no_rekening')
                    ->label('No Rekening'),
                TextColumn::make('email'),
                ImageColumn::make('logo')
                ->circular()
                ->extraImgAttributes(fn() => [
                    'style' => 'width: 50px; height: 50px;',
                ]),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStores::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
