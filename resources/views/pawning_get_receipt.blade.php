

@foreach ($receiptData as $data)

<tr>
    <td style="width:8%;">
        <input type="hidden" name="inputs[`+i+`][receipt_no]" value="`+ receipt_no +`">
        <input type="hidden" name="inputs[`+i+`][receipt_type]" value="`+ receipt_type +`">
        <input type="hidden" name="inputs[`+i+`][receipt_date]" value="`+ receipt_date +`">

        <select class="select form-control" style="text-align: center;" id="dy_category"
        name="inputs[`+i+`][category]" aria-hidden="true" readonly>
            <option value="">Please Select</option>
            @foreach($itemCategory as $categoryData)
                <option value="{{$data['Category']}}"
                {{ $categoryData->category == $data['Category'] ? 'selected' : '' ;}}>
                    {{ $categoryData->category }}
                </option>
            @endforeach
        </select>
    </td>


    <td style="width:8%;">
        <select class="select form-control" style="text-align: center;" id="dy_articles"
        name="inputs[`+i+`][articles]" aria-hidden="true" readonly>
            <option value="">Please Select</option>
            @foreach($itemSetup as $itemSetupData)
                <option value="{{$data['Articles']}}"
                {{ $itemSetupData->ItemDesc == $data['Articles'] ? 'selected' : '' ;}}>
                    {{ $itemSetupData->ItemDesc }}
                </option>
            @endforeach
        </select>
    </td>

    <td style="width:8%;">
        <select class="select form-control" style="text-align: center;" id="dy_condition"
        name="inputs[`+i+`][condition]" aria-hidden="true" readonly>
            <option value="">Please Select</option>
            @foreach($itemCondition as $conditionData)
                <option value="{{$data['Condition']}}"
                {{ $conditionData->conditionsName == $data['Condition'] ? 'selected' : '' ;}}>
                    {{ $conditionData->conditionsName}}</option>
            @endforeach
        </select>
    </td>

    <td style="width:8%;">
        <select class="select form-control" style="text-align: center;" id="dy_karatage"
        name="inputs[`+i+`][karatage]" aria-hidden="true" readonly>
            <option value="">Please Select</option>
            @foreach($itemKaratage  as $karatageData)
                <option value="{{$data['Karatage']}}"
                {{ $karatageData->descrption == $data['Karatage'] ? 'selected' : '' ;}}>
                    {{ $karatageData->descrption}}</option>
            @endforeach
        </select>
    </td>

    <td style="width:8%;">
        <input class="form-control" type="text" style="text-align: center;" placeholder="Weight"
        id="dy_weight" name="inputs[`+i+`][weight]"
        value=" {{$data['Weight']}}" readonly>
    </td>

    <td style="width:8%;">
        <input class="form-control" type="text" style="text-align: center;" placeholder="Total Weight"
        id="dy_total_weight" name="inputs[`+i+`][total_weight]"
        value=" {{$data['Total_Weight']}}" readonly>
    </td>

    <td style="width:8%;">
        <input class="form-control" type="text" style="text-align: center;" placeholder="QTY"
        id="dy_qty" name="inputs[`+i+`][qty]" value="{{$data['QTY']}}" readonly>
    </td>

    <td style="width:8%;">
        <input class="form-control" type="text" style="text-align: center;" placeholder="Value"
        id="dy_value" name="inputs[`+i+`][value]" value="{{$data['Value']}}" readonly>
    </td>

    <td style="width:8%;">
        <center>
            <button type="button" id="delete-btn" class="btn btn-outline-danger text-center shadow remove-input-field m-2"> <i class="far fa-trash-alt me-1"></i> Delete </button>
        </center>
    </td>
</tr>

@endforeach