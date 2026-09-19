@extends('layouts.topnavbar')
@extends('layouts.sidebar')
@section('content')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Create Invoice</title>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"
        integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>

    <script src="http://cdn.bootcss.com/jquery/2.2.4/jquery.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" href="http://cdn.bootcss.com/toastr.js/latest/css/toastr.min.css">
    <style>
        .item-description-wrapper {
            display: inline-block;
            max-width: 400px;
            white-space: normal;
        }
    </style>
    
</head>

<body>
    <div class="main-wrapper">
        <div class="page-wrapper">
<!-- Materio Styled Laravel Blade Invoice Form -->
<div class="content container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card border-0 shadow rounded">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">🧾 Create Invoice</h4>
                </div>

                <div class="card-body">
                    @if (session('delete'))
                        <div class="alert alert-danger shadow-sm text-center">{{ session('delete') }} &#10004;</div>
                    @endif
                    @if (session('added'))
                        <div class="alert alert-success shadow-sm text-center">{{ session('added') }} &#10004;</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger shadow-sm">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if (Session::has('done'))
                        <div class="alert alert-success shadow-sm text-center">{{ Session::get('done') }}</div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                var pdfLink = "{{ Session::get('pdfLink') }}";
                                var newWindow = window.open(pdfLink, '_blank');
                                newWindow.onload = function () {
                                    newWindow.print();
                                };
                            });
                        </script>
                    @endif

                     <form action="{{route('add_invoice')}}" method="post" id="sales_form">
                        @csrf
                        <!-- Invoice Header Section -->
                    <div class="row mb-4">
                            <!-- Customer Code -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="searchCustomer">Customer Code</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-id-card"></i>
                                    </span>
                                    <input type="text" name="customer_nic" id="searchCustomer" class="form-control" placeholder="Enter Customer NIC" required>
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCustomerModel">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Invoice No -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="invoice_no">Invoice No</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-file-invoice"></i>
                                    </span>
                                  <input type="text" name="invoice_no" id="invoice_no" 
                                  class="form-control" value="{{ $generatedInvoiceNo }}" readonly>
                                </div>
                            </div>

                            <!-- Invoice Date -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="invoice_date">Date</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-calendar-alt"></i>
                                    </span>
                                    <input type="date" name="invoice_date" id="invoice_date" class="form-control">
                                </div>
                            </div>
                        </div>


                                    <div class="row form-group">
                                <div class="col-md-4 ">
                                </div>
                                <div class="row">

                                    <div class="customer-data">

                                    </div>
                                    <div class="showCustomer">

                                    </div>

                                </div>
                            </div>

                        <!-- Items Table -->
              <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Item Code</th>
                                <th>Description</th>
                                <th>Unit Price</th>
                                <th>Qty</th>
                                <th>Discount (%)</th>
                                <th>Discount Val</th>
                                <th>Net Value</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="input-group">
                                        <input type="text" id="item_code" name="item_code" class="form-control">
                                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#searchItemModel">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <select id="item_description" name="item_description" class="form-select">
                                        <option value="">Select an item</option>
                                        @foreach($itemCode as $itemData)
                                            <option value="{{ $itemData->Item_description }}">{{ $itemData->Item_description }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="text" name="unit_price" id="unit_price" class="form-control" value="0"></td>
                                <td><input type="text" name="qty" id="qty" class="form-control"></td>
                                <td><input type="number" name="discount" id="discount" class="form-control" value="0" min="0" max="100"></td>
                                <td><input type="text" name="discount_val" id="discount_val" class="form-control" value="0"></td>
                                <td><input type="text" name="net_value" id="net_value" class="form-control" value="0"></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-outline-info btn-lg shadow add-item">
                                        Add <i class="fas fa-plus"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Dynamic Table Display -->
                <table class="table table-bordered mt-3" id="dynamicAdded">
                    <tbody></tbody>
                </table>


                        <!-- Totals Table -->
                        <table class="table table-bordered mt-3">
                            <tbody>
                                <tr class="table-success">
                                    <td colspan="4"><strong>Total:</strong></td>
                                    <td class="text-center total-unit-price"><strong>0.00</strong></td>
                                    <td class="text-center total-discount"><strong>0.00</strong></td>
                                    <td class="text-center total-value"><strong>0.00</strong></td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Payment Section -->
    <!-- Stylish Payment Section with Icons -->
                <div class="row g-3 mt-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold" for="cash_payment">Cash Pay</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-money-bill-wave"></i></span>
                            <input type="text" id="cash_payment" name="cash_payment" class="form-control" placeholder="Enter Cash Payment">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold" for="total_amount">Gross Amount</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-calculator"></i></span>
                            <input type="text" id="total_amount" name="gross_amount" class="form-control" placeholder="Total Amount">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold" for="credite_payment">Credit</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-credit-card"></i></span>
                            <input type="text" id="credite_payment" name="credite_payment" class="form-control" placeholder="Enter Credit Amount">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold" for="paid_discount">Discount</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-percentage"></i></span>
                            <input type="text" id="paid_discount" name="discount" class="form-control" placeholder="Enter Discount">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold" for="cheque_payment">Cheque</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-receipt"></i></span>
                            <input type="text" id="cheque_payment" name="cheque_payment" class="form-control" placeholder="Enter Cheque Amount">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold" for="paid_amount">Net Amount</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-dollar-sign"></i></span>
                            <input type="text" id="paid_amount" name="net_amount" class="form-control" placeholder="Net Payable Amount">
                        </div>
                    </div>
                </div>



                        <!-- Action Buttons -->
                        <div class="mt-4 d-flex gap-3">
                            <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                                <i class="fas fa-save me-1"></i> Save
                            </button>
                            <button type="button" class="btn btn-success btn-lg shadow-sm print_invoice">
                                <i class="fas fa-print me-1"></i> Print
                            </button>
                            @if(Auth::check() && (Auth::user()->role == 'Admin' || Auth::user()->role == 'developer'))
                                <button type="button" id="deleteInvoice" class="btn btn-danger btn-lg shadow-sm pawn_delete">
                                    <i class="fas fa-trash-alt me-1"></i> Delete
                                </button>
                            @endif
                            <button type="reset" class="btn btn-secondary btn-lg shadow-sm">
                                <i class="fas fa-undo me-1"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
    </div>
                         {{--------------search Item Model----------------- --}}
                            <div class="modal fade" id="searchItemModel" tabindex="-1" role="dialog"
                                aria-labelledby="searchItemModelLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title m-2" id="searchItemModelLabel"> Search Item </h4>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close">
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <div class="errMsgContainer"></div>
                                                            <form action="" method="post" id="getItemCode">
                                                                @csrf
                                                                <div class="col"></div>
                                                             

                                                                <div class="col"></div>
                                                            </form>

                                                            {{-- -------------Item Details Table -------------- --}}
                                                            <div class="card-body ">
                                                                <div class="table-responsive ">
                                                                    <div class="table-data ">
                                                                        <table
                                                                            class="table table-bordered table-center table-hover mt-3"
                                                                            id="ItemTable">
                                                                            <thead>
                                                                                <tr class="table-secondary">
                                                                                    <th style="text-align: center;width:10%;">Action</th>
                                                                                    <th style="text-align: center;width:20%;">Barcode</th>
                                                                                    <th style="text-align: center;width:25%;">Item Name</th>
                                                                                    <th style="text-align: center;width:15%;">Stock</th> 
                                                                                    <th style="text-align: center;width:15%;">Sales Price</th>
                                                                                    <th style="text-align: center;width:15%;">Puruchase Price</th> 
                                                                                    <th style="text-align: center;width:20%;">Code</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                    
                                                                                @foreach ($stockDetails as $key=>$ItemData)
                                                                                <tr>
                                                                                    <td>
                                                                                        <a href=""
                                                                                            class="btn btn-outline-info btn-sm shadow"
                                                                                            name="add_item"
                                                                                            id="add_item"
                                                                                            data-bs-toggle="modal"
                                                                                            data-bs-target="#searchItemModel"
                                                                                            data-id="{{$ItemData->id}}"
                                                                                            data-add_item_code="{{$ItemData->Item_code}}"
                                                                                            data-Item_description="{{$ItemData->Item_description}}">
                                                                                            Add <i class="fas fa-plus"></i>
                                                                                        </a>
                                                                                    </td>
                                                                                    
                                                                                    <td>{{$ItemData->Bar_code }}</td>
                                                                                    <td>
                                                                                        <div class="">
                                                                                            {{$ItemData->Item_description}}
                                                                                        </div>
                                                                                    </td>
                                                                                    <td>
                                                                                        <div class="{{ $ItemData->total_qun_in - $ItemData->total_qun_out < 0 ? 'text-danger' : '' }}">
                                                                                            {{$ItemData->total_qun_in - $ItemData->total_qun_out }}
                                                                                        </div>                                                                                        
                                                                                    </td>
                                                                                    <td style="text-align: right">{{$ItemData->saleprice}}</td>
                                                                                    <td style="text-align: right"></td>
                                                                                    <td>{{$ItemData->Item_code}}</td>
                                                                                    
                                                                                    
                                                                                </tr>
                                                                                @endforeach
                                                                            </tbody>
                                                                        </table>

                                                                        <script>
                                                                            $(document).ready(function() {
                                                                                $('#ItemTable').DataTable({
                                                                                    "lengthMenu": [ [5,500, 10, 25], [5,500, 10, 25] ]
                                                                                });
                                                                            });
                                                                        </script>
        
                                                                        <div class="ml-4 mb-3 mt-1">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>


                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


    {!! Toastr::message() !!}

    {{-- form default date set for today --}}
    <script>
        var dateObj = new Date();
        document.getElementById('invoice_date').value = dateObj.toISOString().slice(0, 10);

    </script>

    {{-- CSRF Token --}}
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

    </script>

<script>
    // Custom validation to ensure only one payment type is filled
    document.getElementById('sales_form').addEventListener('submit', function (event) {
        var cashPayment = document.getElementById('cash_payment').value.trim();
        var creditPayment = document.getElementById('credit_payment').value.trim();
        var chequePayment = document.getElementById('cheque_payment').value.trim();

        var filledCount = [cashPayment, creditPayment, chequePayment].filter(function (payment) {
            return payment !== '';
        }).length;

        if (filledCount !== 1) {
            alert('Please fill exactly one payment type.');
            event.preventDefault(); // Prevent form submission
        }
    });
</script>

{{-- Disable form auto-submit on Enter key press --}}
<script>
    document.getElementById('sales_form').addEventListener('keydown', function (event) {
       if (event.keyCode == 13 || event.keyCode == 10) {
           event.preventDefault();
       }
   });
</script>

    {{-- value auto calculation --}}
    <script>
        $(document).ready(function () {
            // Listen for changes in the amount and advance fields
            $('#amount, #advance').on('input', function () {
                // Get values from the Weight and QTY fields
                let amount = parseFloat($('#amount').val()) || 0;
                let advance = parseInt($('#advance').val()) || 0;

                // Calculate the value
                let balance = (amount - advance).toFixed(2);

                // Update the Value field
                $('#balance').val(balance);
            });
        });

    </script>
    
    {{-- print invoice script --}}








{{-- Item description show acording to item code --}}
<script>
    $(document).ready(function () {
        // Listen for changes in the code name fields
        $('#item_code').on('keyup', function () {
            setItemDetails();

        });
    });
</script>




{{-- select items using table row as a button --}}
<script>
    var table = document.getElementById("ItemTable");
    var rows = table.getElementsByTagName("tr");
    // Add a click event listener to each row
    for (var i = 0; i < rows.length; i++) {
        rows[i].addEventListener("click", function() {
            var item_code_add = this.cells[0].textContent;
            $('#item_code').val(item_code_add);
            setItemDetails();
            $("#searchItemModel").modal('hide');
            $('#item_name').val("");
            $('#getItemCode').reset();
        });
    }
</script>

{{-- set item data function when inserting the item code or name --}}
<script>
    function setItemDetails() {
                // Listen for changes in the Weight and QTY fields
                let Item_code = $('#item_code').val();
                $.ajax({
                    url: "{{ route('show_select_item_description_ajax') }}",
                    method: 'GET',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        Item_code: Item_code
                    },
                    success: function (res) {
                        if (res.status == 'success') {

                            var itemDescriptionSelected = $('#item_description');
                            var itemUnit_price = $('#unit_price');
                            var item_s_code = $('#item_s_code');
                            // Change this to match your items select element
                            // Clear existing options
                            itemDescriptionSelected.empty();
                            itemUnit_price.empty();
                            item_s_code.empty();

                            $.each(res.data, function (index, item) {
                                itemDescriptionSelected.append($(
                                    '<option>', {
                                        value: item.Item_description,
                                        text: item.Item_description
                                    }));

                                itemUnit_price.val(item.saleprice);
                                item_s_code.val(item.Bar_code);
                            });
                        }
                    },
                    error: function (err) {
                        $('.errMsgContainer').html('');
                        let error = err.responseJSON;
                        $.each(error.errors, function (index, value) {
                            $('.errMsgContainer').append(
                                '<span class="text-danger">' + value + '<span>' + '<br>'
                            );
                        });
                    }
                });
    }
