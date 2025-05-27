<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{

    public function MyAccount()
    {
        $now = now(); // Current time for status calculation

        $data['getRecord'] = User::getSingle(Auth::user()->id);
        $data['header_title'] = "My Account";
        if (Auth::user()->user_type == 1) {
            return view('admin.my_account', $data);
        } else if (Auth::user()->user_type == 2) {

            return view('teacher.my_account', $data);
        } else if (Auth::user()->user_type == 3) {

            return view('dealer.my_account', $data);
        }
    }

    public function UpdateMyAccount(Request $request)
    {
        $id = Auth::user()->id;

        request()->validate([
            'email' => 'required|email|unique:users,email,' . $id
        ]);

        $user = User::getSingle($id);
        $user->first_name = trim($request->first_name);
        $user->last_name = trim($request->last_name);
        $user->phone_number = '+63' . trim($request->phone_number);
        $user->subject = trim($request->subject);
        if (!empty($request->file('profile_pic'))) {
            if (!empty($student->getProfile)) {
                unlink('upload/profile/' . $user->profile_pic);
            }
            $ext = $request->file('profile_pic')->getClientOriginalExtension();
            $file = $request->file('profile_pic');
            $randomStr = date('Ymdhis') . Str::random(30);
            $filename = strtolower($randomStr) . '.' . $ext;
            $file->move(public_path('upload/profile/'), $filename);

            $user->profile_pic = $filename;
        }

        $user->status = is_numeric($request->status) ? (int) $request->status : 0;
        $user->email = trim($request->email);

        $user->save();

        return redirect()->back()->with('success', "Account successfully Updated");
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
        // $request->validate([
        //     'first_name' => 'required|string|max:255',
        //     'last_name' => 'required|string|max:255', // Added validation for last_name
        //     'email' => 'required|email|unique:users,email',
        //     'password' => 'required|min:6',
        //     'teacher_type' => 'required|in:ICT,Non-ICT',
        //     'subject' => 'nullable|string|max:255',
        // ]);
        Log::info([$request]);


        $user = new User;
        $user->first_name = trim($request->first_name);
        $user->last_name = trim($request->last_name); // Assign last_name
        $user->email = trim($request->email);
        $user->password = Hash::make($request->password);
        $user->user_type = 2; // Set user type to teacher
        $user->teacher_type = $request->teacher_type;
        // Set subject only if teacher_type is ICT, otherwise null it out
        $user->subject = $request->subject;
        // $user->subject = ($request->teacher_type == 'ICT') ? trim($request->subject) : null;
        $user->save();

        Log::info([$user]);

        return redirect('admin/teacher/list')->with('success', 'Teacher Added Successfully');
    }

    public function edit($id)
    {
        $getRecord = User::getSingle($id);
        Log::info([$getRecord]);
        if (!empty($getRecord)) {
            return view('admin.teacher.edit', compact('getRecord'));
        }
        return redirect('admin/teacher/list')->with('error', 'Teacher Not Found');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255', // Added validation for last_name
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'teacher_type' => 'required|in:ICT,Non-ICT',
            'subject' => 'nullable|string|max:255',
        ]);

        $user = User::getSingle($id);
        if (empty($user)) {
            return redirect('admin/teacher/list')->with('error', 'Teacher Not Found');
        }

        $user->first_name = trim($request->first_name);
        $user->last_name = trim($request->last_name); // Assign last_name
        $user->email = trim($request->email);
        if (!empty($request->password)) {
            $user->password = Hash::make($request->password);
        }
        $user->teacher_type = $request->teacher_type;
        // Update subject only if teacher_type is ICT, otherwise null it out
        $user->subject = ($request->teacher_type == 'ICT') ? trim($request->subject) : null;
        $user->save();

        return redirect('admin/teacher/list')->with('success', 'Teacher Updated Successfully');
    }

    public function delete($id)
    {
        $data = User::findOrFail($id);
        $data->delete();

        return redirect()->back()->with('success', "Teacher Successfully Deleted");
    }
}
