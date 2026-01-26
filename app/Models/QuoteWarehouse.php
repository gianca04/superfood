<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo QuoteWarehouse - Cotización Almacén
 *
 * Representa el registro de atención de una cotización por parte del almacén.
 * Gestiona el estado de despacho o preparación de los materiales solicitados en una cotización.
 *
 * @property int $id Identificador único del registro
 * @property int $quote_id ID de la cotización asociada
 * @property int $user_id ID del usuario de almacén que atendió la solicitud
 * @property string $status Estado de la atención (attended, partial, pending)
 * @property \Illuminate\Support\Carbon|null $attended_at Fecha y hora en que se realizó la atención
 * @property string|null $observations Observaciones o notas adicionales sobre la atención
 * @property \Illuminate\Support\Carbon|null $created_at Fecha de creación del registro
 * @property \Illuminate\Support\Carbon|null $updated_at Fecha de última actualización del registro
 *
 * @property-read \App\Models\Quote $quote La cotización asociada
 * @property-read \App\Models\User $user El usuario que atendió la solicitud
 */
class QuoteWarehouse extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'quote_warehouse';

    /**
     * Los atributos que pueden ser asignados masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'quote_id',
        'employee_id',
        'status',
        'attended_at',
        'observations',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'attended_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtiene la cotización asociada a este registro de almacén.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class, 'quote_id');
    }

    /**
     * Obtiene el usuario de almacén que atendió la solicitud.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Calcula el progreso total de atención de los ítems relacionados.
     *
     * @return int
     */
    public function calculateProgress(): int
    {
        // Obtener todos los detalles relacionados
        $details = $this->quote->details;

        // Variables para almacenar los totales
        $totalSolicitado = 0;
        $totalAtendido = 0;

        foreach ($details as $detail) {
            $totalSolicitado += $detail->quantity;

            // Sumar las cantidades atendidas de los detalles de almacén relacionados
            $attendedQuantity = $this->details()
                ->where('quote_detail_id', $detail->id)
                ->sum('attended_quantity');

            $totalAtendido += $attendedQuantity;
        }

        // Calcular el porcentaje de progreso y redondear al entero más cercano
        return $totalSolicitado > 0 ? round(($totalAtendido / $totalSolicitado) * 100) : 0;
    }

    /**
     * Relación con los detalles de almacén.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function details()
    {
        return $this->hasMany(QuoteWarehouseDetail::class, 'quote_warehouse_id');
    }
}