</script>

{{--  add item details to form when click Add button --}}
<script>
    $(document).ready(function () {
        // add item details to form
        $(document).on('click', '#add_item', function () {
            let id = $(this).data('id');
            let add_item_code = $(this).data('add_item_code');
            $('#item_code').val(add_item_code);
            setItemDetails();
        });
    });
</script>


    {{--  get customer data inserting NIC --}}
    <script>
        $(document).ready(function () {
            // search customer data
            $(document).on('keyup', function (e) {
                e.preventDefault();
                var search_string = $('#searchCustomer').val();
                // console.log(search_string);
                if (search_string != null) {
                    if (e.keyCode == 13 || e.keyCode == 10) {
                        $.ajax({
                            url: "{{ route('get_customer_ajax') }}",
                            method: 'GET',
                            data: {
                                search_string: search_string
                            },
                            success: function (res) {
                                $('.showCustomer').html(res);

                                if (res.status == 'not_found') {
                                    $('.showCustomer').html(
                                        `<div class="input-group">
                                            <p class="form-control text-danger text-center">
                                                Customer Not Found ..!!
                                            </p>
                                         </div>`
                                    );
                                }
                            }
                        });
                    }
                }
            })
        });

    </script>
    


{!! Toastr::message() !!}


<script>
    $(document).ready(function () {
    var i = -1;
    let dataArray = [];
    let totalGross = 0;
    let totalDiscount = 0;
    let totalValue = 0;

    $(".add-item").click(function () {
        i++;

        let Store_code = $('#Store_code').val() || '';
        let invoice_no = $('#invoice_no').val() || '';
        let invoice_date = $('#invoice_date').val() || '';
        let item_code = $('#item_code').val();
        let item_description = $('#item_description').val();
        let qty = $('#qty').val();
        let qtyOut = $('#qtyOut').val();
        let unit_price = $('#unit_price').val();
        let discount = $('#discount').val();
        let discount_val = $('#discount_val').val();
        let net_value = $('#net_value').val();

        if (!item_code || !item_description || !qty || !unit_price || !net_value) {
            alert("Please fill in all the fields.");
            return;
        }

        let newRowData = {
            Store_code, invoice_no, invoice_date,
            item_code, item_description,
            qty, qtyOut, unit_price,
            discount, discount_val, net_value
        };

        dataArray.push(newRowData);

        $("#dynamicAdded tbody").prepend(`
            <tr>
                <td>
                    <input type="hidden" name="inputs[${i}][Store_code]" value="${Store_code}">
                    <input type="hidden" name="inputs[${i}][invoice_no]" value="${invoice_no}">
                    <input type="hidden" name="inputs[${i}][invoice_date]" value="${invoice_date}">
                    <input class="form-control text-center" name="inputs[${i}][item_code]" value="${item_code}" readonly>
                </td>
                <td>
                    <input class="form-control text-center" name="inputs[${i}][item_description]" value="${item_description}" readonly>
                </td>
                <td>
                    <input class="form-control text-center" name="inputs[${i}][qty]" value="${qty}" readonly>
                </td>

                <td>
                    <input class="form-control text-center" name="inputs[${i}][unit_price]" value="${unit_price}" readonly>
                </td>
                <td>
                    <input class="form-control text-center" name="inputs[${i}][discount_val]" value="${discount_val}" readonly>
                </td>
                <td>
                    <input class="form-control text-center" name="inputs[${i}][net_value]" value="${net_value}" readonly>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger shadow remove-input-field">
                        <i class="far fa-trash-alt me-1"></i> Delete
                    </button>
                </td>
            </tr>
        `);

        totalGross += parseFloat(unit_price) * parseFloat(qty);
        totalDiscount += parseFloat(discount_val);
        totalValue += parseFloat(net_value);
        setTotal();
        resetTableRow();
    });

    $(document).on('click', '.remove-input-field', function () {
        let row = $(this).closest('tr');
        let unit_price = parseFloat(row.find('input[name$="[unit_price]"]').val());
        let qty = parseFloat(row.find('input[name$="[qty]"]').val());
        let discount_val = parseFloat(row.find('input[name$="[discount_val]"]').val());
        let net_value = parseFloat(row.find('input[name$="[net_value]"]').val());

        totalGross -= (unit_price * qty);
        totalDiscount -= discount_val;
        totalValue -= net_value;
        setTotal();

        row.remove();
    });

    function resetTableRow() {
        $('#item_code').val("");
        $('#item_description').val("");
        $('#qty').val("");
        $('#qtyOut').val("");
        $('#unit_price').val("0");
        $('#discount').val("0");
        $('#discount_val').val("0");
        $('#net_value').val("0");
    }

    function setTotal() {
        $('.total-unit-price').html(`<p><strong>${totalGross.toFixed(2)}</strong></p>`);
        $('.total-discount').html(`<p><strong>${totalDiscount.toFixed(2)}</strong></p>`);
        $('.total-value').html(`<p><strong>${totalValue.toFixed(2)}</strong></p>`);
        $('#total_amount').val(totalGross.toFixed(2));
        $('#paid_discount').val(totalDiscount.toFixed(2));
        $('#paid_amount').val(totalValue.toFixed(2));
    }
});

