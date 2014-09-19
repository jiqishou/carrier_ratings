<?php

class ShippingCalculator  {
	// Defaults
	var $weight = 1;
	var $weight_unit = "lb";
	var $size_length = 4;
	var $size_width = 8;
	var $size_height = 2;
	var $size_unit = "in";
	var $debug = false; // Change to true to see XML sent and recieved 
	
	// Batch (get all rates in one go, saves lots of time)
	var $batch_ups = false; // Currently Unavailable
	var $batch_usps = false; 
	var $batch_fedex = false; // Currently Unavailable
	
	// Config (you can either set these here or send them in a config array when creating an instance of the class)
	var $services;
	var $from_zip;
	var $from_state;
	var $from_country;
	var $to_zip;
	var $to_state;
	var $to_country;
	var $ups_access;
	var $ups_user;
	var $ups_pass;
	var $ups_account;
	var $usps_user;
	var $usps_pass;
	var $fedex_account;
	var $fedex_meter;
	var $fedex_key;
	var $fedex_password;
	
	// Results
	var $rates;
	
	// Setup Class with Config Options
	function ShippingCalculator($config) {
		if($config) {
			foreach($config as $k => $v) $this->$k = $v;
		}
	}
	
	// Calculate
	function calculate($company = NULL,$code = NULL) {
		//echo "here is in Shipping Calculator";
		$this->rates = NULL;
		$services = $this->services;
		if($company and $code) $services[$company][$code] = 1;
		foreach($services as $company => $codes) {
			foreach($codes as $code => $name) {
				switch($company) {
					case "ups": 
						/*if($this->batch_ups == true) $batch[] = $code; // Batch calculation currently unavaiable
						else*/ $this->rates[$company][$code] = $this->calculate_ups($code);
						break;
					case "usps":
						if($this->batch_usps == true) $batch[] = $code;
						else $this->rates[$company][$code] = $this->calculate_usps($code);
						break;
					case "fedex": 
						/*if($this->batch_fedex == true) $batch[] = $code; // Batch calculation currently unavaiable
						else*/ $this->rates[$company][$code] = $this->calculate_fedex($code);
						break;
				}
			}
			// Batch Rates
			//if($company == "ups" and $this->batch_ups == true and count($batch) > 0) $this->rates[$company] = $this->calculate_ups($batch);
			//if($company == "usps" and $this->batch_usps == true and count($batch) > 0) $this->rates[$company] = $this->calculate_usps($batch);
			//if($company == "fedex" and $this->batch_fedex == true and count($batch) > 0) $this->rates[$company] = $this->calculate_fedex($batch);
		}
		
		return $this->rates;
	}
	
	// Calculate UPS
	function calculate_ups($code) {
		$url = "https://www.ups.com/ups.app/xml/Rate";
    	$data = '<?xml version="1.0"?>  
<AccessRequest xml:lang="en-US">  
	<AccessLicenseNumber>'.$this->ups_access.'</AccessLicenseNumber>  
	<UserId>'.$this->ups_user.'</UserId>  
	<Password>'.$this->ups_pass.'</Password>  
</AccessRequest>  
<?xml version="1.0"?>  
<RatingServiceSelectionRequest xml:lang="en-US">  
	<Request>  
		<TransactionReference>  
			<CustomerContext>Bare Bones Rate Request</CustomerContext>  
			<XpciVersion>1.0001</XpciVersion>  
		</TransactionReference>  
		<RequestAction>Rate</RequestAction>  
		<RequestOption>Rate</RequestOption>  
	</Request>  
	<PickupType>  
		<Code>01</Code>  
	</PickupType>  
	<Shipment>  
		<Shipper>  
			<Address>  
				<PostalCode>'.$this->from_zip.'</PostalCode>  
				<CountryCode>'.$this->from_country.'</CountryCode>  
			</Address>  
		<ShipperNumber>'.$this->ups_account.'</ShipperNumber>  
		</Shipper>  
		<ShipTo>  
			<Address>  
				<PostalCode>'.$this->to_zip.'</PostalCode>  
				<CountryCode>'.$this->to_country.'</CountryCode>  
			<ResidentialAddressIndicator/>  
			</Address>  
		</ShipTo>  
		<ShipFrom>  
			<Address>  
				<PostalCode>'.$this->from_zip.'</PostalCode>  
				<CountryCode>'.$this->from_country.'</CountryCode>  
			</Address>  
		</ShipFrom>  
		<Service>  
			<Code>'.$code.'</Code>  
		</Service>  
		<Package>  
			<PackagingType>  
				<Code>02</Code>  
			</PackagingType>  
			<Dimensions>  
				<UnitOfMeasurement>  
					<Code>IN</Code>  
				</UnitOfMeasurement>  
				<Length>'.($this->size_unit != "in" ? $this->convert_sze($this->size_length,$this->size_unit,"in") : $this->size_length).'</Length>  
				<Width>'.($this->size_unit != "in" ? $this->convert_sze($this->size_width,$this->size_unit,"in") : $this->size_width).'</Width>  
				<Height>'.($this->size_unit != "in" ? $this->convert_sze($this->size_height,$this->size_unit,"in") : $this->size_height).'</Height>  
			</Dimensions>  
			<PackageWeight>  
				<UnitOfMeasurement>  
					<Code>LBS</Code>  
				</UnitOfMeasurement>  
				<Weight>'.($this->weight_unit != "lb" ? $this->convert_weight($this->weight,$this->weight_unit,"lb") : $this->weight).'</Weight>  
			</PackageWeight>  
		</Package>  
	</Shipment>  
</RatingServiceSelectionRequest>'; 
		
		// Curl
		$results = $this->curl($url,$data);
		
		// Debug
		if($this->debug == true) {
			print "<xmp>".$data."</xmp><br />";
			print "<xmp>".$results."</xmp><br />";
		}
		
		// Match Rate
		preg_match('/<MonetaryValue>(.*?)<\/MonetaryValue>/',$results,$rate);
		
		//echo "UPS:         ";
		//echo $rate[1];
		return $rate[1];
	}
	
