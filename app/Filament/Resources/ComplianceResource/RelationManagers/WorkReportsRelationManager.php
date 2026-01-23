<?php

namespace App\Filament\Resources\ComplianceResource\RelationManagers;

use App\Filament\Resources\WorkReportResource\RelationManagers\PhotosRelationManager;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Project;
use App\Models\Quote;
use App\Models\SubClient;
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
                                    ->label('Materiales')
                                    ->helperText('Agrega los materiales utilizados durante el trabajo.')
                                    ->schema([
                                        Forms\Components\TextInput::make('material')
                                            ->label('Material')
                                            ->placeholder('Ej: Cemento')
                                            ->required(),
                                        Forms\Components\TextInput::make('unidad')
                                            ->label('Unidad')
                                            ->placeholder('Ej: Sacos'),
                                        Forms\Components\TextInput::make('cantidad')
                                            ->label('Cantidad')
                                            ->placeholder('Ej: 10'),
                                    ])
                                    ->columns(3)
                                    ->columnSpanFull()
                                    ->defaultItems(0)
                                    ->reorderable(false)
                                    ->addActionLabel('Agregar material')
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
