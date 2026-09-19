<head>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-datetimepicker.min.css') }}">
    <script src="{{ asset('assets/js/sidebar-navigation.js') }}" defer></script>
    <!--<link rel="stylesheet" href="http://cdn.bootcss.com/toastr.js/latest/css/toastr.min.css">-->
  <style>

        .sidebar{
            background-color: rgb(248, 248, 248)
        }
        @media (max-width: 991px) {
            .sidebar.sidebar-mobile-open { margin-left: 0; transform: translateX(0); }
        }

        .scroll {
            width: 100%;
            height: 100%;
            overflow-y: scroll;
            scrollbar-width: thin;
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-thumb {
            border-radius: 30px;
            background: -webkit-gradient(linear,left top,left bottom,from(#999999),to(#999999));
            box-shadow: inset 2px 2px 2px rgba(255,255,255,.25), inset -2px -2px 2px rgba(238, 237, 237, 0.25);
        }

        ::-webkit-scrollbar-track {
            background-color: #eeeeee;
            border-radius:10px;
            background: linear-gradient(to right,#eeeeee,#eeeeee 1px,#eeeeee 1px,#eeeeee);
        }

    </style>
</head>

<div class="sidebar shadow" id="sidebar">
    <div class="scroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                {{-- Main --}}
                <li class="menu-title"><span>Main</span></li>
                <li class="{{ Request::is('home') ? 'active' : '' }}">
                    <a href="{{route("home")}}"><i class="fa fa-desktop"></i> <span>Home</span></a>
                </li>

                {{-- User Management --}}
                <li class="submenu {{ Request::is('users') || Request::is('add_user')  ? 'active' : '' }} ">
                    <a href="#" class=" {{ Request::is('users') || Request::is('add_user') ? 'subdrop' : '' }} ">
                        <i class="fa fa-users"></i> <span> User Management</span>
                        <span class="menu-arrow"></span></a>
                    <ul style=" {{ Request::is('users') || Request::is('add_user') ? 'display:block;' : '' }} ">
                        <li class="{{ Request::is('users') || Request::is('add_user') ? 'active ' : '' }}">
                            <a href="{{route("users")}}">
                                <i class="fa fa-angle-right"></i>
                                Users
                            </a>
                        </li>
                        <li><a href="#">
                            <i class="fa fa-angle-right"></i>
                            Roles</a></li>

                        <li><a href="#">
                            <i class="fa fa-angle-right"></i>
                            Sales Commission Agents</a></li>
                    </ul>
                </li>
               {{-- System --}}
                <li class="{{ Request::is('Company*') ? 'active' : '' }}">
                    <a href="#"><i class="fa fa-th-list"></i> <span>System</span>
                        <span class="menu-arrow"></span></a>

                    <ul style=" {{ Request::is('Company*') || Request::is('Company_branchdetails') ? 'display:block;' : '' }}">
                        <li class="{{ Request::is('Company') ? 'active' : '' }}">
                            <a href="{{route("Company")}}">
                            <i class="fa fa-angle-right"></i>
                            Company</a>
                        </li>

                        <li class="dropdown {{ Request::is('Company_branchdetails') ? 'active' : '' }}">
                            <a href="{{route('branchdetails')}}">
                                <i class="fa fa-angle-right"></i>Branches</a>
                        </li>
                    </ul>
                </li>
                
                            <li class="{{ Request::is('home') ? 'active' : '' }}">
    <a href="{{ route('customerUpdatestatus') }}">
        <i class="fa fa-users"></i> <span>Customer</span>
    </a>
</li>


@auth
    @if(auth()->user()->role === 'Admin')
        <li class="{{ Request::routeIs('search.stock') ? 'active' : '' }}">
            <a href="{{ route('search.stock') }}">
                <i class="fa fa-search"></i>
                <span>Stock Search</span>
            </a>
        </li>
    @endif
@endauth


                {{-- Master --}}
                <li class="dropdown {{ Request::is('master*') || Request::is('BankDeltails') || Request::is('Bank_Branch') ? 'active' : '' }} ">
                    <a href="#"><i class="fa fa-sitemap"></i> <span> Master</span> <span
                            class="menu-arrow"></span></a>
                    <ul style=" {{ Request::is('master*') || Request::is('BankDeltails') || Request::is('Bank_Branch')  ? 'display:block;' : '' }}">
                        <li class="dropdown {{ Request::is('master_customers') ? 'active' : '' }}">
                            <a href="{{route("master_customers")}}">
                            <i class="fa fa-angle-right"></i>Customers</a>
                        </li>



                        <li class="{{ Request::is('master_karatage') ? 'active' : '' }}">
                            <a href="{{route('karatage')}}"><i class="fa fa-angle-right"></i>Karatage Rate Setup</a>
                        </li>

                        <li class="{{ Request::is('master_receipt') ? 'active' : '' }}">
                            <a href="{{route('master_receipt')}}"><i class="fa fa-angle-right"></i>Receipt Type</a>
                        </li>

                        <li  class="{{ Request::is('master_item_setup') ? 'active' : '' }}">
                            <a href="{{route('master_item_setup')}}"><i class="fa fa-angle-right"></i>Item Setup</a>
                        </li>

                        <li class="{{ Request::is('master_category') ? 'active' : '' }}">
                            <a href="{{route("master_category")}}"><i class="fa fa-angle-right"></i>Category</a>
                        </li>

                        <li class="{{ Request::is('master_item_condition') ? 'active' : '' }}">
                            <a href="{{route('master_item_condition')}}"><i class="fa fa-angle-right"></i>Item Condition</a>
                        </li>
                        <li class="{{ Request::is('BankDeltails') ? 'active' : '' }}">
                            <a href="{{route("BankDeltails")}}"><i class="fa fa-angle-right"></i>Bank</a>
                        </li>

                        <li class="{{ Request::is('Bank_Branch') ? 'active' : '' }}">
                            <a href="{{route('Bank_Branch')}}"><i class="fa fa-angle-right"></i>Bank Branch</a>
                        </li>
                        
                        
                                     <li class="{{ Request::is('createItem') ? 'active' : '' }}">
                            <a href="{{route('createItem')}}"><i class="fa fa-angle-right"></i>Forfeit Article</a>
                        </li>




                    </ul>
                </li>

                {{-- Pawning --}}
                <!--<li class="{{ Request::is('pawning*') ? 'active' : '' }}">-->
                <!--    <a href="#"><i class="fa fa-th-list"></i> <span>Pawning</span>-->
                <!--        <span class="menu-arrow"></span></a>-->

                <!--    <ul style=" {{ Request::is('pawning*') ? 'display:block;' : '' }}">-->

                <!--        <li class="{{ Request::is('pawning') ? 'active' : '' }}">-->
                <!--            <a href="{{route("pawning")}}">-->
                <!--            <i class="fa fa-angle-right"></i>-->
                <!--            Pawn Receipt</a>-->
                <!--        </li>-->
                       
                        
                <!--        <li class="{{ Request::is('pawning_make_payment') ? 'active' : '' }}">-->
                <!--            <a href="{{route("pawningPartPayment")}}">-->
                <!--            <i class="fa fa-angle-right"></i>-->
                <!--            Part Payment</a>-->
                <!--        </li>-->
                        
                <!--        <li class="{{ Request::is('pawning_redeem') ? 'active' : '' }}"><a href="{{route("pawning_redeem")}}">-->
                <!--            <i class="fa fa-angle-right"></i>-->
                <!--            Redeem Receipt</a>-->
                <!--        </li>-->
                <!--        <li class="{{ Request::is('repawning') ? 'active' : '' }}"><a href="{{route("repawning")}}">-->
                <!--            <i class="fa fa-angle-right"></i>-->
                <!--            Repawning Receipt</a>-->
                <!--        </li>-->

                <!--        <li class="{{ Request::is('pawning_late_letters') ? 'active' : '' }}"><a href="{{route("pawning_late_letters")}}">-->
                <!--            <i class="fa fa-angle-right"></i>-->
                <!--            Redeem Late Letters</a>-->
                <!--        </li>-->
                        
                <!--               <li class="{{ Request::is('forfeitReceipt_List') ? 'active' : '' }}"><a href="{{route('forfeitReceipt_List')}}">-->
                <!--                <i class="fa fa-angle-right"></i>-->
                <!--                Forfeit Receipt List</a>-->
                <!--        </li>-->

                <!--        <li class="{{ Request::is('pawning_forfeit_receipt') ? 'active' : '' }}"><a href="{{route('pawning_forfeit_receipt')}}">-->
                <!--                <i class="fa fa-angle-right"></i>-->
                <!--                Forfeit Receipt</a>-->
                <!--        </li>-->

                <!--        <li class="{{ Request::is('pawning_opening') ? 'active' : '' }}">-->
                <!--            <a href="{{route("pawning_opening")}}">-->
                <!--            <i class="fa fa-angle-right"></i>-->
                <!--            Opening Pawn </a>-->
                <!--        </li>-->
                <!--    </ul>-->
                <!--</li>-->
                
                              <li class="{{ Request::is('pawning*') ? 'active' : '' }}">
                    <a href="#"><i class="fa fa-th-list"></i> <span>Pawning</span>
                        <span class="menu-arrow"></span></a>

                    <ul style=" {{ Request::is('pawning*') ? 'display:block;' : '' }}">

                        <li class="{{ Request::is('pawning') ? 'active' : '' }}">
                            <a href="{{route("pawning")}}">
                            <i class="fa fa-angle-right"></i>
                            Pawn Receipt</a>
                        </li>
                       
                        
                        <li class="{{ Request::is('pawning_make_payment') ? 'active' : '' }}">
                            <a href="{{route("pawningPartPayment")}}">
                            <i class="fa fa-angle-right"></i>
                            Part Payment</a>
                        </li>
                        
                        <li class="{{ Request::is('pawning_redeem') ? 'active' : '' }}"><a href="{{route("pawning_redeem")}}">
                            <i class="fa fa-angle-right"></i>
                            Redeem Receipt</a>
                        </li>
                        <li class="{{ Request::is('repawning') ? 'active' : '' }}"><a href="{{route("repawning")}}">
                            <i class="fa fa-angle-right"></i>
                            Repawning Receipt</a>
                        </li>

                     <li class="{{ Request::is('pawning_late_letters') ? 'active' : '' }}"><a href="{{route("pawning_late_letters")}}">
                            <i class="fa fa-angle-right"></i>
                            Redeem Late Letters</a>
                        </li>

                        <li class="{{ Request::routeIs('receipt.search') ? 'active' : '' }}"><a href="{{ route('receipt.search') }}">
                            <i class="fa fa-angle-right"></i>
                            Receipt Search</a>
                        </li>

                        <li class="{{ Request::is('pawning_opening') ? 'active' : '' }}">
                            <a href="{{route("pawning_opening")}}">
                            <i class="fa fa-angle-right"></i>
                            Opening Pawn </a>
                        </li>
                    </ul>
                </li>


                <li>
                    <a href="#"><i class="fa fa-file-invoice"></i> <span>Forfeit Receipt</span>
                        <span class="menu-arrow"></span></a>

                    <ul style="">
                            <li class="{{ Request::is('forfeitReceipt_List') ? 'active' : '' }}"><a href="{{route('forfeitReceipt_List')}}">
                                <i class="fa fa-angle-right"></i>
                                Forfeit Receipt List</a>    
                        </li>

                        <li class="{{ Request::routeIs('forfeit.reminders.*') ? 'active' : '' }}"><a href="{{ route('forfeit.reminders.index') }}">
                                <i class="fa fa-angle-right"></i>
                                Forfeit Reminder List</a>
                        </li>

                        <li class="{{ Request::is('pawning_forfeit_receipt') ? 'active' : '' }}"><a href="{{route('pawning_forfeit_receipt')}}">
                                <i class="fa fa-angle-right"></i>
                                Forfeit Receipt</a>
                        </li>

                        <li class="{{ Request::is('pawning_forfeit_receipt') ? 'active' : '' }}"><a href="{{route('forfeit_article_receipt')}}">
                                <i class="fa fa-angle-right"></i>
                                Forfeit Article List</a>
                        </li>


                    </ul>
                </li>

                           {{-- Vochers --}}
                           <li class="{{ Request::is('PaymentVoucher*') ? 'active' : '' }}||
                           {{ Request::is('PaymentVoucher*') ? 'active' : '' }} || {{ Request::is('vouchers*') ? 'active' : '' }} ">
                               <a href="#">
                                   <i class="fa fa-barcode"></i> <span> Vouchers</span> <span
                                       class="menu-arrow"></span></a>
                                       <ul style="  {{ Request::is('vouchers*') ? 'display:block;' : '' }}">
                                           <li class="{{ Request::is('PaymentVoucher') ? 'active' : '' }}">
                                               <a href="{{route("PaymentVoucher")}}">
                                               <i class="fa fa-angle-right"></i>
                                               Payment Vouchers</a>
                                           </li>
                                           <li class="{{ Request::is('PettyCash') ? 'active' : '' }}">
                                               <a href="{{route("PettyCash")}}">
                                               <i class="fa fa-angle-right"></i>
                                               Petty Cash</a>
                                           </li>
                                           <li class="{{ Request::is('vouchers_add_expense') ? 'active' : '' }}">
                                                <a href="{{route("vouchers_add_expense")}}">
                                                    <i class="fa fa-angle-right"></i>
                                                    Add Expenses</a>
                                            </li>
                                            <li class="{{ Request::is('gentralreceipt') ? 'active' : '' }}">
                                                <a href="{{route("gentralreceipt")}}">
                                                <i class="fa fa-angle-right"></i>
                                                Gentral Receipt</a>
                                             </li>
                                       </ul>
                                  </li>
                                  
                                  
                                                  <li>
                    <a href="#"><i class="fa fa-file-invoice"></i> <span>Old Redeem Receipt</span>
                        <span class="menu-arrow"></span></a>

                    <ul style="">
                           <li class="{{ Request::is('oldRedeemReceipt') ? 'active' : '' }}"><a href="{{route("oldRedeemReceipt")}}">
                            <i class="fa fa-angle-right"></i>
                           Old Redeem Receipt</a>
                        </li>

                        <li class=""><a href="{{route("old_search_results")}}">
                                <i class="fa fa-angle-right"></i>
                                Old Pawning Report</a>
                        </li>

                    </ul>
                </li>
                                  
                                  
                                            <li class="">
                    <a href="#">
                        <i class="fa fa-credit-card"></i> <span> Sales Invoice </span> <span
                            class="menu-arrow"></span></a>
                            <ul style="">
                                <li class="{{ Request::is('salesInvoice') ? 'active' : '' }}">
                                 <a href="{{route("salesInvoice")}}">
                                 <i class="fa fa-angle-right"></i>
                               Sales Invoice</a>
                                </li>
                                
                                 <li><a href="{{route("salesInvoiceReport")}}"
                                    target ="_ blank" >
                                <i class="fa fa-angle-right"></i> Sales Invoice Report</a></li>
                         
                            </ul>
                </li>


           {{-- Bank --}}
           <li class="">
            <a href="#">
                <i class="fa fa-credit-card"></i> <span> Banking </span> <span
                    class="menu-arrow"></span></a>
                    <ul style="">
                        <li class="">
                            <a href="">
                            <i class="fa fa-angle-right"></i>
                            Cheque Deposit</a>
                        </li>
                        <li class="">
                            <a href="">
                            <i class="fa fa-angle-right"></i>
                            Issued Cheques </a>
                        </li>
                        <li class="">
                            <a href="">
                            <i class="fa fa-angle-right"></i>
                            Cheque Return</a>
                        </li>
                        <li class="">
                            <a href="">
                            <i class="fa fa-angle-right"></i>
                            Banking Recognition</a>
                        </li>
                    </ul>
         </li>

        {{-- Accounting --}}
         <li class="{{ Request::is('account_*') || Request::is('chartofaccount') || Request::is('journalEntry') ? 'active' : '' }}">
            <a href="#">
                <i class="fa fa-table"></i> <span> Accounting</span> <span
                    class="menu-arrow"></span></a>
                    <ul style="{{ Request::is('account_*') || Request::is('chartofaccount') || Request::is('journalEntry')  ? 'display:block;' : '' }}">
                        <li class="{{ Request::is('account_category') ? 'active' : '' }}">
                            <a href="{{route("account_category")}}">
                            <i class="fa fa-angle-right"></i>
                           Category</a>
                        </li>
                        <li class="{{ Request::is('account_type') ? 'active' : '' }}">
                            <a href="{{route("account_type")}}">
                            <i class="fa fa-angle-right"></i>
                           Type</a>
                        </li>
                        <li class="{{ Request::is('chartofaccount') ? 'active' : '' }}">
                            <a href="{{route("chartofaccount")}}">
                            <i class="fa fa-angle-right"></i>
                           Chart Of Accounts</a>
                        </li>
                        <li class="{{ Request::is('journalEntry') ? 'active' : '' }}">
                            <a href="{{route("journalEntry")}}">
                            <i class="fa fa-angle-right"></i>
                            Journal Entries</a>
                        </li>
                    </ul>
         </li>


                {{-- Reports --}}
                <li class="dropdown {{ Request::is('search') ? 'active' : '' }}">
                    <a href="#"><i class="fa fa-chart-area"></i> <span>Reports</span> <span
                            class="menu-arrow"></span></a>
                    <ul style=" {{ Request::is('search') ? 'display:block;' : '' }}">
                        
                        
                        <li><a href="{{route("daily-cash-report.index")}}" target ="_ blank"><i class="fa fa-angle-right"></i>Daily Summary Report</a></li>
                        
                        
                        <li class="{{ Request::is('search') ? 'active' : '' }}">
                            <a href="{{route("search")}}" target ="_ blank"><i class="fa fa-angle-right"></i>Pawning report</a>
                        </li>

                        <li><a href="{{route("pawingarticlereport")}}" target ="_ blank"><i class="fa fa-angle-right"></i>Pawning Article</a></li>

                        <li><a href="{{route("openingPawningReport")}}" target ="_ blank"><i class="fa fa-angle-right"></i>Opening Pawn report</a></li>
                        <li><a href="{{route("openingpawingarticlereport")}}" target ="_ blank"><i class="fa fa-angle-right"></i>Opening Pawn Article</a></li>

                        <li><a href="{{route("redeem")}}" target ="_ blank"><i class="fa fa-angle-right"></i>Redeem report</a></li>
                        
                            <li><a href="{{route("repawningHistoryReport")}}" target ="_ blank"><i class="fa fa-angle-right"></i>RePawning Report</a></li>

                          <li><a href="{{route("PartpaymentHistortyReport")}}" target ="_ blank"><i class="fa fa-angle-right"></i>Part Payment Report</a></li>

                        <li><a href="{{route("forfeit_report")}}" target ="_ blank"><i class="fa fa-angle-right"></i>Forfeit report</a></li>
                        <li><a href="{{route("daily_report")}}" target ="_ blank"><i class="fa fa-angle-right"></i>Daily Summary</a></li>
                        <!--<li><a href="{{route("Stockreport")}}" target ="_ blank"><i class="fa fa-angle-right"></i>Stock report</a></li>-->
                        
                         <li><a href="{{route("StockCheckreport")}}" target ="_ blank"><i class="fa fa-angle-right"></i>Stock  Check Report</a></li>
                         
                        <li><a href="{{route("deleted_pawn_report")}}" target ="_ blank" ><i class="fa fa-angle-right"></i>Deleted Pawning</a></li>


                        <!--<li><a href="{{route("Cancel_Pawning")}}"><i class="fa fa-angle-right"></i>Cancel Pawning Report</a></li>-->
                        <!--<li><a href="{{route("Advance_Payment")}}"><i class="fa fa-angle-right"></i>Advance Payments Report</a></li>-->
                        <!--<li><a href="{{route("Advance_Payment_Balance")}}"><i class="fa fa-angle-right"></i>Advance Payments Balance Report</a></li>-->

                        <!--<li><a href="#"><i class="fa fa-angle-right"></i> Product Purchase Report</a></li>-->
                        <!--<li><a href="#"><i class="fa fa-angle-right"></i> Table Report</a></li>-->
                        <!--<li><a href="#"><i class="fa fa-angle-right"></i> Sales Representative Report</a></li>-->
                        <!--<li><a href="#"><i class="fa fa-angle-right"></i> Register Report</a></li>-->
                        <!--<li><a href="#"><i class="fa fa-angle-right"></i> Sell Payment Report</a></li>-->
                        <!--<li><a href="#"><i class="fa fa-angle-right"></i> Purchase Payment Report</a></li>-->
                        <!--<li><a href="#"><i class="fa fa-angle-right"></i> Product Sell Report</a></li>-->
                        <!--<li><a href="#"><i class="fa fa-angle-right"></i> Items Report</a></li>-->
                        <!--<li><a href="#"><i class="fa fa-angle-right"></i> Purchase & Sale</a></li>-->
                        <!--<li><a href="#"><i class="fa fa-angle-right"></i> Trending Products</a></li>-->
                        <!--<li><a href="#"><i class="fa fa-angle-right"></i> Stock Adjustment Report</a></li>-->
                        <!--<li><a href="#"><i class="fa fa-angle-right"></i> Lot Report</a></li>-->
                        <!--<li><a href="#"><i class="fa fa-angle-right"></i> Stock Report</a></li>-->
                        <!--<li><a href="#"><i class="fa fa-angle-right"></i> Customer Groups Report</a></li>-->
                        <!--<li><a href="#"><i class="fa fa-angle-right"></i> Supplier & Customer Report</a></li>-->
                        <!--<li><a href="#"><i class="fa fa-angle-right"></i> Tax Report</a></li>-->
                        <!--<li><a href="#"><i class="fa fa-angle-right"></i> Activity Log</a></li>-->
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
@yield('content')
