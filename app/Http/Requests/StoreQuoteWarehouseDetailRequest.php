<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuoteWarehouseDetailRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'details' => ['array'], // No es requerido, pero si está presente debe ser un array
            'details.*.a_despachar' => [
                'nullable', // No es requerido
                'numeric',
                'min:0',
            ],
            'details.*.quantity' => [
                'nullable', // No es requerido
                'numeric',
                'min:0',
            ],
            'observations' => ['nullable', 'string'], // Observations no es requerido, pero si está presente debe ser texto
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $details = $this->input('details', []);
            foreach ($details as $i => $detail) {
                if (
                    isset($detail['a_despachar'], $detail['quantity']) &&
                    $detail['a_despachar'] > $detail['quantity']
                ) {
                    $validator->errors()->add(
                        "details.$i.a_despachar",
                        'El valor a despachar no puede ser mayor que la cantidad solicitada.'
                    );
                }
            }
        });
    }

    public function messages()
    {
        return [
            'details.array' => 'El campo detalles debe ser un arreglo.',
            'details.*.a_despachar.numeric' => 'La cantidad a despachar debe ser un número.',
            'details.*.a_despachar.min' => 'La cantidad a despachar no puede ser negativa.',
            'details.*.quantity.numeric' => 'La cantidad solicitada debe ser un número.',
            'details.*.quantity.min' => 'La cantidad solicitada no puede ser negativa.',
            'observations.string' => 'Las observaciones deben ser texto.',
        ];
    }
}
