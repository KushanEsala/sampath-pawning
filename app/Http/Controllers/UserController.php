<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\branchDel;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // function viewUsers(){
    //     $data = user::all();
    //     return view("user.viewUsers")->with("users" , $data);
    // }

    public function showUsers(){
        $branch_code = auth()->user()->BC;
        $user_name = auth()->user()->username;
        $user_role= auth()->user()->role;

        $branch = branchDel::all();

        if($user_name == "Admin" || $user_name == "developer" || $user_role == "Head_Officer" ){
            $data = user::latest()->get();
        }else{
            $data = user::latest()
            ->where('BC',$branch_code)
            ->get();
        }

        return view("users")
        ->with("Branch" , $branch)
        ->with("users" , $data);
    }

    public function showAddUser (){

        $branch = branchDel::all();

        return view('addUserForm')
        ->with("Branch" , $branch);
    }

    public function showRoles(){
        return view('roles');
    }

    public function AddUser(Request $request){
        $prefix = $request->surname;
        $first_name = $request->name;
        $last_name = $request->last_name;
        $fname = $prefix." ".$first_name." ".$last_name;

        $data = new user;
        $data->email = $request->email;
        $data->name = $fname;
        $data->username = $request->user_name;
        $data -> password = Hash::make($request->password);
        $data -> role =$request->role;
        $data -> Branch =$request->Branch;
        $data -> BC =$request->BC;
        $data->save();

        return redirect('/users')->with("added" , "User Added Succsessfully");
    }

    function deleteUsers($id){
        $data = user::find($id);
        $data->delete();
        return redirect()->back()->with("delete", "User deleted");
    }
    function editUsers($id){
        $data = user::find($id);
        return view("editUser")->with("users" , $data);
    }



    // delete user ajax
    public function delete(Request $request){
        user::find($request->user_id)->delete();
        return response()->json([
            'status'=>'success',
        ]);
    }

    //update using ajax
    public function update(Request $request){
        $request->validate([
            // 'up_name'=>'required | max:10 | unique:customers,name,'.$request->up_id,
            // 'up_first_name'=>'required | max:255 ',
            // 'up_last_name'=>'required | max:255 ',
            // 'up_address1'=>'required | max:255 ',
            // 'up_address2'=>'max:255 ',
            // 'up_contact1'=>['required','max:10', 'regex:/^0\d{9,}$/'],
            // 'up_contact2'=>'max:10',
            // 'up_email'=>' max:30 ',
            // 'up_nic'=>'required | max:15',
            // 'up_driving_license'=>'max:15 ',
            // 'up_passport'=>'max:15 ',
            // 'up_other_identifications'=>'max:15 ',
        ]);

        user::where('id',$request->up_id)->update([
            'name'=>$request->up_name,
            'username'=>$request->up_user_name,
            'email'=>$request->up_email,
            'role'=>$request->up_role,
            'Branch'=>$request->up_Branch,
            'BC'=>$request->up_BC,

        ]);

        return response()->json([
            'status'=>'success',
        ]);
    }

//search using ajax
public function search(Request $request){
    $data = user::where('name', 'like', '%'.$request->search_string.'%')
    ->orWhere('username','like','%'.$request->search_string.'%')
    ->orWhere('email','like','%'.$request->search_string.'%')
    ->orWhere('role','like','%'.$request->search_string.'%')
    ->orderBy('id','desc')
    ->paginate(100);

    if($data->count() >= 1){
        return view('customer_pagination')->with("customers",$data)->render();
    }else{
        return response()->json([
            'status'=>'not_found'
        ]);
    }

}

public function getUser(Request $request){
    $itemcode = $request -> category;
    $data = branchDel::where('name',$itemcode)->get();

    return response()->json([
        'status' => 'success',
        'data' => $data
    ]);

}

}