<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo para gestionar los Almacenes (Warehouses).
 *
 * @property int $id Identificador único del almacén.
 * @property string $name Nombre descriptivo del almacén.
 * @property string|null $location Dirección física o referencia de ubicación.
 * @property int $manager_id ID del empleado gestor (Foreign Key).
 * @property bool $is_active Indica si el almacén está activo.
 * @property \Illuminate\Support\Carbon|null $created_at Fecha de creación.
 * @property \Illuminate\Support\Carbon|null $updated_at Fecha de última actualización.
 * 
 * @property-read \App\Models\Employee|null $manager El empleado gestor del almacén.
 */
class Warehouse extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'location',
        'manager_id',
        'is_active',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Obtener el gestor (empleado) asociado al almacén.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }
}
