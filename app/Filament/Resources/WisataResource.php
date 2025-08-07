<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WisataResource\Pages;
use App\Filament\Resources\WisataResource\RelationManagers;
use App\Models\Wisata;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Dotswan\MapPicker\Fields\Map;

class WisataResource extends Resource
{
    protected static ?string $model = Wisata::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->required(),
                Forms\Components\Select::make('kategori_id')
                    ->relationship('kategori', 'nama')
                    ->required(),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->directory('wisata')   
                    ->required(),
                Forms\Components\Textarea::make('deskripsi')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('alamat')
                    ->required()
                    ->columnSpanFull(),
                Map::make('kordinat')
                    ->label('Lokasi Wisata')
                    ->columnSpanFull()
                    // Basic Configuration
                    ->defaultLocation(latitude: -5.4286521, longitude: 105.1148447) // Koordinat default untuk Indonesia
                    ->draggable(true)
                    ->clickable(true)
                    ->zoom(10)
                    ->minZoom(0)
                    ->maxZoom(28)
                    ->tilesUrl("https://tile.openstreetmap.de/{z}/{x}/{y}.png")
                    ->detectRetina(true)
                    // Marker Configuration
                    ->showMarker(true)
                    ->markerColor("#3b82f6")
                    // Controls
                    ->showFullscreenControl(true)
                    ->showZoomControl(true)
                    // Location Features
                    ->showMyLocationButton(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kategori.nama')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListWisatas::route('/'),
            'create' => Pages\CreateWisata::route('/create'),
            'view' => Pages\ViewWisata::route('/{record}'),
            'edit' => Pages\EditWisata::route('/{record}/edit'),
        ];
    }
}
