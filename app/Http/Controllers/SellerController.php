<?php

namespace App\Http\Controllers;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SellerController extends Controller
{
    public function list()
    {
        $data['getRecord']= User::getSeller();
        $data['header_title'] = "Seller";
        return view('admin.seller.list', $data);
    }

    public function add()
    {
        $data['header_title'] = "Add Seller ";
        return view('admin.seller.add', $data);
    }

    public function insert(Request $request)
    {
        request()->validate([
            'email' => 'required|unique:users',
            'password' => 'required|min:8',
           
        ]);
        
        $seller = new User;
        $seller->name = trim($request->name);
        $seller->email = trim($request->email);
        $seller->password = Hash::make($request->password);
        $seller->user_type = 2;
        $seller->save();

        return redirect('admin/seller/list')->with('success',"Inspector Added");

        
    }
}
