<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComplianceResource\Pages;
use App\Filament\Resources\ComplianceResource\RelationManagers\WorkReportsRelationManager;
use App\Models\Compliance;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;

class ComplianceResource extends Resource
{
    protected static ?string $model = Compliance::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = 'Actas de Conformidad';
    protected static ?string $pluralModelLabel = 'Actas de Conformidad';
    protected static ?string $modelLabel = 'Acta de Conformidad';
    protected static ?string $navigationGroup = 'Documentos';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // ═══════════════════════════════════════════════════════════════
                // SECCIÓN A: INFORMACIÓN GENERAL
                // ═══════════════════════════════════════════════════════════════
                Forms\Components\Section::make('Información General')
                    ->description('Seleccione el proyecto para cargar los datos automáticamente')
                    ->icon('heroicon-o-building-office-2')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Select::make('project_id')
                            ->label('Proyecto')
                            ->relationship('project', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->prefixIcon('heroicon-o-briefcase')
                            ->placeholder('Buscar proyecto...')
                            ->helperText('Al seleccionar un proyecto se cargarán los datos del cliente')
                            ->afterStateHydrated(function (Set $set, ?string $state) {
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

                                        $set('razon_social', $client?->business_name ?? '');
                                        $set('ruc', $client?->document_number ?? '');
                                        $set('tienda', $subClient?->name ?? '');
                                        $set('direccion', $subClient?->address ?? '');
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
                            })
                            ->columnSpanFull(),

                        // Datos del Cliente (Solo lectura)
                        Forms\Components\Fieldset::make('Datos del Cliente')
                            ->schema([
                                Forms\Components\TextInput::make('razon_social')
                                    ->label('Razón Social')
                                    ->prefixIcon('heroicon-o-building-storefront')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->placeholder('Se cargará automáticamente'),

                                Forms\Components\TextInput::make('ruc')
                                    ->label('R.U.C.')
                                    ->prefixIcon('heroicon-o-identification')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->placeholder('Se cargará automáticamente'),

                                Forms\Components\TextInput::make('tienda')
                                    ->label('Tienda / Sucursal')
                                    ->prefixIcon('heroicon-o-map-pin')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->placeholder('Se cargará automáticamente'),

                                Forms\Components\TextInput::make('direccion')
                                    ->label('Dirección')
                                    ->prefixIcon('heroicon-o-home')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->placeholder('Se cargará automáticamente'),
                            ])->columns(2),

                        // Fechas del Proyecto
                        Forms\Components\Fieldset::make('Periodo del Proyecto')
                            ->schema([
                                Forms\Components\DatePicker::make('start_date')
                                    ->label('Fecha de Inicio')
                                    ->prefixIcon('heroicon-o-calendar')
                                    ->displayFormat('d/m/Y')
                                    ->disabled()
                                    ->dehydrated(false),

                                Forms\Components\DatePicker::make('end_date')
                                    ->label('Fecha de Fin')
                                    ->prefixIcon('heroicon-o-calendar-days')
                                    ->displayFormat('d/m/Y')
                                    ->disabled()
                                    ->dehydrated(false),
                            ])->columns(2),
                    ]),

                // ═══════════════════════════════════════════════════════════════
                // SECCIÓN B1: ACTIVOS INTERVENIDOS
                // ═══════════════════════════════════════════════════════════════
                Forms\Components\Section::make('Sección B1: Disposición de Activos Intervenidos')
                    ->description('Seleccione y detalle todos los activos intervenidos durante la actividad ejecutada')
                    ->icon('heroicon-o-cube')
                    ->collapsible()
                    ->schema([
                        // Grid de activos - 2 columnas
                        Forms\Components\Grid::make(2)
                            ->schema([
                                // Columna 1: Tableros
                                Forms\Components\Section::make('Tableros Eléctricos')
                                    ->icon('heroicon-o-square-3-stack-3d')
                                    ->compact()
                                    ->schema([
                                        // 1. Tablero Autosoportado
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\Toggle::make('assets.tablero_autosoportado.selected')
                                                    ->label('Tablero Autosoportado')
                                                    ->live()
                                                    ->onColor('success')
                                                    ->offColor('gray')
                                                    ->inline(false),
                                                Forms\Components\TextInput::make('assets.tablero_autosoportado.quantity')
                                                    ->label('Cantidad')
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->suffix('unid.')
                                                    ->default(0)
                                                    ->visible(fn(Get $get) => $get('assets.tablero_autosoportado.selected')),
                                                Forms\Components\TextInput::make('assets.tablero_autosoportado.comments')
                                                    ->label('Comentarios')
                                                    ->placeholder('Observaciones...')
                                                    ->visible(fn(Get $get) => $get('assets.tablero_autosoportado.selected')),
                                            ]),

                                        Forms\Components\Placeholder::make('')
                                            ->content('')
                                            ->extraAttributes(['class' => 'border-b border-gray-200 dark:border-gray-700']),

                                        // 2. Tablero Adosados
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\Toggle::make('assets.tablero_adosados.selected')
                                                    ->label('Tablero Adosados')
                                                    ->live()
                                                    ->onColor('success')
                                                    ->offColor('gray')
                                                    ->inline(false),
                                                Forms\Components\TextInput::make('assets.tablero_adosados.quantity')
                                                    ->label('Cantidad')
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->suffix('unid.')
                                                    ->default(0)
                                                    ->visible(fn(Get $get) => $get('assets.tablero_adosados.selected')),
                                                Forms\Components\TextInput::make('assets.tablero_adosados.comments')
                                                    ->label('Comentarios')
                                                    ->placeholder('Observaciones...')
                                                    ->visible(fn(Get $get) => $get('assets.tablero_adosados.selected')),
                                            ]),
                                    ]),

                                // Columna 2: Otros equipos
                                Forms\Components\Section::make('Equipos y Pozos a Tierra')
                                    ->icon('heroicon-o-bolt')
                                    ->compact()
                                    ->schema([
                                        // 3. Banco de Condensadores
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\Toggle::make('assets.banco_condensadores.selected')
                                                    ->label('Banco de Condensadores')
                                                    ->live()
                                                    ->onColor('success')
                                                    ->offColor('gray')
                                                    ->inline(false),
                                                Forms\Components\TextInput::make('assets.banco_condensadores.quantity')
                                                    ->label('Cantidad')
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->suffix('unid.')
                                                    ->default(0)
                                                    ->visible(fn(Get $get) => $get('assets.banco_condensadores.selected')),
                                                Forms\Components\TextInput::make('assets.banco_condensadores.comments')
                                                    ->label('Comentarios')
                                                    ->placeholder('Observaciones...')
                                                    ->visible(fn(Get $get) => $get('assets.banco_condensadores.selected')),
                                            ]),

                                        Forms\Components\Placeholder::make('')
                                            ->content('')
                                            ->extraAttributes(['class' => 'border-b border-gray-200 dark:border-gray-700']),

                                        // 4. Pozos a Tierra Baja Tensión
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\Toggle::make('assets.pozos_baja_tension.selected')
                                                    ->label('Pozos Tierra (Baja Tensión)')
                                                    ->live()
                                                    ->onColor('warning')
                                                    ->offColor('gray')
                                                    ->inline(false),
                                                Forms\Components\TextInput::make('assets.pozos_baja_tension.quantity')
                                                    ->label('Cantidad')
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->suffix('unid.')
                                                    ->default(0)
                                                    ->visible(fn(Get $get) => $get('assets.pozos_baja_tension.selected')),
                                                Forms\Components\TextInput::make('assets.pozos_baja_tension.comments')
                                                    ->label('Comentarios')
                                                    ->placeholder('Observaciones...')
                                                    ->visible(fn(Get $get) => $get('assets.pozos_baja_tension.selected')),
                                            ]),

                                        Forms\Components\Placeholder::make('')
                                            ->content('')
                                            ->extraAttributes(['class' => 'border-b border-gray-200 dark:border-gray-700']),

                                        // 5. Pozos a Tierra Media Tensión
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\Toggle::make('assets.pozos_media_tension.selected')
                                                    ->label('Pozos Tierra (Media Tensión)')
                                                    ->live()
                                                    ->onColor('danger')
                                                    ->offColor('gray')
                                                    ->inline(false),
                                                Forms\Components\TextInput::make('assets.pozos_media_tension.quantity')
                                                    ->label('Cantidad')
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->suffix('unid.')
                                                    ->default(0)
                                                    ->visible(fn(Get $get) => $get('assets.pozos_media_tension.selected')),
                                                Forms\Components\TextInput::make('assets.pozos_media_tension.comments')
                                                    ->label('Comentarios')
                                                    ->placeholder('Observaciones...')
                                                    ->visible(fn(Get $get) => $get('assets.pozos_media_tension.selected')),
                                            ]),
                                    ]),
                            ]),
                    ]),

                // ═══════════════════════════════════════════════════════════════
                // SECCIÓN B2: OBSERVACIONES GENERALES
                // ═══════════════════════════════════════════════════════════════
                Forms\Components\Section::make('Sección B2: Observaciones de Mantenimiento')
                    ->description('Registre las observaciones generales del mantenimiento realizado')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Forms\Components\RichEditor::make('maintenance_observations')
                            ->label('')
                            ->placeholder('Escriba aquí las observaciones generales del mantenimiento...')
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

                // ═══════════════════════════════════════════════════════════════
                // SECCIÓN C: FIRMAS Y CONFORMIDAD
                // ═══════════════════════════════════════════════════════════════
                Forms\Components\Section::make('Sección C: Responsabilidad y Firmas')
                    ->description('Complete los datos del responsable y capture las firmas de conformidad')
                    ->icon('heroicon-o-pencil-square')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                // Columna 1: Cliente
                                Forms\Components\Section::make('Datos del Cliente')
                                    ->icon('heroicon-o-user')
                                    ->compact()
                                    ->schema([
                                        Forms\Components\TextInput::make('fullname_cliente')
                                            ->label('Nombre Completo')
                                            ->prefixIcon('heroicon-o-user-circle')
                                            ->required()
                                            ->placeholder('Ingrese nombre completo')
                                            ->maxLength(255),

                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Select::make('document_type')
                                                    ->label('Tipo de Documento')
                                                    ->options([
                                                        'DNI' => 'DNI',
                                                        'CARNET DE EXTRANJERIA' => 'Carnet de Extranjería',
                                                        'PASAPORTE' => 'Pasaporte',
                                                    ])
                                                    ->default('DNI')
                                                    ->required()
                                                    ->native(false)
                                                    ->live(),

                                                Forms\Components\TextInput::make('document_number')
                                                    ->label('Número de Documento')
                                                    ->required()
                                                    ->numeric()
                                                    ->minLength(fn(Get $get) => $get('document_type') === 'DNI' ? 8 : 9)
                                                    ->maxLength(fn(Get $get) => $get('document_type') === 'DNI' ? 8 : 12)
                                                    ->hint(fn(Get $get) => match ($get('document_type')) {
                                                        'DNI' => '8 dígitos',
                                                        'CARNET DE EXTRANJERIA' => '9-12 dígitos',
                                                        'PASAPORTE' => '9-12 caracteres',
                                                        default => ''
                                                    })
                                                    ->hintColor('primary'),
                                            ]),

                                        SignaturePad::make('client_signature')
                                            ->label('Firma del Cliente')
                                            ->dotSize(2.0)
                                            ->penColor('#000')
                                            ->penColorOnDark('#00f')
                                            ->lineMinWidth(0.2)
                                            ->lineMaxWidth(2.5)
                                            ->throttle(16)
                                            ->minDistance(5)
                                            ->velocityFilterWeight(0.7)
                                            ->confirmable(),
                                    ]),

                                // Columna 2: Empleado
                                Forms\Components\Section::make('Datos del Empleado')
                                    ->icon('heroicon-o-identification')
                                    ->compact()
                                    ->schema([
                                        Forms\Components\Placeholder::make('employee_info')
                                            ->label('')
                                            ->content(function () {
                                                $employee = Auth::user()?->employee;
                                                if (!$employee) {
                                                    return new \Illuminate\Support\HtmlString("
                                                        <div class='p-4 rounded-lg bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-800'>
                                                            <div class='flex items-center gap-2 text-warning-600 dark:text-warning-400'>
                                                                <svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                                                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'/>
                                                                </svg>
                                                                <span class='font-medium'>Empleado no identificado</span>
                                                            </div>
                                                        </div>
                                                    ");
                                                }

                                                return new \Illuminate\Support\HtmlString("
                                                    <div class='p-4 rounded-lg bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800'>
                                                        <div class='flex items-center gap-3'>
                                                            <div class='flex-shrink-0 w-12 h-12 rounded-full bg-primary-100 dark:bg-primary-800 flex items-center justify-center'>
                                                                <svg class='w-6 h-6 text-primary-600 dark:text-primary-400' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                                                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'/>
                                                                </svg>
                                                            </div>
                                                            <div>
                                                                <p class='font-semibold text-gray-900 dark:text-white'>{$employee->first_name} {$employee->last_name}</p>
                                                                <p class='text-sm text-gray-500 dark:text-gray-400'>
                                                                    <span class='inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'>
                                                                        {$employee->document_type}: {$employee->document_number}
                                                                    </span>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                ");
                                            }),

                                        SignaturePad::make('employee_signature')
                                            ->label('Firma del Supervisor / Técnico')
                                            ->dotSize(2.0)
                                            ->penColor('#000')
                                            ->penColorOnDark('#00f')
                                            ->lineMinWidth(0.2)
                                            ->lineMaxWidth(2.5)
                                            ->throttle(16)
                                            ->minDistance(5)
                                            ->velocityFilterWeight(0.7)
                                            ->confirmable(),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('N°')
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('project.name')
                    ->label('Proyecto')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-o-briefcase')
                    ->wrap()
                    ->limit(30)
                    ->tooltip(fn($record) => $record->project?->name),

                TextColumn::make('project.subClient.client.business_name')
                    ->label('Cliente')
                    ->searchable()
                    ->icon('heroicon-o-building-office')
                    ->toggleable()
                    ->limit(25),

                TextColumn::make('project.start_date')
                    ->label('Inicio')
                    ->date('d/m/Y')
                    ->icon('heroicon-o-calendar')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('project.end_date')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->icon('heroicon-o-calendar-days')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('fullname_cliente')
                    ->label('Responsable')
                    ->searchable()
                    ->icon('heroicon-o-user')
                    ->toggleable(isToggledHiddenByDefault: true),

                // Columna de resumen de activos
                TextColumn::make('assets')
                    ->label('Activos')
                    ->formatStateUsing(function ($state) {
                        if (!is_array($state)) return '-';

                        $count = collect($state)
                            ->filter(fn($asset) => $asset['selected'] ?? false)
                            ->count();

                        return $count > 0 ? "{$count} activo(s)" : 'Sin activos';
                    })
                    ->badge()
                    ->color(
                        fn($state) =>
                        is_array($state) && collect($state)->filter(fn($a) => $a['selected'] ?? false)->count() > 0
                            ? 'success'
                            : 'gray'
                    )
                    ->toggleable(),

                IconColumn::make('client_signature')
                    ->label('Firma Cliente')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->toggleable(),

                IconColumn::make('employee_signature')
                    ->label('Firma Empleado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->filters([
                SelectFilter::make('project_id')
                    ->label('Proyecto')
                    ->relationship('project', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('has_signatures')
                    ->label('Con firmas completas')
                    ->query(
                        fn(Builder $query) => $query
                            ->whereNotNull('client_signature')
                            ->whereNotNull('employee_signature')
                    )
                    ->toggle(),

                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date) => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date) => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators['from'] = 'Desde: ' . \Carbon\Carbon::parse($data['from'])->format('d/m/Y');
                        }
                        if ($data['until'] ?? null) {
                            $indicators['until'] = 'Hasta: ' . \Carbon\Carbon::parse($data['until'])->format('d/m/Y');
                        }
                        return $indicators;
                    }),
            ])
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->icon('heroicon-o-eye')
                        ->color('info'),
                    Tables\Actions\EditAction::make()
                        ->icon('heroicon-o-pencil')
                        ->color('warning'),
                    Tables\Actions\Action::make('downloadExcel')
                        ->label('Descargar Excel')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->url(fn(Compliance $record) => route('actas.excel', $record->id))
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('downloadPdf')
                        ->label('Descargar PDF')
                        ->icon('heroicon-o-document-text')
                        ->color('danger')
                        ->url(fn(Compliance $record) => route('actas.pdf', $record->id))
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('previewActaPdf')
                        ->label('Vista Previa')
                        ->icon('heroicon-o-document-magnifying-glass')
                        ->color('primary')
                        ->url(fn(Compliance $record) => route('actas.preview', $record->id))
                        ->openUrlInNewTab(),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->tooltip('Acciones'),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Eliminar Actas Seleccionadas')
                    ->modalDescription('¿Está seguro de eliminar las actas seleccionadas? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar'),
            ])
            ->emptyStateHeading('No hay actas de conformidad')
            ->emptyStateDescription('Cree una nueva acta de conformidad para comenzar.')
            ->emptyStateIcon('heroicon-o-document-check');
    }

    public static function getRelations(): array
    {
        return [
            WorkReportsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompliances::route('/'),
            'create' => Pages\CreateCompliance::route('/create'),
            'view' => Pages\ViewCompliance::route('/{record}'),
            'edit' => Pages\EditCompliance::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}
