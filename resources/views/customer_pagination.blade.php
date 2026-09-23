<div class="customer-register-meta">
    <span>Showing {{ $customers->firstItem() ?? 0 }}–{{ $customers->lastItem() ?? 0 }} customers in this branch</span>
    <span>Use View to open contact and identification details.</span>
</div>
<div class="table-responsive customer-register-wrap">
    <table class="table table-hover align-middle customer-register mb-0">
        <thead><tr>
            <th class="customer-view-cell">Details</th>
            <th>Code</th>
            <th>Customer</th>
            <th>NIC</th>
            <th>Telephone</th>
            <th>Status</th>
        </tr></thead>
        <tbody>
        @forelse($customers as $data)
            @php($detailId = 'customer-detail-'.$data->id)
            <tr>
                <td><button type="button" class="btn btn-sm btn-outline-primary customer-detail-toggle" data-target="{{ $detailId }}" aria-controls="{{ $detailId }}" aria-expanded="false"><i class="far fa-eye me-1"></i>View</button></td>
                <td class="customer-code">{{ $data->Code }}</td>
                <td class="customer-name">{{ trim(implode(' ', array_filter([$data->Title, $data->First_name, $data->Middle_name, $data->Last_name]))) ?: ($data->Name ?: '—') }}</td>
                <td class="customer-code">{{ $data->NIC ?: '—' }}</td>
                <td class="customer-phone">{{ $data->Contact_1 ?: '—' }}</td>
                <td><span class="customer-status {{ (int) $data->Status === 1 ? 'is-active' : 'is-blacklisted' }}">{{ (int) $data->Status === 1 ? 'Active' : 'Blacklisted' }}</span></td>
            </tr>
            <tr id="{{ $detailId }}" class="customer-detail-row d-none">
                <td colspan="6">
                    <div class="customer-detail-panel">
                        <div class="customer-detail-grid">
                            <div><span class="customer-detail-label">Address</span><div>{{ $data->Address_1 ?: '—' }}@if($data->City_1), {{ $data->City_1 }}@endif</div></div>
                            <div><span class="customer-detail-label">Other address</span><div>{{ $data->Address_2 ?: '—' }}@if($data->City_2), {{ $data->City_2 }}@endif</div></div>
                            <div><span class="customer-detail-label">Additional contact</span><div>{{ $data->Contact_2 ?: '—' }}</div></div>
                            <div><span class="customer-detail-label">Email</span><div>{{ $data->Email ?: '—' }}</div></div>
                            <div><span class="customer-detail-label">Gender</span><div>{{ $data->Gender ?: '—' }}</div></div>
                            <div><span class="customer-detail-label">Driving licence</span><div>{{ $data->Driving_license ?: '—' }}</div></div>
                            <div><span class="customer-detail-label">Passport</span><div>{{ $data->Passport ?: '—' }}</div></div>
                            <div><span class="customer-detail-label">Other identification</span><div>{{ $data->Other_identifications ?: '—' }}</div></div>
                        </div>
                        <div class="customer-detail-actions">
                            <button type="button" class="btn btn-sm btn-success update_customer_form"
                                data-bs-toggle="modal" data-bs-target="#updateCustomerModel"
                                data-id="{{ $data->id }}" data-code="{{ $data->Code }}"
                                data-title="{{ $data->Title }}" data-gender="{{ $data->Gender }}"
                                data-first_name="{{ $data->First_name }}" data-middle_name="{{ $data->Middle_name }}"
                                data-last_name="{{ $data->Last_name }}" data-address1="{{ $data->Address_1 }}"
                                data-city1="{{ $data->City_1 }}" data-address2="{{ $data->Address_2 }}"
                                data-city2="{{ $data->City_2 }}" data-contact1="{{ $data->Contact_1 }}"
                                data-contact2="{{ $data->Contact_2 }}" data-email="{{ $data->Email }}"
                                data-nic="{{ $data->NIC }}" data-driving_license="{{ $data->Driving_license }}"
                                data-passport="{{ $data->Passport }}" data-other_identifications="{{ $data->Other_identifications }}"
                                data-status="{{ $data->Status }}"><i class="far fa-edit me-1"></i>Edit customer</button>
                            <button type="button" class="btn btn-sm btn-outline-danger delete_customer" data-id="{{ $data->id }}"><i class="far fa-trash-alt me-1"></i>Delete</button>
                        </div>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="customer-empty">No customers match this search. Try a name, NIC, phone number or code.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="customer-pager">{{ $customers->links() }}</div>
