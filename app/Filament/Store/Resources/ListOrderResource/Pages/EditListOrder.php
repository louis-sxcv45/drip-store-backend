<?php

namespace App\Filament\Store\Resources\ListOrderResource\Pages;

use App\Filament\Store\Resources\ListOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditListOrder extends EditRecord
{
    protected static string $resource = ListOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
