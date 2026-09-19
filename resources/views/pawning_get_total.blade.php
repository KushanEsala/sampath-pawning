<head>
  <style>
      @import 'https://fonts.googleapis.com/css?family=Open+Sans:600,700';


.rwd-table {
margin: auto;
min-width: 300px;
max-width: 75%;
border-collapse: collapse;
font-family: 'Open Sans', sans-serif;
}


.rwd-table tr:first-child {
border-top: none;
background: #428bca;
color: #fff;
}

.rwd-table tr {
border-top: 1px solid #ddd;
border-bottom: 1px solid #ddd;
background-color: #f5f9fc;
}

.rwd-table tr:nth-child(odd):not(:first-child) {
background-color: #ebf3f9;
}

.rwd-table th {
display: none;
}

.rwd-table td {
display: block;
}

.rwd-table td:first-child {
margin-top: .5em;
}

.rwd-table td:last-child {
margin-bottom: .5em;
}

.rwd-table td:before {
content: attr(data-th) ": ";
font-weight: bold;
width: 120px;
display: inline-block;
color: #000;
}

.rwd-table th,
.rwd-table td {
text-align: left;
}

.rwd-table {
color: #333;
border-radius: .4em;
overflow: hidden;
}

.rwd-table tr {
border-color: #bfbfbf;
}

.rwd-table th,
.rwd-table td {
padding: .5em 1em;
}
@media screen and (max-width: 601px) {
.rwd-table tr:nth-child(2) {
  border-top: none;
}
}
@media screen and (min-width: 600px) {
.rwd-table tr:hover:not(:first-child) {
  background-color: #d8e7f3;
}
.rwd-table td:before {
  display: none;
}
.rwd-table th,
.rwd-table td {
  display: table-cell;
  padding: .25em .5em;
}
.rwd-table th:first-child,
.rwd-table td:first-child {
  padding-left: 0;
}
.rwd-table th:last-child,
.rwd-table td:last-child {
  padding-right: 0;
}
.rwd-table th,
.rwd-table td {
  padding: 1em !important;
}
}

.container {
display: block;
text-align: center;
width: 100%; /* Set the width to 80% of its parent container */
max-width: 1500px; /* Set a maximum width to prevent it from growing too wide */
margin: 0 auto; /* Center the container horizontally */
}
  </style>
</head>

<br>
<br>


<div class="container">
  <div class="row"></div>
  <div class="table-responsive">
    <input style="font-weight:bold;"
    class="form-control form-control-lg" type="hidden" 
    value="{{$receiptData}}&nbsp;&nbsp;"
    placeholder="Pawning Advance Amount :" id="pawning_amouunt"
    name="pawning_amouunt" >
    <input style="font-weight:bold;"
    class="form-control form-control-lg" type="hidden" 
    value="&nbsp;&nbsp;{{$InterestRate}} "
    placeholder="Pawning Advance Amount :" id="InterestRate"
    name="InterestRate" >
    <table class="rwd-table">
      <tbody>
        <tr style="background-color: #428bca">
          <th>01 Months</th>
          <th>02 Months</th>
          <th>03 Months</th>
          <th>04 Months</th>
          <th>05 Months</th>
          <th>06 Months</th>
          <th>07 Months</th>
          <th>08 Months</th>
          <th>09 Months</th>
          <th>10 Months</th>
          <th>11 Months</th>
          <th>12 Months</th>
        </tr>
        <tr>
          <td id="output1" name="01 Months" ></td>
          <td id="output2" name="02 Months"  ></td>
          <td id="output3" name="03 Months" ></td>
          <td id="output4" name="04 Months" ></td>
          <td id="output5" name="05 Months" ></td>
          <td id="output6" name="06 Months" ></td>
          <td id="output7" name="07 Months" ></td>
          <td id="output8" name="08 Months" ></td>
          <td id="output9" name="09 Months" ></td>
          <td id="output10" name="10 Months" ></td>
          <td id="output11"name="11 Months" ></td>
          <td id="output12" name="12 Months"></td>
        </tr>
      </tbody>
    </table>

  </div>
</div>

<script>
  // Function to format numbers as Sri Lankan Rupees (LKR)
  function formatCurrency(amount) {
    // Round to two decimal places
    amount = parseFloat(amount).toFixed(2);

    // Add commas for thousands separator
    amount = amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");

    // Add 'Rs.' prefix for LKR
    return amount;
  }

  // Function to calculate and update table values
  function calculateValues() {

    let receiptData = {{$receiptData}}; 
    
    let receiptDatavalue = {{$receiptData}}/100; // Example value, replace with your actual data
    // Calculate interest amount based on your logic
    let monthlyRate = {{$InterestRate}}; // Example monthly interest rate (5%)

    let Interestamount = monthlyRate * receiptDatavalue; 

    // Update table cell values directly
    document.getElementById("output1").textContent = formatCurrency(receiptData);
    document.getElementById("output2").textContent = formatCurrency(receiptData - Interestamount);
    document.getElementById("output3").textContent = formatCurrency(receiptData - (Interestamount * 2));
    document.getElementById("output4").textContent = formatCurrency(receiptData - (Interestamount * 3));
    document.getElementById("output5").textContent = formatCurrency(receiptData - (Interestamount * 4));
    document.getElementById("output6").textContent = formatCurrency(receiptData - (Interestamount * 5));
    document.getElementById("output7").textContent = formatCurrency(receiptData - (Interestamount * 6));
    document.getElementById("output8").textContent = formatCurrency(receiptData - (Interestamount * 7));
    document.getElementById("output9").textContent = formatCurrency(receiptData - (Interestamount * 8));
    document.getElementById("output10").textContent = formatCurrency(receiptData - (Interestamount * 9));
    document.getElementById("output11").textContent = formatCurrency(receiptData - (Interestamount * 10));
    document.getElementById("output12").textContent = formatCurrency(receiptData - (Interestamount * 11));
  }

  calculateValues();
</script>

<script>
  // Add event listeners for the table cells with specific IDs
  for (let i = 1; i <= 12; i++) {
    document.getElementById('output' + i).addEventListener('click', function() {
      // When a cell is clicked, set the value of the Valid_Period input field
      document.getElementById('Valid_Period').value = this.textContent; // Update with the clicked value
    });
  }
</script>