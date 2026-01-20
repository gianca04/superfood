<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo QuoteCategory - Categorías de Cotización
 *
 * Este modelo representa las categorías disponibles para clasificar las cotizaciones.
 * Cada categoría agrupa tipos específicos de servicios o productos que pueden ser cotizados.
 *
 * @property int $id Identificador único de la categoría
 * @property string $name Nombre de la categoría
 * @property string|null $description Descripción breve de qué incluye esta categoría
 * @property \Illuminate\Support\Carbon|null $created_at Fecha de creación del registro
 * @property \Illuminate\Support\Carbon|null $updated_at Fecha de última actualización del registro
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Quote[] $quotes Cotizaciones asociadas a esta categoría
 */
class QuoteCategory extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'quote_categories';

    /**
     * Los atributos que pueden ser asignados masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtiene las cotizaciones asociadas a esta categoría.
     *
     * Una categoría puede tener múltiples cotizaciones asociadas.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Quote>
     */
    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }
}
