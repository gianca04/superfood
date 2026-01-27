<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisitResource\Pages;
use App\Filament\Resources\VisitResource\RelationManagers;
use App\Filament\Resources\VisitResource\RelationManagers\VisitPhotosRelationManager;
use App\Models\Employee;
use App\Models\Visit;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;

class VisitResource extends Resource
{
    use Translatable;
    protected static ?string $model = Visit::class;
    protected static ?string $title = 'Visitas';
    protected static ?string $modelLabel = 'Visita';
    protected static ?string $pluralModelLabel = 'Visitas';
    protected static ?string $singularModelLabel = 'Visita';
    protected static ?string $navigationGroup = 'Control de operaciones';
    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('MainTabs')
                    ->tabs([
                        // TAB DE INFORMACIÓN GENERAL
                        Tab::make('Información general')
                            ->icon('heroicon-o-information-circle')
                            ->columns(2)
                            ->schema([
                                // Proyecto
                                Forms\Components\Select::make('project_id')
                                    ->required()
                                    ->label('Proyecto')
                                    ->prefixIcon('heroicon-m-building-office-2')
                                    ->relationship('project', 'name')
                                    ->searchable(['name', 'code'])
                                    ->preload()
                                    ->helperText('Selecciona el proyecto al que pertenece esta visita')
                                    ->columnSpanFull(),

                                // Inspector
                                Forms\Components\Select::make('inspector_id')
                                    ->label('Inspector')
                                    ->prefixIcon('heroicon-m-user-circle')
                                    ->options(
                                        Employee::query()
                                            ->select('id', 'first_name', 'last_name', 'document_number')
                                            ->get()
                                            ->mapWithKeys(function ($employee) {
                                                return [$employee->id => $employee->full_name . ' (' . $employee->document_number . ')'];
                                            })
                                            ->toArray()
                                    )
                                    ->searchable()
                                    ->placeholder('Seleccionar inspector')
                                    ->helperText('Inspector responsable de la visita')
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('view_inspector')
                                            ->icon('heroicon-o-eye')
                                            ->tooltip('Ver información del inspector')
                                            ->color('info')
                                            ->modalContent(function (callable $get) {
                                                $inspectorId = $get('inspector_id');
                                                if (!$inspectorId) return null;

                                                $employee = Employee::with('user')->find($inspectorId);
                                                if (!$employee) return null;

                                                return view('filament.components.employee-info-modal', compact('employee'));
                                            })
                                            ->modalHeading('Información del Inspector')
                                            ->modalSubmitAction(false)
                                            ->modalCancelActionLabel('Cerrar')
                                            ->modalWidth('2xl')
                                            ->visible(fn(callable $get) => !empty($get('inspector_id')))
                                    ),

                                // Cotizador
                                Forms\Components\Select::make('quoted_by_id')
                                    ->label('Cotizador')
                                    ->prefixIcon('heroicon-m-calculator')
                                    ->options(
                                        Employee::query()
                                            ->select('id', 'first_name', 'last_name', 'document_number')
                                            ->get()
                                            ->mapWithKeys(function ($employee) {
                                                return [$employee->id => $employee->full_name . ' (' . $employee->document_number . ')'];
                                            })
                                            ->toArray()
                                    )
                                    ->searchable()
                                    ->placeholder('Seleccionar cotizador')
                                    ->helperText('Persona que realizó la cotización')
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('view_quoter')
                                            ->icon('heroicon-o-eye')
                                            ->tooltip('Ver información del cotizador')
                                            ->color('info')
                                            ->modalContent(function (callable $get) {
                                                $quoterId = $get('quoted_by_id');
                                                if (!$quoterId) return null;

                                                $employee = Employee::with('user')->find($quoterId);
                                                if (!$employee) return null;

                                                return view('filament.components.employee-info-modal', compact('employee'));
                                            })
                                            ->modalHeading('Información del Cotizador')
                                            ->modalSubmitAction(false)
                                            ->modalCancelActionLabel('Cerrar')
                                            ->modalWidth('2xl')
                                            ->visible(fn(callable $get) => !empty($get('quoted_by_id')))
                                    ),

                                // Fecha de visita
                                Forms\Components\DatePicker::make('visit_date')
                                    ->label('Fecha de visita')
                                    ->native(false)
                                    ->default(now())
                                    ->displayFormat('d/m/Y')
                                    ->required()
                                    ->prefixIcon('heroicon-m-calendar')
                                    ->helperText('Fecha en que se realizó la visita'),

                                // Hora de ingreso
                                Forms\Components\TimePicker::make('entry_time')
                                    ->label('Hora de ingreso')
                                    ->seconds(false)
                                    ->displayFormat('H:i')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->helperText('Hora de ingreso a la visita')
                                    ->reactive(),

                                // Hora de salida
                                Forms\Components\TimePicker::make('exit_time')
                                    ->label('Hora de salida')
                                    ->seconds(false)
                                    ->displayFormat('H:i')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->helperText('Hora de salida de la visita')
                                    ->afterStateUpdated(function ($state, $get) {
                                        $entryTime = $get('entry_time');
                                        $exitTime = $state;

                                        if (!$entryTime || !$exitTime) {
                                            return;
                                        }

                                        $entryCarbon = \Carbon\Carbon::parse($entryTime);
                                        $exitCarbon = \Carbon\Carbon::parse($exitTime);

                                        if ($exitCarbon->lessThan($entryCarbon)) {
                                            Notification::make()
                                                ->title('Error de validación')
                                                ->body('La hora de salida no puede ser anterior a la hora de ingreso.')
                                                ->danger()
                                                ->duration(5000)
                                                ->send();
                                        }
                                    }),

                                // Monto
                                Forms\Components\TextInput::make('amount')
                                    ->label('Monto (S/.)')
                                    ->numeric()
                                    ->prefix('S/ ')
                                    ->prefixIcon('heroicon-m-banknotes')
                                    ->inputMode('decimal')
                                    ->step('0.01')
                                    ->minValue(0)
                                    ->helperText('Monto en soles')
                                    ->columnSpanFull(),
                            ]),

