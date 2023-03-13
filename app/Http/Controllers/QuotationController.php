<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Helpers\AppHelper;
use App\Http\Requests\QuotationRequest;
use App\Models\Company;
use App\Models\Currency;
use Illuminate\Http\Request;
use App\Models\QuotationDetail;
use App\Models\User;
use App\Services\CalculateService;
use App\Traits\ResponseAPI;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class QuotationController extends Controller
{
    use ResponseAPI;

    public $calculateService;


    public function __construct(CalculateService $calculateService)
    {
        $this->calculateService = $calculateService;
    }

    public function listQuotations() {
        $items = Quotation::orderBy('id', 'desc')->get();

        // format 
        $items->transform(function($item) {
            return $item->format();
        });

        return $this->success($items, 200);
        // return $this->errors('Not found', 404);

        // $items->map(function($item) {
        //     $item['countDetail'] = QuotationDetail::where('quotation_id', $item['id'])->get()->count();
        //     $item['company'] = Company::where('id', $item['company_id'])->first();
        //     $item['currency'] = Currency::where('id', $item['currency_id'])->first();
        //     $item['user'] = User::where('id', $item['created_by'])->first();
        // });

        // return response()->json([
        //     'quotations' => $items,
        // ]);
    }
    
    public function listQuotationDetail($id) {
        $item = Quotation::orderBy('id', 'desc')->where('id', $id)->first();
        $item['countDetail'] = QuotationDetail::where('quotation_id', $item['id'])->get()->count();
        $item['company'] = Company::where('id', $item['company_id'])->first();
        $item['currency'] = Currency::where('id', $item['currency_id'])->first();
        $item['user'] = User::where('id', $item['created_by'])->first();

        $details = QuotationDetail::where('quotation_id', $id)->get();

        return response()->json([
            'quotation' => $item,
            'details' => $details,
        ]);
    }

    public function addQuotation(QuotationRequest $request) {

        DB::beginTransaction();

            $addQuotation = new Quotation();
            $addQuotation->quotation_number = AppHelper::generateQuotationNumber('QT-', 6);
            $addQuotation->quotation_name = $request['quotation_name'];
            $addQuotation->start_date = $request['start_date'];
            $addQuotation->end_date = $request['end_date'];
            $addQuotation->note = $request['note'];
            $addQuotation->discount = $request['discount'];
            $addQuotation->tax = $request['tax'];
            $addQuotation->company_id = $request['company_id'];
            $addQuotation->currency_id = $request['currency_id'];
            $addQuotation->created_by = Auth::user('api')->id;
            $addQuotation->save();

            $sumSubTotal = 0;

            /** add quotation details */
            if(!empty($request['quotation_details'])) {
                foreach($request['quotation_details'] as $item) {
                    // return $item;

                    $addQuotationDetail = new QuotationDetail();
                    $addQuotationDetail->order_number = $item['order'];
                    $addQuotationDetail->name = $item['name'];
                    $addQuotationDetail->description = $item['description'];
                    $addQuotationDetail->quantity = $item['quantity'];
                    $addQuotationDetail->price = $item['price'];
                    $addQuotationDetail->total = $item['quantity'] * $item['price'];

                    $addQuotationDetail->quotation_id = $addQuotation['id'];
                    $addQuotationDetail->save();

                    $sumSubTotal += $item['quantity'] * $item['price'];
                }
            }

            /** calculate the price */
            $this->calculateService->calculateTotal($request, $sumSubTotal, $addQuotation['id']);

        DB::commit();

        return response()->json([
            'success' => true,
            'msg' => 'Successfully added'
        ]);
    }

    public function addQuotationDetail(QuotationRequest $request) {

        $addQuotationDetail = new QuotationDetail();
        $addQuotationDetail->order_number = $request['order'];
        $addQuotationDetail->quotation_id = $request['id'];
        $addQuotationDetail->name = $request['name'];
        $addQuotationDetail->description = $request['description'];
        $addQuotationDetail->quantity = $request['quantity'];
        $addQuotationDetail->price = $request['price'];
        $addQuotationDetail->total = $request['quantity'] * $request['price'];
        $addQuotationDetail->save();

        /** update the total price */
        $addQuotation = Quotation::find($request['id']);
        $sumSubTotal = QuotationDetail::where('quotation_id', $request['id'])->get()->sum('total');

        $calculateTax = $sumSubTotal * $addQuotation['tax'] / 100;
        $calculateDiscount = $sumSubTotal * $addQuotation['discount'] / 100;
        $sumTotal = ($sumSubTotal - $calculateDiscount) + $calculateTax;

        $addQuotation->sub_total = $sumSubTotal;
        $addQuotation->total = $sumTotal;
        $addQuotation->save();
        
        return response()->json([
            'success' => true,
            'msg' => 'Successfully added'
        ]);
    }

    public function editQuotation(QuotationRequest $request) {

        $editQuotation = Quotation::find($request['id']);
        $editQuotation->quotation_name = $request['quotation_name'];
        $editQuotation->start_date = $request['start_date'];
        $editQuotation->end_date = $request['end_date'];
        $editQuotation->note = $request['note'];
        $editQuotation->discount = $request['discount'];
        $editQuotation->tax = $request['tax'];
        $editQuotation->company_id = $request['company_id'];
        $editQuotation->currency_id = $request['currency_id'];
        $editQuotation->created_by = Auth::user('api')->id;
        $editQuotation->save();
        
        $this->calculateService->calculateUpdateTotal($request);
        
        // /** update the total price */
        // $sumSubTotal = QuotationDetail::where('quotation_id', $request['id'])->get()->sum('total');
        // $calculateTax = $sumSubTotal * $request['tax'] / 100;
        // $calculateDiscount = $sumSubTotal * $request['discount'] / 100;
        // $sumTotal = ($sumSubTotal - $calculateDiscount) + $calculateTax;

        // $editQuotation->sub_total = $sumSubTotal;
        // $editQuotation->total = $sumTotal;
        // $editQuotation->save();

        return response()->json([
            'success' => true,
            'msg' => 'Successfully edited'
        ]);
    }
    
    public function editQuotationDetail(QuotationRequest $request) {

        $editQuotationDetail = QuotationDetail::find($request['id']);
        $editQuotationDetail->order_number = $request['order'];
        $editQuotationDetail->name = $request['name'];
        $editQuotationDetail->description = $request['description'];
        $editQuotationDetail->quantity = $request['quantity'];
        $editQuotationDetail->price = $request['price'];
        $editQuotationDetail->total = $request['quantity'] * $request['price'];
        $editQuotationDetail->save();

        /** update the total price */
        $editQuotation = Quotation::find($editQuotationDetail['quotation_id']);

        // $this->calculateService->calculateUpdateTotal($request, $editQuotation['id']);

        $sumSubTotal = QuotationDetail::where('quotation_id', $editQuotationDetail['quotation_id'])->get()->sum('total');

        $calculateTax = $sumSubTotal * $editQuotation['tax'] / 100;
        $calculateDiscount = $sumSubTotal * $editQuotation['discount'] / 100;
        $sumTotal = ($sumSubTotal - $calculateDiscount) + $calculateTax;

        $editQuotation->sub_total = $sumSubTotal;
        $editQuotation->total = $sumTotal;
        $editQuotation->save();
        
        return response()->json([
            'success' => true,
            'msg' => 'Successfully edited'
        ]);
    }
    
    public function deleteQuotation(QuotationRequest $request) {

        $deleteQuotation = Quotation::find($request['id']);
        $deleteQuotation->delete();

        return response()->json([
            'success' => true,
            'msg' => 'Successfully deleted'
        ]);
    }

    public function deleteQuotationDetail(QuotationRequest $request) {

        $deleteQuotationDetail = QuotationDetail::find($request['id']);
        $deleteQuotationDetail->delete();

        /** update the total price */
        $updateQuotation = Quotation::find($deleteQuotationDetail['quotation_id']);
        $sumSubTotal = QuotationDetail::where('quotation_id', $deleteQuotationDetail['quotation_id'])->get()->sum('total');

        $calculateTax = $sumSubTotal * $updateQuotation['tax'] / 100;
        $calculateDiscount = $sumSubTotal * $updateQuotation['discount'] / 100;
        $sumTotal = ($sumSubTotal - $calculateDiscount) + $calculateTax;

        $updateQuotation->sub_total = $sumSubTotal;
        $updateQuotation->total = $sumTotal;
        $updateQuotation->save();

        return response()->json([
            'success' => true,
            'msg' => 'Successfully edited'
        ]);
    }
}