	// Calculate USPS
	function calculate_usps($code) {

		// may need to urlencode xml portion 		
		$url = "http://Production.ShippingAPIs.com/ShippingAPI.dll";

		$ounces = ceil(($weight - floor($weight)) * 16);
		$container = "Variable";
		$size = "Regular";
		$machinalbe = "True";

		$str = $url."?API=RateV4&XML=<RateV4Request%20USERID=\""; 		
        $str .= urlencode($this->usps_user) . "\"%20PASSWORD=\"" . urlencode($this->usps_pass) . "\"><Revision/><Package%20ID=\"0\"><Service>"; 
        $str .= urlencode($code) . "</Service><ZipOrigination>" . urlencode($this->from_zip) . "</ZipOrigination>"; 
        $str .= "<ZipDestination>" . urlencode($this->to_zip) . "</ZipDestination>"; 
        $str .= "<Pounds>" . urlencode($this->weight) . "</Pounds><Ounces>" . urlencode($ounces) . "</Ounces>"; 
        $str .= "<Container>" . urlencode($container) . "</Container><Size>" . urlencode($size) . "</Size>";
		$str .= "<Width>".urlencode($this->size_width)."</Width><Length>".urlencode($this->size_length)."</Length><Height>".urlencode($this->size_height)."</Height>"; 
        $str .= "<Machinable>" . urlencode($machinable) . "</Machinable></Package></RateV4Request>"; 

		@$fp = fopen($str, "r"); // or exit("Error <br>Cannot connect to the USPS server. Please select a different shipping option.");  
		if($fp){
		  while(!feof($fp)){  
			  $result = fgets($fp, 500);  
			  $body.=$result; 
		  }  
		  fclose($fp); 
		}
		else{
			$body = "Error <br>Cannot connect to the USPS server. Please select a different shipping option.";
		}
        # note: using split for systems with non-perl regex (don't know how to do it in sys v regex) 
        if (!preg_match("/Error/", $body)) { 
            $split = explode("<Rate>", $body);  
            $body = explode("</Rate>", $split[1]); 
            $price = $body[0]; 
            //echo "USPS:          ";
            //echo $price;
            return($price); 
        } else{ 
			if(preg_match("/Description/", $body)) { 
				$split = explode("<Description>", $body);  
            	$body = explode("</Description>", $split[1]); 
           	 	$error = $body[0]; 
           		return($error);
			}
			return false;
            
        }
		
		/* here is the end of modification of usps code*/
	}
	
