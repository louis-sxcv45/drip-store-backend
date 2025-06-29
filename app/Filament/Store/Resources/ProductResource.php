<?php

namespace App\Filament\Store\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Store\Resources\ProductResource\Pages;
use App\Filament\Store\Resources\ProductResource\RelationManagers;
use App\Filament\Store\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Store\Resources\ProductResource\Pages\ListProducts;
use App\Filament\Store\Resources\ProductResource\Pages\CreateProduct;
use Filament\Support\Enums\Alignment;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                TextInput::make('name_product')
                    ->required()
                    ->label('Product Name')
                    ->placeholder('Input Product Name'),
                TextInput::make('price')
                    ->numeric()
                    ->required()
                    ->step(0.01)
                    ->prefix('Rp.')
                    ->placeholder('0'),
                TextInput::make('category')
                    ->required()
                    ->placeholder('Input Category e.g. Pakaian, Sepatu'),
                TextInput::make('quantity')
                    ->integer()
                    ->required()
                    ->placeholder('Input Quantity'),
                Textarea::make('description')
                    ->required(),
                FileUpload::make('image')
                    ->required()
                    ->image(),
                Repeater::make('productSizes')
                    ->relationship()
                    ->addActionAlignment(Alignment::Start)
                    ->schema([
                        TextInput::make('size')
                            ->label('Product Size')
                            ->required(),
                    ])
                    ->label('Fill Product Sizes')
                    ->addActionLabel('Add Product Size')
                    ->grid(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('name_product')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->sortable(),
                TextColumn::make('category')
                    ->searchable(),
                TextColumn::make('quantity'),
                TextColumn::make('description'),
                TextColumn::make('productSizes')
                    ->label('Sizes')
                    ->formatStateUsing(fn($record) => $record->productSizes->pluck('size')->join(', ')),
                ImageColumn::make('image')
                    ->extraImgAttributes(fn() => [
                        'class' => 'rounded-md',
                        'style' => 'width: 100px; height: 100px;',
                    ]),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('store_id', auth('store')->id());
    }
}
