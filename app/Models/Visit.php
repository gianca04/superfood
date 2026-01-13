<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    //
    protected $fillable = [

        'project_id',      // ID del Proyecto (Vínculo principal)
        'inspector_id',      // Inspector (Nuevo)
        'quoted_by_id',      // Cotizador (Nuevo)
        'visit_date',        // Fecha visita
        'entry_time',        // Hora ingreso
        'exit_time',         // Hora salida
        'amount',            // Monto SOL
        'description',       // "

        // CAMPOS LEGACY
        'suggestions',       // Se mantiene como legacy/soporte
        'name',
        'employee_signature',
        'manager_signature',
        'tools',
        'materials',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Relación con el Inspector
    public function inspector()
    {
        return $this->belongsTo(Employee::class, 'inspector_id');
    }

    // Relación con el Cotizador
    public function quotedBy()
    {
        return $this->belongsTo(Employee::class, 'quoted_by_id');
    }

    public function visitPhotos()
    {
        return $this->hasMany(VisitPhoto::class, 'visit_id');
    }
    public function requests()
    {
        return $this->belongsToMany(Request::class, 'request_visit', 'visit_id', 'request_id');
    }
}
