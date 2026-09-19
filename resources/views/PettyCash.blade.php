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
  <div class="row">
</div>

<label for="">Interest Rate :</label> 
<input type="text" id="inputValue" oninput="calculateValues()">
<div class="table-responsive">
<table  class="rwd-table">
  <tbody>
      <tr style="background-color: #428bca">
          <th width = >01 Months</th>
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
          <td id="output1"></td>
          <td id="output2"></td>
          <td id="output3"></td>
          <td id="output4"></td>
          <td id="output5"></td>
          <td id="output6"></td>
          <td id="output7"></td>
          <td id="output8"></td>
          <td id="output9"></td>
          <td id="output10"></td>
          <td id="output11"></td>
          <td id="output12"></td>
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

function calculateValues() {
  // Get the input value
  let inputValue = {{$receipt_data}};

  // Convert the input value to a number for calculations
  let inputNumber = (inputValue);

  let Amount = {{$receiptData}}/100;
  let monthlyRate = (inputValue);
  let Interestamount = monthlyRate * Amount;

  
  // Perform calculations for each output cell
  document.getElementById("output1").textContent = {{$receiptData}};
  document.getElementById("output2").textContent = {{$receiptData}}-Interestamount ;
  document.getElementById("output3").textContent = {{$receiptData}}-(Interestamount * 2) ;
  document.getElementById("output4").textContent = {{$receiptData}}-(Interestamount * 3);
  document.getElementById("output5").textContent = {{$receiptData}}-(Interestamount * 4);
  document.getElementById("output6").textContent = {{$receiptData}}-(Interestamount * 5);
  document.getElementById("output7").textContent = {{$receiptData}}-(Interestamount * 6);
  document.getElementById("output8").textContent = {{$receiptData}}-(Interestamount * 7);
  document.getElementById("output9").textContent = {{$receiptData}}-(Interestamount * 8);
  document.getElementById("output10").textContent ={{$receiptData}}-(Interestamount * 9);
  document.getElementById("output11").textContent ={{$receiptData}}-(Interestamount * 10);
  document.getElementById("output12").textContent = {{$receiptData}}-(Interestamount * 11);

}
</script>



i need without is fild <input type="text" id="inputValue" oninput="calculateValues()">