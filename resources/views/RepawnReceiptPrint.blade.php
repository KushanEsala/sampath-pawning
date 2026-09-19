<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<title>invoice</title>


		<!-- Favicon -->
		{{-- <link rel="icon" href="./images/favicon.png" type="image/x-icon" /> --}}

		<!-- Invoice styling -->
		<style>
			body {
				font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
				text-align: center;
				color: #777;
			}




			body a {
				color: #06f;
			}

			.invoice-box {
				max-width: 800px;
				margin: auto;
				padding: 30px;
				border: 1px solid #eee;
				box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
				font-size: 16px;
				line-height: 24px;
				font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
				color: #555;
			}

            .tr_details{
                max-width: 800px;
                border-bottom: none;
				line-height: 1px;
				color: #222121;
            }

			.invoice-box table {
				width: 100%;
				line-height: inherit;
				text-align: left;
				border-collapse: collapse;
			}

			.invoice-box table td {
				padding: 5px;
				vertical-align: top;
			}


			.invoice-box table tr.top table td {
				padding-bottom: 20px;
			}

			.invoice-box table tr.top table td.title {
				font-size: 45px;
				line-height: 45px;
				color: #333;
			}

			.invoice-box table tr.information table td {
				padding-bottom: 40px;
			}

			.invoice-box table tr.heading td {
				background: #eee;
				border-bottom: 1px solid #ddd;
				font-weight: bold;
			}

			.invoice-box table tr.details td {
				padding-bottom: 20px;
			}

			.invoice-box table tr.item td {
				border-bottom: 1px solid #eee;
			}

			.invoice-box table tr.item.last td {
				border-bottom: none;
			}

			.invoice-box table tr.total td:nth-child(2) {
				border-top: 2px solid #ffffff;
				font-weight: bold;
			}

			@media only screen and (max-width: 600px) {
				.invoice-box table tr.top table td {
					width: 100%;
					display: block;
					text-align: center;
				}

				.invoice-box table tr.information table td {
					width: 100%;
					display: block;
					text-align: center;
				}
			}
		</style>

<style>
    .clearfix:after {
        content: "";
        display: table;
        clear: both;
    }

    a {
        color: #5D6975;
        text-decoration: underline;
    }

    body {
        margin-top: 30px;
        position: relative;
        width: 18cm;
        height: 15cm;
        /* height: 29.7cm; */
        margin: 0 auto;
        color: #001028;
        background: #FFFFFF;
        font-family: Arial, sans-serif;
        font-size: 16px;
        font-family: Arial;
    }

    .subhead {
        font-size: 17px;
    }

    header {
        padding: 10px 0;
        margin-bottom: 20px;
    }

    #logo {
        text-align: center;
        margin-bottom: 10px;
    }

    #logo img {
        width: 90px;
    }

    .border {
        border-top: 1px solid #5e6163;
        border-bottom: 1px solid #5e6163;
        text-align: center;
        color: #5e6163;
        margin: 0 0 20px 0;
        line-height: 1.4em;
    }

    .fullborder {
        padding-top: 33px;
        /*margin-top: 30px;*/
        /* border-top: 1px solid #1a1a1a;
        border-bottom: 1px solid #1a1a1a;
        border-left: 1px solid #1a1a1a;
        border-right: 1px solid #1a1a1a; */

        /* border-top-style: solid;
        border-right-style: solid;
        border-bottom-style: solid;
        border-left-style: solid; */

        text-align: center;
        /* color: #000000; */
        margin: 0 0 20px 0;
        line-height: 1.3em;
    }

    h1 {

        color: #5e6163;
        font-size: 2.5em;
        margin-bottom: 5px;
        font-weight: normal;
        text-align: center;

        /* background: url(assets/pdf/dimension.png); */
    }

    #project {
        float: left;
    }

    #project span {
        /* color: #2f3030; */
        text-align: left;
        width: 60px;
        margin-right: 10px;
        display: inline-block;
        font-size: 16px;
    }

    #company {
        float: right;
        text-align: left;
    }

    #project div,
    #company div {
        white-space: nowrap;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        border-spacing: 0;
        margin-bottom: 20px;

    }

    /* table tr:nth-child(2n-1) td {
        background: #F5F5F5;
    } */

    table th,
    table td {
        text-align: center;
    }

    table th {
        padding: 5px 20px;
        color: #5D6975;
        border-bottom: 1px solid #C1CED9;
        white-space: nowrap;
        font-weight: normal;
    }

    table .service,
    table .desc {
        text-align: left;
    }

    table td {
        padding: 5px;
        text-align: left;
    }

    table td.service,
    table td.desc {
        vertical-align: top;
    }

    table td.unit,
    table td.total {
        font-size: 1.2em;
    }

    .qty {
        font-size: 1.1em;
    }

    table td.grand {
        border-top: 1px solid #494d52;
        border-bottom: 1px solid #494d52;
        ;
    }

    #notices .notice {
        color: #5D6975;
        font-size: 1.1em;
    }

    footer {
        color: #5D6975;
        width: 100%;
        height: 30px;
        position: absolute;
        bottom: 0;
        border-top: 1px solid #C1CED9;
        padding: 8px 0;
        text-align: center;
    }

    #items_data{
        height: 5px;
    }

