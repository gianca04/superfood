<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeProject extends Model
{
    use HasFactory;

    protected $table = 'employee_project';

    protected $fillable = [
        'employee_id',
        'project_id',
    ];

    // Relación con Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Relación con Project
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
