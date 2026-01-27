<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkReportResource\Pages;
use App\Filament\Resources\WorkReportResource\RelationManagers;
use App\Filament\Resources\WorkReportResource\RelationManagers\PhotosRelationManager;
use App\Models\Client;
use App\Models\Employee;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Forms\Components\Actions\Action as FormAction;
use App\Models\Project;
use Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction;
use Closure;
use Illuminate\Validation\ValidationException;
use App\Models\Quote;
use App\Models\SubClient;
use App\Models\Position;
use Illuminate\Support\Facades\Auth;
use App\Models\WorkReport;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;

class WorkReportResource extends Resource
{
    use Translatable;
    protected static ?string $modelLabel = 'Reporte de Trabajo';
    protected static ?string $pluralModelLabel = 'Reportes de Trabajo';

    protected static ?string $model = WorkReport::class;

    protected static ?string $navigationGroup = 'Control de operaciones';

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Solo Sistemas y Gerencial pueden ver este recurso
        // return $user && ($user->hasRole('Sistemas') || $user->hasRole('Gerencial'));
        return $user && ($user->hasRole('Sistemas'));
    }


    public static function form(Form $form): Form
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

                                // INICIO DE SELECT DE EMPLEADO
                                Forms\Components\Select::make('employee_id')
                                    ->default(fn() => Auth::user()?->employee_id)->required()
                                    ->columns(2)
                                    ->reactive()
                                    ->prefixIcon('heroicon-m-user')
                                    ->label('Supervisor / Técnico') // Título para el campo 'Empleado'
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
                                    ->helperText('Selecciona el empleado responsable de esta cotización.') // Ayuda para el campo de empleado

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

                                Forms\Components\Select::make('project_id')
                                    ->required()
                                    ->prefixIcon('heroicon-m-briefcase')
                                    ->default(fn() => session('project_id'))
                                    ->label('Proyecto')
                                    ->options(
                                        fn() => Project::query()
                                            // 1. Filtramos por los dos estados requeridos
                                            ->whereIn('status', ['En Ejecución'])
                                            // 2. Ordenamos por fecha de creación (descendente) para obtener los recientes primero
                                            ->latest()
                                            // 3. Formateamos para el select
                                            ->pluck('name', 'id')
                                    )

                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, callable $set) => $set('sub_client_id', null))
                                    ->helperText('Selecciona un proyecto.')
                                    ->relationship('project', 'name')
                                    // Botón para ver información del proyecto
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('view_client')
                                            ->icon('heroicon-o-eye')
                                            ->tooltip('Ver información del proyecto')
                                            ->color('info')
                                            ->action(function (callable $get) {
                                                $projectId = $get('project_id');
                                                if (!$projectId) {
                                                    Notification::make()
                                                        ->title('Selecciona un proyecto primero')
                                                        ->warning()
                                                        ->send();
                                                    return;
                                                }
                                            })
                                            ->modalContent(function (callable $get) {
                                                $projectId = $get('project_id');
                                                if (!$projectId)
                                                    return null;

                                                $project = Project::with('clients')->find($projectId);
                                                if (!$project)
                                                    return null;

                                                return view('filament.components.project-info-modal', compact('project'));
                                            })
                                            ->modalHeading('Información del Proyecto')
                                            ->modalSubmitAction(false)
                                            ->modalCancelActionLabel('Cerrar')
                                            ->modalWidth('2xl')
                                            ->visible(fn(callable $get) => !empty($get('project_id')))
                                    )

                                    ->createOptionForm([
                                        Forms\Components\Section::make('Información básica del proyecto')
                                            ->columns(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Nombre del proyecto')
                                                    ->required()
                                                    ->maxLength(255),
                                                Forms\Components\Select::make('quote_id')
                                                    ->label('Cotización')
                                                    ->searchable()
                                                    ->prefixIcon('heroicon-m-calculator')
                                                    ->options(function (callable $get) {
                                                        $search = $get('search');
                                                        $sessionQuoteId = session('quote_id');
                                                        $query = Quote::query()
                                                            ->select('quotes.id', 'quotes.correlative', 'quotes.project_description', 'sub_clients.name as sub_client_name', 'clients.business_name as client_name')
                                                            ->leftJoin('sub_clients', 'quotes.sub_client_id', '=', 'sub_clients.id')
                                                            ->leftJoin('clients', 'quotes.client_id', '=', 'clients.id')
                                                            ->when($search, function ($query) use ($search) {
                                                                $query->where('quotes.correlative', 'like', "%{$search}%")
                                                                    ->orWhere('quotes.project_description', 'like', "%{$search}%")
                                                                    ->orWhere('sub_clients.name', 'like', "%{$search}%")
                                                                    ->orWhere('clients.business_name', 'like', "%{$search}%");
                                                            })
                                                            ->limit(30);

                                                        // Si hay un quote_id en sesión y no está en los resultados, inclúyelo
                                                        if ($sessionQuoteId) {
                                                            $query->orWhere('quotes.id', $sessionQuoteId);
                                                        }

                                                        return $query->get()
                                                            ->unique('id')
                                                            ->mapWithKeys(function ($quote) {
                                                                $label = "{$quote->correlative} - {$quote->project_description} ({$quote->sub_client_name} / {$quote->client_name})";
                                                                return [$quote->id => $label];
                                                            })
                                                            ->toArray();
                                                    })
                                                    ->default(fn() => session('quote_id')),


                                                // ...existing code...
                                                Forms\Components\DatePicker::make('start_date')
                                                    ->label('Fecha de inicio')
                                                    ->default(now())
                                                    ->required()
                                                    ->maxDate(fn(callable $get) => $get('end_date')), // Valida contra end_date

                                                Forms\Components\DatePicker::make('end_date')
                                                    ->label('Fecha de finalización')
                                                    ->default(now()->addDays(30))
                                                    ->required()
                                                    ->minDate(fn(callable $get) => $get('start_date')), // Valida contra start_date
                                                // ...existing code...


                                            ]),

                                        Split::make([
                                            Section::make([
                                                Forms\Components\Select::make('client_id')
                                                    ->required()
                                                    ->prefixIcon('heroicon-m-briefcase')
                                                    ->label('Cliente') // Título para el campo 'Cliente'
                                                    ->options(
                                                        function (callable $get) {
                                                            return Client::query()
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
                                                    ->helperText('Selecciona el cliente para esta cotización.') // Ayuda para el campo de cliente

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
                                                        Forms\Components\Section::make('Información principal')
                                                            ->description('Datos generales del cliente')
                                                            ->icon('heroicon-o-identification')
                                                            ->schema([
                                                                Forms\Components\Select::make('document_type')
                                                                    ->label('Tipo de documento')
                                                                    ->options([
                                                                        'RUC' => 'RUC',
                                                                        'DNI' => 'DNI',
                                                                        'FOREIGN_CARD' => 'Carné de Extranjería',
                                                                        'PASSPORT' => 'Pasaporte',
                                                                    ])
                                                                    ->required()
                                                                    ->searchable()
                                                                    ->placeholder('Selecciona tipo de documento')
                                                                    ->columnSpan(1)
                                                                    ->prefixIcon('heroicon-o-identification'),
                                                                Forms\Components\TextInput::make('document_number')
                                                                    ->label('Número de documento')
                                                                    ->placeholder('Ej: 12345678901')
                                                                    ->required()
                                                                    ->maxLength(11)
                                                                    ->minLength(8)
                                                                    ->alphaNum()
                                                                    ->columnSpan(1)
                                                                    ->prefixIcon('heroicon-o-hashtag'),
                                                                Forms\Components\Select::make('person_type')
                                                                    ->label('Tipo de persona')
                                                                    ->options([
                                                                        'Natural Person' => 'Persona Natural',
                                                                        'Legal Entity' => 'Persona Jurídica',
                                                                    ])
                                                                    ->required()
                                                                    ->searchable()
                                                                    ->placeholder('Selecciona tipo de persona')
                                                                    ->columnSpan(1)
                                                                    ->prefixIcon('heroicon-o-user-group'),
                                                                Forms\Components\TextInput::make('business_name')
                                                                    ->label('Razón social')
                                                                    ->placeholder('Nombre de la empresa o persona')
                                                                    ->required()
                                                                    ->maxLength(255)
                                                                    ->columnSpan(2)
                                                                    ->prefixIcon('heroicon-o-building-office-2'),
                                                            ])
                                                            ->columns(2),
                                                        Forms\Components\Section::make('Contacto')
                                                            ->icon('heroicon-o-phone')
                                                            ->description('Información de contacto y dirección')
                                                            ->schema([
                                                                Forms\Components\Textarea::make('description')
                                                                    ->label('Descripción')
                                                                    ->placeholder('Descripción del cliente')
                                                                    ->columnSpanFull()
                                                                    ->rows(2)
                                                                    ->autosize(),
                                                                Forms\Components\TextInput::make('address')
                                                                    ->label('Dirección')
                                                                    ->placeholder('Dirección fiscal o comercial')
                                                                    ->maxLength(255)
                                                                    ->columnSpan(2)
                                                                    ->prefixIcon('heroicon-o-map-pin'),
                                                                Forms\Components\TextInput::make('contact_phone')
                                                                    ->label('Teléfono de contacto')
                                                                    ->placeholder('Ej: +51 999 999 999')
                                                                    ->tel()
                                                                    ->maxLength(15)
                                                                    ->columnSpan(1)
                                                                    ->prefixIcon('heroicon-o-phone'),
                                                                Forms\Components\TextInput::make('contact_email')
                                                                    ->label('Correo electrónico')
                                                                    ->placeholder('correo@ejemplo.com')
                                                                    ->email()
                                                                    ->maxLength(255)
                                                                    ->columnSpan(1)
                                                                    ->prefixIcon('heroicon-o-envelope'),
                                                            ])
                                                            ->columns(2),
                                                    ])
                                                    ->createOptionUsing(function (array $data): int {
                                                        $client = Client::create($data);
                                                        return $client->id;
                                                    })
                                                    ->createOptionAction(function (FormAction $action) {
                                                        return $action
                                                            ->modalHeading('Crear nuevo cliente')
                                                            ->modalButton('Crear cliente')
                                                            ->modalWidth('3xl');
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
                                            ]),

                                            Section::make([
                                                Forms\Components\Select::make('sub_client_id')
                                                    ->required()
                                                    ->prefixIcon('heroicon-m-home-modern')
                                                    ->label('Sede') // Título para el campo 'Sede'
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
                                                    ->helperText('Selecciona el Sede para esta cotización.') // Ayuda para el campo 'Sede'

                                                    // Cuando se carga un registro existente, seleccionar automáticamente el cliente
                                                    ->afterStateHydrated(function ($state, callable $set) {
                                                        if ($state) {
                                                            $subClient = SubClient::find($state);
                                                            if ($subClient) {
                                                                $set('client_id', $subClient->client_id);
                                                            }
                                                        }
                                                    })

                                                    // Botón para ver información de la sede
                                                    ->suffixAction(
                                                        Forms\Components\Actions\Action::make('view_sub_client')
                                                            ->icon('heroicon-o-eye')
                                                            ->tooltip('Ver información de la sede')
                                                            ->color('info')
                                                            ->action(function (callable $get) {
                                                                $subClientId = $get('sub_client_id');
                                                                if (!$subClientId) {
                                                                    Notification::make()
                                                                        ->title('Selecciona una sede primero')
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
                                                        Forms\Components\Hidden::make('client_id')
                                                            ->default(fn(callable $get) => $get('client_id')),
                                                        Forms\Components\Section::make('Información de la Sede')
                                                            ->description('Datos de la nueva sede')
                                                            ->icon('heroicon-o-building-office')
                                                            ->schema([
                                                                Forms\Components\TextInput::make('name')
                                                                    ->label('Nombre de la sede')
                                                                    ->placeholder('Ej: Sede Central, Sucursal Norte')
                                                                    ->required()
                                                                    ->maxLength(255)
                                                                    ->prefixIcon('heroicon-o-building-office-2'),
                                                                Forms\Components\Textarea::make('description')
                                                                    ->label('Descripción')
                                                                    ->placeholder('Descripción de la sede')
                                                                    ->maxLength(500)
                                                                    ->rows(2)
                                                                    ->autosize(),
                                                                Forms\Components\TextInput::make('location')
                                                                    ->label('Ubicación')
                                                                    ->placeholder('Dirección de la sede')
                                                                    ->required()
                                                                    ->maxLength(255)
                                                                    ->prefixIcon('heroicon-o-map-pin'),
                                                            ])
                                                            ->columns(1),
                                                    ])
                                                    ->createOptionUsing(function (array $data, callable $get): int {
                                                        $data['client_id'] = $get('client_id');
                                                        $subClient = SubClient::create($data);
                                                        return $subClient->id;
                                                    })
                                                    ->createOptionAction(function (FormAction $action) {
                                                        return $action
                                                            ->modalHeading('Crear nueva sede')
                                                            ->modalButton('Crear sede')
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
                                        ])
                                            ->from('md')
                                            ->columnSpanFull(),

                                        // Sección: Coordenadas geográficas
                                        Forms\Components\Section::make('Coordenadas geográficas')
                                            ->columns(1)
                                            ->collapsed()
                                            ->description('Ubicación geográfica del proyecto')
                                            ->schema([
                                                \App\Forms\Components\ubicacion::make('location')
                                                    ->label('Ubicación en el mapa')
                                                    ->helperText('Selecciona la ubicación del proyecto en el mapa o ingresa una dirección para buscar.')
                                                    ->columnSpanFull()
                                                    ->default([
                                                        'latitude' => -12.046374,
                                                        'longitude' => -77.042793,
                                                        'location' => ''
                                                    ]),
                                            ]),
                                    ])
                                    ->createOptionUsing(function (array $data): int {
                                        $project = Project::create($data);
                                        return $project->id;
                                    })
                                    ->afterStateUpdated(function (callable $get, callable $set) {
                                        $projectId = $get('project_id');
                                        if ($projectId) {
                                            // Cargar toda la información del cliente en una sola consulta
                                            $project = Project::find($projectId);
                                            if ($project) {
                                                // Actualizar los campos de 'business_name' y 'document_number' solo si hay un cliente
                                                $set('business_name', $project->business_name);
                                                $set('document_type_client', $project->document_type);
                                                $set('document_number_client', $project->document_number);
                                                $set('contact_phone', $project->contact_phone);
                                                $set('contact_email', $project->contact_email);
                                            }
                                        } else {
                                            // Limpiar los campos si no hay cliente seleccionado
                                            $set('business_name', null);
                                            $set('document_number', null);
                                        }
                                    }),
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
                                    //->default(now()->format('H:i'))
                                    
                                    ->seconds(false)
                                    ->displayFormat(format: 'H:i')
                                    ->helperText('Selecciona la hora de inicio del trabajo'),
                                // FIN DE INPUT DE HORA DE INICIO

                                // INICIO DE INPUT DE HORA DE FINALIZACIÓN
                                Forms\Components\TimePicker::make('end_time')
                                    ->label('Hora de finalización')
                                    ->default(now()->format('H:i'))
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
                                            ->placeholder('Ej: 2')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0),
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
                                            ->placeholder('Ej: 2')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0),
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

                                                // Select para cargo (visible cuando is_not_registered = false)
                                                Forms\Components\Select::make('position_id')
                                                    ->label('Cargo')
                                                    ->placeholder('Seleccionar cargo...')
                                                    ->options(fn() => Position::orderBy('name')->pluck('name', 'id'))
                                                    ->searchable()
                                                    ->preload()
                                                    ->live()
                                                    ->visible(fn(callable $get) => !$get('is_not_registered'))
                                                    ->afterStateUpdated(function ($state, callable $set) {
                                                        if ($state) {
                                                            $position = Position::find($state);
                                                            if ($position) {
                                                                $set('position_name', $position->name);
                                                            }
                                                        } else {
                                                            $set('position_name', null);
                                                        }
                                                    })
                                                    ->createOptionForm([
                                                        Forms\Components\TextInput::make('name')
                                                            ->label('Nombre del cargo')
                                                            ->required()
                                                            ->maxLength(255),
                                                    ])
                                                    ->createOptionUsing(function (array $data): int {
                                                        $position = Position::create($data);
                                                        return $position->id;
                                                    })
                                                    ->createOptionAction(function (FormAction $action) {
                                                        return $action
                                                            ->modalHeading('Crear nuevo cargo')
                                                            ->modalButton('Crear cargo')
                                                            ->modalWidth('md');
                                                    })
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
                        Tabs\Tab::make('Conclusiones')
                            ->icon('heroicon-o-check-badge')
                            ->columns(2)
                            ->schema([
                                Forms\Components\RichEditor::make('conclusions')
                                    ->label('Conclusiones')
                                    ->columnSpanFull()
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
                                    ->helperText('Proporciona sugerencias o comentarios adicionales sobre el trabajo realizado.')
                                    ->maxLength(5000)
                                    ->columnSpanFull()
                                    ->toolbarButtons([
                                        'bold',
                                        'h2',
                                        'h3',
                                        'orderedList',
                                        'bulletList',
                                        'redo',
                                        'underli ne',
                                        'undo',
                                    ]),
                            ]),
                        /*
                        // INICIO DE TAB DE FIRMAS
                        Tabs\Tab::make('Firmas')
                            ->icon('heroicon-o-pencil-square')
                            ->columns(2)
                            ->schema([
                                SignaturePad::make('manager_signature')
                                    ->label('Firma del gerente / subgerente')
                                    ->dotSize(2.0)
                                    ->penColor('#000')  // Color negro en modo claro
                                    ->penColorOnDark('#00f')  // Color azul en modo oscuro para mayor visibilidad
                                    ->lineMinWidth(0.2)
                                    ->lineMaxWidth(2.5)
                                    ->throttle(16)
                                    ->minDistance(5)
                                    ->velocityFilterWeight(0.7)
                                    ->confirmable(),
                                SignaturePad::make('supervisor_signature')
                                    ->label('Firma del Validado por supervisor / técnico')
                                    ->dotSize(2.0)
                                    ->penColor('#000')  // Color negro en modo claro
                                    ->penColorOnDark('#00f')  // Color azul en modo oscuro para mayor visibilidad
                                    ->lineMinWidth(0.2)
                                    ->lineMaxWidth(2.5)
                                    ->throttle(16)
                                    ->minDistance(5)
                                    ->velocityFilterWeight(0.7)
                                    ->confirmable(),

                            ]),
                        // FIN DE TAB DE FIRMAS
                        */
                    ])
                    ->columnSpan('full'),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre del Reporte')
                    ->searchable()
                    ->extraAttributes(['class' => 'font-bold'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('project.name')
                    ->label('Proyecto')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

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

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employee')
                    ->relationship('employee', 'first_name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Proyecto')
                    ->relationship('project', 'name')
                    ->searchable()
                    ->preload()
                    ->default(fn() => session('filter_project_id'))
                    ->placeholder('Todos los proyectos'),

                Tables\Filters\Filter::make('recent')
                    ->label('Últimas 24 horas')
                    ->query(fn(Builder $query): Builder => $query->where('created_at', '>=', now()->subDay())),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                // El ActionGroup es el que crea el menú de 3 puntitos
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->icon('heroicon-o-eye')
                        ->color('info'),

                    Tables\Actions\EditAction::make()
                        ->icon('heroicon-o-pencil-square')
                        ->color('primary'),

                    Tables\Actions\DeleteAction::make()
                        ->icon('heroicon-o-trash')
                        ->color('danger'),

                    RelationManagerAction::make('photos-relation-manager')
                        ->label('Ver fotografías')
                        ->slideOver(true)
                        ->relationManager(PhotosRelationManager::make()),

                    Tables\Actions\Action::make('generate_evidence_report')
                        ->label('Informe de Evidencias')
                        ->color('danger')
                        ->icon('heroicon-o-camera')
                        ->url(fn($record) => route('evidence-report.pdf', $record->id))
                        ->openUrlInNewTab()
                        ->visible(fn($record) => $record->photos()->count() > 0)
                        ->tooltip('Generar informe PDF con evidencias fotográficas'),

                    /*Tables\Actions\Action::make('preview_work_report')
                        ->label('Previsualizar PDF')
                        ->color('info')
                        ->icon('heroicon-o-eye')
                        ->url(fn($record) => route('work-report.preview', $record->id))
                        ->openUrlInNewTab()
                        ->tooltip('Previsualizar reporte de trabajo en PDF'),
                    */
                    Tables\Actions\Action::make('download_work_report_pdf')
                        ->label('Descargar PDF')
                        ->color('primary')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn($record) => route('work-report.pdf', $record->id))
                        ->openUrlInNewTab()
                        ->tooltip('Descargar reporte de trabajo en PDF'),

                    /*Tables\Actions\Action::make('generate_pdf_excel')
                        ->label('PDF desde Excel')
                        ->color('warning')
                        ->icon('heroicon-o-document-text')
                        ->url(fn($record) => route('work-report.pdf-excel', $record->id))
                        ->openUrlInNewTab()
                        ->tooltip('Generar PDF desde plantilla Excel'),
                    */

                    Tables\Actions\Action::make('generate_excel')
                        ->label('Generar Excel')
                        ->color('success')
                        ->icon('heroicon-o-document-arrow-down')
                        ->url(fn($record) => route('work-report.xls', $record->id))
                        ->openUrlInNewTab()
                        ->tooltip('Generar reporte Excel'),
                ])
                    ->icon('heroicon-m-ellipsis-vertical') // Aquí defines que sea el icono de 3 puntos
                    ->tooltip('Opciones')
            ])
            ->headerActions([
                Tables\Actions\Action::make('back_to_project')
                    ->label('Volver al Proyecto')
                    ->icon('heroicon-o-arrow-left')
                    ->color('gray')
                    ->visible(fn() => session()->has('project_id'))
                    ->action(function () {
                        $projectId = session('project_id');
                        if ($projectId) {
                            // Limpiar la sesión
                            session()->forget('project_id');
                            return redirect(route('filament.dashboard.resources.projects.edit', $projectId));
                        }
                    }),
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
            RelationManagers\PhotosRelationManager::class,
        ];
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkReports::route('/'),
            'create' => Pages\CreateWorkReport::route('/create'),
            'view' => Pages\ViewWorkReport::route('/{record}'),
            'edit' => Pages\EditWorkReport::route('/{record}/edit'),
        ];
    }
}
