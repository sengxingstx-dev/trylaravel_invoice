<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class QuotationRequest extends FormRequest
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
        if($this->isMethod('put') && $this->routeIs('edit.quotation') 
            || $this->isMethod('put') && $this->routeIs('edit.quotation.detail')
            || $this->isMethod('post') && $this->routeIs('add.quotation.detail')
            || $this->isMethod('delete') && $this->routeIs('delete.quotation')
            || $this->isMethod('delete') && $this->routeIs('delete.quotation.detail')
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
        if($this->isMethod('post') && $this->routeIs('add.quotation')) {
            return [
                'quotation_name' => [
                    'required',
                    // 'max:25',
                    'min:2',
                    Rule::unique('quotations', 'quotation_name')
                    ->ignore($this->id)
                ],
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'note' => 'nullable',
                'discount' => 'nullable|numeric',
                'tax' => 'required|numeric',
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
                'quotation_details' => 'required|array',
                'quotation_details.*.order' => [
                    'required',
                    'numeric',
                    Rule::unique('quotation_details', 'order_number')
                    ->ignore($this->id)
                ],
                'quotation_details.*.name' => 'required',
                'quotation_details.*.description' => 'required',
                'quotation_details.*.quantity' => 'required|numeric',
                'quotation_details.*.price' => 'required|numeric',
            ];
        }

        if($this->isMethod('put') && $this->routeIs('edit.quotation')) {
            return [
                'id' => [
                    'required',
                    'numeric',
                    Rule::exists('quotations', 'id')
                ],
                'quotation_name' => [
                    'required',
                    // 'max:25',
                    'min:2',
                    Rule::unique('quotations', 'quotation_name')
                    ->ignore($this->id)
                ],
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'note' => 'nullable',
                'discount' => 'nullable|numeric',
                'tax' => 'required|numeric',
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

        if($this->isMethod('put') && $this->routeIs('edit.quotation.detail')) {
            return [
                'id' => [
                    'required',
                    'numeric',
                    Rule::exists('quotation_details', 'id')
                ],
                'order' => [
                    'required',
                    'numeric',
                    Rule::unique('quotation_details', 'order_number')
                    ->ignore($this->id)
                ],
                'name' => 'required',
                'description' => 'required',
                'quantity' => 'required|numeric',
                'price' => 'required|numeric',

            ];
        }
        
        if($this->isMethod('post') && $this->routeIs('add.quotation.detail')) {
            return [
                'id' => [
                    'required',
                    'numeric',
                    Rule::exists('quotations', 'id')
                ],
                'order' => [
                    'required',
                    'numeric',
                    Rule::unique('quotation_details', 'order_number')
                    ->ignore($this->id)
                ],
                'name' => 'required',
                'description' => 'required',
                'quantity' => 'required|numeric',
                'price' => 'required|numeric',

            ];
        }
        
        if($this->isMethod('delete') && $this->routeIs('delete.quotation')) {
            return [
                'id' => [
                    'required',
                    'numeric',
                    Rule::exists('quotations', 'id')
                ]
            ];
        }

        if($this->isMethod('delete') && $this->routeIs('delete.quotation.detail')) {
            return [
                'id' => [
                    'required',
                    'numeric',
                    Rule::exists('quotation_details', 'id')
                ]
            ];
        }
    }
}
