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

    $criteria['RequiredField'] = array('ID','Title','Initials','Surname','Type');
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
        //Count the offers
        $results[$key]->offers = count($offers);
        //Now check if they are selling anything a.k.a Vendor
        $vendorProps = false;
        try{
            $cat['id'] = $applicant->ID;
            $vendorProps = $client->__soapCall('GetVendorProperties', $cat);
        }catch(Exception $e){}
        //test
        if($vendorProps && !empty($vendorProps)){
            //Loop em and find out if they are in the property db
            $count = 0;
            foreach($vendorProps as $vProp){
                //test if prop has offer
                $V_offers = false;
                try{
                    //Get the Offers for the property
                    $poc['id'] = $vProp->ID;
                    //make the call to get properties
                    $V_offers = $vendorProps = $client->__soapCall('GetOffers', $poc);
                }catch(Exception $e){}
                //test if property has offers,only add ones with offers
                if($V_offers && !empty($V_offers)){
                    //Set proceed
                    $proceed = false;
                    //There must at least be an offer which is accepted
                    foreach($V_offers as $of){
                        if($of->Status==="Offer Accepted"){
                            if(isset($of->Chain) && !empty($of->Chain) && count($of->Chain) > 1){
                                $proceed = true;
                            }
                        }
                    }
                    //test if we can proceed
                    if($proceed){
                        //get db provider id
                        $db_prop_id = str_replace('romrps_test-','',$vProp->ID);
                        //find in db
                        $sql = 'SELECT `provider_property_id` FROM `property` WHERE provider_property_id="'.$db_prop_id.'"';
                        //get the res
                        $res = $conn->query($sql);
                        //test
                        if ($res->num_rows > 0){
                            $count++;
                        }
                    }
                }
            }
            if($count===0){
                unset($results[$key]);
            }else{
                //count em
                $results[$key]->vendor_props = $count;
            }
        }else{
            unset($results[$key]);
        }
    }else{
        unset($results[$key]);
    }
}

printer($results);