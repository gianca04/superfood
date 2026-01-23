<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo ProjectConsumption
 *
 * Representa el consumo de materiales en un proyecto.
 *
 * @property int $id Identificador único
 * @property int $project_id ID del proyecto asociado
 * @property int $quote_warehouse_detail_id ID del detalle de almacén (trazabilidad)
 * @property int|null $work_report_id ID del reporte de trabajo (opcional)
 * @property float $quantity Cantidad realmente instalada/gastada
 * @property \Illuminate\Support\Carbon $consumed_at Fecha del consumo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Project $project
 * @property-read \App\Models\QuoteWarehouseDetail $quoteWarehouseDetail
 * @property-read \App\Models\WorkReport|null $workReport
 */
class ProjectConsumption extends Model
{
    use HasFactory;

    protected $table = 'project_consumptions';

    protected $fillable = [
        'project_id',
        'quote_warehouse_detail_id',
        'work_report_id',
        'quantity',
        'consumed_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'consumed_at' => 'date',
        'work_report_id' => 'integer',
        'project_id' => 'integer',
        'quote_warehouse_detail_id' => 'integer',
    ];

    /**
     * Relación: Un consumo pertenece a un proyecto.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Relación: Un consumo pertenece a un detalle de almacén.
     */
    public function quoteWarehouseDetail(): BelongsTo
    {
        return $this->belongsTo(QuoteWarehouseDetail::class, 'quote_warehouse_detail_id');
    }

    /**
     * Relación: Un consumo puede pertenecer a un reporte de trabajo.
     */
    public function workReport(): BelongsTo
    {
        return $this->belongsTo(WorkReport::class);
    }
}
