<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Model Project
 *
 * Represents a project entity with location and scheduling data.
 */
class Project extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        // 1. DATOS GENERALES / SOLICITUD
        'name',             // Descripción de la solicitud
        'service_code',     // Código de Servicio Correlativo
        'request_number',   // N° de Solicitud
        'service_start_date',       // Fecha solicitud
        'sub_client_id',    // Cliente (ID)
        'location',         // Tienda (JSON)
        'comment',          // Comentario

        // 2. SERVICE (EXECUTION)
        'work_order_number',    // Antes: ot
        'service_start_date',   // Antes: fecha_inicio_servicio
        'service_end_date',     // Antes: fecha_fin_servicio
        'service_days',         // Antes: dias
        'task_type',            // Antes: tarea
        'has_quote',            // Antes: cotizacion
        'has_report',           // Antes: informe

        //3. BILLING
        'fracttal_status', // Antes: fracttal
        'purchase_order', // Antes: orden_compra
        'migo_code', // Antes: migo

        //4. TRACKING DATA
        'status',             // estado: Pendiente, Enviada, Aprobado...
        'quote_sent_at',      // fecha_cot_enviada
        'quote_approved_at',  // fecha_cot_aprobada
        'wo_review_at',       // fecha_ot_revision
        'wo_completed_at',    // fecha_ot_finalizado
        'days_to_completion', // dias_hasta_finalizacion
        'final_comments',     // comentario_observación: Observaciones finales

        // Campos legacy para compatibilidad
        'latitude',
        'longitude',
        'quote_id',
        'end_date',

        //Supervisor string:
        'supervisor_name',
    ];

    protected $casts = [
        // 1. DATOS GENERALES
        'start_date' => 'date',
        'end_date' => 'date',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'location' => 'array',
        'sub_client_id' => 'integer',

        // 2. SERVICE (EXECUTION)
        'service_start_date' => 'date',
        'service_end_date' => 'date',
        'service_days' => 'integer',
        'has_quote' => 'string',
        'has_report' => 'string',

        'fracttal_status' => 'string',

        'status' => 'string',

        // 4. TRACKING DATA (Fechas y Números)
        'quote_sent_at' => 'datetime',
        'quote_approved_at' => 'datetime',
        'wo_review_at' => 'datetime',
        'wo_completed_at' => 'datetime',
        'days_to_completion' => 'integer',
        // Otros
        'quote_id' => 'integer',
        // Se eliminaron tools, personnel y materials por no estar en fillable
    ];

    public function visit()
    {
        return $this->hasOne(Visit::class);
    }
    // Simplificación de relación para evitar duplicidad
    public function workReports()
    {
        // Si es una relación directa de muchos:
        return $this->hasMany(WorkReport::class, 'project_id');

        // O si es Muchos a Muchos (como sugería tu método Work_reports):
        // return $this->belongsToMany(WorkReport::class, 'work_report_project')->withTimestamps();
    }
    // En tu modelo Project
    public function compliance()
    {
        // Relación 1 a 1: Un proyecto tiene un (o ningún) acta de conformidad
        return $this->hasOne(Compliance::class, 'project_id');
    }

    public function quotes()
    {
        return $this->hasMany(\App\Models\Quote::class, 'project_id');
    }

    public function latestQuote(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Quote::class, 'project_id')->latestOfMany();
    }
    /**
     * The attributes that should be cast to native types.
     *
     * This ensures proper handling of date and decimal types.
     *
     * @var array<string, string>
     */


    public function scopeAllowedForUser(Builder $query, ?User $user = null): Builder
    {
        /** @var \App\Models\User $user */
        $user = $user ?? Auth::user();

        // 1. Seguridad base: Si no hay usuario, nadie ve nada.
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        // 2. SUPER USUARIOS (Usando Spatie hasRole)
        // Pasa un array con los NOMBRES de los roles que ven todo.
        // 'super_admin' es el defecto de Shield, agrega tu rol de gerencia aquí.
        if ($user->hasRole(['Administrador', 'Gerencial'])) {
            return $query;
        }

        // 3. RESTO DE USUARIOS (Inspectores, etc.)
        $employeeId = $user->employee_id;

        if (!$employeeId) {
            return $query->whereRaw('1 = 0');
        }

        // Filtramos por la asignación en la tabla pivote
        return $query->whereHas('inspectors', function (Builder $q) use ($employeeId) {
            $q->where('employee_id', $employeeId);
        });
    }
    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_project');
    }

    public function attendances()
    {
        return $this->hasManyThrough(Attendance::class, Timesheet::class);
    }

    public function timesheets()
    {
        return $this->hasMany(Timesheet::class);
    }

    /**
     * Get the latitude from the location JSON field
     */
    /**
     * Relación: Un proyecto pertenece a un subcliente.
     */
    public function subClient()
    {
        return $this->belongsTo(SubClient::class, 'sub_client_id');
    }

    /**
     * Relación: Un proyecto pertenece a un cliente (a través de la cotización).
     */
    public function client()
    {
        return $this->hasOneThrough(Client::class, Quote::class, 'id', 'id', 'quote_id', 'client_id');
    }

    /**
     * Relación: Un proyecto tiene muchos reportes de trabajo.
     */


    /**
     * Relación: Un proyecto tiene muchas fotos a través de los reportes de trabajo.
     */
    public function photos()
    {
        return $this->hasManyThrough(Photo::class, WorkReport::class, 'project_id', 'work_report_id');
    }

    /**
     * Relación: Un proyecto tiene muchos empleados a través de timesheets.
     */
    public function timesheetEmployees()
    {
        return $this->belongsToMany(Employee::class, 'timesheets');
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_project')
            ->withTimestamps();
        // Si usas el modelo pivote personalizado:
        // ->using(EmployeeProject::class);
    }

    public function inspectors()
    {
        // Esto apunta al modelo intermedio EmployeeProject
        return $this->hasMany(EmployeeProject::class, 'project_id');
    }
    public function supervisors()
    {
        return $this->belongsToMany(Employee::class, 'employee_project');
    }



    public function getLocationLatitudeAttribute()
    {
        if (!$this->location || !is_array($this->location))
            return null;
        return $this->location['latitude'] ?? null;
    }

    /**
     * Get the longitude from the location JSON field
     */
    public function getLocationLongitudeAttribute()
    {
        if (!$this->location || !is_array($this->location))
            return null;
        return $this->location['longitude'] ?? null;
    }

    public function Work_reports()
    {
        return $this->belongsToMany(WorkReport::class, 'work_report_project')
            ->withTimestamps();
    }

    /**
     * Get the address from the location JSON field
     */
    public function getLocationAddressAttribute()
    {
        if (!$this->location || !is_array($this->location))
            return null;
        return $this->location['location'] ?? null;
    }

    /**
     * Get formatted coordinates as string
     */
    public function getCoordinatesAttribute()
    {
        $lat = $this->location_latitude;
        $lng = $this->location_longitude;

        if ($lat && $lng) {
            return sprintf('%.6f, %.6f', $lat, $lng);
        }

        return null;
    }

    /**
     * Check if project is currently active (within date range)
     */
    public function getIsActiveAttribute()
    {
        $now = now()->toDateString();
        return $this->start_date <= $now && $this->end_date >= $now;
    }
}
