<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{


    public function landing()
    {
        return view('auth.landing');
    }

    public function login()
    {

        if(!empty(Auth::check()))
        {
            if(Auth::user()->user_type == 1)
            {
                return redirect('admin/schedules/AllList'); 
            }
            else if(Auth::user()->user_type == 2)
            {
                return redirect('teacher/dashboard'); 
            } 
        }

        return view('auth.login');
    }

    public function Authlogin(Request $request)
    {
        $remember = !empty($request->remember) ? true : false;

        if(Auth::attempt(['email' => $request->email, 'password' => $request->password], $remember))
        {
            if(Auth::user()->user_type == 1)
            {
                return redirect('admin/schedules/AllList'); 
            }
            else if(Auth::user()->user_type == 2)
            { 
                return redirect('teacher/dashboard'); 
            }
             
        }
        else{
            return redirect ()->back()->with('error','Please enter correct username and password');
        }
    }


    public function register()
    {
        return view('auth.register');
    }

    public function postRegister(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:3|confirmed',
    
        ]);
    
        // Create the user
        $user = new User();
        $user->first_name = $validatedData['first_name'];
        $user->email = $validatedData['email'];
        $user->password = Hash::make($validatedData['password']);
        $user->user_type = 2;
      
    
        $user->save();
    
        // You can customize the redirection after registration as per your requirement
        return redirect('loginfront')->with('success', 'Registration successful. You can now log in.');
    }

    public function logout()
    {
        Auth::logout();
        return redirect(url('/'));
    }
}
