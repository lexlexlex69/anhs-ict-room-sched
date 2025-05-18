<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    
    public function MyAccount()
    {
        $data['getRecord'] = User::getSingle(Auth::user()->id);
        $data['header_title'] = "My Account";
        if(Auth::user()->user_type == 1)
        {
            return view('admin.my_account',$data);
        }
        else if(Auth::user()->user_type == 2)
        {
          
            return view('teacher.my_account',$data);
        }
        else if(Auth::user()->user_type == 3)
        {
            
            return view('dealer.my_account',$data);
        }
    }

    public function UpdateMyAccount(Request $request)
    {
        $id = Auth::user()->id;
        
        request()->validate([
            'email' => 'required|email|unique:users,email,'.$id
        ]);

        $user = User::getSingle($id);
        $user->first_name = trim($request->first_name);
        $user->last_name = trim($request->last_name);
        $user->phone_number = '+63' . trim($request->phone_number);
        $user->subject = trim($request->subject);
        if(!empty($request->file('profile_pic')))
        {
            if(!empty($student->getProfile))
            {
                unlink('upload/profile/'.$user->profile_pic);
            }
            $ext = $request->file('profile_pic')->getClientOriginalExtension();
            $file = $request->file('profile_pic');
            $randomStr = date('Ymdhis').Str::random(30);
            $filename = strtolower($randomStr).'.'.$ext;
            $file->move(public_path('upload/profile/'), $filename);

            $user->profile_pic = $filename;
        }

        $user->status = is_numeric($request->status) ? (int) $request->status : 0;
        $user->email = trim($request->email);
        
        $user->save();

        return redirect()->back()->with('success',"Account successfully Updated");
    
    }



    //Add Teacher Account

    public function list()
    {
        $data['getRecord'] = User::GetTeacher(); // Fetch records from the database
        return view('admin.teacher.list', $data);
    }
    

    public function add()
    {
        $data['header_title'] = "Teacher ";
        return view('admin.teacher.add');
    }


    public function insert(Request $request)
    {

        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:3',
    
        ]);
    
        $user = new User();
        $user->first_name = $validatedData['first_name'];
        $user->email = $validatedData['email'];
        $user->password = Hash::make($validatedData['password']);
        $user->subject = trim($request->subject);
        $user->user_type = 2;
      
    
        $user->save();
    
        return redirect('admin/teacher/list')->with('success',"Teacher Added");
    }


    public function edit($id)
    {
        $data['getRecord'] = User::getSingle($id);
        if(!empty($data['getRecord']))
        {
            $data['header_title'] = "Edit Teacher ";
            return view('admin.teacher.edit',$data);
        }
        else
        {
                abort(404);
        }
        
    }


    public function update($id, Request $request)
    {
        

        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:3',
    
        ]);

        $user = User::getSingle($id);
        $user->first_name = $validatedData['first_name'];
        $user->email = $validatedData['email'];
        $user->password = Hash::make($validatedData['password']);
        $user->subject = trim($request->subject);
        $user->user_type = 2;
  
        $user->save();
    
   
        return redirect('admin/teacher/list')->with('success',"Teacher successfully update");
    }


    public function delete($id)
    {
        $data = User::findOrFail($id);
        $data->delete(); 
    
        return redirect()->back()->with('success', "Teacher Successfully Deleted");
    }
}
