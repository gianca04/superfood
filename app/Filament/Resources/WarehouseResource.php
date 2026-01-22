<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseResource\Pages;
use App\Filament\Resources\WarehouseResource\RelationManagers;
use App\Models\Employee;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Notification;

class WarehouseResource extends Resource
{
    protected static ?string $model = Warehouse::class;
protected static ?string $modelLabel = 'Almacén';
    protected static ?string $pluralModelLabel = 'Almacenes';

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('Nombre')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('Ubicación')
                    ->columnSpanFull(),
                Forms\Components\Select::make('manager_id')
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


                Forms\Components\Toggle::make('is_active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('manager.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
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
            'index' => Pages\ListWarehouses::route('/'),
            //'create' => Pages\CreateWarehouse::route('/create'),
            //'edit' => Pages\EditWarehouse::route('/{record}/edit'),
        ];
    }
}