</style>

<style>
    .fullborder {
        border: 1px solid rgb(74, 75, 77);
        border-collapse: collapse;
        width: 100%;
    }

    .fullborder td {
        border: 1px solid rgb(55, 55, 56);
        padding: 5px;
    }

    .text-right {
        text-align: right;
    }

    .spacer {
        height: 40px;
    }

    .spacer-large {
        height: 50px;
    }

    .padded {
        padding-top: 35px;
    }
</style>


	</head>
	<body>
        <table class="fullborder">
            <tbody>
                <tr>
                    <td style="height: 60px;width: 25%;text-align: center;">
                        <img src="{{ public_path('images/logo3.jpg') }}" alt="Logo New"/>
                    </td>
                    <td colspan="3" style="height: 60px; width: 75%; text-align: center; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; vertical-align: middle;">
                        @foreach($companyData as $sumData)
                            <div style="margin-bottom: 5px;">
                                <span style="display: block; font-size: 16px; font-weight: bold;">{{ $sumData['name'] }}</span>
                                <span style="display: block; font-size: 14px;">{{ $sumData['address'] }}</span>
                                <span style="display: block; font-size: 14px;">{{ $sumData['co_number'] }}</span>
                                <span style="display: block; font-size: 14px;">Email: {{ $sumData['email'] }}</span>
                                <span style="display: block; font-size: 14px;">A/C 101010001477 HNB</span>
                                
                                <span style="display: block; font-size: 14px;font-weight: bold">Re-Mortgagor </span>
                                
                            </div>
                        @endforeach
                    </td>
                </tr>                
                <tr>
                    <td style="width: 30%;font-weight: bold; ">Cashier</td>
                    <td style="width: 20%;"  class="text-right">
                    @foreach($pawnSumData as $sumData)
                        {{$sumData['OC']}}
                    </td>
                    @endforeach
                    <td style="width: 25%;font-weight: bold;font-size:13px ">Re-Pawn Ticket Number
                    </td>
                    @foreach($pawnSumData as $sumData)
                    <td style="width: 25%;" class="text-right">{{$sumData['RePawning_date']}}</td>
                    @endforeach
                </tr>
                <tr>
                    @php
                    $currentTimeUTC = \Carbon\Carbon::now('UTC');
                    $currentTimeFormatted = $currentTimeUTC->setTimezone('Asia/Kolkata')->format('h:i A');
                    @endphp

                    <td style="width: 25%;font-weight: bold;">Date</td>
                    @foreach($pawnSumData as $sumData)
                    <td style="width: 25%;font-size:12px " class="text-right">{{$sumData['Receipt_Date']}}&nbsp;{{ $currentTimeFormatted}}</td>
                    @endforeach
                    <td style="width: 25%;font-weight: bold;font-size:13px ">Amount(RS)</td>
                    @foreach($pawnSumData as $sumData)
                    <td style="width: 25%;" class="text-right">  {{ number_format($sumData['RePawning_amount'], 2) }}</td>
                    @endforeach
                </tr>
                <tr>
                    <td style="width: 25%;font-weight: bold;font-size:13px ">NIC.No</td>
                    @foreach($pawnSumData as $sumData)
                    <td style="width: 25%;" class="text-right">{{$sumData['Customer_NIC']}}</td>
                    @endforeach
                    <td style="width: 25%;font-weight: bold;font-size:13px ">Total Weight(gms)</td>
                    @foreach($pawnSumData as $sumData)
                    <td style="width: 25%;" class="text-right">{{$sumData['Total_Weight']}}</td>
                    @endforeach
                </tr>
                <tr>
                    <td style="width: 25%;font-weight: bold;font-size:13px ">Mobile No.</td>
                    @foreach($pawnSumData as $sumData)
                    <td style="width: 25%;" class="text-right">{{$sumData['Customer_Phone']}}</td>
                    <td style="width: 25%;font-weight: bold;font-size:13px ">Interest Rate</td>
                    <td style="width: 25%;" class="text-right">{{$sumData['Interest_Rate']}}</td>
                    @endforeach
                </tr>
                <tr>
                    @foreach($pawnSumData as $sumData)
                    <tr>
                        <td style="width: 25%; font-weight: bold; font-size: 13px;" colspan="2"></td>
                        <td style="width: 25%; font-weight: bold; font-size: 13px;">Interest for Months</td>
                        <td style="width: 25%;" class="text-right">
                            {{ number_format(($sumData['RePawning_amount'] / 100) * $sumData['Interest_Rate'], 2) }}
                        </td>
                    </tr>
                @endforeach
                
                </tr>
                <tr>
                    <td style="width: 40%;font-weight: bold;font-size:13px ">Name And Address Of Mortgagor</td>
                    <td style="width: 50%;font-weight: bold;font-size:13px" colspan="2" >Details Of Items Mortgaged </td>
                    <td style="width: 10%;font-weight: bold;font-size:13px ">Gold content(Katats)</td>
                </tr>
                <tr>
                    <td style="width: 40%;">
                        <div style="font-size:11px;text-align: left;" >
                            @foreach($pawnSumData as $sumData)
                            {{$sumData['Customer_Name']}} <br> {{$sumData['Customer_Address']}}
                            @endforeach
                        </div>
                    </td>
                    <td style="width: 50%;" class="text-right" colspan="2" >
                        <div  style="font-size:13px;text-align: left;">
                            @foreach($pawnDetailsData as $DetailsData)
                            {{$DetailsData['Articles']}} &nbsp;&nbsp; qty: {{$DetailsData['QTY']}}
                            &nbsp;&nbsp; {{$DetailsData['Condition']}}
                            &nbsp;&nbsp;<br>
                            @endforeach
                        </div>
                    </td>
                    <td style="width: 10%;border:" class="text-right" >
                        <div style="text-align: center;font-size:13px;">
                            @foreach($pawnDetailsData as $DetailsData)
                            {{$DetailsData['Karatage']}}-24K <br>
                            @endforeach
                        </div>
                    </td>
                </tr>
                <tr>
                    @foreach($pawnSumData as $sumData)
                    <td style="width: 25%;font-weight: bold;font-size:13px">Duration (Months) </td>
                    <td style="width: 25%;" class="text-right" >{{$sumData['Valid_Period']}}&nbsp;Months</td>
                    <td style="width: 25%; font-weight: bold; font-size: 13px; " colspan="2"></td>
                    @endforeach
                </tr>
                <tr>
                    <td  style="width: 25%;font-weight: bold;font-size:13px;" >Expired Date : </td>
                    @foreach($pawnSumData as $sumData)
                    @php
                        // Parse the Receipt Date
                        $receiptDate = new DateTime($sumData['Receipt_Date']);
                        
                        // Check if the Valid Period is null or not set, and default to 0 if it is
                        $validPeriod = isset($sumData['Valid_Period']) && $sumData['Valid_Period'] !== null ? (int)$sumData['Valid_Period'] : 0;
                
                        // Add the Valid Period (in months)
                        $receiptDate->modify("+{$validPeriod} months");
                        
                        // Format the due date
                        $dueDate = $receiptDate->format('Y-m-d');
                    @endphp
                    
                    <td style="width: 25%;" class="text-right padded">{{ $dueDate }}</td>
                    @endforeach
                    <td style="width: 40%;font-weight: bold;font-size:13px" class="text-right padded"> Repawning Pawning  Rs.</td>
                    <td style="width: 10%;" class="text-right padded">
                        @foreach($pawnSumData as $sumData)
                         {{ number_format($sumData['RePawning_amount'], 2) }}
                        @endforeach
                    </td>
                </tr>
            </tbody>
        </table>
        {{-- <br>
        <p style="font-size: 9px;text-align: left">I hereby pledge and hand over the articles described above to @foreach($companyData as $sumData){{ $sumData['name'] }}@endforeach as collateral for the loan obtained. I fully understand and agree that these pledged items shall remain in the possession of @foreach($companyData as $sumData){{ $sumData['name'] }}@endforeach until the loan amount, along with any applicable interest and charges, is fully repaid. I also acknowledge that failure to repay the loan within the agreed-upon period may result in the forfeiture of the pledged items, allowing @foreach($companyData as $sumData){{ $sumData['name'] }}@endforeach to take necessary action as per the prevailing rules and regulations. Furthermore, I confirm that the pledged articles are lawfully owned by me and are free from any prior claims, disputes, or encumbrances.</p>
        <br> --}}
<br>
<br>
        <table>
            <tbody>
                <tr>
                    <td style="text-align: center">................................................</td>
                    <td></td>
                    <td style="text-align: center">.................................................</td>
                    <td></td>
                    <td style="text-align: center">..................................................</td>
                </tr>
                <tr>
                    <td style="text-align: center">Mortgagor</td>
                    <td></td>
                    <td style="text-align: center">Valuer</td>
                    <td></td>
                    <td style="text-align: center">Authorized Officer</td>
                </tr>
            </tbody>

        </table>

        
        
	</body>
</html>