</script>
    {{-- delete added item rows script --}}
    <script>
        $(document).on('click', '.remove-input-field', function () {
            var row = $(this).parents('tr');
            var re_item_code = row.find('#dy_item_code');
            var re_item_description = row.find('#dy_item_description');
            var re_qty = row.find('#dy_qty');
            var re_unit_price = row.find('#dy_unit_price');
            var re_total_discount = row.find('#dy_discount_val');
            var re_net_value = row.find('#dy_net_value');

            // var re_weightValue = re_weight.val();
            var re_grossValue = re_unit_price.val()*re_qty.val();
            var re_discountValue = re_total_discount.val();
            var re_valueValue = re_net_value.val();

            // totalWeight = parseInt(totalWeight) - parseInt(re_weightValue);
            // total_totalWeight = parseInt(total_totalWeight) - parseInt(re_total_weightValue);
            totalGross = parseInt(totalGross) - parseInt(re_grossValue);
            totalDiscount = parseInt(totalDiscount) - parseInt(re_discountValue);
            totalValue = parseInt(totalValue) - parseInt(re_valueValue);

            // Remove data from the array
            var index = row.index();
            dataArray.splice(index, 1);

            $(this).parents('tr').remove();
            setTotal();
        });

    </script>

    {{-- change net value calculation when change the QTY field --}}
    <script>
        $(document).on('keyup', '#qty', function () {
            let unit_price_value = $('#unit_price').val();
            let unit_qty = $('#qty').val();
            // let qty_decimal = Math.trunc(unit_qty);
            let net_value_row = unit_qty * unit_price_value;

            if (unit_qty != null) {
                $('#net_value').val(net_value_row);
            }
        })

    </script>

    {{-- change net value calculation when change the discount precentag field --}}
    <script>
        $(document).on('keyup', '#discount', function () {
            let unit_price_value = $('#unit_price').val();
            let unit_qty = $('#qty').val();
            // let qty_decimal = Math.trunc(unit_qty);
            let net_value_row = unit_qty * unit_price_value;

            let unit_discount = $('#discount').val();
            let discounted_value = net_value_row * (unit_discount / 100);
            let discounted_net_value = net_value_row - discounted_value;

            if (unit_discount > 0) {
                $('#discount_val').val(discounted_value);
                $('#net_value').val(discounted_net_value);
            } else {
                // $('#discount').val("0");
                $('#net_value').val(net_value_row);
            }
        })

    </script>

    {{-- change net value calculation when change the discount value field --}}
    <script>
            $(document).on('keyup', '#discount_val', function () {
                let unit_price_value = $('#unit_price').val();
                let unit_qty = $('#qty').val();
                // let qty_decimal = Math.trunc(unit_qty);
                let net_value_row = unit_qty * unit_price_value;

                let unit_discount = $('#discount_val').val();
                let discounted_value = unit_discount;
                let discounted_net_value = net_value_row - discounted_value;

                if (unit_discount > 0) {
                    // $('#discount_val').val(discounted_value);
                    $('#net_value').val(discounted_net_value);
                } else {
                    // $('#discount').val("0");
                    $('#net_value').val(net_value_row);
                }
            })

    </script>

