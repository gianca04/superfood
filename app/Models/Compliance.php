<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Compliance extends Model
{
    use HasFactory;

    protected $table = 'compliance';

    protected $fillable = [
        'project_id',
        'assets',
        'maintenance_observations',
        'fullname_cliente',
        'document_type',
        'document_number',
        'client_signature', //firma cliente
        'employee_signature', //firma empleado
        'state'
    ];

    protected $casts = [
        'assets' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'project_id' => 'integer',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function workReports()
    {
        // Accede a los WorkReports a través del Project asociado
        return $this->hasManyThrough(
            WorkReport::class,
            Project::class,
            'id',
            'project_id',
            'project_id',
            'id'
        );
    }
}
