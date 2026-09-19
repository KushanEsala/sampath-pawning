<table class="table table-bordered table-center table-hover datatable " id="customerTable">
    <thead >
        <tr class="table-secondary">
            <th>Code</th>
            <th>Title</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Gender</th>
            <th>Contact</th>
            <th>Email</th>
            <th>NIC</th>
            <th>Status</th>
            <th>Address</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>

        @foreach ($customers as $key=>$data)
        <tr>
            <td>{{$data->Code}}</td>
            <td>{{$data->Title}}</td>
            <td>{{$data->First_name}}</td>
            <td>{{$data->Last_name}}</td>
            <td>{{$data->Gender}}</td>
            <td>{{$data->Contact_1}}</td>
            <td>{{$data->Email}}</td>
            <td>{{$data->NIC}}</td>
            <td>
                @if ($data->Blacklisted == 1)
                <span style="color: rgb(252, 4, 4)">Blacklisted</span>
                @else
                <span style="color: rgb(20, 247, 13)">Active</span>
                @endif
            </td>
            <td>{{$data->Address_1}}</td>
            <td>
                {{-- ..............Edit button................................. --}}
                {{-- <button type="button" class="btn btn-sm btn-success bg-success-light text-success me-2" data-bs-toggle="modal"
                    data-bs-target="#edit-customer-{{$data->Code}}">
                    <i class="far fa-edit me-1"></i> Edit
                </button> --}}
                <a href=""
                    class="btn btn-sm btn-success update_customer_form bg-success-light text-success me-2"
                    data-bs-toggle="modal" data-bs-target="#updateCustomerModel"
                    data-id="{{$data->id}}"
                    data-code="{{$data->Code}}"
                    data-title="{{$data->Title}}"
                    data-gender="{{$data->Gender}}"
                    data-name="{{$data->Name}}"
                    data-address1="{{$data->Address_1}}"
                    data-address2="{{$data->Address_2}}"
                    data-contact1="{{$data->Contact_1}}"
                    data-contact2="{{$data->Contact_2}}"
                    data-email="{{$data->Email}}"
                    data-nic="{{$data->NIC}}"
                    data-driving_license="{{$data->Driving_license}}"
                    data-passport="{{$data->Passport}}"
                    data-other_identifications="{{$data->Other_identifications}}"
                    data-blacklisted="{{$data->Blacklisted}}" >
                    <i class="far fa-edit me-1"></i> Edit
                </a>

                <button type="button" class="btn btn-sm delete_customer btn-danger bg-danger-light text-danger me-2"
                data-id="{{$data->id}}">
                    <i class="far fa-trash-alt me-1"></i> Delete
                </button>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
{!! $customers->links() !!}
