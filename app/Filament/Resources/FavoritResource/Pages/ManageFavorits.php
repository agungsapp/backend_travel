<?php

namespace App\Filament\Resources\FavoritResource\Pages;

use App\Filament\Resources\FavoritResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageFavorits extends ManageRecords
{
    protected static string $resource = FavoritResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
