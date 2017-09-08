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
$dayPlus = 6; //day increment
$fromDate = ((isset($_GET['startdate']))?$_GET['startdate']:'2015-01-01'); //starting date
$res= array(); //results holder
for($i=0;$i<=14;$i=$i+7){
    //set to date off start date
    $toDate = date('Y-m-d',strtotime($fromDate.' + '.$dayPlus.' days'));
    //------------------------------------------------------------------------------------------------------------------
    //Set criteria to get all applicants
    $criteria['FromDate'] = $fromDate;
    $criteria['ToDate'] = $toDate;

    //Tell me what was the final date
    echo '<h3>Date loop:  '.$fromDate.' : '.$toDate.'</h3>';

    $criteria['RequiredField'] = array('ID','Title','Initials','Surname','Type','OfficeID');
    //add search parameters to the call
    $params = array('Criteria' => $criteria);
    //Get all applicants for our criteria
    try{
        $results = $client->__soapCall('GetApplicants', $params);
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
//carrier
$carrier = array();
//Loop to get the applicants offers
foreach ($results as $key => $applicant){
    //get the offers for the applicant
    $offers = false;
    try{
        $ao['id'] = $applicant->ID;
        $offers = $client->__soapCall('GetApplicantOffers', $ao);
    }catch(Exception $e){}
    //test
    if($offers && !empty($offers)){
//        printer($applicant);
//        exit();
        //counter
        $count = 0;
        $accepted_offers = [];
        //Loop the offers to check if the offer is on one of the props in our db
        foreach($offers as $key => $offer){
            if($offer->Status==="Offer Accepted"){
                //test for the chain
                if(isset($offer->Chain) && !empty($offer->Chain) && count($offer->Chain)>1){
                    //get db provider id
                    $db_prop_id = str_replace('romrps_test-','',$offer->Property->ID);
                    //find in db
                    $sql = 'SELECT `provider_property_id` FROM `property` WHERE provider_property_id="'.$db_prop_id.'"';
                    //get the res
                    $res = $conn->query($sql);
                    //test
                    if ($res->num_rows > 0){
                        $accepted_offers[] = $offer;
                        $count++;
                    }
                }
            }
        }
        //state the number of props found if offers linked ot props in our db
        if($count>0){
            $carrier[$key] = array();
            $carrier[$key]['applicant'] = $applicant;
            $carrier[$key]['accepted_offers_count'] = count($accepted_offers);
            $carrier[$key]['accepted_offers'] = $accepted_offers;
            $carrier[$key]['offers_with_db_props'] = $count;
        }else{
//            unset($results[$key]);
        }
    }else{
//        unset($results[$key]);
    }
}

printer($carrier);