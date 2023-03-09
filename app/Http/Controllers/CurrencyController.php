<?php

namespace App\Http\Controllers;

use App\Http\Requests\CurrencyRequest;
use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function listCurrencies() {

        $items = Currency::orderBy('id', 'desc')->get();

        return response()->json([
            'currency' => $items,
        ]);
    }

    public function addCurrency(CurrencyRequest $request) {

        $addCurrency = new Currency();
        $addCurrency->name = $request['name'];
        $addCurrency->short_name = $request['short_name'];
        $addCurrency->save();

        return response()->json([
            'success' => true,
            'msg' => 'successfully added'
        ]);
    }
    
    public function editCurrency(CurrencyRequest $request) {
        $editCurrency = Currency::find($request['id']);
        $editCurrency->name = $request['name'];
        $editCurrency->short_name = $request['short_name'];
        $editCurrency->save();
    
        return response()->json([
            'success' => true,
            'msg' => 'successfully edited'
        ]);
    }

    public function deleteCurrency(CurrencyRequest $request) {
        
        $deleteCurrency = Currency::find($request['id']);
        $deleteCurrency->delete();

        return response()->json([
            'success' => true,
            'msg' => 'successfully deleted'
        ]);
    }

}
