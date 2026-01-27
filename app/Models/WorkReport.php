<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

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
        'materials', // Asegúrate de que esté aquí
        'work_to_do',      // Trabajos a realizar
        'start_time',  // Hora de inicio del trabajo
        'end_time',    // Hora de finalizacin del trabajo
        'report_date',  // Fecha del reporte (solo fecha)
    ];

    protected $casts = [
        'personnel' => 'array',
        'materials' => 'array', // Asegúrate de que esté aquí
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
     * Relación: Un reporte de trabajo puede tener muchos consumos de proyecto.
     */
    public function projectConsumptions(): HasMany
    {
        return $this->hasMany(ProjectConsumption::class);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($workReport) {
            // Eliminar en cascada los consumos asociados
            $workReport->projectConsumptions()->delete();
        });
    }

    public function getAvailableMaterials()
    {
        // Si el reporte no tiene proyecto, devolvemos colección vacía
        if (!$this->project_id) {
            return collect();
        }
        return DB::table('quote_warehouse_details as qwd')
            ->join('quote_warehouse as qw', 'qwd.quote_warehouse_id', '=', 'qw.id')
            ->join('quotes as q', 'qw.quote_id', '=', 'q.id')
            ->join('quote_details as qd', 'qwd.quote_detail_id', '=', 'qd.id')
            ->join('pricelists as p', 'qd.pricelist_id', '=', 'p.id')
            ->join('units as u', 'p.unit_id', '=', 'u.id')
            ->where('q.project_id', $this->project_id)
            ->where('qd.item_type', 'SUMINISTRO')
            ->where('qwd.attended_quantity', '>', 0)
            ->select([
                'qwd.id',
                'p.sat_description',
                'p.sat_line',
                'u.name as unit_name',
                'qwd.attended_quantity'
            ])
            ->get();
    }
}
