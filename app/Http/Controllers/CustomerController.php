<?php
namespace App\Http\Controllers;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\CustomerContactSyncService;
use App\Services\CustomerPawnPolicy;
use Illuminate\Validation\ValidationException;

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
    public function create(Request $request, CustomerPawnPolicy $policy){
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
            'status' => 'required|in:0,1',
            'limit_amount' => 'nullable|numeric|min:0',
            'limit_pawn_count' => 'nullable|integer|min:0',
        ]);
        $nic = trim((string) $request->nic);
        if ($policy->branchCustomer($nic, auth()->user()->BC)) {
            throw ValidationException::withMessages(['nic' => 'This customer already exists in this branch.']);
        }
        $existing = $policy->customers($nic);
        $effective = $existing->isEmpty() ? null : $policy->policy($existing);
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
        $customer->NIC=$nic;
        $customer->Driving_license=$request->driving_license;
        $customer->Passport=$request->passport;
        $customer->Other_identifications=$request->other_identifications;
        $customer->Status=$effective ? (int) $effective['active'] : (int) $request->status;
        $customer->Limit_Amount=$effective ? $effective['amount_limit'] : $request->limit_amount;
        $customer->Limit_Pawn_Count=$effective ? $effective['count_limit'] : $request->limit_pawn_count;
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
    public function update(Request $request, CustomerContactSyncService $contactSync, CustomerPawnPolicy $policy){
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
            'up_status' => 'required|in:0,1',
            'up_limit_amount' => 'nullable|numeric|min:0',
            'up_limit_pawn_count' => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($request, $contactSync, $policy) {
        $query = Customer::where('id', $request->up_id);
        if (auth()->user()->role !== 'Admin') $query->where('BC', auth()->user()->BC);
        $customer = $query->lockForUpdate()->firstOrFail();
        $oldNic = $customer->NIC;
        $newNic = trim((string) $request->up_nic);
        if (CustomerPawnPolicy::key($oldNic) !== CustomerPawnPolicy::key($newNic)
            && $policy->customers($newNic)->isNotEmpty()) {
            throw ValidationException::withMessages(['up_nic' => 'This NIC already belongs to another customer.']);
        }
        $before = ['NIC' => $customer->NIC, 'Name' => $customer->Name,
            'Address_1' => $customer->Address_1, 'Contact_1' => $customer->Contact_1];
        $policy->updateIdentityPolicy($oldNic, (int) $request->up_status,
            $request->filled('up_limit_amount') ? (float) $request->up_limit_amount : null,
            $request->filled('up_limit_pawn_count') ? (int) $request->up_limit_pawn_count : null);
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
            'NIC'=>$newNic,
            'Driving_license'=>$request->up_driving_license,
            'Passport'=>$request->up_passport,
            'Other_identifications'=>$request->up_other_identifications,
            'Status'=>$request->up_status,
            'Limit_Amount'=>$request->up_limit_amount,
            'Limit_Pawn_Count'=>$request->up_limit_pawn_count,
            'OC'=>auth()->user()->username,
        ]);
        if ($oldNic !== $newNic || $before['Name'] !== $customer->Name
            || $before['Address_1'] !== $customer->Address_1
            || $before['Contact_1'] !== $customer->Contact_1) {
            $contactSync->syncActiveSnapshots($customer->fresh(), $oldNic, $before);
        }
        Log::info('Customer policy updated from Master Customer', [
            'customer_id' => $customer->id, 'updated_by' => auth()->id(),
        ]);
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
            'status' => 'nullable|in:all,active,inactive,blacklisted',
            'per_page' => 'nullable|in:10,25,50',
        ]);
        $search = trim((string) $request->input('q', ''));
        $query = Customer::query();
        if (auth()->user()->role === 'Admin') {
            $query->whereNotNull('BC');
        } else {
            $query->where('BC', auth()->user()->BC);
        }

        if (in_array($request->input('status'), ['active', 'inactive', 'blacklisted'], true)) {
            $inactiveNics = Customer::where('Status', 0)->pluck('NIC')
                ->map(fn ($nic) => CustomerPawnPolicy::key((string) $nic))->unique()->values()->all();
            if ($request->input('status') === 'active') {
                $query->whereNotIn('NIC', $inactiveNics);
            } else {
                $query->whereIn('NIC', $inactiveNics);
            }
        }

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

        $page = $query->orderByDesc('id')
            ->simplePaginate((int) $request->input('per_page', 25))
            ->withQueryString();
        $policy = app(CustomerPawnPolicy::class);
        $keys = $page->getCollection()->pluck('NIC')
            ->map(fn ($nic) => CustomerPawnPolicy::key((string) $nic))->unique()->values()->all();
        $policyRows = $keys ? Customer::whereIn('NIC', $keys)->get()
            ->groupBy(fn (Customer $row) => CustomerPawnPolicy::key((string) $row->NIC)) : collect();
        $page->getCollection()->each(function (Customer $customer) use ($policy, $policyRows) {
            $matching = $policyRows->get(CustomerPawnPolicy::key((string) $customer->NIC), collect([$customer]));
            $effective = $policy->policy($matching);
            $customer->setAttribute('effective_status', $effective['active'] ? 1 : 0);
            $customer->setAttribute('effective_amount_limit', $effective['amount_limit']);
            $customer->setAttribute('effective_count_limit', $effective['count_limit']);
        });
        return $page;
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
