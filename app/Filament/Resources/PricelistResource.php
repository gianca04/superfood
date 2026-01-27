<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PricelistResource\Pages;
use App\Filament\Resources\PricelistResource\RelationManagers;
use App\Models\Pricelist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PricelistResource extends Resource
{
    protected static ?string $model = Pricelist::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = 'Preciario';
    protected static ?string $pluralModelLabel = 'Preciario';
    protected static ?string $modelLabel = 'Preciario';
    protected static ?string $navigationGroup = 'Control de operaciones';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user->roles()->whereIn('id', [1, 2])->exists();
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('sat_line')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('sat_description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('unit_id')
                    ->relationship('unit', 'name')
                    ->required(),
                Forms\Components\Select::make('price_type_id')
                    ->relationship('priceType', 'name')
                    ->default(null),
                Forms\Components\TextInput::make('unit_price')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sat_line')
                    ->searchable()
                    ->sortable()
                    ->badge('badge badge-primary')
                    ->label('Línea SAP'),
                Tables\Columns\TextColumn::make('sat_description')
                    ->searchable()
                    ->sortable()
                    ->label('Descripción SAP')  // Label más corto
                    ->limit(50),
                Tables\Columns\TextColumn::make('priceType.name')
                    ->numeric()
                    ->grow(false)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Tipo')
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit.name')
                    ->numeric()
                    ->label('Unidad')
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_price')
                    ->numeric()
                    ->label('Precio U.')
                    ->prefix('S/ ')
                    ->alignEnd()
                    ->sortable(),
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
            'index' => Pages\ListPricelists::route('/'),
        ];
    }
}