                        // TAB DE DESCRIPCIÓN Y SUGERENCIAS
                        Tab::make('Descripción')
                            ->icon('heroicon-o-clipboard-document-check')
                            ->columns(1)
                            ->schema([
                                Forms\Components\RichEditor::make('description')
                                    ->label('Comentarios / Descripción')
                                    ->helperText('Proporciona una descripción detallada de la visita realizada.')
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
                                    ])
                                    ->columnSpanFull(),

                                Forms\Components\RichEditor::make('suggestions')
                                    ->label('Sugerencias')
                                    ->helperText('Proporciona sugerencias o recomendaciones adicionales.')
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
                                    ])
                                    ->columnSpanFull(),
                            ]),

                        // TAB DE FIRMAS (CAMPOS LEGACY - Mantener por compatibilidad)
                        Tab::make('Firmas')
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

                                SignaturePad::make('employee_signature')
                                    ->label('Firma del supervisor / técnico')
                                    ->dotSize(2.0)
                                    ->penColor('#000')
                                    ->penColorOnDark('#00f')
                                    ->lineMinWidth(0.2)
                                    ->lineMaxWidth(2.5)
                                    ->throttle(16)
                                    ->minDistance(5)
                                    ->velocityFilterWeight(0.7)
                                    ->confirmable(),
                            ])
                            ->hidden(fn($record) => $record === null), // Ocultar en creación si no se necesitan firmas
                    ])
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('project.name')
                    ->label('Proyecto')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn($record) => $record->project?->name),

                Tables\Columns\TextColumn::make('inspector.full_name')
                    ->label('Inspector')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Sin asignar'),

                Tables\Columns\TextColumn::make('quotedBy.full_name')
                    ->label('Cotizador')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Sin asignar'),

                Tables\Columns\TextColumn::make('visit_date')
                    ->label('Fecha de visita')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('entry_time')
                    ->label('Hora ingreso')
                    ->time('H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('exit_time')
                    ->label('Hora salida')
                    ->time('H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto (S/.)')
                    ->money('PEN', divideBy: 1)
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado el')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado el')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalWidth(fn() => strpos(request()->userAgent(), 'Mobile') !== false ? 'screen' : '7xl'),
                Tables\Actions\EditAction::make()
                    ->modalWidth(fn() => strpos(request()->userAgent(), 'Mobile') !== false ? 'screen' : '7xl'),
                Tables\Actions\Action::make('generate_report')
                    ->label('Generar PDF')
                    ->color('danger')
                    ->icon('heroicon-o-document')
                    ->url(fn($action) => route('visit-report.pdf', $action->getRecord()->id))
                    ->openUrlInNewTab()
                    ->visible(fn($action) => $action->getRecord()->visitPhotos()->count() > 0)
                    ->tooltip('Generar reporte PDF del trabajo realizado'),

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
            VisitPhotosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVisits::route('/'),
            'create' => Pages\CreateVisit::route('/create'),
            'view' => Pages\ViewVisit::route('/{record}'),
            'edit' => Pages\EditVisit::route('/{record}/edit'),
        ];
    }
}
