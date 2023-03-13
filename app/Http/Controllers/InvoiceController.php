<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Currency;
use App\Helpers\AppHelper;
use App\Traits\ResponseAPI;
use Illuminate\Http\Request;
use App\Models\InvoiceDetail;
use App\Services\CalculateService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\InvoiceRequest;

class InvoiceController extends Controller
{
    use ResponseAPI;

    public $calculateService;


    public function __construct(CalculateService $calculateService)
    {
        $this->calculateService = $calculateService;
    }

    public function listInvoices() 
    {
        // return 'data';

        $items = Invoice::orderBy('id', 'desc')->get();
        $items->map(function($item) {
            $item['countDetail'] = InvoiceDetail::where('invoice_id', $item['id'])->get()->count();
            $item['company'] = Company::where('id', $item['company_id'])->first();
            $item['currency'] = Currency::where('id', $item['currency_id'])->first();
            $item['user'] = User::where('id', $item['created_by'])->first();
        });

        return response()->json([
            'quotations' => $items,
        ]);
    }
    
    public function listInvoiceDetail($id) 
    {
        // return 'data';

        $item = Invoice::orderBy('id', 'desc')->where('id', $id)->first();
        $item['countDetail'] = InvoiceDetail::where('invoice_id', $item['id'])->get()->count();
        $item['company'] = Company::where('id', $item['company_id'])->first();
        $item['currency'] = Currency::where('id', $item['currency_id'])->first();
        $item['user'] = User::where('id', $item['created_by'])->first();

        $details = InvoiceDetail::where('invoice_id', $id)->get();

        return response()->json([
            'quotation' => $item,
            'details' => $details,
        ]);
    }

    public function addInvoice(InvoiceRequest $request) 
    {
        // return $request->all();

        DB::beginTransaction();

            $addInvoice = new Invoice();
            $addInvoice->invoice_number = AppHelper::generateInvoiceNumber('INV-', 6);
            $addInvoice->invoice_name = $request['invoice_name'];
            $addInvoice->start_date = $request['start_date'];
            $addInvoice->end_date = $request['end_date'];
            $addInvoice->note = $request['note'];
            $addInvoice->discount = $request['discount'];
            $addInvoice->tax = $request['tax'];
            $addInvoice->status = $request['status'];
            $addInvoice->company_id = $request['company_id'];
            $addInvoice->currency_id = $request['currency_id'];
            $addInvoice->created_by = Auth::user('api')->id;
            $addInvoice->save();

            $sumSubTotal = 0;

            /** add invoice details */
            if(!empty($request['invoice_details'])) {

                foreach($request['invoice_details'] as $item) {

                    // return $item;

                    $addInvoiceDetail = new InvoiceDetail();
                    $addInvoiceDetail->order_number = $item['order'];
                    $addInvoiceDetail->name = $item['name'];
                    $addInvoiceDetail->description = $item['description'];
                    $addInvoiceDetail->quantity = $item['quantity'];
                    $addInvoiceDetail->price = $item['price'];
                    $addInvoiceDetail->total = $item['quantity'] * $item['price'];

                    $addInvoiceDetail->invoice_id = $addInvoice['id'];
                    $addInvoiceDetail->save();

                    $sumSubTotal += $item['quantity'] * $item['price'];
                }
            }

            /** calculate the price */
            $this->calculateService->calculateInvoiceTotal($request, $sumSubTotal, $addInvoice['id']);

        DB::commit();

        return response()->json([
            'success' => true,
            'msg' => 'Successfully added'
        ]);
    }

    public function editInvoice(InvoiceRequest $request)
    {
        // return $request->all();

        $editInvoice = Invoice::find($request['id']);
        $editInvoice->invoice_name = $request['invoice_name'];
        $editInvoice->start_date = $request['start_date'];
        $editInvoice->end_date = $request['end_date'];
        $editInvoice->note = $request['note'];
        $editInvoice->discount = $request['discount'];
        $editInvoice->tax = $request['tax'];
        $editInvoice->status = $request['status'];
        $editInvoice->company_id = $request['company_id'];
        $editInvoice->currency_id = $request['currency_id'];
        $editInvoice->created_by = Auth::user('api')->id;
        $editInvoice->save();
        
        $this->calculateService->calculateUpdateInvoiceTotal($request);

        return response()->json([
            'success' => true,
            'msg' => 'Successfully edited'
        ]);
    }

