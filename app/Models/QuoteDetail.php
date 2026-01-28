<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo QuoteDetail - Detalle de Cotización
 *
 * Este modelo representa los ítems o líneas de una cotización.
 * Cada detalle pertenece a una cotización y contiene información
 * sobre servicios, viáticos, suministros, mano de obra u otros conceptos.
 *
 * @property int $id Identificador único del ítem
 * @property int $quote_id ID de la cotización padre
 * @property int $line Orden de los ítems (1, 2, 3...)
 * @property string|null $budget_code Código del tarifario/preciario
 * @property string $item_type Tipo de ítem (SERVICIO, VIATICOS, SUMINISTRO, MANO DE OBRA, OTROS)
 * @property string|null $description Detalle del ítem
 * @property float $quantity Cantidad de unidades
 * @property float $unit_price Precio unitario
 * @property string|null $comment Notas adicionales por línea
 * @property \Illuminate\Support\Carbon|null $created_at Fecha de creación del registro
 * @property \Illuminate\Support\Carbon|null $updated_at Fecha de última actualización del registro
 *
 * @property-read float $subtotal Subtotal calculado (Cantidad * Precio_Unitario)
 * @property-read \App\Models\Quote $quote Cotización a la que pertenece este detalle
 */
class QuoteDetail extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'quote_details';

    /**
     * Los atributos que pueden ser asignados masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'quote_id',
        'pricelist_id',
        'subtotal',
        'item_type',
        'quantity',
        'unit_price',
        'comment',
        'line',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'pricelist_id' => 'integer',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Los atributos que deben ser añadidos al array/JSON del modelo.
     *
     * @var array<int, string>
     */
    protected $appends = ['subtotal'];

    /**
     * Calcula el subtotal del ítem (Cantidad * Precio Unitario).
     *
     * @return float
     */
    public function getSubtotalAttribute(): float
    {
        return round((float) $this->quantity * (float) $this->unit_price, 2);
    }

    /**
     * Obtiene la cotización a la que pertenece este detalle.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Quote, \App\Models\QuoteDetail>
     */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class, 'quote_id');
    }
    public function pricelist(): BelongsTo
    {
        return $this->belongsTo(Pricelist::class, 'pricelist_id');
    }
}
