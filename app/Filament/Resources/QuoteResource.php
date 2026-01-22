<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuoteResource\Pages;
use App\Filament\Resources\QuoteResource\RelationManagers;
use App\Models\Quote;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuoteResource extends Resource
{
    protected static ?string $model = Quote::class;
    protected static ?string $title = 'Cotización';
    protected static ?string $modelLabel = 'Cotización';
    protected static ?string $pluralModelLabel = 'Cotizaciones';
    protected static ?string $singularModelLabel = 'Cotización';
    protected static ?string $navigationGroup = 'Control de operaciones';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('request_number')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Select::make('employee_id')
                    ->relationship('employee', 'id')
                    ->default(null),
                Forms\Components\Select::make('sub_client_id')
                    ->relationship('subClient', 'name')
                    ->default(null),
                Forms\Components\Select::make('quote_category_id')
                    ->relationship('quoteCategory', 'name')
                    ->default(null),
                Forms\Components\TextInput::make('energy_sci_manager')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('ceco')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\DatePicker::make('quote_date'),
                Forms\Components\DatePicker::make('execution_date'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('request_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('employee.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subClient.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quoteCategory.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('energy_sci_manager')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ceco')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('quote_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('execution_date')
                    ->date()
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
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListQuotes::route('/'),
            'create' => Pages\CreateQuote::route('/create'),
            'edit' => Pages\EditQuote::route('/{record}/edit'),
        ];
    }
}