	// Calculate FedEX
	function calculate_fedex($code) {	
		$newline = "<br />";
		//The WSDL is not included with the sample code.
		//Please include and reference in $path_to_wsdl variable.
		$path_to_wsdl = "RateService_v9.wsdl";

		ini_set("soap.wsdl_cache_enabled", "0");
 
		//echo urlencode($code);
		$client = new SoapClient($path_to_wsdl, array('trace' => 1)); // Refer to http://us3.php.net/manual/en/ref.soap.php for more information

		$request['WebAuthenticationDetail'] = array('UserCredential' =>
                                      array('Key' => $this->fedex_key, 'Password' => $this->fedex_password)); 
		$request['ClientDetail'] = array('AccountNumber' => $this->fedex_account, 'MeterNumber' => $this->fedex_meter);
		$request['TransactionDetail'] = array('CustomerTransactionId' => ' *** Rate Request v9 using PHP ***');
		$request['Version'] = array('ServiceId' => 'crs', 'Major' => '9', 'Intermediate' => '0', 'Minor' => '0');
		$request['ReturnTransitAndCommit'] = true;
		$request['RequestedShipment']['DropoffType'] = 'REGULAR_PICKUP'; // valid values REGULAR_PICKUP, REQUEST_COURIER, ...
		$request['RequestedShipment']['ShipTimestamp'] = date('c');
		$request['RequestedShipment']['ServiceType'] = urlencode($code);//'FEDEX_GROUND'; // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...
		$request['RequestedShipment']['PackagingType'] = 'YOUR_PACKAGING'; // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
		$request['RequestedShipment']['TotalInsuredValue']=array('Ammount'=>100,'Currency'=>'USD');

		$request['RequestedShipment']['Shipper'] = array(
			'Address' => array(
	            'StateOrProvinceCode' => $this->from_state,
    	        'PostalCode' => $this->from_zip,
        	    'CountryCode' => $this->from_country,
            	'Residential' => false)
				);
		$request['RequestedShipment']['Recipient'] = array(
			'Address' => array(
            	'StateOrProvinceCode' => $this->to_state,
            	'PostalCode' => $this->to_zip,
            	'CountryCode' => $this->to_country,
            	'Residential' => false)
			);
		$request['RequestedShipment']['ShippingChargesPayment'] = array('PaymentType' => 'SENDER',
                                                        'Payor' => array('AccountNumber' => $this->fedex_account,
                                                                     'CountryCode' => 'US'));
		$request['RequestedShipment']['RateRequestTypes'] = 'ACCOUNT'; 
		$request['RequestedShipment']['RateRequestTypes'] = 'LIST'; 
		$request['RequestedShipment']['PackageCount'] = '2';
		$request['RequestedShipment']['PackageDetail'] = 'INDIVIDUAL_PACKAGES';  //  Or PACKAGE_SUMMARY
		$request['RequestedShipment']['RequestedPackageLineItems'] = array('0' => array('Weight' => array('Value' => $this->weight,
                                                                                    'Units' => 'LB'),
                                                                                    'Dimensions' => array('Length' => $this->size_length,
                                                                                        'Width' => $this->size_width,
                                                                                        'Height' => $this->size_height,
                                                                                        'Units' => 'IN')));
		try 
		{
	
			$response = $client ->getRates($request);
	
    		if ($response -> HighestSeverity != 'FAILURE' && $response -> HighestSeverity != 'ERROR')
    		{  	
    			$rateReply = $response -> RateReplyDetails;
    			//$serviceType = '<td>'.$rateReply -> ServiceType . '</td>';
        		$amount = number_format($rateReply->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Amount,2,".",",");	
        		return $amount;
	    	}
    		else
    		{
        	printError($client, $response);
    		} 	
		}catch (SoapFault $exception) {
  			printFault($exception, $client);        
		}
	}

	function printNotifications($notes){
		foreach($notes as $noteKey => $note){
			if(is_string($note)){    
            	echo $noteKey . ': ' . $note . Newline;
        	}
        	else{
        		printNotifications($note);
        	}
		}
		echo Newline;
	}

	function printError($client, $response){
    	echo '<h2>Error returned in processing transaction</h2>';
		echo "\n";
		printNotifications($response -> Notifications);
    	printRequestResponse($client, $response);
	}
	
	// Curl
	function curl($url,$data = NULL) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);  
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		if($data) {
			curl_setopt($ch, CURLOPT_POST,1);  
			curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
		}  
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		$contents = curl_exec ($ch);
		
		return $contents;
		
		curl_close ($ch);
	}
	
	// Convert Weight
	function convert_weight($weight,$old_unit,$new_unit) {
		$units['oz'] = 1;
		$units['lb'] = 0.0625;
		$units['gram'] = 28.3495231;
		$units['kg'] = 0.0283495231;
		
		// Convert to Ounces (if not already)
		if($old_unit != "oz") $weight = $weight / $units[$old_unit];
		
		// Convert to New Unit
		$weight = $weight * $units[$new_unit];
		
		// Minimum Weight
		if($weight < .1) $weight = .1;
		
		// Return New Weight
		return round($weight,2);
	}
	
	// Convert Size
	function convert_size($size,$old_unit,$new_unit) {
		$units['in'] = 1;
		$units['cm'] = 2.54;
		$units['feet'] = 0.083333;
		
		// Convert to Inches (if not already)
		if($old_unit != "in") $size = $size / $units[$old_unit];
		
		// Convert to New Unit
		$size = $size * $units[$new_unit];
		
		// Minimum Size
		if($size < .1) $size = .1;
		
		// Return New Size
		return round($size,2);
	}
	
	// Set Value
	function set_value($k,$v) {
		$this->$k = $v;
	}
}
?>