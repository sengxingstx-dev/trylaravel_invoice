<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth('api')->user()->hasRole('superadmin|admin'); 
    }

    public function prepareForValidation() {
        if($this->isMethod('put') && $this->routeIs('edit.invoice') 
            || $this->isMethod('put') && $this->routeIs('edit.invoice.detail')
            || $this->isMethod('post') && $this->routeIs('add.invoice.detail')
            || $this->isMethod('delete') && $this->routeIs('delete.invoice')
            || $this->isMethod('delete') && $this->routeIs('delete.invoice.detail')
        ) {  
            $this->merge([
                'id' => $this->route()->parameters['id']
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        /** add invoice */
        if($this->isMethod('post') && $this->routeIs('add.invoice')) {
            return [
                'invoice_name' => [
                    'required',
                    // 'max:25',
                    'min:2',
                    Rule::unique('invoices', 'invoice_name')
                    ->ignore($this->id)
                ],
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'note' => 'nullable',
                'discount' => 'nullable|numeric',
                'tax' => 'required|numeric',
                'status' => [
                    'required',
                    Rule::in(['created', 'pending', 'paid', 'canceled'])
                ],
                'company_id' => [
                    'required',
                    'numeric',
                    Rule::exists('companies', 'id')
                ],
                'currency_id' => [
                    'required',
                    'numeric',
                    Rule::exists('currencies', 'id')
                ],
                'invoice_details' => 'required|array',
                'invoice_details.*.order' => [
                    'required',
                    'numeric',
                    Rule::unique('invoice_details', 'order_number')
                    ->ignore($this->id)
                ],
                'invoice_details.*.name' => 'required',
                'invoice_details.*.description' => 'required',
                'invoice_details.*.quantity' => 'required|numeric',
                'invoice_details.*.price' => 'required|numeric',
            ];
        }

        /** edit invoice */
        if($this->isMethod('put') && $this->routeIs('edit.invoice')) {
            return [
                'id' => [
                    'required',
                    'numeric',
                    Rule::exists('invoices', 'id')
                ],
                'invoice_name' => [
                    'required',
                    // 'max:25',
                    'min:2',
                    Rule::unique('invoices', 'invoice_name')
                    ->ignore($this->id)
                ],
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'note' => 'nullable',
                'discount' => 'nullable|numeric',
                'tax' => 'required|numeric',
                'status' => [
                    'required',
                    Rule::in(['created', 'pending', 'paid', 'canceled'])
                ],
                'company_id' => [
                    'required',
                    'numeric',
                    Rule::exists('companies', 'id')
                ],
                'currency_id' => [
                    'required',
                    'numeric',
                    Rule::exists('currencies', 'id')
                ]
            ];
        }

        /** delete invoice */
        if($this->isMethod('delete') && $this->routeIs('delete.invoice')) {
            return [
                'id' => [
                    'required',
                    'numeric',
                    Rule::exists('invoices', 'id')
                ]
            ];
        }

        /** Invoice Degail */
        // add invoice detail
        if($this->isMethod('post') && $this->routeIs('add.invoice.detail')) {
            return [
                'id' => [
                    'required',
                    'numeric',
                    Rule::exists('invoices', 'id')
                ],
                'order' => [
                    'required',
                    'numeric',
                    Rule::unique('invoice_details', 'order_number')
                    ->ignore($this->id)
                ],
                'name' => 'required',
                'description' => 'required',
                'quantity' => 'required|numeric',
                'price' => 'required|numeric',

            ];
        }

        /** edit invoice detail */
        if($this->isMethod('put') && $this->routeIs('edit.invoice.detail')) {
            return [
                'id' => [
                    'required',
                    'numeric',
                    Rule::exists('invoice_details', 'id')
                ],
                'order' => [
                    'required',
                    'numeric',
                    Rule::unique('invoice_details', 'order_number')
                    ->ignore($this->id)
                ],
                'name' => 'required',
                'description' => 'required',
                'quantity' => 'required|numeric',
                'price' => 'required|numeric',

            ];
        }

        /** delete invoice detail */
        if($this->isMethod('delete') && $this->routeIs('delete.invoice.detail')) {
            return [
                'id' => [
                    'required',
                    'numeric',
                    Rule::exists('invoice_details', 'id')
                ]
            ];
        }
    }
}
