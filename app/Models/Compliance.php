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
    ];

    protected $casts = [
        'assets' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
