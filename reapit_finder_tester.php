<?php
//Error reporting
error_reporting(E_ALL);
ini_set("display_errors", 1);
//----------------------------------------------------------------------------------------------------------------------
function printer($val){
    if(is_array($val) || is_object($val)){
        echo '<pre>';
        print_r($val);
        echo '</pre>';
    }else{
        echo '<br />';
        var_dump($val);
        echo '<br />';
    }
}
function get_password($reapitID){
    //Do a CURL call to password generator to get Vendor password---------------------------------------
    $ch = curl_init('http://reapitweb.reapit.com/tools/encode.php?password='.$reapitID.'&encoding=CRC16');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    $result = curl_exec($ch);
    //read returned html as simple xml
    $doc = new DOMDocument();
    $doc->strictErrorChecking = FALSE;
    $doc->loadHTML($result);
    $xml = simplexml_import_dom($doc);
    //set the Vendors password
    return current($xml->body->input['value']);
}
//exit("Pop the cache!!!");
//----------------------------------------------------------------------------------------------------------------------
//Get the Contacts from Reapit wsdl
//--------------------------------------------------------------------------------------------------------------
//Create the new SOAP Cllient instance
$client = new SoapClient('http://romans.reapitcloud.com/dev-webservice/?wsdl', array('cache_wsdl' => WSDL_CACHE_NONE));
//Set the Authentication Headers array
$authHeaders = array(
    new SoapHeader('http://soapinterop.org/echoheader/', 'ClientID', 'lab'),
    new SoapHeader('http://soapinterop.org/echoheader/', 'Password', 'fa6d7d15e3fe38a')
);
//set headers
$client->__setSoapHeaders($authHeaders);
//        try{
//--------------------------------------------------------------------------------------------------------------
$dayPlus = 6;
$fromDate = '2017-01-01';
$res= array();
for($i=0;$i<=7;$i=$i+7){
    //set to date off start date
    $toDate = date('Y-m-d',strtotime($fromDate.' + '.$dayPlus.' days'));
    //------------------------------------------------------------------------------------------------------------------
    //Set criteria to get all contacts
    $criteria['FromDate'] = $fromDate;
    $criteria['ToDate'] = $toDate;
    $criteria['RequiredField'] = array('ID','isVendor','Title','Initials','Surname','Salutation');
    //add search parameters to the call
    $params = array('Criteria' => $criteria);
    //Get all contacts for our criteria
    try{
        $results = $client->__soapCall('GetContacts', $params);
    }catch(Exception $e){}
    //------------------------------------------------------------------------------------------------------------------
    if(!empty($results)){
        $res = array_merge($res,$results);
    }
    //------------------------------------------------------------------------------------------------------------------
    //set the new from to the current to date
    $fromDate = $toDate;
    //------------------------------------------------------------------------------------------------------------------
}
//reset
$results = $res;
//Test
if(!empty($results)){
    //set the counter
    $count = 0;
    //open
    echo '<table cellpadding="1" cellspacing="1" width="100%" border="1">';
    //loop
    foreach($results as $vendor){
        //set
        $vendorProps = false;
        //test
        try{
            //set criteria
            $cat['id'] = $vendor->ID;
            //make call to get the vendor props
            $vendorProps = $client->__soapCall('GetVendorProperties', $cat);
        }catch(Exception $e){}
        //test if they have more than 1 property
        if(!empty($vendorProps)){
            //talk
            echo '<tr><td>'.$count.'</td><td>'.$vendor->ID.'</td><td>'.((isset($vendor->Salutation))?$vendor->Salutation:'na').'</td><td>'.count($vendorProps).'</td></tr>';
            //increment count
            $count++;
        }
    }
    //close
    echo '<table>';
}