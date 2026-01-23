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
        // Suponiendo que recibes un array de detalles bajo 'details'
        // y cada detalle tiene 'a_despachar' y 'quantity'
        return [
            'details' => ['required', 'array'],
            'details.*.a_despachar' => [
                'required',
                'numeric',
                'min:0',
                // El valor máximo será validado en withValidator
            ],
            'details.*.quantity' => [
                'required',
                'numeric',
                'min:0',
            ],
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
            'details.*.a_despachar.required' => 'Debes ingresar la cantidad a despachar.',
            'details.*.a_despachar.numeric' => 'La cantidad a despachar debe ser un número.',
            'details.*.a_despachar.min' => 'La cantidad a despachar no puede ser negativa.',
            'details.*.quantity.required' => 'La cantidad solicitada es obligatoria.',
            'details.*.quantity.numeric' => 'La cantidad solicitada debe ser un número.',
            'details.*.quantity.min' => 'La cantidad solicitada no puede ser negativa.',
        ];
    }
}
