<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    public function list()
    {
        $data['getRecord'] = Room::getRoom(); // Fetch records from the database
        return view('admin.room.list', $data);
    }
    

    public function add()
    {
        $data['header_title'] = "Room ";
        return view('admin.room.add');
    }


    public function insert(Request $request)
    {
    
        $room = new Room;
        $room->room_name = trim($request->room_name);
        $room->capacity = trim($request->capacity);
        $room->availability = trim($request->availability);
        $room->subject = trim($request->subject);
        $room->created_by = Auth::user()->id;
    
        $room->save();
    
        return redirect('admin/room/list')->with('success',"Room Added");
    }


    public function edit($id)
    {
        $data['getRecord'] = Room::getSingle($id);
        if(!empty($data['getRecord']))
        {
            $data['header_title'] = "Edit Room ";
            return view('admin.room.edit',$data);
        }
        else
        {
                abort(404);
        }
        
    }


    public function update($id, Request $request)
    {
        

        $room = Room::getSingle($id);
        $room->room_name = trim($request->room_name);
        $room->capacity = trim($request->capacity);
        $room->availability = trim($request->availability);
        $room->subject = trim($request->subject);
        $room->created_by = Auth::user()->id;
    
        $room->save();
    

        return redirect('admin/room/list')->with('success',"Room successfully update");
    }


    public function delete($id)
    {
        $data = Room::findOrFail($id);
        $data->delete(); 
    
        return redirect()->back()->with('success', "Room Successfully Deleted");
    }
}

