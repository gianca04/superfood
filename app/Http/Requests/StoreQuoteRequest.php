<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request para validar la creación de una cotización.
 */
class StoreQuoteRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Ajustar según la lógica de autorización necesaria
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'request_number' => 'nullable|string|max:255',
            'employee_id' => 'nullable|exists:employees,id',
            'sub_client_id' => 'nullable|exists:sub_clients,id',
            'quote_category_id' => 'nullable|exists:quote_categories,id',
            'energy_sci_manager' => 'nullable|string|max:255',
            'ceco' => 'nullable|string|max:255',
            'service_name' => 'nullable|string|max:255',
            'client_name' => 'nullable|string|max:255',
            'sub_client_name' => 'nullable|string|max:255',
            'quote_date' => 'nullable|date',
            'execution_date' => 'nullable|date',
            // status se establece automáticamente como 'POR HACER'
        ];
    }

    /**
     * Obtiene los mensajes de error personalizados para las reglas de validación.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'El estado de la cotización es obligatorio.',
            'status.in' => 'El estado debe ser uno de: POR HACER, ENVIADO, APROBADO, RECHAZADA.',
            'execution_date.after_or_equal' => 'La fecha de ejecución debe ser igual o posterior a la fecha de cotización.',
        ];
    }
}
