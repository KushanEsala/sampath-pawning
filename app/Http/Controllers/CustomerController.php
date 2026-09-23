<?php
namespace App\Http\Controllers;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\CustomerContactSyncService;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = $this->customerPage($request);
        return view('customers', compact('customers'));
    }

    public function nextCode()
    {
        return response()->json(['code' => ((int) Customer::max('Code')) + 1]);
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
        $validated = $request->validate(['customer_id' => 'required|integer']);
        Customer::where('id', $validated['customer_id'])
            ->where('BC', auth()->user()->BC)->firstOrFail()->delete();
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
        return view('customer_pagination', ['customers' => $this->customerPage($request)])->render();
    }

    // ............search using ajax.................
    public function search(Request $request){
        $request->merge(['q' => $request->input('search_string', $request->input('q'))]);
        $customers = $this->customerPage($request);
        if ($customers->count() > 0) {
            return view('customer_pagination', compact('customers'))->render();
        }else{
            return response()->json([
                'status'=>'not_found'
            ]);
        }
    }

    private function customerPage(Request $request)
    {
        $request->validate([
            'q' => 'nullable|string|max:80',
            'status' => 'nullable|in:all,active,blacklisted',
            'per_page' => 'nullable|in:10,25,50',
        ]);
        $search = trim((string) $request->input('q', ''));
        $query = Customer::query()->where('BC', auth()->user()->BC);

        if ($request->input('status') === 'active') $query->where('Status', 1);
        if ($request->input('status') === 'blacklisted') $query->where('Status', 0);

        if ($search !== '') {
            $prefix = $search.'%';
            $contains = '%'.$search.'%';
            $query->where(function ($q) use ($prefix, $contains) {
                $q->where('Code', 'like', $prefix)
                    ->orWhere('NIC', 'like', $prefix)
                    ->orWhere('Contact_1', 'like', $prefix)
                    ->orWhere('Contact_2', 'like', $prefix)
                    ->orWhere('First_name', 'like', $contains)
                    ->orWhere('Middle_name', 'like', $contains)
                    ->orWhere('Last_name', 'like', $contains)
                    ->orWhere('Name', 'like', $contains);
            });
        }

        return $query->orderByDesc('id')
            ->simplePaginate((int) $request->input('per_page', 25))
            ->withQueryString();
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
