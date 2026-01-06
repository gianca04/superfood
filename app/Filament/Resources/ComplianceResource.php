<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComplianceResource\Pages;
use App\Models\Compliance;
use App\Models\Project;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ComplianceResource extends Resource
{
    protected static ?string $model = Compliance::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationLabel = 'Compliance Acts';

    protected static ?string $pluralModelLabel = 'Compliance Acts';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General Information')
                    ->schema([
                        Forms\Components\Select::make('project_id')
                            ->label('Project')
                            ->relationship('project', 'name')
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateHydrated(function (Set $set, ?string $state) {
                                // Se ejecuta cuando se carga el formulario (edit)
                                if ($state) {
                                    $project = Project::with(['subClient.client'])->find($state);

                                    if ($project) {
                                        $subClient = $project->subClient;
                                        $client = $subClient?->client;

                                        $set('razon_social', $client?->business_name ?? '');
                                        $set('ruc', $client?->document_number ?? '');
                                        $set('tienda', $subClient?->name ?? '');
                                        $set('direccion', $subClient?->address ?? '');
                                        $set('start_date', $project->start_date?->format('Y-m-d'));
                                        $set('end_date', $project->end_date?->format('Y-m-d'));
                                    }
                                }
                            })
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if ($state) {
                                    $project = Project::with(['subClient.client'])->find($state);

                                    if ($project) {
                                        $subClient = $project->subClient;
                                        $client = $subClient?->client;

                                        // A1) Razón Social - desde Client
                                        $set('razon_social', $client?->business_name ?? '');

                                        // R.U.C. - desde Client (document_number)
                                        $set('ruc', $client?->document_number ?? '');

                                        // A2) Tienda - desde SubClient (name)
                                        $set('tienda', $subClient?->name ?? '');

                                        // Dirección - desde SubClient (address)
                                        $set('direccion', $subClient?->address ?? '');

                                        // Fechas desde Project
                                        $set('start_date', $project->start_date?->format('Y-m-d'));
                                        $set('end_date', $project->end_date?->format('Y-m-d'));
                                    }
                                } else {
                                    $set('razon_social', '');
                                    $set('ruc', '');
                                    $set('tienda', '');
                                    $set('direccion', '');
                                    $set('start_date', null);
                                    $set('end_date', null);
                                }
                            }),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('razon_social')
                                    ->label('A1) Razón Social')
                                    ->disabled()
                                    ->dehydrated(false),

                                Forms\Components\TextInput::make('ruc')
                                    ->label('R.U.C.')
                                    ->disabled()
                                    ->dehydrated(false),

                                Forms\Components\TextInput::make('tienda')
                                    ->label('A2) Tienda')
                                    ->disabled()
                                    ->dehydrated(false),

                                Forms\Components\TextInput::make('direccion')
                                    ->label('Dirección')
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('start_date')
                                    ->label('Fecha de Inicio')
                                    ->disabled()
                                    ->dehydrated(false),

                                Forms\Components\DatePicker::make('end_date')
                                    ->label('Fecha de Fin')
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),

                    ])->columns(1),

                Forms\Components\Section::make('SECCIÓN B: Disposición de los activos intervenidos')
                    ->description('B1) En esta sección, el contratista o proveedor del servicio deberá enlistar todos los activos intervenidos durante la actividad ejecutada.')
                    ->schema([
                        // 1. Tablero Autosoportado
                        Forms\Components\Fieldset::make('1. Tablero Autosoportado')
                            ->schema([
                                Forms\Components\Toggle::make('assets.tablero_autosoportado.selected')
                                    ->label('Seleccionar')
                                    ->live()
                                    ->inline(false),
                                Forms\Components\TextInput::make('assets.tablero_autosoportado.quantity')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->minValue(0)
                                    ->visible(fn(Get $get) => $get('assets.tablero_autosoportado.selected')),
                                Forms\Components\TextInput::make('assets.tablero_autosoportado.comments')
                                    ->label('Comentarios')
                                    ->visible(fn(Get $get) => $get('assets.tablero_autosoportado.selected')),
                            ])->columns(3),

                        // 2. Tablero Adosados
                        Forms\Components\Fieldset::make('2. Tablero Adosados')
                            ->schema([
                                Forms\Components\Toggle::make('assets.tablero_adosados.selected')
                                    ->label('Seleccionar')
                                    ->live()
                                    ->inline(false),
                                Forms\Components\TextInput::make('assets.tablero_adosados.quantity')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->minValue(0)
                                    ->visible(fn(Get $get) => $get('assets.tablero_adosados.selected')),
                                Forms\Components\TextInput::make('assets.tablero_adosados.comments')
                                    ->label('Comentarios')
                                    ->visible(fn(Get $get) => $get('assets.tablero_adosados.selected')),
                            ])->columns(3),

                        // 3. Banco de Condensadores
                        Forms\Components\Fieldset::make('3. Banco de Condensadores')
                            ->schema([
                                Forms\Components\Toggle::make('assets.banco_condensadores.selected')
                                    ->label('Seleccionar')
                                    ->live()
                                    ->inline(false),
                                Forms\Components\TextInput::make('assets.banco_condensadores.quantity')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->minValue(0)
                                    ->visible(fn(Get $get) => $get('assets.banco_condensadores.selected')),
                                Forms\Components\TextInput::make('assets.banco_condensadores.comments')
                                    ->label('Comentarios')
                                    ->visible(fn(Get $get) => $get('assets.banco_condensadores.selected')),
                            ])->columns(3),

                        // 4. Pozos a Tierra Baja Tensión
                        Forms\Components\Fieldset::make('4. Pozos a Tierra Baja Tensión')
                            ->schema([
                                Forms\Components\Toggle::make('assets.pozos_baja_tension.selected')
                                    ->label('Seleccionar')
                                    ->live()
                                    ->inline(false),
                                Forms\Components\TextInput::make('assets.pozos_baja_tension.quantity')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->minValue(0)
                                    ->visible(fn(Get $get) => $get('assets.pozos_baja_tension.selected')),
                                Forms\Components\TextInput::make('assets.pozos_baja_tension.comments')
                                    ->label('Comentarios')
                                    ->visible(fn(Get $get) => $get('assets.pozos_baja_tension.selected')),
                            ])->columns(3),

                        // 5. Pozos a Tierra Media Tensión
                        Forms\Components\Fieldset::make('5. Pozos a Tierra Media Tensión')
                            ->schema([
                                Forms\Components\Toggle::make('assets.pozos_media_tension.selected')
                                    ->label('Seleccionar')
                                    ->live()
                                    ->inline(false),
                                Forms\Components\TextInput::make('assets.pozos_media_tension.quantity')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->minValue(0)
                                    ->visible(fn(Get $get) => $get('assets.pozos_media_tension.selected')),
                                Forms\Components\TextInput::make('assets.pozos_media_tension.comments')
                                    ->label('Comentarios')
                                    ->visible(fn(Get $get) => $get('assets.pozos_media_tension.selected')),
                            ])->columns(3),
                    ]),

                Forms\Components\Section::make('SECCIÓN B2: Observaciones')
                    ->schema([
                        Forms\Components\RichEditor::make('maintenance_observations')
                            ->label('Observaciones Generales de Mantenimiento')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'bulletList',
                                'orderedList',
                                'h2',
                                'h3',
                                'blockquote',
                                'redo',
                                'undo',
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),

                TextColumn::make('project.name')
                    ->label('Project')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('project.start_date')
                    ->label('Fecha Inicio')
                    ->date('d/m/Y'),

                TextColumn::make('project.end_date')
                    ->label('Fecha Fin')
                    ->date('d/m/Y'),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('downloadExcel')
        ->label('Excel')
        ->icon('heroicon-o-document-arrow-down')
        ->color('success')
        ->url(fn (Compliance $record) => route('actas.excel', $record->id))
        ->openUrlInNewTab(),
    Tables\Actions\Action::make('downloadPdf')
        ->label('PDF')
        ->icon('heroicon-o-document-text')
        ->color('danger')
        ->url(fn (Compliance $record) => '#') // TODO: implementar
        ->openUrlInNewTab(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompliances::route('/'),
            'create' => Pages\CreateCompliance::route('/create'),
            'edit' => Pages\EditCompliance::route('/{record}/edit'),
        ];
    }
}
