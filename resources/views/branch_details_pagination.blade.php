<table class="table table-bordered " id="branchTable">
    <thead>
        <tr class="table-secondary">
            {{-- <th>No</th> --}}
            <th>BC Code</th>
            <th>Name</th>
            <th>Address</th>
            <th>Contact 1</th>
            <th>Contact 2</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($branchDel as $key=>$branch)
            <tr>
                {{-- <th>{{$key+1}}</th> --}}
                <th>{{$branch->bccode }}</th>
                <td>{{$branch->name }}</td>
                <td>{{$branch->address}}</td>
                <td>{{$branch->contact1}}</td>
                <td>{{$branch->contact2}}</td>
                <td>{{$branch->date}}</td>
                <td>
                    <a href=""
                        class="btn btn-sm btn-success update_branch_form bg-success-light text-success me-2"
                        data-bs-toggle="modal" data-bs-target="#updateBranchModel"
                        data-id="{{$branch->id}}"
                        data-bccode="{{$branch->bccode}}"
                        data-name="{{$branch->name}}"
                        data-address="{{$branch->address}}"
                        data-contact1="{{$branch->contact1}}"
                        data-contact2="{{$branch->contact2}}"
                        data-date="{{$branch->date}}">
                        <i class="far fa-edit me-1"></i> Edit
                    </a>
                    <a href=""
                        class="btn btn-sm btn-danger delete_branch bg-danger-light text-danger me-2"
                        data-id="{{$branch->id}}">
                        <i class="far fa-trash-alt me-1"></i> Delete
                    </a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
{!! $branchDel->links() !!}
