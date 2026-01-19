<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Filament\Resources\ProjectResource\RelationManagers\EmployeesRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\TimesheetsRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\WorkReportsRelationManager;
use App\Forms\Components\ClientMainInfo;
use App\Exports\ProjectAttendancesReportExport;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\SelectColumn;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Project;
use App\Models\Quote;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Carbon\Carbon;
use App\Models\SubClient;
use App\Models\WorkReport;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use FontLib\Table\Type\name;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ProjectResource extends Resource
{
    use Translatable;
    protected static ?string $pluralModelLabel = 'Proyectos';
    protected static ?string $modelLabel = 'Proyecto';
    protected static ?string $model = Project::class;
    protected static ?string $navigationGroup = 'Control de operaciones';
    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    // BUSQUEDA GLOBAL DE PROYECTOS
    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];  // Verifica que estos atributos sean los más relevantes para la búsqueda
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Nombre' => $record->name,
        ];
    }
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        // Optimiza la consulta, asegurando que solo cargue lo necesario
        return parent::getGlobalSearchEloquentQuery(); // Selecciona solo las columnas necesarias del modelo Employee
    }
    public static function getEloquentQuery(): Builder
    {
        // 1. Obtenemos la consulta base
        $query = parent::getEloquentQuery();

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $isSuperUser = $user->roles()->whereIn('id', [1, 3])->exists();

        if ($isSuperUser) {
            return $query;
        }

        $employeeId = $user->employee_id;

        if (!$employeeId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $q) use ($employeeId) {
            $q->whereHas('inspectors', function (Builder $pivotQuery) use ($employeeId) {
                $pivotQuery->where('employee_id', $employeeId);
            });
        });
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Tabs::make('Solicitud')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Datos Generales')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Descripción de la solicitud')
                                            ->required()
                                            ->columnSpan(2),
                                        Forms\Components\TextInput::make('service_code')
                                            ->label('Codigo de Servicio')
                                            ->default('COT-' . (Project::max('id') + 1))
                                            // ->helperText('Correlativo generado automáticamente y no editable')
                                            ->disabled()
                                            ->dehydrated()
                                            ->columnSpan(1),
                                    ]),

                                Grid::make(4)
                                    ->schema([
                                        Forms\Components\TextInput::make('request_number')
                                            ->label('N° de Solicitud')
                                            ->columnSpan(2)
                                            ->maxLength(255),
                                        Forms\Components\DatePicker::make('requested_at')
                                            ->label('Fecha de Solicitud')
                                            ->columnSpan(2)
                                            ->default(now()),

                                        Forms\Components\Select::make('client_id')
                                            ->required()
                                            ->columnSpan(2)
                                            ->prefixIcon('heroicon-m-briefcase')
                                            ->label('Cliente') // Título para el campo 'Cliente'
                                            ->options(
                                                function (callable $get) {
                                                    return Client::whereIn('id', [127, 164])  // Filtra solo los IDs especificados
                                                        ->select('id', 'business_name', 'document_number')
                                                        ->when($get('search'), function ($query, $search) {
                                                            $query->where('business_name', 'like', "%{$search}%")
                                                                ->orWhere('document_number', 'like', "%{$search}%");
                                                        })
                                                        ->get()
                                                        ->mapWithKeys(function ($client) {
                                                            return [$client->id => $client->business_name . ' - ' . $client->document_number];
                                                        })
                                                        ->toArray();
                                                }
                                            )
                                            ->searchable() // Activa la búsqueda asincrónica
                                            ->reactive() // Hace el campo reactivo
                                            ->afterStateUpdated(fn($state, callable $set) => $set('sub_client_id', null))
                                            ->helperText('Selecciona el cliente para esta cotización.')

                                            // Botón para ver información del cliente
                                            ->suffixAction(
                                                Forms\Components\Actions\Action::make('view_client')
                                                    ->icon('heroicon-o-eye')
                                                    ->tooltip('Ver información del cliente')
                                                    ->color('info')
                                                    ->action(function (callable $get) {
                                                        $clientId = $get('client_id');
                                                        if (!$clientId) {
                                                            Notification::make()
                                                                ->title('Selecciona un cliente primero')
                                                                ->warning()
                                                                ->send();
                                                            return;
                                                        }
                                                    })
                                                    ->modalContent(function (callable $get) {
                                                        $clientId = $get('client_id');
                                                        if (!$clientId)
                                                            return null;

                                                        $client = Client::with('subClients')->find($clientId);
                                                        if (!$client)
                                                            return null;

                                                        return view('filament.components.client-info-modal', compact('client'));
                                                    })
                                                    ->modalHeading('Información del Cliente')
                                                    ->modalSubmitAction(false)
                                                    ->modalCancelActionLabel('Cerrar')
                                                    ->modalWidth('2xl')
                                                    ->visible(fn(callable $get) => !empty($get('client_id')))
                                            )

                                            ->createOptionForm([
                                                ClientMainInfo::make()
                                            ])

                                            ->createOptionUsing(function (array $data): int {
                                                $client = Client::create($data);
                                                return $client->id;
                                            })
                                            ->createOptionAction(function (FormAction $action) {
                                                return $action
                                                    ->modalHeading('Crear nuevo cliente')
                                                    ->modalButton('Crear cliente')
                                                    ->modalWidth('6xl');
                                            })

                                            ->afterStateUpdated(function (callable $get, callable $set) {
                                                $clientId = $get('client_id');
                                                if ($clientId) {
                                                    // Cargar toda la información del cliente en una sola consulta
                                                    $client = Client::find($clientId);
                                                    if ($client) {
                                                        // Actualizar los campos de 'business_name' y 'document_number' solo si hay un cliente
                                                        $set('business_name', $client->business_name);
                                                        $set('document_type_client', $client->document_type);
                                                        $set('document_number_client', $client->document_number);
                                                        $set('contact_phone', $client->contact_phone);
                                                        $set('contact_email', $client->contact_email);
                                                    }
                                                } else {
                                                    // Limpiar los campos si no hay cliente seleccionado
                                                    $set('business_name', null);
                                                    $set('document_number', null);
                                                }
                                            }),

                                        Forms\Components\Select::make('sub_client_id')
                                            ->columnSpan(2)
                                            ->prefixIcon('heroicon-m-home-modern')
                                            ->label('Tienda') // Título para el campo 'Tienda'
                                            ->required()
                                            ->options(
                                                function (callable $get) {
                                                    $clientId = $get('client_id');
                                                    return SubClient::where('client_id', $clientId)
                                                        ->get()
                                                        ->mapWithKeys(function ($subClient) {
                                                            return [$subClient->id => $subClient->name];
                                                        })
                                                        ->toArray();
                                                }
                                            )
                                            ->reactive()
                                            ->searchable()
                                            ->disabled(fn($get) => !$get('client_id')) // Deshabilita si no hay cliente seleccionado
                                            ->helperText('Selecciona el Sede para esta cotización.') // Ayuda para el campo 'Tienda'

                                            // Cuando se carga un registro existente, seleccionar automáticamente el cliente
                                            ->afterStateHydrated(function ($state, callable $set) {
                                                if ($state) {
                                                    $subClient = SubClient::find($state);
                                                    if ($subClient) {
                                                        $set('client_id', $subClient->client_id);
                                                    }
                                                }
                                            })

                                            // Botón para ver información de la tienda
                                            ->suffixAction(
                                                Forms\Components\Actions\Action::make('view_sub_client')
                                                    ->icon('heroicon-o-eye')
                                                    ->tooltip('Ver información de la tienda')
                                                    ->color('info')
                                                    ->action(function (callable $get) {
                                                        $subClientId = $get('sub_client_id');
                                                        if (!$subClientId) {
                                                            Notification::make()
                                                                ->title('Selecciona una tienda primero')
                                                                ->warning()
                                                                ->send();
                                                            return;
                                                        }
                                                    })
                                                    ->modalContent(function (callable $get) {
                                                        $subClientId = $get('sub_client_id');
                                                        if (!$subClientId)
                                                            return null;

                                                        $subClient = SubClient::with('client')->find($subClientId);
                                                        if (!$subClient)
                                                            return null;

                                                        return view('filament.components.sub-client-info-modal', compact('subClient'));
                                                    })
                                                    ->modalHeading('Información de la Sede')
                                                    ->modalSubmitAction(false)
                                                    ->modalCancelActionLabel('Cerrar')
                                                    ->modalWidth('2xl')
                                                    ->visible(fn(callable $get) => !empty($get('sub_client_id')))
                                            )

                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Nombre del subcliente')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->prefixIcon('heroicon-o-user'),

                                                Forms\Components\TextInput::make('address')
                                                    ->label('Dirección')
                                                    ->columnSpanFull()
                                                    ->placeholder('Dirección del subcliente')
                                                    ->maxLength(255)
                                                    ->prefixIcon('heroicon-o-map-pin'),

                                                Forms\Components\Textarea::make('description')
                                                    ->label('Descripción')
                                                    ->maxLength(500)
                                                    ->autosize()
                                                    ->columnSpanFull(),
                                            ])
                                            ->createOptionUsing(function (array $data, callable $get): int {
                                                $data['client_id'] = $get('client_id');
                                                $subClient = SubClient::create($data);
                                                return $subClient->id;
                                            })
                                            ->createOptionAction(function (FormAction $action) {
                                                return $action
                                                    ->modalHeading('Crear nueva tienda')
                                                    ->modalButton('Crear tienda')
                                                    ->modalWidth('2xl');
                                            })
                                            ->afterStateUpdated(function (callable $get, callable $set) {
                                                $subClientId = $get('sub_client_id');
                                                if ($subClientId) {
                                                    // Cargar toda la información del Sede en una sola consulta
                                                    $subClient = SubClient::find($subClientId);
                                                    if ($subClient) {
                                                    }
                                                } else {
                                                    // Limpiar los campos si no hay Sede seleccionado
                                                    $set('name', null);
                                                    $set('location', null);
                                                }
                                            }),

                                    ]),

                                Forms\Components\Textarea::make('comment')
                                    ->label('Comentario')
                                    ->rows(3),
                            ]),

                        Forms\Components\Tabs\Tab::make('Datos de la Visita')
                            ->schema([
                                // ACA COLOCAREMOS SOLAMENTE  A Supervisor de seguimiento.
                                Select::make('supervisor_name')
                                    ->label('Supervisor de seguimiento')
                                    ->options(
                                        Employee::whereIn('id', [40, 50, 55])
                                            ->with('user')
                                            ->get()
                                            ->mapWithKeys(function ($employee) {
                                                return [$employee->id => $employee->fullname];
                                            })
                                            ->toArray()
                                    )
                                    ->searchable()
                                    ->afterStateUpdated(function ($state, $set) {
                                        // Busca el empleado y guarda el nombre en supervisor_name
                                        $employee = Employee::find($state);
                                        $set('supervisor_name', $employee ? $employee->fullname : null);
                                    }),
                                Repeater::make('inspectors')
                                    ->relationship()
                                    ->label('Inspectores asignados')
                                    ->minItems(1)
                                    ->schema([
                                        Forms\Components\Select::make('employee_id')
                                            //->default(fn() => Auth::user()?->employee_id)->required()
                                            ->columns(2)
                                            ->reactive()
                                            ->prefixIcon('heroicon-m-user')
                                            ->label('Inspector de la visita') // Título para el campo 'Empleado'
                                            ->options(
                                                function (callable $get) {
                                                    return Employee::query()
                                                        ->select('id', 'first_name', 'last_name', 'document_number')
                                                        ->when($get('search'), function ($query, $search) {
                                                            $query->where('first_name', 'like', "%{$search}%")
                                                                ->orWhere('last_name', 'like', "%{$search}%")
                                                                ->orWhere('document_number', 'like', "%{$search}%");
                                                        })
                                                        ->get()
                                                        ->mapWithKeys(function ($employee) {
                                                            return [$employee->id => $employee->full_name];
                                                        })
                                                        ->toArray();
                                                }
                                            )
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
                                            ->searchable() // Activa la búsqueda asincrónica
                                            ->placeholder('Seleccionar un empleado') // Placeholder
                                            ->helperText('Selecciona el empleado responsable de la cotización.') // Ayuda para el campo de empleado

                                            // Botón para ver información del empleado
                                            ->suffixAction(
                                                Forms\Components\Actions\Action::make('view_employee')
                                                    ->icon('heroicon-o-eye')
                                                    ->tooltip('Ver información del supervisor')
                                                    ->color('info')
                                                    ->action(function (callable $get) {
                                                        $employeeId = $get('employee_id');
                                                        if (!$employeeId) {
                                                            Notification::make()
                                                                ->title('Selecciona un supervisor primero')
                                                                ->warning()
                                                                ->send();
                                                            return;
                                                        }
                                                    })
                                                    ->modalContent(function (callable $get) {
                                                        $employeeId = $get('employee_id');
                                                        if (!$employeeId)
                                                            return null;

                                                        $employee = Employee::with('user')->find($employeeId);
                                                        if (!$employee)
                                                            return null;

                                                        return view('filament.components.employee-info-modal', compact('employee'));
                                                    })
                                                    ->modalHeading('Información del Supervisor')
                                                    ->modalSubmitAction(false)
                                                    ->modalCancelActionLabel('Cerrar')
                                                    ->modalWidth('2xl')
                                                    ->visible(fn(callable $get) => !empty($get('employee_id')))
                                            )
                                            ->afterStateHydrated(function (callable $get, callable $set) {
                                                $employeeId = $get('employee_id');
                                                if ($employeeId) {
                                                    $employee = Employee::with('user')->find($employeeId);
                                                    if ($employee) {
                                                        $set('document_type', $employee->document_type);
                                                        $set('document_number', $employee->document_number);
                                                        $set('address', $employee->address);
                                                        $set('date_contract', $employee->date_contract);
                                                        $set('user_email', $employee->user?->email);
                                                        $set('user_is_active', $employee->user?->is_active ? 'Activo' : 'Inactivo');
                                                    } else {
                                                        $set('user_email', null);
                                                        $set('user_is_active', null);
                                                    }
                                                }
                                            }),

                                        // FIN DE SELECT DE EMPLEADO

                                    ])
                                    ->createItemButtonLabel('Agregar Empleado')
                                    ->columnSpanFull(),

                                Forms\Components\Group::make()
                                    ->relationship('visit')
                                    ->schema([
                                        // INICIO DE SELECT DE EMPLEADO
                                        Forms\Components\Select::make('quoted_by_id')
                                            //->default(fn() => Auth::user()?->employee_id)->required()
                                            ->columns(2)
                                            ->reactive()
                                            ->prefixIcon('heroicon-m-user')
                                            ->label('Cotizador') // Título para el campo 'Empleado'
                                            ->options(
                                                function (callable $get) {
                                                    return Employee::query()
                                                        ->select('id', 'first_name', 'last_name', 'document_number')
                                                        ->when($get('search'), function ($query, $search) {
                                                            $query->where('first_name', 'like', "%{$search}%")
                                                                ->orWhere('last_name', 'like', "%{$search}%")
                                                                ->orWhere('document_number', 'like', "%{$search}%");
                                                        })
                                                        ->get()
                                                        ->mapWithKeys(function ($employee) {
                                                            return [$employee->id => $employee->full_name];
                                                        })
                                                        ->toArray();
                                                }
                                            )
                                            ->searchable() // Activa la búsqueda asincrónica
                                            ->placeholder('Seleccionar un empleado') // Placeholder
                                            ->helperText('Selecciona el empleado responsable de la visita.') // Ayuda para el campo de empleado

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

                                            // Botón para ver información del empleado
                                            ->suffixAction(
                                                Forms\Components\Actions\Action::make('view_employee')
                                                    ->icon('heroicon-o-eye')
                                                    ->tooltip('Ver información del supervisor')
                                                    ->color('info')
                                                    ->action(function (callable $get) {
                                                        $employeeId = $get('employee_id');
                                                        if (!$employeeId) {
                                                            Notification::make()
                                                                ->title('Selecciona un supervisor primero')
                                                                ->warning()
                                                                ->send();
                                                            return;
                                                        }
                                                    })
                                                    ->modalContent(function (callable $get) {
                                                        $employeeId = $get('employee_id');
                                                        if (!$employeeId)
                                                            return null;

                                                        $employee = Employee::with('user')->find($employeeId);
                                                        if (!$employee)
                                                            return null;

                                                        return view('filament.components.employee-info-modal', compact('employee'));
                                                    })
                                                    ->modalHeading('Información del Supervisor')
                                                    ->modalSubmitAction(false)
                                                    ->modalCancelActionLabel('Cerrar')
                                                    ->modalWidth('2xl')
                                                    ->visible(fn(callable $get) => !empty($get('employee_id')))
                                            )
                                            ->afterStateHydrated(function (callable $get, callable $set) {
                                                $employeeId = $get('employee_id');
                                                if ($employeeId) {
                                                    $employee = Employee::with('user')->find($employeeId);
                                                    if ($employee) {
                                                        $set('document_type', $employee->document_type);
                                                        $set('document_number', $employee->document_number);
                                                        $set('address', $employee->address);
                                                        $set('date_contract', $employee->date_contract);
                                                        $set('user_email', $employee->user?->email);
                                                        $set('user_is_active', $employee->user?->is_active ? 'Activo' : 'Inactivo');
                                                    } else {
                                                        $set('user_email', null);
                                                        $set('user_is_active', null);
                                                    }
                                                }
                                            }),

                                        // FIN DE SELECT DE EMPLEADO

                                        Forms\Components\DatePicker::make('visit_date')
                                            ->label('Fecha de la visita'),

                                        Forms\Components\TimePicker::make('entry_time')
                                            ->label('Hora de ingreso'),

                                        Forms\Components\TimePicker::make('exit_time')
                                            ->label('Hora de salida'),

                                        Forms\Components\TextInput::make('amount')
                                            ->numeric()
                                            ->prefix('S/ ')
                                            ->label('Monto de la visita'),

                                        Forms\Components\Textarea::make('description')
                                            ->label('Comentarios de la visita')
                                            ->rows(2),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Datos del Servicio')
                            ->schema([
                                Forms\Components\TextInput::make('work_order_number')
                                    ->label('N° de Orden de Trabajo')
                                    ->maxLength(255),

                                Forms\Components\Grid::make(3)->schema([

                                    // 1. FECHA INICIO
                                    Forms\Components\DatePicker::make('service_start_date')
                                        ->label('Fecha de inicio del servicio')
                                        ->live() // ⚡ IMPORTANTE: Escucha cambios
                                        ->afterStateUpdated(function (Get $get, Set $set) {
                                            // Recalcular cuando cambia la fecha de inicio
                                            $start = $get('service_start_date');
                                            $end = $get('service_end_date');

                                            if ($start && $end) {
                                                $startDate = Carbon::parse($start);
                                                $endDate = Carbon::parse($end);

                                                // Evitar negativos
                                                if ($endDate->lt($startDate)) {
                                                    $set('service_days', 0);
                                                    return;
                                                }

                                                // diffInDays + 1 para incluir el día de inicio como trabajado
                                                $set('service_days', $startDate->diffInDays($endDate) + 1);
                                            }
                                        }),

                                    // 2. FECHA FIN
                                    DatePicker::make('service_end_date')
                                        ->label('Fecha de fin del servicio')
                                        ->live()
                                        ->afterStateUpdated(fn(Get $get, Set $set) => self::calculateDays($get, $set)),
                                    // 3. DÍAS (AUTOMÁTICO)
                                    Forms\Components\TextInput::make('service_days')
                                        ->label('Días de servicio')
                                        ->numeric()
                                        ->readOnly() // Bloqueado para que el usuario no lo rompa
                                        ->dehydrated() // Asegura que se envíe a la BD aunque sea ReadOnly
                                        ->suffix('días'),
                                ]),

                                Forms\Components\Select::make('task_type')
                                    ->label('Tipo de tarea')
                                    ->options([
                                        'OPEX' => 'OPEX',
                                        'CAPEX' => 'CAPEX',
                                    ]),

                                Forms\Components\Select::make('has_quote')
                                    ->label('¿Tiene cotización?')
                                    ->options([
                                        'SI' => 'SI',
                                        'NO' => 'NO',
                                    ]),

                                Forms\Components\Select::make('has_report')
                                    ->label('¿Tiene informe?')
                                    ->options([
                                        'SI' => 'SI',
                                        'NO' => 'NO',
                                    ]),

                                Forms\Components\Select::make('compliance_relation_view') // Nombre virtual único
                                    ->label('Acta de Conformidad Relacionada')
                                    ->placeholder('No se ha generado Acta para este proyecto')

                                    // 1. Cargar la opción si existe la relación
                                    ->options(function (?Project $record) {
                                        if (!$record || !$record->compliance) {
                                            return [];
                                        }
                                        // Mostramos el ID y el Estado del acta encontrada
                                        return [
                                            $record->compliance->id => "Acta #{$record->compliance->id} - Estado: {$record->compliance->state}"
                                        ];
                                    })

                                    // 2. Pre-seleccionar el valor (Hidratar)
                                    ->afterStateHydrated(function ($component, ?Project $record) {
                                        // Le asignamos al select el ID del acta relacionada
                                        $component->state($record?->compliance?->id);
                                    })

                                    // 3. Configuraciones visuales y de seguridad
                                    ->disabled()        // Bloqueado porque no puedes cambiar el acta desde aquí (es 1:1)
                                    ->dehydrated(false) // IMPORTANTE: Esto evita que Filament intente guardar este campo en la tabla 'projects'
                                    ->prefixIcon('heroicon-m-document-check')

                                    // 4. Botón de Acción para ir al Acta o Descargarla (Opcional pero muy útil)
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('view_compliance_pdf')
                                            ->icon('heroicon-o-eye')
                                            ->tooltip('Ver/Descargar PDF')
                                            ->color('success')
                                            ->url(
                                                fn(?Project $record) => $record?->compliance
                                                    ? url("/actas/{$record->compliance->id}/preview")
                                                    : null
                                            )
                                            ->openUrlInNewTab()
                                            ->visible(fn(?Project $record) => $record?->compliance !== null)
                                    ),

                            ]),

                        Forms\Components\Tabs\Tab::make('Datos de Facturación')
                            ->schema([
                                Forms\Components\Select::make('fracttal_status')
                                    ->label('Estado en Fracttal')
                                    ->options([
                                        'Sin OT' => 'Sin OT',
                                        'En Proceso' => 'En Proceso',
                                        'En Revisión' => 'En Revisión',
                                        'Finalizado' => 'Finalizado',
                                    ])
                                    ->default('Sin OT'),

                                Forms\Components\TextInput::make('purchase_order')
                                    ->label('Orden de Compra')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('migo_code')
                                    ->label('MIGO')
                                    ->maxLength(255),
                            ]),

                        Forms\Components\Tabs\Tab::make('Seguimiento')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Estado del servicio')
                                    ->options([
                                        'Pendiente' => 'Pendiente',
                                        'Enviada' => 'Enviada',
                                        'Aprobado' => 'Aprobado',
                                        'En Ejecución' => 'En Ejecución',
                                        'Completado' => 'Completado',
                                        'Facturado' => 'Facturado',
                                        'Anulado' => 'Anulado',
                                    ])
                                    ->default('Pendiente')
                                    ->live(),

                                Forms\Components\DatePicker::make('quote_sent_at')
                                    ->label('Fecha Cotización Enviada'),

                                Forms\Components\DatePicker::make('quote_approved_at')
                                    ->label('Fecha Cotización Aprobada'),

                                Forms\Components\DatePicker::make('wo_review_at')
                                    ->label('Fecha OT en Revisión')
                                    ->live(),

                                DatePicker::make('wo_completed_at')
                                    ->label('Fecha OT Finalizado')
                                    ->live() // Importante para que el cambio sea instantáneo
                                    ->afterStateUpdated(fn(Get $get, Set $set) => self::calculateDays($get, $set)),

                                TextInput::make('days_to_completion')
                                    ->label('Días desde OT Finalizado')
                                    ->readOnly()
                                    ->numeric()
                                    ->dehydrated(),

                                Forms\Components\Textarea::make('final_comments')
                                    ->label('Comentarios Finales')
                                    ->rows(2),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
    public static function calculateDays(Get $get, Set $set)
    {
        $start = $get('service_start_date');
        $end = $get('service_end_date');
        $completedAt = $get('wo_completed_at');

        // 1. Cálculo de días de servicio (Lógica que ya tenías)
        if ($start && $end) {
            $startDate = Carbon::parse($start);
            $endDate = Carbon::parse($end);

            if ($endDate->lt($startDate)) {
                Notification::make()
                    ->title('Error en fechas')
                    ->body('La fecha fin no puede ser anterior al inicio.')
                    ->warning()
                    ->send();
                $set('service_end_date', null);
                $set('service_days', 0);
            } else {
                $set('service_days', $startDate->diffInDays($endDate) + 1);
            }
        }

        // 2. Cálculo de días hasta finalización de OT (Lo nuevo)
        if ($end && $completedAt) {
            $endDate = Carbon::parse($end);
            $completedDate = Carbon::parse($completedAt);

            // diffInDays devuelve el valor absoluto, si quieres permitir negativos quita el 'true'
            // o usa un cálculo manual según tu necesidad de negocio
            $diff = $endDate->diffInDays($completedDate, false);

            $set('days_to_completion', (int) $diff);
        } else {
            $set('days_to_completion', null);
        }
    }
    public static function table(Table $table): Table
    {
        return $table

            ->columns([
                TextColumn::make('service_code')
                    ->label('Correlativo')
                    ->alignJustify()
                    ->badge()
                    ->extraAttributes(['class' => 'font-bold'])
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nombre del Proyecto')
                    ->searchable()
                    ->alignJustify()
                    ->extraAttributes(['class' => 'font-bold'])
                    ->limit(30)
                    ->tooltip(fn($record) => $record->name)
                    ->sortable(),

                TextColumn::make('subClient.client.business_name')
                    ->label('Cliente')
                    ->placeholder('No definido')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('subClient.name')
                    ->label('Tienda')
                    ->placeholder('No definido')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('visit.quotedBy.first_name')
                    ->label('Cotizador')
                    ->placeholder('No definido')
                    ->formatStateUsing(fn($record) => $record->visit?->quotedBy
                        ? $record->visit->quotedBy->first_name . ' ' . $record->visit->quotedBy->last_name
                        : null)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('visit.quotedBy', function (Builder $q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->join('visits', 'projects.id', '=', 'visits.project_id')
                            ->join('employees', 'visits.quoted_by_id', '=', 'employees.id')
                            ->orderBy('employees.first_name', $direction);
                    })
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->placeholder('No definido')
                    ->badge()
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        null, '' => 'No definido',
                        default => ucfirst($state),
                    })
                    ->color(fn(?string $state): string => match ($state) {
                        'pending', 'Pendiente' => 'warning',
                        'Enviada' => 'info',
                        'Aprobado' => 'success',
                        'En Ejecución' => 'primary',
                        'Completado', 'Facturado' => 'success',
                        'Anulado' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('service_start_date')
                    ->label('Fecha Inicio')
                    ->placeholder('No definido')
                    ->date('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('request_number')
                    ->label('N° de Solicitud')
                    ->placeholder('No definido')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('service_end_date')
                    ->label('Fecha Fin')
                    ->placeholder('No definido')
                    ->date('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('work_order_number')
                    ->label('N° de Orden de Trabajo')
                    ->placeholder('No definido')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),

                TextColumn::make('fracttal_status')
                    ->label('Estado Fracttal')
                    ->placeholder('No definido')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'Pendiente' => 'danger',
                        'En Proceso' => 'warning',
                        'Completado' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('purchase_order')
                    ->label('OC')
                    ->placeholder('No definido')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('migo_code')
                    ->label('MIGO')
                    ->placeholder('No definido')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(),

                // Columnas adicionales del modelo
                TextColumn::make('service_days')
                    ->label('Días de Servicio')
                    ->placeholder('No definido')
                    ->suffix(' días')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(),

                TextColumn::make('task_type')
                    ->label('Tipo de Tarea')
                    ->placeholder('No definido')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'OPEX' => 'info',
                        'CAPEX' => 'warning',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(),

                TextColumn::make('quote_sent_at')
                    ->label('Cotización Enviada')
                    ->placeholder('No definido')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(),

                TextColumn::make('quote_approved_at')
                    ->label('Cotización Aprobada')
                    ->placeholder('No definido')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(),

                TextColumn::make('wo_review_at')
                    ->label('OT en Revisión')
                    ->placeholder('No definido')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(),

                TextColumn::make('wo_completed_at')
                    ->label('OT Finalizado')
                    ->placeholder('No definido')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(),

                TextColumn::make('days_to_completion')
                    ->label('Días hasta Finalización')
                    ->placeholder('No definido')
                    ->suffix(' días')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filtersFormColumns(3)
            ->columnToggleFormColumns(3)

            ->filters([

                // Filtro de Cliente
                SelectFilter::make('client')
                    ->label('Cliente')
                    ->relationship('subClient.client', 'business_name')
                    ->searchable()
                    ->preload()
                    ->native(false),

                // Filtro de SubCliente (Tienda)
                SelectFilter::make('sub_client_id')
                    ->label('Tienda')
                    ->options(fn() => SubClient::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray())
                    ->searchable()
                    ->preload()
                    ->native(false),

                SelectFilter::make('fracttal_status')
                    ->label('Estado Fracttal')
                    ->native(false)
                    ->options([
                        'Pendiente' => 'Pendiente',
                        'Completado' => 'Completado',
                        'En Proceso' => 'En Proceso',
                    ]),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('service_start_date')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('serviceend_date')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['service_start_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('service_start_date', '>=', $date),
                            );
                    }),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'Enviada' => 'Enviada',
                        'Aprobado' => 'Aprobado',
                        'En Ejecución' => 'En Ejecución',
                        'Completado' => 'Completado',
                        'Facturado' => 'Facturado',
                        'Anulado' => 'Anulado',
                    ])
                    ->searchable(),
            ])
            ->actions([

                //ACA COLOCAREMOS EL REPORTE DE ACTAS DEL PROYECTO Y REPORTE DE TRABAJO DESCARGA PDF

                Tables\Actions\ActionGroup::make([
                    // 1. ACCIÓN EDITAR
                    Tables\Actions\EditAction::make()
                        ->label('Editar Registro')
                        ->color('info'),
                    Tables\Actions\Action::make('aprobar_proyecto')
                        ->label('Marcar como Aprobado')
                        ->icon('heroicon-m-check-badge')
                        ->color('success')
                        ->visible(fn($record) => !in_array(strtolower($record->status), ['aprobado', 'completado']))->requiresConfirmation()
                        ->modalHeading('¿Aprobar proyecto?')
                        ->modalDescription('¿Estás seguro de que deseas marcar este proyecto como Aprobado?')
                        ->action(function ($record) {
                            $record->status = 'Aprobado';
                            $record->save();

                            Notification::make()
                                ->title('Proyecto aprobado')
                                ->success()
                                ->body('El proyecto ha sido marcado como Aprobado.')
                                ->send();
                        }),
                    Tables\Actions\Action::make('cambiar_estado')
                        ->label('Cambiar Estado')
                        ->icon('heroicon-m-arrow-path')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('nuevo_estado')
                                ->label('Nuevo estado')
                                ->options([
                                    'Pendiente' => 'Pendiente',
                                    'Enviada' => 'Enviada',
                                    'Aprobado' => 'Aprobado',
                                    'En Ejecución' => 'En Ejecución',
                                    'Completado' => 'Completado',
                                    'Facturado' => 'Facturado',
                                    'Anulado' => 'Anulado',
                                ])
                                ->required()
                                ->default(fn($record) => $record->status),
                        ])
                        ->action(function (array $data, $record) {
                            $record->status = $data['nuevo_estado'];
                            $record->save();

                            Notification::make()
                                ->title('Estado actualizado')
                                ->success()
                                ->body('El estado del proyecto ha sido actualizado a: ' . $data['nuevo_estado'])
                                ->send();
                        }),

                    // 2. ACCIÓN DESCARGAR DOCUMENTOS (Lógica dinámica de tu primer bloque)
                    Tables\Actions\Action::make('descargar_documentos')
                        ->label(fn($record) => match (true) {
                            $record->compliance && $record->workReports()->exists() => 'Acta + Reportes (PDF)',
                            (bool) $record->compliance => 'Acta de conformidad',
                            $record->workReports()->exists() => 'Reportes de trabajo',
                            default => 'Sin documentos',
                        })
                        ->icon('heroicon-m-arrow-down-tray')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading(fn($record) => $record->compliance && $record->workReports()->exists() ? 'Descargar Acta y Reportes' : 'Confirmar descarga')
                        ->modalDescription(fn($record) => match (true) {
                            $record->compliance && $record->workReports()->exists() => 'Estás a punto de descargar el Acta junto con los Reportes en un solo PDF.',
                            (bool) $record->compliance => 'Vas a descargar solo el Acta de Conformidad.',
                            $record->workReports()->exists() => 'Vas a descargar solo los Reportes de Trabajo.',
                            default => 'No hay archivos disponibles.',
                        })
                        ->action(function ($record) {
                            $compliance = $record->compliance;
                            $hasReports = $record->workReports()->exists();

                            if ($compliance && $hasReports) {
                                return redirect()->route('actas.pdf-with-reports', $compliance->id);
                            } elseif ($compliance) {
                                return redirect()->route('actas.pdf', $compliance->id);
                            } elseif ($hasReports) {
                                return redirect()->route('work-reports.download-multiple-pdf', $record->id);
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('Sin documentos')
                                ->warning()
                                ->send();
                        })
                        ->visible(fn($record) => $record->compliance || $record->workReports()->exists())
                        ->openUrlInNewTab(),

                    // 3. ACCIÓN INFORME CONSOLIDADO (Tu segunda acción del primer bloque)
                    Tables\Actions\Action::make('pdf_report')
                        ->label('Informe Consolidado')
                        ->icon('heroicon-m-document-text')
                        ->color('info')
                        ->visible(fn($record): bool => $record->workReports()->exists())
                        ->url(fn($record): string => route('project.consolidated-report.pdf', [
                            'project' => $record->id,
                            'inline' => '1'
                        ]))
                        ->openUrlInNewTab(),

                ])
                    ->icon('heroicon-m-cog-6-tooth')
                    ->button()
                    ->label('Opciones')
                    ->color('gray')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getNavigationBadge(): ?string
    {
        // Llamamos al Scope 'allowedForUser' directamente
        return (string) static::getModel()::allowedForUser(Auth::user())->count();
    }
    public static function getRelations(): array
    {
        return [
            //

            //WorkReportsRelationManager::class,
            //TimesheetsRelationManager::class,
            //EmployeesRelationManager::class, // Relación con empleados (supervisores)
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
