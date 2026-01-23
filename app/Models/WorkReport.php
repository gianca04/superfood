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
        'compliance_id',
        'name',
        'supervisor_signature', //NO LOS NECESITO PERO NO LOS BORARRE
        'manager_signature', //NO LOS NECESITO PERO NO LOS BORARRE
        'suggestions',
        'tools',
        'conclusions',
        'personnel',
        'materials',
        'work_to_do',      // Trabajos a realizar
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
    public function compliance()
    {
        return $this->belongsTo(Compliance::class);
    }
    public function getSubClientAttribute()
    {
        return $this->project?->subClient;
    }

    public function photos()
    {
        return $this->hasMany(Photo::class, 'work_report_id');
    }

    /**
     * Relación: Un reporte de trabajo puede tener muchos consumos asociados.
     */
    public function projectConsumptions()
    {
        return $this->hasMany(ProjectConsumption::class, 'work_report_id');
    }
}
