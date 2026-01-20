<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Quote - Cotizaciones
 *
 * Este modelo representa las cotizaciones emitidas a los subclientes.
 * Cada cotización está asociada a un empleado cotizador, un subcliente y una categoría.
 *
 * @property int $id Identificador único de la cotización
 * @property string|null $request_number Número de solicitud de referencia
 * @property int|null $employee_id ID del empleado cotizador
 * @property int|null $sub_client_id ID del subcliente al que se le cotiza
 * @property int|null $quote_category_id ID de la categoría de cotización
 * @property string|null $energy_sci_manager Nombre del Jefe de Energía/SCI o contacto responsable
 * @property string|null $ceco Centro de Costos asociado a la cotización
 * @property string $status Estado de la cotización (POR HACER, ENVIADO, APROBADO, RECHAZADA)
 * @property \Illuminate\Support\Carbon|null $quote_date Fecha en que se emite el documento
 * @property \Illuminate\Support\Carbon|null $execution_date Fecha estimada de ejecución del servicio
 * @property \Illuminate\Support\Carbon|null $created_at Fecha de creación del registro
 * @property \Illuminate\Support\Carbon|null $updated_at Fecha de última actualización del registro
 *
 * @property-read \App\Models\Employee|null $employee Empleado cotizador asignado
 * @property-read \App\Models\SubClient|null $subClient Subcliente al que se le cotiza
 * @property-read \App\Models\QuoteCategory|null $quoteCategory Categoría de la cotización
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Project[] $projects Proyectos generados desde esta cotización
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Visit[] $visits Visitas asociadas a esta cotización
 */
class Quote extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'quotes';

    /**
     * Los atributos que pueden ser asignados masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'service_name',
        'request_number',
        'employee_id',
        'sub_client_id',
        'quote_category_id',
        'energy_sci_manager',
        'ceco',
        'status',
        'quote_date',
        'execution_date',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quote_date' => 'date',
        'execution_date' => 'date',
        'energy_sci_manager' => 'string',
        'ceco' => 'string',
        'status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtiene el empleado cotizador asignado a esta cotización.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Employee, \App\Models\Quote>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Obtiene el subcliente al que se le emite la cotización.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\SubClient, \App\Models\Quote>
     */
    public function subClient(): BelongsTo
    {
        return $this->belongsTo(SubClient::class, 'sub_client_id');
    }

    /**
     * Obtiene la categoría de la cotización.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\QuoteCategory, \App\Models\Quote>
     */
    public function quoteCategory(): BelongsTo
    {
        return $this->belongsTo(QuoteCategory::class, 'quote_category_id');
    }

    /**
     * Obtiene los proyectos generados a partir de esta cotización.
     *
     * Una cotización puede generar múltiples proyectos.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Project>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'quote_id');
    }

    /**
     * Obtiene los detalles/ítems de esta cotización.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\QuoteDetail>
     */
    public function details(): HasMany
    {
        return $this->hasMany(QuoteDetail::class, 'quote_id');
    }

    /**
     * Alias para obtener los detalles/ítems de esta cotización (compatibilidad con el controlador).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\QuoteDetail>
     */
    public function quoteDetails(): HasMany
    {
        return $this->details();
    }

    /**
     * Obtiene las visitas asociadas a esta cotización.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Visit>
     */
    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class, 'quote_id');
    }

    /**
     * Obtiene el monto total de la cotización (suma de todos los detalles).
     *
     * @return float
     */
    public function getTotalAmountAttribute(): float
    {
        // Sumamos el producto de quantity * unit_price de cada detalle
        return (float) $this->details->sum(function ($detail) {
            return $detail->quantity * $detail->unit_price;
        });
    }

    /**
     * Scope para búsqueda avanzada.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('request_number', 'LIKE', "%{$search}%")
                ->orWhereHas('subClient', function ($sq) use ($search) {
                    $sq->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('employee', function ($sq) use ($search) {
                    $sq->where('name', 'LIKE', "%{$search}%");
                });
        });
    }

    /**
     * Obtiene los detalles con relaciones cargadas.
     */
    protected $appends = ['total_amount'];
}
