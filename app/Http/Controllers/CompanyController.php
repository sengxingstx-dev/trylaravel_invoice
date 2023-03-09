<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function saveImage($request) {

        if($request->hasFile('logo')) {

            $destination_path = '/images/company/logo';
            $imageFile = $request->file('logo');

            // Get file ext
            $extension = $imageFile->getClientOriginalExtension();

            // Filename to store
            $filename = 'company_logo' . '_' . time() . '.' . $extension;
            Storage::disk('public')->putFileAs($destination_path, $imageFile, $filename);

            return $filename;
        }
    }

    // 
    public function listCompanies() {
        $items = Company::orderBy('id', 'desc')->get();

        $items->transform(function($item) {
            return $item->format();
        });

        return response()->json([
            'company' => $items,
        ]);
    }

    public function addCompany(CompanyRequest $request) {

        $filename = $this->saveImage($request);

        $addCompany = new Company();
        $addCompany->company_name = $request['name'];
        $addCompany->phone = $request['phone'];
        $addCompany->email = $request['email'];
        $addCompany->address = $request['address'];
        $addCompany->logo = $filename;
        $addCompany->save();

        return response()->json([
            'success' => true,
            'msg' => 'Successfully added'
        ]);
    }
    
    public function editCompany(CompanyRequest $request) {

        $editCompany = Company::find($request['id']);
        $editCompany->company_name = $request['name'];
        $editCompany->phone = $request['phone'];
        $editCompany->email = $request['email'];
        $editCompany->address = $request['address'];

        if(isset($request['logo'])) {
            // upload file
            $filename = $this->saveImage($request);

            // remove for old file in folder
            if(isset($editCompany->logo)) {
                $file_path = 'images/company/logo/' . $editCompany->logo;
                if(Storage::disk('public')->exists($file_path)) {
                    Storage::disk('public')->delete($file_path);
                }
            }
            $editCompany->logo = $filename;
        }

        $editCompany->save();
    
        return response()->json([
            'success' => true,
            'msg' => 'Successfully edited'
        ]);
    }

    public function deleteCompany(CompanyRequest $request) {
        
        $deleteCompany = Company::find($request['id']);
        
        // remove for old file in folder
        if(isset($deleteCompany->logo)) {
            $file_path = 'images/company/logo/' . $deleteCompany->logo;
            if(Storage::disk('public')->exists($file_path)) {
                Storage::disk('public')->delete($file_path);
            }
        }

        $deleteCompany->delete();
        
        return response()->json([
            'success' => true,
            'msg' => 'successfully deleted'
        ]);
    }

    
}
