<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkReport extends Model
{
    use HasFactory;
    protected $table = 'work_reports';

    protected $fillable = [
        'employee_id',
        'project_id',
        'name',
        'supervisor_signature',
        'manager_signature',
        'suggestions',
        'tools',
        'personnel',
        'materials',
        'work_to_do',      // Trabajos a realizar
        'work_done',       // Trabajos realizados
        'start_time',  // Hora de inicio del trabajo
        'end_time',    // Hora de finalizacin del trabajo
        'report_date',  // Fecha del reporte (solo fecha)
    ];

    protected $casts = [
        'personnel' => 'array',
        'materials' => 'array',
        'tools' => 'array',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'report_date' => 'date',
    ];

    /**
     * Relacin: Un reporte de trabajo pertenece a un empleado.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Relacin: Un reporte de trabajo pertenece a un proyecto.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function getSubClientAttribute()
    {
        return $this->project?->subClient;
    }

    public function photos()
    {
        return $this->hasMany(Photo::class, 'work_report_id');
    }
}
