<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    // format data
    public function format()
    {
        return [
            "id" => $this->id,
            "quotation_number" => $this->quotation_number,
            "quotation_name" => $this->quotation_name,
            "start_date" => $this->start_date,
            "end_date" => $this->end_date,
            "note" => $this->note,
            "sub_total" => $this->sub_total,
            "discount" => $this->discount,
            "tax" => $this->tax,
            "total" => $this->total,
            "countDetails" => $this->quotationDetail->count(),
            "currency" => $this->currency,
            "company" => $this->company,
            "user" => $this->createdBy->first(),
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }

    /** relation model */
    public function quotationDetail()
    {
        return $this->hasMany(QuotationDetail::class);
    }
    
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
