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
//--------------------------------------------------------------------------------------------------------------
//Make db connection
$servername = "localhost";
$username = "root";
$password = "peter123";
// Create connection
$conn = new mysqli($servername, $username, $password,'romans');
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//Carry on ---------------------------------------------------------------------
//Get the general properties
//Set the proiperty fields we wish to get back from reapit
$criteria['PropertyField'] = array(
    'ID','HouseName','SalePriceString','RentString',
    'Description','Latitude', 'Longitude', 'Bedrooms',
    'Polygon', 'Address1', 'Address2', 'Address3',
    'Address4', 'Postcode', 'Area', 'Country', 'Negotiator', 'PrimaryDevelopment',
    'AgencyType', 'Office', 'IsDevelopment', 'Developer', 'Image'
);
//set offset
$criteria['Offset'] = ((isset($_GET['offset']))?$_GET['offset']:'0');
//set limit
$criteria['Limit'] = ((isset($_GET['limit']))?$_GET['limit']:'10');;
//add seach parameters to call to call
$params = array('Criteria' => $criteria);
//----------------------------------------------------------------------------------------------------------------------
//Get all properties for our criteria
$results = $client->__soapCall('GetGeneralProperties', $params);
//
echo "<table cellpadding='0' cellspacing='0' width='100%' border='1'>";
//test
if($results && !empty($results)){
    //loop em
    foreach($results as $key => $Prop){
        //get the viewings
        $viewings = false;
        try{
            //
            $vc['id'] = $Prop->ID;
            $viewings = $client->__soapCall('GetViewings', $vc);
        }catch(Exception $e){}
        if($viewings){
            //
            echo '<tr><td width="30%">'.$Prop->ID.'</td><td>'.count($viewings).'</td>';
            //count
            $count = 0;
            $collect = array();
            //loop
            foreach($viewings as $view){
                if(!empty($view->FollowUp)){
                    $count++;
                    $collect[] = $view->FollowUp;
                }
            }
            //
            echo '<td>'.$count.'</td>';
            echo '<td>'.implode('<br /> ' ,$collect).'</td></tr>';
        }
    }
}
//
echo '</table>';