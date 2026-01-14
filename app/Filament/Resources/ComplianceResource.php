<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComplianceResource\Pages;
use App\Filament\Resources\ComplianceResource\RelationManagers\WorkReportsRelationManager;
use App\Models\Compliance;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
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
    public static function updateProjectDetails($state, \Filament\Forms\Set $set)
    {
        if (!$state) {
            $set('razon_social', null);
            $set('ruc', null);
            $set('tienda', null);
            $set('direccion', null);
            $set('start_date', null);
            $set('end_date', null);
            return;
        }

        // Buscamos el proyecto con sus relaciones
        $project = Project::with(['subClient.client'])->find($state);

        if ($project) {
            $subClient = $project->subClient;
            $client = $subClient?->client;

            $set('razon_social', $client?->business_name);
            $set('ruc', $client?->document_number);
            $set('tienda', $subClient?->name);
            $set('direccion', $subClient?->address);

            // Importante: formatear para que el DatePicker lo entienda
            $set('start_date', $project->start_date?->format('Y-m-d'));
            $set('end_date', $project->end_date?->format('Y-m-d'));
        }
    }
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
                            ->required()
                            ->prefixIcon('heroicon-m-briefcase')
                            ->helperText('Solo se listan proyectos que están en estado "Aprobado".')
                            ->searchable()
                            ->preload()
                            ->live()
                            // 1. LÓGICA DE BÚSQUEDA PERSONALIZADA
                            ->getSearchResultsUsing(function (string $search) {
                                // Detectamos si lo que escribe el usuario es puramente numérico
                                $isNumeric = is_numeric($search);

                                return Project::query()
                                    ->where('status', 'Aprobado')
                                    ->where(function ($query) use ($search, $isNumeric) {
                                        // Busca por nombre
                                        $query->where('name', 'like', "%{$search}%")
                                            // Busca por código tal cual escribe el usuario
                                            ->orWhere('service_code', 'like', "%{$search}%");

                                        // Si es numérico (ej: 138), busca también como "COT-138"
                                        if ($isNumeric) {
                                            $query->orWhere('service_code', 'like', "%COT-{$search}%");
                                        }
                                    })
                                    ->limit(50)
                                    ->get()
                                    // Formato visual en la lista: "COT-138 - Nombre del Proyecto"
                                    ->mapWithKeys(fn($project) => [
                                        $project->id => "{$project->service_code} - {$project->name}"
                                    ]);
                            })
                            // 2. LÓGICA PARA MOSTRAR LA OPCIÓN SELECCIONADA
                            ->getOptionLabelUsing(function ($value): ?string {
                                $project = Project::find($value);
                                return $project
                                    ? "{$project->service_code} - {$project->name}"
                                    : null;
                            })
                            // 3. EVENTOS (Tus eventos originales)
                            ->afterStateUpdated(fn($state, $set) => self::updateProjectDetails($state, $set))
                            ->afterStateHydrated(function ($state, $set) {
                                if ($state) {
                                    $project = Project::find($state);
                                    if ($project) {
                                        $set('project_name', $project->name);
                                    }
                                }
                            })
                            // ACCIÓN DEL SUFIJO
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('view_project')
                                    ->icon('heroicon-o-eye')
                                    ->color('info')
                                    ->tooltip('Ver detalles')
                                    // Esta línea hace que aparezca SOLO cuando hay un ID seleccionado
                                    ->visible(fn($get) => !empty($get('project_id')))
                                    ->modalHeading('Información del Proyecto')
                                    ->modalWidth('3xl')
                                    ->modalSubmitAction(false)
                                    ->modalCancelActionLabel('Cerrar')
                                    // Importante: Pasamos el proyecto actual al modal
                                    ->modalContent(function ($get) {
                                        $record = Project::with(['subClient.client'])->find($get('project_id'));
                                        if (! $record) return null;

                                        return view('filament.components.project-info', ['project' => $record]);
                                    })
                            )
                            ->columnSpanFull(),
                        // ACA EL ESTADO DEL PROYECTO
                        Select::make('state')
                            ->label('Estado')
                            ->required()
                            ->default('En Ejecución')
                            ->options([
                                'En Ejecución' => 'En Ejecución',
                                'Completado'   => 'Completado',
                            ])
                            ->native(false)
                            ->columnSpanFull(),
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

                                // ---------------------------------------------------------
                                // COLUMNA 1: DATOS DEL CLIENTE
                                // ---------------------------------------------------------
                                Forms\Components\Section::make('Datos del Cliente')
                                    ->icon('heroicon-o-user')
                                    ->columnSpan(1)
                                    ->schema([
                                        Forms\Components\TextInput::make('fullname_cliente')
                                            ->label('Nombre Completo')
                                            ->prefixIcon('heroicon-o-user-circle')
                                            ->placeholder('Ingrese nombre completo')
                                            // Se eliminó ->required()
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
                                                    ->native(false)
                                                    ->live(),

                                                Forms\Components\TextInput::make('document_number')
                                                    ->label('N° de documento')
                                                    ->numeric()
                                                    ->minLength(fn(Get $get) => $get('document_type') === 'DNI' ? 8 : 9)
                                                    ->maxLength(fn(Get $get) => $get('document_type') === 'DNI' ? 8 : 12)
                                                    ->hint(fn(Get $get) => match ($get('document_type')) {
                                                        'DNI' => '8 dígitos',
                                                        'CARNET DE EXTRANJERIA' => '9-12 dígitos',
                                                        'PASAPORTE' => '9-12 dígitos',
                                                        default => ''
                                                    })
                                                    ->hintColor('primary'),
                                            ]),

                                        SignaturePad::make('client_signature')
                                            ->label('Firma del Cliente')
                                            ->dotSize(2.0)
                                            ->lineMinWidth(0.5)
                                            ->lineMaxWidth(2.5)
                                            ->throttle(16)
                                            ->minDistance(5)
                                            ->velocityFilterWeight(0.7)
                                            ->confirmable(),
                                    ]),

                                // ---------------------------------------------------------
                                // COLUMNA 2: DATOS DEL EMPLEADO
                                // ---------------------------------------------------------
                                Forms\Components\Section::make('Datos del Empleado')
                                    ->icon('heroicon-o-identification')
                                    ->columnSpan(1)
                                    ->schema([
                                        Forms\Components\Placeholder::make('employee_info')
                                            ->label('Responsable Técnico')
                                            ->content(function () {
                                                $employee = Auth::user()?->employee;

                                                if (!$employee) {
                                                    return new \Illuminate\Support\HtmlString("
                                        <div class='p-4 border rounded-lg bg-warning-50 dark:bg-warning-900/20 border-warning-200 dark:border-warning-800'>
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
                                    <div class='p-4 border rounded-lg bg-primary-50 dark:bg-primary-900/20 border-primary-200 dark:border-primary-800'>
                                        <div class='flex items-center gap-3'>
                                            <div class='flex items-center justify-center flex-shrink-0 w-12 h-12 rounded-full bg-primary-100 dark:bg-primary-800'>
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
                                            ->lineMinWidth(0.5)
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
                TextColumn::make('project.service_code')
                    ->label('Código de Servicio')
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

                TextColumn::make('project.subClient.name')
                    ->label('Tienda')
                    ->searchable()
                    ->icon('heroicon-o-building-office')
                    ->toggleable()
                    ->limit(25),

                TextColumn::make('project.service_start_date')
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
                TextColumn::make('state')
                    ->label('Estado')
                    ->sortable()
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'En Ejecución' => 'primary',
                        'Completado'   => 'gray',
                        default        => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('assets')
                    ->label('Activos Seleccionados')
                    ->html()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(function ($record) {
                        // 1. Obtenemos el dato crudo (la lógica que te funcionó)
                        $rawAttribute = $record->getAttributes()['assets'] ?? null;

                        if (is_null($rawAttribute)) return '---';

                        // 2. Decodificamos el JSON
                        $assets = is_string($rawAttribute) ? json_decode($rawAttribute, true) : $rawAttribute;

                        if (!is_array($assets)) return '---';

                        // 3. Tu Diccionario de nombres
                        $labels = [
                            'tablero_autosoportado' => 'Tablero Autosoportado',
                            'tablero_adosados'      => 'Tablero Adosados',
                            'banco_condensadores'   => 'Banco de Condensadores',
                            'pozos_baja_tension'    => 'Pozos Tierra (BT)',
                            'pozos_media_tension'   => 'Pozos Tierra (MT)',
                        ];

                        // 4. Filtramos solo los TRUE y aplicamos el diccionario
                        return collect($assets)
                            ->filter(function ($item) {
                                // Filtramos por el booleano 'selected'
                                return isset($item['selected']) && ($item['selected'] === true || $item['selected'] === "true");
                            })
                            ->map(function ($item, $key) use ($labels) {
                                // Buscamos el nombre en el diccionario o limpiamos la key si no existe
                                $nombreLimpio = $labels[$key] ?? str($key)->replace('_', ' ')->title();
                                $cantidad = $item['quantity'] ?? 0;

                                // Formato final para la fila
                                return "<strong>{$cantidad}</strong> x {$nombreLimpio}";
                            })
                            ->join('<br>'); // Salto de línea para que se vea como lista
                    }),

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
                SelectFilter::make('state')
                    ->label('Estado')
                    ->options([
                        'En ejecución'    => 'En ejecución',
                        'Completado'      => 'Completado',
                    ]),

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
                    Tables\Actions\EditAction::make()
                        ->label('Editar Registro')
                        ->color('info'), // Azul para edición es estándar

                    // Acción Excel
                    Tables\Actions\Action::make('downloadExcel')
                        ->label('Exportar a Excel')
                        ->icon('heroicon-m-table-cells') // Icono más específico de Excel
                        ->color('success')
                        ->action(function (Compliance $record) {
                            Notification::make()
                                ->title('Preparando Excel')
                                ->body('La descarga comenzará en un momento.')
                                ->success()
                                ->send();
                        })
                        ->url(fn(Compliance $record) => route('actas.excel', $record->id))
                        ->openUrlInNewTab(),

                    // Acción PDF
                    Tables\Actions\Action::make('downloadPdf')
                        ->label('Descargar Acta PDF')
                        ->icon('heroicon-m-arrow-down-tray')
                        ->color('danger')
                        ->requiresConfirmation() // Evita descargas accidentales
                        ->modalHeading('Generar Documento PDF')
                        ->modalDescription('El sistema procesará los activos y generará el acta oficial. ¿Continuar?')
                        ->modalSubmitActionLabel('Descargar ahora')
                        ->action(function (Compliance $record) {
                            Notification::make()
                                ->title('Generando archivo...')
                                ->body('El PDF se abrirá en una nueva pestaña.')
                                ->warning()
                                ->send();
                        })
                        ->url(fn(Compliance $record) => route('actas.pdf', $record->id))
                        ->openUrlInNewTab(),

                    //DESCARGAR EL ACTA CON SUS REPORTES DE TRABAJO
                    Tables\Actions\Action::make('downloadActaWithReports')
                        ->label('Acta + Reportes PDF')
                        ->icon('heroicon-m-document-arrow-down')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('Descargar Acta y Reportes')
                        ->modalDescription('¿Deseas descargar el acta de conformidad junto con todos los reportes de trabajo del proyecto?')
                        ->modalSubmitActionLabel('Descargar')
                        ->action(function (Compliance $record) {
                            Notification::make()
                                ->title('Generando PDF combinado...')
                                ->body('El archivo se abrirá en una nueva pestaña.')
                                ->success()
                                ->send();
                        })
                        ->url(fn(Compliance $record) => route('actas.pdf-with-reports', $record->id))
                        ->openUrlInNewTab(),
                    // Acción Vista Previa
                    Tables\Actions\Action::make('previewActaPdf')
                        ->label('Vista Rápida')
                        ->icon('heroicon-m-magnifying-glass-circle')
                        ->color('gray')
                        ->url(fn(Compliance $record) => route('actas.preview', $record->id))
                        ->openUrlInNewTab(),
                ])
                    ->icon('heroicon-m-cog-6-tooth') // Cambiado a un engranaje (ajustes/acciones)
                    ->button() // Esto lo convierte en un botón con texto en lugar de solo iconos
                    ->label('Opciones')
                    ->color('gray')
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
