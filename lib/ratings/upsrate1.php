<?php

  //include('ups.php');
  include('countries.php');

  // Shipping Calculator Class
require_once "ShippingCalculator.php";

// UPS
$services['ups']['14'] = 'Next Day Air Early AM';
$services['ups']['01'] = 'Next Day Air';
//$services['ups']['65'] = 'Saver';
//$services['ups']['59'] = '2nd Day Air Early AM';
$services['ups']['02'] = '2nd Day Air';
$services['ups']['12'] = '3 Day Select';
$services['ups']['03'] = 'Ground';
//$services['ups']['11'] = 'Standard';
//$services['ups']['07'] = 'Worldwide Express';
//$services['ups']['54'] = 'Worldwide Express Plus';
//$services['ups']['08'] = 'Worldwide Expedited';
// USPS
$services['usps']['EXPRESS'] = 'Express';
$services['usps']['PRIORITY'] = 'Priority';
//$services['usps']['PARCEL'] = 'Parcel';
//$services['usps']['FIRST CLASS'] = 'First Class';
//$services['usps']['EXPRESS SH'] = 'Express SH';
//$services['usps']['BPM'] = 'BPM';
$services['usps']['MEDIA '] = 'Media';
$services['usps']['LIBRARY'] = 'Library';
// FedEx
$services['fedex']['PRIORITY_OVERNIGHT'] = 'PRIORITYOVERNIGHT';
$services['fedex']['STANDARD_OVERNIGHT'] = 'STANDARDOVERNIGHT';
//$services['fedex']['FIRSTOVERNIGHT'] = 'First Overnight';
//$services['fedex']['FEDEX2DAY'] = '2 Day';
//$services['fedex']['FEDEXEXPRESSSAVER'] = 'Express Saver';
$services['fedex']['FEDEX_GROUND'] = 'FEDEXGROUND';
//$services['fedex']['FEDEX1DAYFREIGHT'] = 'Overnight Day Freight';
//$services['fedex']['FEDEX2DAYFREIGHT'] = '2 Day Freight';
//$services['fedex']['FEDEX3DAYFREIGHT'] = '3 Day Freight';
//$services['fedex']['GROUNDHOMEDELIVERY'] = 'Home Delivery';
//$services['fedex']['INTERNATIONALECONOMY'] = 'International Economy';
//$services['fedex']['INTERNATIONALFIRST'] = 'International First';
//$services['fedex']['INTERNATIONALPRIORITY'] = 'International Priority';
  
  $s_zip=$_POST[s_zip];
  $s_country=$_POST[s_country];
  $t_zip =$_POST[t_zip];
  $t_country=$_POST[t_country];
  $weight = $_POST[weight];
  $residential=$_POST[residential];
  $pickup=$_POST[pickup];

  if($s_zip=='' || $s_country=='' || $t_zip=='' || $t_country==''
    || $weight==''){
     $error_msg="please provide all information";

  }else {

  // Config
	$config = array(
		// Services
		'services' => $services,
		// Weight
		'weight' => $weight, // Default = 1
		'weight_units' => 'lb', // lb (default), oz, gram, kg
		// Size
		'size_length' => 5, // Default = 8
		'size_width' => 6, // Default = 4
		'size_height' => 3, // Default = 2
		'size_units' => 'in', // in (default), feet, cm
		// From
		'from_zip' => $s_zip, 
		'from_state' => "CA", // Only Required for FedEx
		'from_country' => $s_country,
		// To
		'to_zip' => $t_zip,
		'to_state' => "CA", // Only Required for FedEx
		'to_country' => $t_country,
	
		// Service Logins
		'ups_access' => 'ACD1FF50928BEA72', // UPS Access License Key
		'ups_user' => 'jiqishou', // UPS Username
		'ups_pass' => 'Jiqi0308!', // UPS Password
		'ups_account' => 'V62E47', // UPS Account Number
		'usps_user' => '402USC006379', // USPS User Name
		'usps_pass' => '374RC32OB469', // USPS password
		'fedex_account' => '551361543', // FedEX Account Number
		'fedex_meter' => '106544517', // FedEx Meter Number 
		'fedex_key' => 'nL3MA7H7u60XjJZ7', //Fedex Key Number
		'fedex_password' => 'zLh7MUxjZJSKoyebs0IJHjvGu' //Fedex Password
	);

	// Create Class (with config array)
	$ship = new ShippingCalculator($config);
	// Get Rates
	//echo "zhaochen";
	//$rates = '1';
	$rates = $ship->calculate('', '');
	//echo $rates;
	
  }

  
  if ($s_country == '' ) $s_country='US';
  if ($t_country == '' ) $t_country='US';


  if (! isset($_POST[pickup]) ) {
	$error_msg="";
	$opt_res1="checked";
	$opt_pickup2="checked";
  }else{

    if ($residential == 1 ) $opt_res1 = "checked";
    if ($residential == 0 ) $opt_res2 = "checked";

    if ($pickup =='01') $opt_pickup1='checked';
    if ($pickup =='03') $opt_pickup2='checked';
    if ($pickup =='06') $opt_pickup3='checked';
    if ($pickup =='07') $opt_pickup4='checked';
    if ($pickup =='11') $opt_pickup5='checked';
    if ($pickup =='19') $opt_pickup6='checked';

  }
