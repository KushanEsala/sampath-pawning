<?php
namespace App\Http\Controllers;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\CustomerContactSyncService;

class CustomerController extends Controller
{
    public function index()
    {
        $branch_code = auth()->user()->BC;
        $data = Customer::all();
;
        
        $maxCustomerCode = Customer::orderBy('Code', 'desc')
                ->value('Code');
                
        $maxCustomerCodes = str_pad($maxCustomerCode, 4, '0', STR_PAD_LEFT);
        
        return view("customers")
        ->with("maxCustomer", $maxCustomerCodes)
        ->with("customers" , $data);
    }


    // create customer ajax
    public function create(Request $request){
        $request->validate([
            'code'=>'required | max:10 ',
            'first_name'=>'required | max:255 ',
            'last_name'=>'required | max:255 ',
            'address1'=>'required | max:255 ',
            'address2'=>'max:255 ',
            'contact1'=>['required','max:10', 'regex:/^0\d{9,}$/'],
            'contact2'=>'max:10',
            'email'=> 'max:30',
            'nic'=>'required | max:15',
            'driving_license'=>'max:15 ',
            'passport'=>'max:15 ',
            'other_identifications'=>'max:15 ',
        ]);
        $customer = new Customer();
        $customer->Code=$request->code;
        $customer->Title=$request->title;
        $customer->Gender=$request->gender;
        $customer->Name=$request->name;
        $customer->First_name=$request->first_name;
        $customer->Middle_name=$request->middle_name;
        $customer->Last_name=$request->last_name;
        $customer->Address_1=$request->address1;
        $customer->City_1=$request->city1;
        $customer->Address_2=$request->address2;
        $customer->City_2=$request->city2;
        $customer->Contact_1=$request->contact1;
        $customer->Contact_2=$request->contact2;
        $customer->Email=$request->email;
        $customer->NIC=$request->nic;
        $customer->Driving_license=$request->driving_license;
        $customer->Passport=$request->passport;
        $customer->Other_identifications=$request->other_identifications;
        $customer->Status=$request->status;
        $customer->BC = auth()->user()->BC;
        $customer->OC = auth()->user()->username;
        $customer->save();
        return response()->json([
            'status'=>'success',
        ]);
    }

    // delete customer ajax
    public function delete(Request $request){
        Customer::find($request->customer_id)->delete();
        return response()->json([
            'status'=>'success',
        ]);
    }

    // ............update using ajax.................
    public function update(Request $request, CustomerContactSyncService $contactSync){
        $request->validate([
            'up_code'=>'required | max:10 ',
            'up_first_name'=>'required | max:255 ',
            'up_last_name'=>'required | max:255 ',
            'up_address1'=>'required | max:255 ',
            'up_address2'=>'max:255 ',
            'up_contact1'=>['required','max:10', 'regex:/^0\d{9,}$/'],
            'up_contact2'=>'max:10',
            'up_email'=>' max:30 ',
            'up_nic'=>'required | max:15',
            'up_driving_license'=>'max:15 ',
            'up_passport'=>'max:15 ',
            'up_other_identifications'=>'max:15 ',
        ]);

        DB::transaction(function () use ($request, $contactSync) {
        $customer = Customer::where('id', $request->up_id)->where('BC', auth()->user()->BC)->lockForUpdate()->firstOrFail();
        $oldNic = $customer->NIC;
        $before = ['NIC' => $customer->NIC, 'Address_1' => $customer->Address_1, 'Contact_1' => $customer->Contact_1];
        $fullName = trim(implode(' ', array_filter([$request->up_first_name, $request->up_middle_name, $request->up_last_name])));
        $customer->update([
            'Code'=>$request->up_code,
            'Title'=>$request->up_title,
            'Gender'=>$request->up_gender,
            'Name'=>$fullName,
            'First_name'=>$request->up_first_name,
            'Middle_name'=>$request->up_middle_name,
            'Last_name'=>$request->up_last_name,
            'Address_1'=>$request->up_address1,
            'City_1'=>$request->up_city1,
            'Address_2'=>$request->up_address2,
            'City_2'=>$request->up_city2,
            'Contact_1'=>$request->up_contact1,
            'Contact_2'=>$request->up_contact2,
            'Email'=>$request->up_email,
            'NIC'=>$request->up_nic,
            'Driving_license'=>$request->up_driving_license,
            'Passport'=>$request->up_passport,
            'Other_identifications'=>$request->up_other_identifications,
            'Status'=>$request->up_status,
            'BC'=>auth()->user()->BC,
            'OC'=>auth()->user()->username,
        ]);
        $contactSync->syncActiveSnapshots($customer->fresh(), $oldNic, $before);
        });

        return response()->json([
            'status'=>'success',
        ]);
    }