<script>
    function fillCustomerCode(code) {
        const input = document.getElementById('searchCustomer');
        input.value = code;

        // Send the AJAX request immediately
        $.ajax({
            url: "{{ route('get_customer_ajax') }}",
            method: 'GET',
            data: {
                search_string: code
            },
            success: function (res) {
                if (res.status == 'not_found') {
                    $('.showCustomer').html(
                        `<div class="input-group">
                            <p class="form-control text-danger text-center">
                                Customer Not Found ..!!
                            </p>
                         </div>`
                    );
                } else {
                    $('.showCustomer').html(res);
                }
            }
        });
    }
</script>


                <script src="assets/js/jquery-3.6.0.min.js"></script>
                <script src="assets/js/feather.min.js"></script>
                <script src="assets/js/toastr.min.js"></script>

                <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
                <script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
                <script src="assets/plugins/datatables/datatables.min.js"></script>
                <script src="assets/js/script.js"></script>
                <script src="assets/plugins/apexchart/apexcharts.min.js"></script>
                <script src="assets/plugins/apexchart/chart-data.js"></script>
                {{-- <script src="http://cdn.bootcss.com/toastr.js/latest/js/toastr.min.js"></script> --}}
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
                    integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz"
                    crossorigin="anonymous">
                </script>

</body>
</html>
@endsection