?>

<html>
<head>
<title>Shipping Rates Calculation</title>

<style>

body {
	background: #fff;
	color: #000;
	font-family: 'Lucida Grande', 'Lucida Sans Unicode', Verdana, sans-serif;
	font-size: 0.8em;
}

table tr td, table tr th{
	font-family: 'Lucida Grande', 'Lucida Sans Unicode', Verdana, sans-serif;
        font-size: 0.8em;

}


</style>
</head>
<body>
<h2>Shipping Rates Calculation</h2>
<?php 
    if ( $error_msg != "" ){
	print "<font color='red'>$error_msg</font>";
    }
?>
<?php
   if ($rates) { ?>

<H3>Result</H3>
 
<table border=1>
<tr>
  <th>Comapny</th>
  <th>Service</th>
  <th>Total Charge</th>

</tr>
<?php

 $ct= count($rates);
/*
 for($i=0;$i<$ct;$i++){
 	echo "zhaochen\n";
 	echo $ct;
 	echo "where are you my darling\n";
 	print_r($rates);
  //echo "<tr><td>".$ups_service[$ups_rates[$i][service]]."</td>";
 // echo "<td>\$".number_format($ups_rates[$i][basic],2)."</td>";
  //echo "<td>\$".number_format($ups_rates[$i][option],2)."</td>";
  //echo "<td>\$".number_format($ups_rates[$i][total],2)."</td>";
  //echo "<td>".$ups_rates[$i][days]."</td>";
  //echo "<td>".$ups_rates[$i][time]."</td></tr>";



 }*/
 
 foreach($rates as $company => $codes) {
	foreach($codes as $code => $rate) {
		switch ($code){
			case '14': 
				$code = 'Next Day Air Early AM';
				break;
			case '01':
				$code = 'Next Day Air';
				break;
			case '02':
				$code = '2nd Day Air';
				break;
			case '12':
				$code = '3 Day Select';
				break;
			case '03':
				$code = 'Ground';
				break;
		}
		
		echo "<tr><td>".$company."</td>";
		echo "<td>".$code."</td>";
		echo "<td>\$".number_format($rate,2)."</td></tr>";
	}
}
 
?>

 </table>

<?php } ?>


<form action='upsrate1.php' method=post>
<table border=1>
<tr><td valign=top>Shipping From</td>
    <td valign=top>
Country:    <select name='s_country'>
    <?php list_countries($s_country)?>
    </select><BR>
Zip: <input type=text size=10 name='s_zip' value='<?php print $s_zip; ?>'>
    </td>
</tr>
<tr><td valign=top>Shipping To</td>
    <td valign=top>
Country:    <select name='t_country'>
    <?php list_countries($t_country)?>
    </select><BR>
Zip: <input type=text size=10 name='t_zip' value='<?php print $t_zip; ?>' >
    </td>
</tr>
<tr><td valign=top>Weight</td>
    <td valign=top>Cannot be over 150 lbs<BR>
    <input type=text size=3 name='weight' value='<?php print $weight; ?>'>
</tr>
<tr><td valign=top>Residential/Commercial</td>
    <td>
	<input type=radio name='residential' value='1' <?php print $opt_res1; ?> id='opt_res1'><label for='opt_res1'>Residential</label>
        <input type=radio name='residential' value='0' id='opt_res2' <?php print $opt_res2; ?>><label for='opt_res2'>Commercial</label>
    </td>
</tr>
<tr>
   <td valign='top'>PickUp</td>
   <td>
	<input type=radio name='pickup' value='01' <?php print $opt_pickup1; ?> id='opt_pickup1'><label for='opt_pickup1'>Daily Pickup</label><br>
	<input type=radio name='pickup' value='03' <?php print $opt_pickup2; ?> id='opt_pickup2'><label for='opt_pickup2'>Customer Counter(Drop off)</label><br>
	<input type=radio name='pickup' value='06' <?php print $opt_pickup3; ?> id='opt_pickup3'><label for='opt_pickup3'>On Time Pickup</label><br>
	<input type=radio name='pickup' value='07' <?php print $opt_pickup4; ?> id='opt_pickup4'><label for='opt_pickup4'>On Call Air</label><br>
	<input type=radio name='pickup' value='11' <?php print $opt_pickup5; ?> id='opt_pickup5'><label for='opt_pickup5'>Suggested Retail Rates</label><br>
	<input type=radio name='pickup' value='19' <?php print $opt_pickup6; ?> id='opt_pickup6'><label for='opt_pickup6'>Letter Center</label><br>
	<!--<input type=radio name='pickup' value='20' id='opt_pickup7'><label for='opt_pickup7'>Air Service Center</input>-->
   </td>
</tr>
<tr><td>More options</td>
    <td>There are more options : package type, (default is customer supplied package) and dimension. But I guess I am too lazy :) </td>
</tr>
</table>

<input type=submit value='Calculate Shipping Cost'>

</form>



<p>
Jason Chen 05/26/2014<br>
</body>



