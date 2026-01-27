<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo QuoteWarehouseDetail - Detalles de Atención de Almacén
 *
 * Representa el detalle de los ítems atendidos en una solicitud de almacén.
 * Vincula el registro de atención de almacén (QuoteWarehouse) con el detalle de la cotización (QuoteDetail).
 *
 * @property int $id Identificador único del detalle
 * @property int $quote_warehouse_id ID de la atención de almacén asociada
 * @property int $quote_detail_id ID del detalle de la cotización original
 * @property float $attended_quantity Cantidad atendida por almacén para este ítem
 * @property \Illuminate\Support\Carbon|null $created_at Fecha de creación del registro
 * @property \Illuminate\Support\Carbon|null $updated_at Fecha de última actualización del registro
 *
 * @property-read \App\Models\QuoteWarehouse $quoteWarehouse La atención de almacén asociada
 * @property-read \App\Models\QuoteDetail $quoteDetail El detalle de la cotización asociado
 */
class QuoteWarehouseDetail extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'quote_warehouse_details';

    /**
     * Los atributos que pueden ser asignados masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'quote_warehouse_id',
        'quote_detail_id',
        'attended_quantity',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'attended_quantity' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtiene la atención de almacén asociada.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function quoteWarehouse(): BelongsTo
    {
        return $this->belongsTo(QuoteWarehouse::class, 'quote_warehouse_id');
    }

    /**
     * Obtiene el detalle de la cotización asociado.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function quoteDetail(): BelongsTo
    {
        return $this->belongsTo(QuoteDetail::class, 'quote_detail_id');
    }

    /**
     * Relación: Un detalle de almacén puede tener muchos consumos traza.
     */
    public function projectConsumptions()
    {
        return $this->hasMany(ProjectConsumption::class, 'quote_warehouse_detail_id');
    }

    /**
     * Scope para incluir el registro de pricelist a través de las relaciones.
     */
    public function scopeWithPricelist($query)
    {
        return $query->with('quoteDetail.pricelist');
    }
}
