<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class CustomerController extends Controller
{
    public function list()
    {
        $data['getRecord']= User::getCustomer();
        $data['header_title'] = "Customer";
        return view('admin.customer.list', $data);
    }
}
