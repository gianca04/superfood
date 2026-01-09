<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;

class ProjectMainInfo
{
    public static function make(): Section
    {
        return Section::make('Información del Proyecto')
            ->description('Datos generales, servicio, facturación y seguimiento')
            ->columns(3)
            ->schema([
                // === 1. DATOS GENERALES / SOLICITUD ===
                Section::make('Datos Generales')
                    ->columns(2)
                    ->columnSpan(3)
                    ->schema([
                        TextInput::make('name')
                            ->label('Descripción de la Solicitud')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        TextInput::make('service_code')
                            ->label('Código de Servicio')
                            ->maxLength(100),

                        TextInput::make('request_number')
                            ->label('N° de Solicitud')
                            ->maxLength(100),

                        DatePicker::make('start_date')
                            ->label('Fecha Solicitud')
                            ->default(now())
                            ->required(),

                        Select::make('sub_client_id')
                            ->label('Subcliente')
                            ->relationship('subClient', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Textarea::make('comment')
                            ->label('Comentario')
                            ->rows(2)
                            ->columnSpan(2),
                    ]),

                // === 2. SERVICE (EXECUTION) ===
                Section::make('Ejecución del Servicio')
                    ->columns(2)
                    ->columnSpan(3)
                    ->schema([
                        TextInput::make('work_order_number')
                            ->label('N° Orden de Trabajo')
                            ->maxLength(100),

                        DatePicker::make('service_start_date')
                            ->label('Fecha Inicio Servicio')
                            ->maxDate(fn(callable $get) => $get('service_end_date')),

                        DatePicker::make('service_end_date')
                            ->label('Fecha Fin Servicio')
                            ->minDate(fn(callable $get) => $get('service_start_date'))
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $startDate = $get('service_start_date');
                                if ($startDate && $state) {
                                    $days = \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($state));
                                    $set('service_days', $days);
                                }
                            }),

                        TextInput::make('service_days')
                            ->label('Días de Servicio')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(),

                        TextInput::make('task_type')
                            ->label('Tipo de Tarea')
                            ->maxLength(255),

                        Select::make('has_quote')
                            ->label('¿Tiene Cotización?')
                            ->options([
                                'Si' => 'Sí',
                                'No' => 'No',
                            ])
                            ->default('No'),

                        Select::make('has_report')
                            ->label('¿Tiene Informe?')
                            ->options([
                                'Si' => 'Sí',
                                'No' => 'No',
                            ])
                            ->default('No'),
                    ]),

                // === 3. BILLING ===
                Section::make('Facturación')
                    ->columns(3)
                    ->columnSpan(3)
                    ->schema([
                        Select::make('fracttal_status')
                            ->label('Estado Fracttal')
                            ->options([
                                'Pendiente' => 'Pendiente',
                                'Completado' => 'Completado',
                                'En Proceso' => 'En Proceso',
                            ])
                            ->default('Pendiente'),

                        TextInput::make('purchase_order')
                            ->label('Orden de Compra')
                            ->maxLength(100),

                        TextInput::make('migo_code')
                            ->label('Código MIGO')
                            ->maxLength(100),
                    ]),

                // === 4. TRACKING DATA ===
                Section::make('Seguimiento')
                    ->columns(2)
                    ->columnSpan(3)
                    ->schema([
                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'Pendiente' => 'Pendiente',
                                'Enviada' => 'Enviada',
                                'Aprobado' => 'Aprobado',
                                'En Ejecución' => 'En Ejecución',
                                'Completado' => 'Completado',
                                'Cancelado' => 'Cancelado',
                            ])
                            ->default('Pendiente')
                            ->required(),

                        Select::make('quote_id')
                            ->label('Cotización Asociada')
                            ->relationship('quote', 'correlative')
                            ->searchable()
                            ->preload(),

                        DatePicker::make('quote_sent_at')
                            ->label('Fecha Cotización Enviada')
                            ->displayFormat('d/m/Y'),

                        DatePicker::make('quote_approved_at')
                            ->label('Fecha Cotización Aprobada')
                            ->displayFormat('d/m/Y'),

                        DatePicker::make('wo_review_at')
                            ->label('Fecha OT en Revisión')
                            ->displayFormat('d/m/Y'),

                        DatePicker::make('wo_completed_at')
                            ->label('Fecha OT Finalizado')
                            ->displayFormat('d/m/Y'),

                        Textarea::make('final_comments')
                            ->label('Observaciones Finales')
                            ->rows(3)
                            ->columnSpan(2),
                    ]),
            ]);
    }
}
