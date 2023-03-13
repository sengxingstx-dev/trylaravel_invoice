<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Quotation;
use App\Models\QuotationDetail;

class CalculateService
{
    /** calculate total */
    public function calculateTotal($request, $sumSubTotal, $id)
    {
        // return 'data here';

        /** calculate the price */
        $calculateTax = $sumSubTotal * $request['tax'] / 100;
        $calculateDiscount = $sumSubTotal * $request['discount'] / 100;
        $sumTotal = ($sumSubTotal - $calculateDiscount) + $calculateTax;

        $addQuotation = Quotation::find($id);
        $addQuotation->sub_total = $sumSubTotal;
        $addQuotation->total = $sumTotal;
        $addQuotation->save();
    }

    public function calculateUpdateTotal($request)
    {
        /** update the total price */
        $sumSubTotal = QuotationDetail::where('quotation_id', $request['id'])->get()->sum('total');
        $calculateTax = $sumSubTotal * $request['tax'] / 100;
        $calculateDiscount = $sumSubTotal * $request['discount'] / 100;
        $sumTotal = ($sumSubTotal - $calculateDiscount) + $calculateTax;

        $editQuotation = Quotation::find($request['id']);
        $editQuotation->sub_total = $sumSubTotal;
        $editQuotation->total = $sumTotal;
        $editQuotation->save();
    }

    public function calculateInvoiceTotal($request, $sumSubTotal, $id)
    {
        // return 'calculate';

        /** calculate the price */
        $calculateTax = $sumSubTotal * $request['tax'] / 100;
        $calculateDiscount = $sumSubTotal * $request['discount'] / 100;
        $sumTotal = ($sumSubTotal - $calculateDiscount) + $calculateTax;

        $addInvoice = Invoice::find($id);
        $addInvoice->sub_total = $sumSubTotal;
        $addInvoice->total = $sumTotal;
        $addInvoice->save();
    }

    public function calculateUpdateInvoiceTotal($request)
    {
        // return 'calculate updated';

        /** update the total price */
        $sumSubTotal = InvoiceDetail::where('invoice_id', $request['id'])->get()->sum('total');
        $calculateTax = $sumSubTotal * $request['tax'] / 100;
        $calculateDiscount = $sumSubTotal * $request['discount'] / 100;
        $sumTotal = ($sumSubTotal - $calculateDiscount) + $calculateTax;

        $editInvoice = Invoice::find($request['id']);
        $editInvoice->sub_total = $sumSubTotal;
        $editInvoice->total = $sumTotal;
        $editInvoice->save();
    }
}