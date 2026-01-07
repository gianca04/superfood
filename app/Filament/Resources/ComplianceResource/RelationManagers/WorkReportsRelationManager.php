<?php

namespace App\Filament\Resources\ComplianceResource\RelationManagers;

use App\Filament\Resources\WorkReportResource\RelationManagers\PhotosRelationManager;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Quote;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction;
use Illuminate\Support\Facades\Auth;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;

class WorkReportsRelationManager extends RelationManager
{
    protected static ?string $title = 'Reportes de Trabajo';

    protected static ?string $modelLabel = 'Reporte de Trabajo';
    protected static ?string $pluralModelLabel = 'Reportes de Trabajo';
    protected static string $relationship = 'workReports';

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

                                // INICIO DE SELECT DE EMPLEADO
                                Forms\Components\Select::make('employee_id')
                                    ->default(fn() => Auth::user()?->employee_id)->required()
                                    ->columns(2)
                                    ->reactive()
                                    ->prefixIcon('heroicon-m-user')
                                    ->label('Supervisor / Técnico')
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
                                    ->searchable()
                                    ->placeholder('Seleccionar un empleado')
                                    ->helperText('Selecciona el empleado responsable de esta cotización.'),
                                // FIN DE SELECT DE EMPLEADO

                                // INICIO DE SELECT DE PROYECTO
                                Forms\Components\Select::make('project_id')
                                    ->prefixIcon('heroicon-m-briefcase')
                                    ->default(fn() => $this->ownerRecord->project_id)
                                    ->label('Proyecto')
                                    ->options(
                                        function (callable $get) {
                                            return Project::query()
                                                ->select('id', 'name', 'quote_id')
                                                ->when($get('search'), function ($query, $search) {
                                                    $query->where('name', 'like', "%{$search}%")
                                                        ->orWhere('quote_id', 'like', "%{$search}%");
                                                })
                                                ->get()
                                                ->mapWithKeys(function ($project) {
                                                    return [$project->id => $project->name . ' - ' . $project->quote_id];
                                                })
                                                ->toArray();
                                        }
                                    )
                                    ->searchable()
                                    ->reactive()
                                    ->helperText('Selecciona un proyecto.'),
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
                                    ->default(now())
                                    ->displayFormat('d/m/Y')
                                    ->required()
                                    ->helperText('Selecciona la fecha y hora del trabajo'),
                                // FIN DE INPUT DE FECHA

                                // INICIO DE INPUT DE HORA DE INICIO
                                Forms\Components\TimePicker::make('start_time')
                                    ->label('Hora de inicio')
                                    ->default(now()->format('H:i'))
                                    ->native(false)
                                    ->seconds(false)
                                    ->displayFormat(format: 'H:i')
                                    ->helperText('Selecciona la hora de inicio del trabajo'),
                                // FIN DE INPUT DE HORA DE INICIO

