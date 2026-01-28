<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request para validar la actualización de una cotización.
 */
class UpdateQuoteRequest extends FormRequest
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
            'project_id' => 'sometimes|nullable|exists:projects,id',
            'request_number' => 'sometimes|nullable|string|max:255',
            'employee_id' => 'sometimes|nullable|exists:employees,id',
            'sub_client_id' => 'sometimes|nullable|exists:sub_clients,id',
            'quote_category_id' => 'sometimes|nullable|exists:quote_categories,id',
            'energy_sci_manager' => 'sometimes|nullable|string|max:255',
            'ceco' => 'sometimes|nullable|string|max:255',
            'project_name' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|string|in:Pendiente,Enviado,Aprobado,Anulado',
            'quote_date' => 'sometimes|nullable|date',
            'execution_date' => 'sometimes|nullable|date|after_or_equal:quote_date',
            'items.*.pricelist_id' => 'required|exists:pricelists,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.item_type' => 'required|string',
            'items.*.comment' => 'nullable|string',
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
            'request_number.max' => 'El número de solicitud no puede exceder 255 caracteres.',
            'employee_id.exists' => 'El empleado seleccionado no existe.',
            'sub_client_id.exists' => 'El subcliente seleccionado no existe.',
            'quote_category_id.exists' => 'La categoría seleccionada no existe.',
            'energy_sci_manager.max' => 'El nombre del gerente no puede exceder 255 caracteres.',
            'ceco.max' => 'El CECO no puede exceder 255 caracteres.',
            'service_name.required' => 'El nombre del servicio es obligatorio.',
            'service_name.max' => 'El nombre del servicio no puede exceder 255 caracteres.',
            'status.required' => 'El estado de la cotización es obligatorio.',
            'status.in' => 'El estado debe ser uno de: Pendiente, Enviado, Aprobado, Anulado.',
            'quote_date.date' => 'La fecha de cotización no es válida.',
            'execution_date.date' => 'La fecha de ejecución no es válida.',
            'execution_date.after_or_equal' => 'La fecha de ejecución debe ser igual o posterior a la fecha de cotización.',
            'items.array' => 'El formato de los ítems no es válido.',
            'items.*.pricelist_id.required' => 'El ítem debe tener un precio seleccionado.',
            'items.*.pricelist_id.exists' => 'El precio seleccionado para el ítem no existe.',
            'items.*.quantity.required' => 'La cantidad del ítem es obligatoria.',
            'items.*.quantity.numeric' => 'La cantidad del ítem debe ser un número.',
            'items.*.quantity.min' => 'La cantidad mínima permitida es 0.01.',
            'items.*.unit_price.required' => 'El precio unitario del ítem es obligatorio.',
            'items.*.unit_price.numeric' => 'El precio unitario debe ser un número.',
            'items.*.unit_price.min' => 'El precio unitario mínimo es 0.',
            'items.*.item_type.required' => 'El tipo de ítem es obligatorio.',
            'items.*.item_type.string' => 'El tipo de ítem debe ser texto.',
            'items.*.comment.string' => 'El comentario debe ser texto.',
        ];
    }
}
