<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;


class AppHelper {
    // generate quotation number
    public static function generateQuotationNumber($prefix, $length) {
        if(function_exists('random_bytes')) {
            $bytes = random_bytes(ceil($length / 2));
        } elseif(function_exists('openssl_random_pseudo_random_bytes')) {
            $bytes = openssl_random_pseudo_bytes(ceil($length / 2));
        } else {
            throw new \Exception('no cryptographically secure random function available');
        }

        $uniqueCod = $prefix . strtoupper(substr(bin2hex($bytes), 0, $length));

        $exists = DB::table('quotations')->where('quotation_number', $uniqueCod)->exists();
        if($exists) {
            return self::generateQuotationNumber($prefix, $length);
        }
        return $uniqueCod;
    }

    // generate invoice number
    public static function generateInvoiceNumber($prefix, $length) {
        if(function_exists('random_bytes')) {
            $bytes = random_bytes(ceil($length / 2));
        } elseif(function_exists('openssl_random_pseudo_random_bytes')) {
            $bytes = openssl_random_pseudo_bytes(ceil($length / 2));
        } else {
            throw new \Exception('no cryptographically secure random function available');
        }

        $uniqueCod = $prefix . strtoupper(substr(bin2hex($bytes), 0, $length));

        $exists = DB::table('invoices')->where('invoice_number', $uniqueCod)->exists();
        if($exists) {
            return self::generateQuotationNumber($prefix, $length);
        }
        return $uniqueCod;
    }
}