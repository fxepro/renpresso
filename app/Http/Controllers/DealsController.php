<?php

namespace App\Http\Controllers;

class DealsController extends Controller
{
    public function insurance()
    {
        return view('dashboard.deals.insurance');
    }

    public function coupons()
    {
        return view('dashboard.deals.coupons');
    }
}