    public function deleteInvoice(InvoiceRequest $request)
    {
        // return 'deleted';

        $deleteInvoice = Invoice::find($request['id']);
        $deleteInvoice->delete();

        return response()->json([
            'success' => true,
            'msg' => 'Successfully deleted'
        ]);
    }

    /** Invoice Detail */
    public function addInvoiceDetail(InvoiceRequest $request) 
    {
        // return $request->all();

        $addInvoiceDetail = new InvoiceDetail();
        $addInvoiceDetail->order_number = $request['order'];
        $addInvoiceDetail->invoice_id = $request['id'];
        $addInvoiceDetail->name = $request['name'];
        $addInvoiceDetail->description = $request['description'];
        $addInvoiceDetail->quantity = $request['quantity'];
        $addInvoiceDetail->price = $request['price'];
        $addInvoiceDetail->total = $request['quantity'] * $request['price'];
        $addInvoiceDetail->save();

        /** update the total price */
        $addInvoice = Invoice::find($request['id']);
        $sumSubTotal = InvoiceDetail::where('invoice_id', $request['id'])->get()->sum('total');

        $calculateTax = $sumSubTotal * $addInvoice['tax'] / 100;
        $calculateDiscount = $sumSubTotal * $addInvoice['discount'] / 100;
        $sumTotal = ($sumSubTotal - $calculateDiscount) + $calculateTax;

        $addInvoice->sub_total = $sumSubTotal;
        $addInvoice->total = $sumTotal;
        $addInvoice->save();
        
        return response()->json([
            'success' => true,
            'msg' => 'Successfully added'
        ]);
    }

    /** edit invoice detail */
    public function editInvoiceDetail(InvoiceRequest $request) 
    {
        // return 'added';

        $editInvoiceDetail = InvoiceDetail::find($request['id']);
        $editInvoiceDetail->order_number = $request['order'];
        $editInvoiceDetail->name = $request['name'];
        $editInvoiceDetail->description = $request['description'];
        $editInvoiceDetail->quantity = $request['quantity'];
        $editInvoiceDetail->price = $request['price'];
        $editInvoiceDetail->total = $request['quantity'] * $request['price'];
        $editInvoiceDetail->save();

        /** update the total price */
        $editInvoice = Invoice::find($editInvoiceDetail['invoice_id']);

        // $this->calculateService->calculateUpdateInvoiceTotal($editInvoice);

        $sumSubTotal = InvoiceDetail::where('invoice_id', $editInvoiceDetail['invoice_id'])->get()->sum('total');

        $calculateTax = $sumSubTotal * $editInvoice['tax'] / 100;
        $calculateDiscount = $sumSubTotal * $editInvoice['discount'] / 100;
        $sumTotal = ($sumSubTotal - $calculateDiscount) + $calculateTax;

        $editInvoice->sub_total = $sumSubTotal;
        $editInvoice->total = $sumTotal;
        $editInvoice->save();
        
        return response()->json([
            'success' => true,
            'msg' => 'Successfully edited'
        ]);
    }

    /** delete invoice detail */
    public function deleteInvoiceDetail(InvoiceRequest $request)
    {
        $deleteInvoiceDetail = InvoiceDetail::find($request['id']);
        $deleteInvoiceDetail->delete();

        /** update the total price */
        $updateInvoice = Invoice::find($deleteInvoiceDetail['invoice_id']);
        $sumSubTotal = InvoiceDetail::where('invoice_id', $deleteInvoiceDetail['invoice_id'])->get()->sum('total');

        $calculateTax = $sumSubTotal * $updateInvoice['tax'] / 100;
        $calculateDiscount = $sumSubTotal * $updateInvoice['discount'] / 100;
        $sumTotal = ($sumSubTotal - $calculateDiscount) + $calculateTax;

        $updateInvoice->sub_total = $sumSubTotal;
        $updateInvoice->total = $sumTotal;
        $updateInvoice->save();

        return response()->json([
            'success' => true,
            'msg' => 'Successfully edited'
        ]);
    }
}
