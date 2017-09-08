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
//exit("Clear cache!!!");
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
$offset = ((isset($_GET['offset']))?$_GET['offset']:'0');
$limit = ((isset($_GET['limit']))?$_GET['limit']:'10');
//get property entities
$sql = 'SELECT `provider_property_id` FROM `property` LIMIT '.$offset.','.$limit;
//get the res
$res = $conn->query($sql);
//test
if ($res->num_rows > 0){
    //collect vendors
    $vendors = array();
    //loop em
    while($row = $res->fetch_assoc()) {
        //Get the actual db id for the property
        $reapit_property_id = 'romrps_test-'.$row['provider_property_id'];
        //------------------------------------------------------------------------
        //See if there is a vendor for this property
        try{
            $c['id'] = $reapit_property_id;
            $vendor = $client->__soapCall('GetVendor', $c);
        }catch(Exception $e){}
        //test
        if(isset($vendor) && !empty($vendor)){
            //Check if our vendor is also an applicant
            $applicant = false;
            try{
                $ac['id'] = $vendor->ID;
                $applicant = $client->__soapCall('GetApplicant', $ac);
                printer($applicant);
                exit("We found an vendor who is also an applicant!!");

            }catch(Exception $e){}
            //We only want applicants
            if($applicant){
                //add to collection
                if(!isset($vendors[$vendor->ID])){
                    $vendors[$vendor->ID] = array();
                    $vendors[$vendor->ID]['details'] = $vendor;
                    $vendors[$vendor->ID]['db_props'] = array();
                    $vendors[$vendor->ID]['api_props'] = array();
                    $vendors[$vendor->ID]['db_props'][$reapit_property_id] = $row['provider_property_id'];
                }else{
                    $vendors[$vendor->ID]['db_props'][$reapit_property_id] = $row['provider_property_id'];
                }
                //Set
                $vProps = false;
                //Get the Vendors properties from the API
                try{
                    $vpc['id'] = $vendor->ID;
                    $vProps = $client->__soapCall('GetVendorProperties', $vpc);
                }catch(Exception $e){}
                //Test
                if(isset($vProps) && !empty($vProps)){
                    //loop em
                    foreach($vProps as $vProp){
                        //add to collection
                        $vendors[$vendor->ID]['api_props'][$vProp->ID] = str_replace('romrps_test-','',$vProp->ID);
                    }
                }
                ksort($vendors[$vendor->ID]['api_props']);
                ksort($vendors[$vendor->ID]['db_props']);
            }
        }
        //------------------------------------------------------------------------
    }
    if(!empty($vendors)){
        //sort vendors
        ksort($vendors);
        //Styling---------------------------------------------------------------------------------------------------------------
        echo '
                <style>
                    body,th,td{
                        font-size: 10px;
                        font-family: Verdana, Arial, sans-serif;
                    }
                </style>
            ';
        //print them
        foreach ($vendors as $ven){
            //start table
            echo '<table cellpadding="1" cellspacing="1" border="1" width="100%">
                  <tr>
                    <th width="25%">Vendor ID</th>
                    <th width="25%">Salutation</th>
                    <th width="25%">DB Props Count</th>
                    <th width="25%">API Props Count</th>
                  </tr>';
            //info row
            echo '<tr><td>'.$ven['details']->ID.'</td><td>'.$ven['details']->Salutation.'</td><td>'.count($ven['db_props']).'</td><td>'.count($ven['api_props']).'</td></tr>';
            //db props
            echo '<tr><td colspan="2" valign="top"><table cellpadding="1" cellspacing="1" border="1" width="100%"><tr><th>DB Props</th></tr>';
            if(!empty($ven['db_props'])){
                foreach($ven['db_props'] as $ID => $prop){
                    //Get any Offers on the property
                    try{
                        $oc['id'] = $ID;
                        $propOffers = $client->__soapCall('GetOffers', $oc);
                    }catch(Exception $e){}
                    //test
                    if(!empty($propOffers)){
                        //                        printer($propOffers);
                        //                        exit("breaker!!");
                        echo '<tr><td>'.$prop.' / Offers: '.count($propOffers).'</td></tr>';

                        foreach($propOffers as $offer){
                            //only show ones which have been accepted
                            if($offer->Status==="Offer Accepted"){
                                echo '<tr><td>Status: '.$offer->Status.' / Chain Count:'.count($offer->Chain).'</td></tr>';
                            }
                        }

                    }else{
                        echo '<tr><td>'.$prop.'</td></tr>';
                    }
                }
            }else{
                echo '<tr><td>No DB Props</td></tr>';
            }
            echo '</table></td>';
            //API props
            echo '<td colspan="2" valign="top"><table cellpadding="1" cellspacing="1" border="1" width="100%"><tr><th>API Props</th></tr>';
            if(!empty($ven['api_props'])){
                foreach($ven['api_props'] as $prop){
                    echo '<tr><td>'.$prop.'</td></tr>';
                }
            }else{
                echo '<tr><td>No API Props</td></tr>';
            }
            echo '</table></td></tr>';
            //close table
            echo '</table><p>&nbsp;</p>';
        }
    }else{
        echo '<h3>There are no vendors who are applicants!</h3>';
    }
}

