<?php

namespace App\Filament\Store\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\ListOrder;
use Filament\Tables\Table;
use App\Models\Transaction;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Store\Resources\ListOrderResource\Pages;
use App\Filament\Store\Resources\ListOrderResource\RelationManagers;

class ListOrderResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $label = 'List Orders';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('user.name')
                    ->label('Purchaser'),
                TextColumn::make('user.phone')
                    ->label('Phone Number'),
                TextColumn::make('status')
                    ->label('Order Status'),
                TextColumn::make('user.address')
                    ->label('Address')
                    ->wrap()
                    ->limit(50),
                TextColumn::make('barang_dipesan')
                    ->label('Ordered Items')
                    ->getStateUsing(
                        fn($record) =>
                        $record->transactionItems->map(
                            fn($item) =>
                            $item->product->name . ' x' . $item->quantity
                        )->join(', ')
                    )
                    ->wrap(),

                TextColumn::make('harga_per_barang')
                    ->label('Price per Item')
                    ->getStateUsing(
                        fn($record) =>
                        $record->transactionItems->map(
                            fn($item) =>
                            'Rp ' . number_format($item->price, 0, ',', '.')
                        )->join(', ')
                    )
                    ->wrap(),

                ImageColumn::make('gambar_produk')
                    ->label('Product Image')
                    ->getStateUsing(
                        fn($record) =>
                        $record->transactionItems->first()?->product->image ?? null
                    )
                    ->circular(),
                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => match ($state) {
                        0 => 'Belum Dibayar',
                        1 => 'Menunggu Pembayaran',
                        2 => 'Dibayar / Sukses',
                        3 => 'Dibatalkan',
                        4 => 'Kadaluwarsa',
                        default => 'Tidak Diketahui',
                    })

            ])
            ->filters([
                //
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
            'index' => Pages\ListListOrders::route('/'),
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('store_id', auth('store')->id());
    }
}
