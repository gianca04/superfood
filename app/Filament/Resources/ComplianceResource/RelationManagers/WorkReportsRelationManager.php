<?php

namespace App\Filament\Resources\ComplianceResource\RelationManagers;

use App\Filament\Resources\WorkReportResource\RelationManagers\PhotosRelationManager;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Project;
use App\Models\Quote;
use App\Models\QuoteWarehouseDetail;
use App\Models\SubClient;
use App\Models\WorkReport;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Actions\Action as FormAction;
use Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;

class WorkReportsRelationManager extends RelationManager
{
    protected static ?string $title = 'Reportes de Trabajo';

    protected static ?string $modelLabel = 'Reporte de Trabajo';
    protected static ?string $pluralModelLabel = 'Reportes de Trabajo';
    protected static string $relationship = 'workReports';
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Si es un nuevo registro, asignar el project_id del Compliance
        if (!isset($data['id'])) {
            $data['project_id'] = $this->ownerRecord->project_id;
        }
        return $data;
    }
    // Hook para creación
    protected function afterCreate(): void
    {
        $report = $this->getRecord();
        $this->syncProjectConsumptions($report);
    }

    // Hook para edición
    protected function afterSave(): void
    {
        $report = $this->getRecord();
        // Limpieza previa para evitar duplicados en la tabla project_consumptions
        \App\Models\ProjectConsumption::where('work_report_id', $report->id)->delete();
        $this->syncProjectConsumptions($report);
    }

    // Hook para eliminación (antes de borrar, para cascada)
    protected function beforeDelete(): void
    {
        $report = $this->getRecord();
        // Verificar si el reporte tiene datos en materials antes de eliminar consumos
        if (!empty($report->materials)) {
            // Si borras el reporte y tiene materials, eliminamos los consumos de la tabla de proyectos
            \App\Models\ProjectConsumption::where('work_report_id', $report->id)->delete();
        }
    }

    protected function syncProjectConsumptions($report): void
    {
        // Aseguramos tener el project_id
        $projectId = $report->project_id ?? $this->getOwnerRecord()->project_id;
        $materials = $report->materials ?? [];

        if (empty($materials)) {
            Log::info("No hay materiales para el reporte ID: {$report->id}");
            // Si no hay materiales, eliminamos todos los consumos existentes para este reporte
            \App\Models\ProjectConsumption::where('work_report_id', $report->id)->delete();
            return;
        }

        // Obtener consumos existentes para este reporte, indexados por material_id
        $existingConsumptions = \App\Models\ProjectConsumption::where('work_report_id', $report->id)
            ->get()
            ->keyBy('quote_warehouse_detail_id');

        foreach ($materials as $item) {
            // Validamos que los datos del repeater no estén vacíos
            if (empty($item['material_id']) || empty($item['used_quantity'])) {
                continue;
            }

            $materialId = $item['material_id'];
            $quantity = $item['used_quantity'];
            $consumedAt = $report->report_date ?? now();

            if (isset($existingConsumptions[$materialId])) {
                // Material ya existe: actualizar solo si la cantidad cambió
                $consumption = $existingConsumptions[$materialId];
                if ($consumption->quantity != $quantity || $consumption->consumed_at != $consumedAt) {
                    $consumption->update([
                        'quantity' => $quantity,
                        'consumed_at' => $consumedAt,
                    ]);
                    Log::info("Consumo actualizado para proyecto: {$projectId}, material: {$materialId}, nueva cantidad: {$quantity}");
                }
                // Marcar como procesado
                unset($existingConsumptions[$materialId]);
            } else {
                // Material nuevo: crear consumo
                \App\Models\ProjectConsumption::create([
                    'project_id' => $projectId,
                    'quote_warehouse_detail_id' => $materialId,
                    'work_report_id' => $report->id,
                    'quantity' => $quantity,
                    'consumed_at' => $consumedAt,
                ]);
                Log::info("Consumo creado para proyecto: {$projectId}, material: {$materialId}");
            }
        }

        // Eliminar consumos obsoletos (materiales que ya no están en el reporte)
        if (!empty($existingConsumptions)) {
            foreach ($existingConsumptions as $obsoleteConsumption) {
                $obsoleteConsumption->delete();
                Log::info("Consumo eliminado para material obsoleto: {$obsoleteConsumption->quote_warehouse_detail_id}");
            }
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('MainTabs')
                    ->tabs([
                        // INICIO DE TAB DE INFORMACIÓN GENERAL
                        Tabs\Tab::make('Información general')
                            ->icon('heroicon-o-information-circle')
                            ->columns(2)
                            ->schema([

                                Forms\Components\Hidden::make('employee_id')
                                    ->default(fn() => Auth::user()?->employee_id)
                                    ->required()
                                    ->label('Supervisor / Técnico'),

                                Forms\Components\Select::make('project_id')
                                    ->hidden()
                                    ->dehydrated() // También añadir aquí
                                    ->default(fn() => $this->ownerRecord->project_id)
                                    ->helperText('Proyecto asociado a este reporte.'), // FIN DE SELECT DE EMPLEADO
                                //ACA EL ID DE ACTA : COMPLIANCE_ID
                                Forms\Components\Hidden::make('compliance_id')
                                    ->default(function () {
                                        // Intenta ownerRecord, si no, usa la URL
                                        return $this->ownerRecord->id ?? request()->route('record');
                                    })
                                    ->dehydrated(),
                                // INICIO DE SELECT DE PROYECTO
                                Forms\Components\Select::make('project_id')
                                    ->hidden()
                                    // 1. Preselecciona el ID del registro padre (Compliance/Proyecto)
                                    ->default(fn() => $this->ownerRecord->project_id)

                                    ->helperText('Proyecto asociado a este reporte.'),

                                // FIN DE SELECT DE PROYECTO

                                // INICIO DE INPUT DE NOMBRE DEL REPORTE
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Nombre del reporte'),
                                // FIN DE INPUT DE NOMBRE DEL REPORTE

                                // INICIO DE INPUT DE FECHA
                                Forms\Components\DatePicker::make('report_date')
                                    ->label('Fecha')
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->required()
                                    ->helperText('Selecciona la fecha del trabajo o presiona el botón para establecer hoy')
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('set_today')
                                            ->icon('heroicon-o-calendar')
                                            ->tooltip('Establecer fecha de hoy')
                                            ->color('primary')
                                            ->action(function (callable $set) {
                                                $set('report_date', now()->format('Y-m-d'));
                                            })
                                    ),
                                // FIN DE INPUT DE FECHA

                                // INICIO DE INPUT DE HORA DE INICIO
                                Forms\Components\TimePicker::make('start_time')
                                    ->label('Hora de inicio')
                                    ->native(false)
                                    ->seconds(false)
                                    ->displayFormat(format: 'H:i')
                                    ->helperText('Selecciona la hora de inicio del trabajo'),
                                // FIN DE INPUT DE HORA DE INICIO

                                // INICIO DE INPUT DE HORA DE FINALIZACIÓN
                                Forms\Components\TimePicker::make('end_time')
                                    ->label('Hora de finalización')
                                    ->native(false)
                                    ->seconds(false)
                                    ->displayFormat(format: 'H:i')
                                    ->helperText('Selecciona la hora de finalización del trabajo')
                                // Usamos afterStateUpdated para validar y limpiar el campo

                                // FIN DE INPUT DE HORA DE FINALIZACIÓN
                            ]),

                        // FIN DE TAB DE INFORMACIÓN GENERAL

                        // FIN DE TAB DE ORDEN DE TRABAJO

                        // INICIO TAB ACTIVIDADES DEL REPORTE
                        Tabs\Tab::make('Actividades')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->columns(2)
                            ->schema([

                                Forms\Components\RichEditor::make('work_to_do')
                                    ->label('Trabajos a realizar')
                                    ->helperText('Proporciona sugerencias o comentarios adicionales sobre el trabajo realizado.')
                                    ->maxLength(5000)
                                    ->columnSpanFull()
                                    ->toolbarButtons([

                                        'bold',
                                        'bulletList',
                                        'h2',
                                        'h3',
                                        'italic',
                                        'orderedList',
                                        'redo',
                                        'strike',
                                        'underline',
                                        'undo',
                                    ]),
                            ]),
                        // FIN TAB ACTIVIDADES DEL REPORTE

                        // INICIO DEL TAB DE HERRAMIENTAS Y MATERIALES
                        Tabs\Tab::make('Herramientas y materiales')
                            ->icon('heroicon-o-wrench')
                            ->columns(2)
                            ->schema([
                                Forms\Components\Repeater::make('tools')
                                    ->label('Herramientas')
                                    ->helperText('Agrega las herramientas utilizadas durante el trabajo.')
                                    ->schema([
                                        Forms\Components\TextInput::make('herramienta')
                                            ->label('Herramienta')
                                            ->placeholder('Ej: Taladro')
                                            ->required(),
                                        Forms\Components\TextInput::make('unidad')
                                            ->label('Unidad')
                                            ->placeholder('Ej: Unidad'),
                                        Forms\Components\TextInput::make('cantidad')
                                            ->label('Cantidad')
                                            ->placeholder('Ej: 2'),
                                    ])
                                    ->columns(3)
                                    ->columnSpanFull()
                                    ->defaultItems(0)
                                    ->reorderable(false)
                                    ->addActionLabel('Agregar herramienta')
                                    ->disabled(fn(string $operation): bool => $operation === 'view'),

                                Forms\Components\Repeater::make('materials')
                                    ->label('Materiales Utilizados')
                                    ->schema([
                                        Forms\Components\Grid::make(4)
                                            ->schema([
                                                // Selector de material usando tu función del modelo
                                                Forms\Components\Select::make('material_id')
                                                    ->label('Descripción del Material / Insumo')
                                                    ->options(function () {
                                                        // Usamos la función del modelo pasándole el project_id del dueño (Compliance)
                                                        $project = Project::find($this->getOwnerRecord()->project_id);
                                                        if (!$project) return [];

                                                        // Instanciamos un modelo temporal para usar tu función getAvailableMaterials
                                                        $reportModel = new WorkReport(['project_id' => $project->id]);

                                                        return $reportModel->getAvailableMaterials()
                                                            ->pluck('sat_description', 'id');
                                                    })
                                                    ->searchable()
                                                    ->preload()
                                                    ->live()
                                                    ->required()
                                                    ->columnSpanFull()
                                                    ->afterStateUpdated(function ($state, callable $set) {
                                                        if ($state) {
                                                            // Buscamos los datos técnicos para llenar los campos informativos
                                                            $material = QuoteWarehouseDetail::with(['quoteDetail.pricelist.unit'])->find($state);
                                                            if ($material && $material->quoteDetail->pricelist) {
                                                                $pricelist = $material->quoteDetail->pricelist;
                                                                $set('sat_line', $pricelist->sat_line);
                                                                $set('unit_name', $pricelist->unit->name ?? 'N/A');
                                                                // Calcular el total consumido en el proyecto actual para este material
                                                                $totalConsumed = \App\Models\ProjectConsumption::where('project_id', $this->getOwnerRecord()->project_id)
                                                                    ->where('quote_warehouse_detail_id', $state)
                                                                    ->sum('quantity');
                                                                $remaining = $material->attended_quantity - $totalConsumed;
                                                                if ($remaining <= 0) {
                                                                    Notification::make()->title('El almacén o los materiales entregados se han acabado. Vuelve a pedir a almacén.')->danger()->send();
                                                                    $set('material_id', null);
                                                                    $set('sat_line', null);
                                                                    $set('unit_name', null);
                                                                    $set('attended_quantity', null);
                                                                    $set('used_quantity', null);
                                                                } else {
                                                                    $set('attended_quantity', $remaining);
                                                                    $set('used_quantity', $remaining);
                                                                }
                                                            }
                                                        }
                                                    }),

                                                Forms\Components\TextInput::make('sat_line')
                                                    ->label('Línea SAT')
                                                    ->readOnly()
                                                    ->columnSpan(1),

                                                Forms\Components\TextInput::make('unit_name')
                                                    ->label('Unidad')
                                                    ->readOnly()
                                                    ->columnSpan(1),

                                                Forms\Components\TextInput::make('attended_quantity')
                                                    ->label('En stock')
                                                    ->numeric()
                                                    ->readOnly()
                                                    ->extraAttributes(['class' => 'bg-gray-50 font-bold text-primary-600'])
                                                    ->columnSpan(1),

                                                Forms\Components\TextInput::make('used_quantity')
                                                    ->label('Cant. a Reportar')
                                                    ->numeric()
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->suffix(fn($get) => $get('unit_name'))
                                                    ->columnSpan(1)
                                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                        $max = (float) $get('attended_quantity');
                                                        if ((float)$state > $max) {
                                                            $set('used_quantity', $max);
                                                            Notification::make()->title('Ajustado al máximo disponible')->warning()->send();
                                                        }
                                                    }),
                                            ])
                                    ])
                                    ->columnSpanFull()
                                    ->defaultItems(0)
                                    ->addActionLabel('Agregar material')
                                    ->reorderable(false)
                                    ->disabled(fn(string $operation): bool => $operation === 'view'),
                            ]),
                        // FIN DEL TAB DE HERRAMIENTAS Y MATERIALES


                        // INICIO DEL TAB DE LISTA DE PERSONAL
                        Tabs\Tab::make('Personal')
                            ->icon('heroicon-o-user-group')
                            ->columns(1)
                            ->schema([
                                Forms\Components\Repeater::make('personnel')
                                    ->label('Personal que realizó el trabajo')
                                    ->helperText('Agrega el personal que participó en el trabajo y las horas hombre.')
                                    ->schema([
                                        Forms\Components\Grid::make(3)
                                            ->schema([


                                                // Select para empleado (visible cuando is_not_registered = false)
                                                Forms\Components\Select::make('employee_id')
                                                    ->label('Empleado')
                                                    ->placeholder('Seleccionar empleado...')
                                                    ->options(fn() => Employee::where('active', true)
                                                        ->orderBy('first_name')
                                                        ->get()
                                                        ->mapWithKeys(fn($e) => [$e->id => $e->first_name . ' ' . $e->last_name]))
                                                    ->searchable()
                                                    ->preload()
                                                    ->live()
                                                    ->visible(fn(callable $get) => !$get('is_not_registered'))
                                                    ->afterStateUpdated(function ($state, callable $set) {
                                                        if ($state) {
                                                            $employee = Employee::with('position')->find($state);
                                                            if ($employee) {
                                                                $set('employee_name', $employee->first_name . ' ' . $employee->last_name);
                                                                $set('position_id', $employee->position_id);
                                                                $set('position_name', $employee->position?->name);
                                                            }
                                                        } else {
                                                            $set('employee_name', null);
                                                            $set('position_id', null);
                                                            $set('position_name', null);
                                                        }
                                                    })
                                                    ->createOptionForm([
                                                        Forms\Components\Section::make('Nuevo Empleado')
                                                            ->description('Datos básicos del empleado')
                                                            ->schema([
                                                                Forms\Components\TextInput::make('first_name')
                                                                    ->label('Nombres')
                                                                    ->required()
                                                                    ->maxLength(255),
                                                                Forms\Components\TextInput::make('last_name')
                                                                    ->label('Apellidos')
                                                                    ->required()
                                                                    ->maxLength(255),
                                                                Forms\Components\Select::make('document_type')
                                                                    ->label('Tipo de documento')
                                                                    ->options([
                                                                        'DNI' => 'DNI',
                                                                        'PASAPORTE' => 'Pasaporte',
                                                                        'CARNET DE EXTRANJERIA' => 'Carné de Extranjería',
                                                                    ])
                                                                    ->default('DNI'),
                                                                Forms\Components\TextInput::make('document_number')
                                                                    ->label('Número de documento')
                                                                    ->required()
                                                                    ->maxLength(20),
                                                                Forms\Components\Select::make('position_id')
                                                                    ->label('Cargo')
                                                                    ->options(fn() => Position::orderBy('name')->pluck('name', 'id'))
                                                                    ->searchable()
                                                                    ->preload(),
                                                            ])
                                                            ->columns(2),
                                                    ])
                                                    ->createOptionUsing(function (array $data): int {
                                                        $data['active'] = true;
                                                        $employee = Employee::create($data);
                                                        return $employee->id;
                                                    })
                                                    ->createOptionAction(function (FormAction $action) {
                                                        return $action
                                                            ->modalHeading('Crear nuevo empleado')
                                                            ->modalButton('Crear empleado')
                                                            ->modalWidth('2xl');
                                                    })
                                                    ->columnSpan(1),

                                                // TextInput para nombre manual (visible cuando is_not_registered = true)
                                                Forms\Components\TextInput::make('employee_name')
                                                    ->label('Nombre del personal')
                                                    ->placeholder('Escribir nombre...')
                                                    ->visible(fn(callable $get) => $get('is_not_registered'))
                                                    ->required(fn(callable $get) => $get('is_not_registered'))
                                                    ->maxLength(255)
                                                    ->columnSpan(1),

                                                Forms\Components\TextInput::make('hh')
                                                    ->label('H.H')
                                                    ->numeric()
                                                    ->step(0.5)
                                                    ->minValue(0)
                                                    ->placeholder('0')
                                                    ->suffix('hrs')
                                                    ->columnSpan(1),

                                                // TextInput para cargo (visible cuando is_not_registered = false)
                                                Forms\Components\TextInput::make('position_name')
                                                    ->label('Cargo')
                                                    ->readonly() // Solo lectura, se llena automáticamente
                                                    ->visible(fn(callable $get) => !$get('is_not_registered'))
                                                    ->columnSpan(1),

                                                // TextInput para cargo manual (visible cuando is_not_registered = true)
                                                Forms\Components\TextInput::make('position_name')
                                                    ->label('Nombre del cargo')
                                                    ->placeholder('Escribir cargo...')
                                                    ->visible(fn(callable $get) => $get('is_not_registered'))
                                                    ->maxLength(255)
                                                    ->columnSpan(1),
                                                Forms\Components\Toggle::make('is_not_registered')
                                                    ->label('No Registrado')
                                                    ->default(false)
                                                    ->live()
                                                    ->afterStateUpdated(function (callable $set) {
                                                        $set('employee_id', null);
                                                        $set('employee_name', null);
                                                        $set('position_id', null);
                                                        $set('position_name', null);
                                                    })
                                                    ->columnSpan(1),
                                            ]),
                                    ])
                                    ->addActionLabel('Agregar personal')
                                    ->reorderable(false)
                                    ->defaultItems(0)
                                    ->collapsible()
                                    ->afterStateHydrated(function ($component, $state) {
                                        // Hidratar employee_name y position_name desde IDs para registros existentes
                                        if (!is_array($state))
                                            return;

                                        $hydratedState = collect($state)->map(function ($item) {
                                            if (!is_array($item))
                                                return $item;

                                            // Migrar is_custom_text o is_custom_position a is_not_registered
                                            if (!isset($item['is_not_registered'])) {
                                                $item['is_not_registered'] = ($item['is_custom_text'] ?? false) || ($item['is_custom_position'] ?? false);
                                            }

                                            // Hidratar employee_name si tiene employee_id pero no employee_name
                                            if (empty($item['employee_name']) && !empty($item['employee_id'])) {
                                                $employee = Employee::with('position')->find($item['employee_id']);
                                                if ($employee) {
                                                    $item['employee_name'] = $employee->first_name . ' ' . $employee->last_name;
                                                    $item['position_id'] = $employee->position_id;
                                                    $item['position_name'] = $employee->position?->name;
                                                }
                                            }

                                            // Hidratar position_name si tiene position_id pero no position_name
                                            if (empty($item['position_name']) && !empty($item['position_id'])) {
                                                $position = Position::find($item['position_id']);
                                                if ($position) {
                                                    $item['position_name'] = $position->name;
                                                }
                                            }

                                            return $item;
                                        })->toArray();

                                        $component->state($hydratedState);
                                    })
                                    ->itemLabel(fn(array $state): ?string => $state['employee_name'] ?? 'Personal sin nombre')
                                    ->columnSpanFull()
                                    ->disabled(fn(string $operation): bool => $operation === 'view'),
                            ]),
                        // FIN DL TAB DE LISTA DE PERSONAL

                        // INICIO DE TAB DE CONCLUSIONES
                        // INICIO DE TAB DE CONCLUSIONES
                        Tabs\Tab::make('Conclusiones')
                            ->icon('heroicon-o-check-badge')
                            ->columns(2) // Esto define que habrá 2 columnas
                            ->schema([
                                Forms\Components\RichEditor::make('conclusions')
                                    ->label('Conclusiones')
                                    // Quitamos columnSpanFull() para que use solo 1 de las 2 columnas
                                    ->maxLength(5000)
                                    ->toolbarButtons([
                                        'bold',
                                        'h2',
                                        'h3',
                                        'orderedList',
                                        'bulletList',
                                        'redo',
                                        'underline',
                                        'undo',
                                    ]),

                                Forms\Components\RichEditor::make('suggestions')
                                    ->label('Recomendaciones')
                                    ->helperText('Proporciona sugerencias o comentarios adicionales.')
                                    // Quitamos columnSpanFull() para que ocupe la segunda columna
                                    ->maxLength(5000)
                                    ->toolbarButtons([

                                        'bold',
                                        'bulletList',
                                        'h2',
                                        'h3',
                                        'italic',
                                        'orderedList',
                                        'redo',
                                        'strike',
                                        'underline',
                                        'undo',
                                    ]),
                            ]),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre del Reporte')
                    ->searchable()
                    ->extraAttributes(['class' => 'font-bold'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('report_date')
                    ->label('Fecha')
                    ->weight('bold')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('employee.first_name')
                    ->label('Supervisor')
                    ->formatStateUsing(fn($record) => $record->employee->first_name . ' ' . $record->employee->last_name)
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('photos_count')
                    ->label('Evidencias')
                    ->counts('photos')
                    ->badge()
                    ->color(fn(string $state): string => match (true) {
                        $state == 0 => 'danger',
                        $state < 5 => 'warning',
                        default => 'success',
                    }),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->label('Actualizado')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employee')
                    ->label('Colaborador')
                    ->relationship('employee', 'first_name')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Crear nuevo Reporte de Trabajo')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->modalHeading('Nuevo Reporte de Trabajo')
                    ->mutateFormDataUsing(function (array $data): array {
                        // Forzamos los IDs desde el ownerRecord (el Compliance padre)
                        $data['compliance_id'] = $this->ownerRecord->id;
                        $data['project_id'] = $this->ownerRecord->project_id;
                        return $data;
                    })->after(function (WorkReport $record) {
                        $this->syncProjectConsumptions($record);
                    }),
                /*
                    Tables\Actions\Action::make('download_all_reports')
                    ->label('Descargar Todos los Reportes de esta acta')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->visible(fn() => $this->ownerRecord && $this->ownerRecord->project_id)
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar descarga')
                    ->modalDescription('¿Deseas descargar todos los reportes de trabajo de este proyecto?')
                    ->modalSubmitActionLabel('Descargar')
                    ->modalCancelActionLabel('Cancelar')
                    ->action(function () {
                        $projectId = $this->ownerRecord->project_id;

                        Notification::make()
                            ->title('✅ Descarga iniciada')
                            ->body('Los reportes se están descargando...')
                            ->success()
                            ->duration(3000)
                            ->send();

                        return redirect(route('work-reports.download-multiple-pdf', $projectId));
                    }),
                */
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    // Acciones de Vista y Edición
                    Tables\Actions\ViewAction::make()
                        ->color('info'),
                    Tables\Actions\EditAction::make()
                        ->color('primary')
                        ->mutateFormDataUsing(function (array $data): array {
                            // Aseguramos que al editar no pierda la relación
                            $data['compliance_id'] = $this->ownerRecord->id;
                            $data['project_id'] = $this->ownerRecord->project_id;
                            return $data;
                        })->after(function (WorkReport $record) {
                            // Limpiamos anteriores
                            \App\Models\ProjectConsumption::where('work_report_id', $record->id)->delete();
                            // Sincronizamos nuevos
                            $this->syncProjectConsumptions($record);
                        }),
                    Tables\Actions\Action::make('preview_report')
                        ->label('Previsualizar')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->url(fn($record) => route('work-report.preview', $record->id))
                        ->openUrlInNewTab()
                        ->tooltip('Ver previsualización del reporte'),
                    // Relación de Fotos
                    RelationManagerAction::make('photos-relation-manager')
                        ->label('Ver fotografías')
                        ->icon('heroicon-o-photo') // Icono más descriptivo
                        ->slideOver(true)
                        ->relationManager(PhotosRelationManager::make()),

                    // Reporte PDF
                    Tables\Actions\Action::make('generate_evidence_report')
                        ->label('Informe de Evidencias')
                        ->color('danger')
                        ->icon('heroicon-o-document-arrow-down') // Cambiado para indicar descarga/PDF
                        ->url(fn($record) => route('evidence-report.pdf', $record->id))
                        ->openUrlInNewTab()
                        ->visible(fn($record) => $record->photos()->exists()) // .exists() es más eficiente que .count()
                        ->tooltip('Generar informe PDF con evidencias fotográficas'),

                    // Acción de Borrar al final (por seguridad y convención)
                    Tables\Actions\DeleteAction::make(),
                ])
                    ->icon('heroicon-m-ellipsis-vertical') // Icono clásico de menú
                    ->tooltip('Acciones')
                    ->color('gray')
                    ->button() // Opcional: convierte el grupo en un botón con texto si prefieres
            ]);
    }
}