                                // INICIO DE INPUT DE HORA DE FINALIZACIÓN
                                Forms\Components\TimePicker::make('end_time')
                                    ->label('Hora de finalización')
                                    ->default(now()->format('H:i'))
                                    ->native(false)
                                    ->seconds(false)
                                    ->displayFormat(format: 'H:i')
                                    ->helperText('Selecciona la hora de finalización del trabajo')
                                    ->afterStateUpdated(function ($state, $get, $livewire) {
                                        $startTime = $get('start_time');
                                        $endTime = $state;

                                        if (!$startTime || !$endTime) {
                                            return;
                                        }

                                        $startCarbon = \Carbon\Carbon::parse($startTime);
                                        $endCarbon = \Carbon\Carbon::parse($endTime);

                                        if ($endCarbon->lessThan($startCarbon)) {
                                            Notification::make()
                                                ->title('Error de validación')
                                                ->body('La hora de finalización no puede ser anterior a la hora de inicio.')
                                                ->danger()
                                                ->duration(5000)
                                                ->send();

                                            $livewire->form->fill(['end_time' => null]);
                                        }
                                    }),
                                // FIN DE INPUT DE HORA DE FINALIZACIÓN

                            ]),

                        // FIN DE TAB DE INFORMACIÓN GENERAL

                        // INICIO TAB DESCRIPCIÓN DEL REPORTE
                        Tabs\Tab::make('Descripción')
                            ->icon('heroicon-o-clipboard-document-check')
                            ->columns(2)
                            ->schema([
                                Forms\Components\RichEditor::make('description')
                                    ->label('Descripción del reporte')
                                    ->required()
                                    ->helperText('Proporciona una descripción detallada del trabajo realizado.')
                                    ->toolbarButtons([
                                        'attachFiles',
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
                                Forms\Components\RichEditor::make('suggestions')
                                    ->label('Sugerencias')
                                    ->helperText('Proporciona sugerencias o comentarios adicionales sobre el trabajo realizado.')
                                    ->maxLength(5000)
                                    ->toolbarButtons([
                                        'attachFiles',
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
                        // FIN TAB DESCRIPCIÓN DEL REPORTE

                        // INICIO DEL TAB DE HERRAMIENTAS Y MATERIALES
                        Tabs\Tab::make('Herramientas y materiales')
                            ->icon('heroicon-o-wrench')
                            ->columns(2)
                            ->schema([
                                Forms\Components\RichEditor::make('tools')
                                    ->label('Herramientas')
                                    ->helperText('Detalla las herramientas utilizadas durante el trabajo.')
                                    ->maxLength(5000)
                                    ->toolbarButtons([
                                        'attachFiles',
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
                                Forms\Components\RichEditor::make('materials')
                                    ->label('Materiales')
                                    ->helperText('Detalla los materiales utilizados durante el trabajo.')
                                    ->maxLength(5000)
                                    ->toolbarButtons([
                                        'attachFiles',
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
                        // FIN DEL TAB DE HERRAMIENTAS Y MATERIALES

                        // INICIO DEL TAB DE LISTA DE PERSONAL
                        Tabs\Tab::make('Personal')
                            ->icon('heroicon-o-user-group')
                            ->columns(2)
                            ->schema([
                                Forms\Components\RichEditor::make('personnel')
                                    ->label('Lista de personal')
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
                            ]),
                        // FIN DL TAB DE LISTA DE PERSONAL

                        // INICIO DE TAB DE FIRMAS
                        Tabs\Tab::make('Firmas')
                            ->icon('heroicon-o-pencil-square')
                            ->columns(2)
                            ->schema([
                                SignaturePad::make('manager_signature')
                                    ->label('Firma del gerente / subgerente')
                                    ->dotSize(2.0)
                                    ->penColor('#000')
                                    ->penColorOnDark('#00f')
                                    ->lineMinWidth(0.2)
                                    ->lineMaxWidth(2.5)
                                    ->throttle(16)
                                    ->minDistance(5)
                                    ->velocityFilterWeight(0.7)
                                    ->confirmable(),
                                SignaturePad::make('supervisor_signature')
                                    ->label('Firma del Validado por supervisor / técnico')
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
                        // FIN DE TAB DE FIRMAS
                    ])->columnSpanFull()
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
                Tables\Actions\Action::make('create_work_report')
                    ->label('Crear nuevo Reporte de Trabajo')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->url(function () {
                        $projectId = $this->ownerRecord->project_id;
                        return route('filament.dashboard.resources.work-reports.create', [], false)
                            . '?project_id=' . $projectId;
                    })
                    ->openUrlInNewTab(false),
            ])
            ->actions([
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
                    ->url(fn($action) => route('evidence-report.pdf', $action->getRecord()->id))
                    ->openUrlInNewTab()
                    ->visible(fn($action) => $action->getRecord()->photos()->count() > 0)
                    ->tooltip('Generar informe PDF con evidencias fotográficas'),
            ])
            ->emptyStateHeading('No hay reportes registrados')
            ->emptyStateDescription('Comienza creando el primer reporte para esta acta de conformidad.')
            ->emptyStateIcon('heroicon-o-wrench-screwdriver')
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
