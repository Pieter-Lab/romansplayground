<?php
//Error reporting
error_reporting(E_ALL);
ini_set("display_errors", 0);
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
//Look for the applicant id
if(isset($_GET) && isset($_GET['app-id']) && !empty($_GET['app-id'])){
    //set the applicant id
    $applicant_id = $_GET['app-id'];
    //set the applicant data
    $applicant_data = false;
    //try
    try{
        $ac['id'] = $applicant_id;
        $applicant_data = $client->__soapCall('GetApplicant', $ac);
    }catch(Exception $e){}
    //test
    if($applicant_data){
        printer($applicant_data);
        exit("Die Die!!!!");
    }else{
        //this is a single app report so need a applicant id
        echo '<h3>No Applicant data found for '.$_GET['app-id'].'</h3>';
    }
}else{
    //this is a single app report so need a applicant id
    echo '<h3>Please supply an Applicant ID</h3>';
}