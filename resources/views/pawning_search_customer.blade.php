<style>
    .customer-card {
        background: #ffffff;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
    }

    .stat-box input {
        font-size: 1.3rem;
        font-weight: bold;
        text-align: center;
        border: none;
        background: #f8f9fa;
    }

    .stat-box label {
        width: 100%;
        font-weight: 600;
        text-align: center;
    }

    .customer-label {
        font-weight: 600;
        color: #555;
    }
</style>

<style>
    .customer-card {
        background: #ffffff;
        border-radius: 14px;
        padding: 24px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
        margin-bottom: 24px;
        border: 1px solid #eef0f2;
        transition: box-shadow 0.2s ease;
    }

    .customer-card:hover {
        box-shadow: 0 8px 22px rgba(0, 0, 0, 0.09);
    }

    .stat-box {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 10px 8px;
        text-align: center;
    }

    .stat-box label {
        width: 100%;
        font-weight: 600;
        font-size: 0.8rem;
        letter-spacing: 0.03em;
        text-align: center;
        margin-bottom: 6px;
        display: block;
    }

    .stat-box input {
        font-size: 1.3rem;
        font-weight: bold;
        text-align: center;
        border: none;
        background: #ffffff;
        border-radius: 6px;
        padding: 6px 4px;
        width: 100%;
    }

    .customer-label {
        font-weight: 600;
        color: #555;
        font-size: 0.85rem;
        margin-bottom: 4px;
        display: block;
    }

    .form-control-lg {
        border-radius: 8px;
        border: 1px solid #e2e5e9;
    }

    .form-control-lg[readonly] {
        background-color: #fbfbfc;
    }
</style>

@if(isset($customer_get) && $customer_get->count() > 0)

    @foreach ($customer_get as $customer)

        <div class="customer-card">
            <div class="row g-3 align-items-center">

                <!-- Hidden Customer ID -->
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">

                <!-- Late (Optional – Set 0 if not calculated) -->
                <div class="col-md-1 stat-box">
                    <label class="text-danger">
                        LATE
                        <input type="text"
                               class="form-control text-danger"
                               value="{{ $customer->LateRedim ?? 0 }}"
                               readonly>
                    </label>
                </div>

                <!-- Pending -->
                <div class="col-md-1 stat-box">
                    <label class="text-warning">
                        PENDING
                        <input type="text"
                               class="form-control text-warning"
                               value="{{ $pending_count }}"
                               readonly>
                    </label>
                </div>

                <!-- Redeemed -->
                <div class="col-md-1 stat-box">
                    <label class="text-success">
                        REDEEMED
                        <input type="text"
                               class="form-control text-success"
                               value="{{ $redeemed_count }}"
                               readonly>
                    </label>
                </div>

                <!-- Customer Name -->
                <div class="col-md-3">
                    <label class="customer-label">Customer Name</label>
                    <input type="text"
                           class="form-control form-control-lg" name="customer_name"
                           value="{{ $customer->First_name }} {{ $customer->Last_name }}"
                           readonly>
                </div>

                <!-- Telephone -->
                <div class="col-md-2">
                    <label class="customer-label" for="customer_contact_1">Telephone Number</label>
                    <input type="text" name="customer_contact_1" id="customer_contact_1"
                           class="form-control form-control-lg"
                           value="{{ $customer->Contact_1 }}"
                           readonly>
                </div>

                <!-- Address -->
                <div class="col-md-4">
                    <label class="customer-label">Customer Address</label>
                    <input type="text" name="customer_address"
                           class="form-control form-control-lg"
                           value="{{ $customer->Address_1 }}"
                           readonly>
                </div>



                <div class="col-12">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="customer-label">Total Pawn Amount</label>
                        <input type="text" class="form-control text" id="pendingPawnTotal"
                               value="{{ $pendingPawnTotal }}" readonly>
                    </div>

                          <div class="col-md-3">
                       <label class="customer-label">Current Pawn Count</label>
                         <input type="text" class="form-control text" id="currentPawnCount"
                      value="{{ $pending_count }}" readonly>

                    </div>

                    <div class="col-md-3">
                        <label class="customer-label">Limit Amount</label>
                    <input type="text" class="form-control text" id="limitAmount"
                      value="{{ $Limit_Amount }}" readonly>
                    </div>


                    <div class="col-md-3">
                       <label class="customer-label">Limit Pawn Count</label>
                         <input type="text" class="form-control text" id="limitPawnCount"
           value="{{ $Limit_Pawn_Count }}" readonly>

                    </div>
                </div>
                </div>

</div>



                <!-- Hidden Fields -->
                <input type="hidden" name="first_name" value="{{ $customer->First_name }}">
                <input type="hidden" name="middle_name" value="{{ $customer->Middle_name }}">
                <input type="hidden" name="last_name" value="{{ $customer->Last_name }}">

        </div>

    @endforeach

@else
    <div class="alert alert-warning text-center">
        No customer found for this NIC.
    </div>
@endif