    // ............ customer pagination using ajax.................
    public function pagination(Request $request){
        $branch_code = auth()->user()->BC;
        
        $data = Customer::where('BC',$branch_code)
                ->latest()
                ->paginate(7);
        
        return view('customer_pagination')->with("customers",$data)->render();
    }

    // ............search using ajax.................
    public function search(Request $request){
        $branch_code = auth()->user()->BC;
        
        $data = Customer::where('BC', $branch_code)
        ->where(function($query) use ($request) {
            $query->where('Code', 'like', '%' . $request->search_string . '%')
                ->orWhere('First_name', 'like', '%' . $request->search_string . '%')
                ->orWhere('Middle_name', 'like', '%' . $request->search_string . '%')
                ->orWhere('Last_name', 'like', '%' . $request->search_string . '%')
                ->orWhere('Address_1', 'like', '%' . $request->search_string . '%')
                ->orWhere('City_1', 'like', '%' . $request->search_string . '%')
                ->orWhere('Address_2', 'like', '%' . $request->search_string . '%')
                ->orWhere('City_2', 'like', '%' . $request->search_string . '%')
                ->orWhere('Contact_1', 'like', '%' . $request->search_string . '%')
                ->orWhere('Contact_2', 'like', '%' . $request->search_string . '%')
                ->orWhere('Email', 'like', '%' . $request->search_string . '%')
                ->orWhere('NIC', 'like', '%' . $request->search_string . '%')
                ->orWhere('Driving_license', 'like', '%' . $request->search_string . '%')
                ->orWhere('Passport', 'like', '%' . $request->search_string . '%')
                ->orWhere('Other_identifications', 'like', '%' . $request->search_string . '%');
        })
        ->orderBy('Code', 'desc')
        ->paginate(7);
        
        // $data = Customer::where('Code', 'like', '%'.$request->search_string.'%')
        // ->orWhere('First_name','like','%'.$request->search_string.'%')
        // ->orWhere('Middle_name','like','%'.$request->search_string.'%')
        // ->orWhere('Last_name','like','%'.$request->search_string.'%')
        // ->orWhere('Address_1','like','%'.$request->search_string.'%')
        // ->orWhere('City_1','like','%'.$request->search_string.'%')
        // ->orWhere('Address_2','like','%'.$request->search_string.'%')
        // ->orWhere('City_2','like','%'.$request->search_string.'%')
        // ->orWhere('Contact_1','like','%'.$request->search_string.'%')
        // ->orWhere('Contact_2','like','%'.$request->search_string.'%')
        // ->orWhere('Email','like','%'.$request->search_string.'%')
        // ->orWhere('NIC','like','%'.$request->search_string.'%')
        // ->orWhere('Driving_license','like','%'.$request->search_string.'%')
        // ->orWhere('Passport','like','%'.$request->search_string.'%')
        // ->orWhere('Other_identifications','like','%'.$request->search_string.'%')
        // ->orderBy('Code','desc')
        // ->paginate(5);

        if($data->count() >= 1){
            return view('customer_pagination')->with("customers",$data)->render();
        }else{
            return response()->json([
                'status'=>'not_found'
            ]);
        }
    }

    public function getByID(Request $request)
    {
        $code= $request->code;
        $Cusdata = Customer::where('Code', $code)->get();
        // dd($Cusdata);
        return redirect('pawning')->with('getByID', $Cusdata);
    }

    public function show($id)
    { }

    public function edit($id)
    {   }

    public function indexEdit($code){
        $Cusdata = Customer::where('Code', $code)->get();
        // dd($Cusdata);
        return view('editCustomers')->with('Cusdata', $Cusdata);
    }


    public function destroy($Code)
    {
        $data = Customer::where('Code', $Code)->delete();
        return redirect()->back()->with("delete", "Receipt deleted");
    }
}
