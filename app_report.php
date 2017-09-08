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
//key count
$keY_count = 0;
//Loop to get the applicants offers
foreach ($results as $key => $applicant){
    //double check this is an applicant by finding them in GetApplicant ReapIt method
    $reapItApplicant = false;
    //Try
    try{
        $ac['id'] = $applicant->ID;
        $reapItApplicant = $client->__soapCall('GetApplicant', $ac);
    }catch(Exception $e){}
    //Test
    if($reapItApplicant){
        //Add to carrier
        $carrier[$keY_count] = array();
        $carrier[$keY_count]['applicant'] = $reapItApplicant;
        //Setup up viewings holder
        $applicant_viewings = false;
        //try
        try{
            $vc['id'] = $applicant->ID;
            $applicant_viewings = $client->__soapCall('GetApplicantViewings', $vc);
        }catch(Exception $e){}
        //Test
        if($applicant_viewings){
            //add to carrier
            $carrier[$keY_count]['viewings'] = $applicant_viewings;
        }
        //Get the applicant offers
        $applicant_offers = false;
        //try
        try{
            $aoc['id'] = $applicant->ID;
            $applicant_offers = $client->__soapCall('GetApplicantOffers', $aoc);
        }catch(Exception $e){}
        //test
        if($applicant_offers){
            //add to carrier
            $carrier[$keY_count]['offers'] = $applicant_offers;
        }
        //increment key
        $keY_count++;
    }
}

//printer($carrier);

foreach($carrier as $key=>$applicant){
    echo '<table width="100%" cellpadding="1" cellspacing="1" border="1">';
        echo '<tr>';
            echo '<th>ApplicantTime Registered</th>';
            echo '<th>Applicant ID</th>';
            echo '<th>ApplicantName</th>';
            echo '<th>ApplicantEmail</th>';
            echo '<th>ApplicantType</th>';
            echo '<th>OfficeID</th>';
            echo '<th>Applicant Matched General Properties</th>';
            echo '<th>Applicant Viewed General Properties</th>';
        echo '</tr>';
        echo '<tr>';
            echo '<td>'.$applicant['applicant']->TimeRegistered.'</td>';
            echo '<td>'.$applicant['applicant']->ID.'</td>';
            echo '<td>'.$applicant['applicant']->Title.' '.$applicant['applicant']->Initials.' '.$applicant['applicant']->Surname.'</td>';
            echo '<td>'.$applicant['applicant']->Email.'</td>';
            echo '<td>'.$applicant['applicant']->Type.'</td>';
            echo '<td>'.$applicant['applicant']->OfficeID.'</td>';
            echo '<td>'.count($applicant['applicant']->MatchedGeneralProperties).'</td>';
            echo '<td>'.count($applicant['applicant']->ViewedGeneralProperties).'</td>';
        echo '</tr>';
        //Test for viewings
        if(isset($applicant['viewings']) && !empty($applicant['viewings'])){
            foreach($applicant['viewings'] as $viewing){
                echo '<tr>';
                    echo '<th>Viewing:</th>';
                    echo '<td colspan="7">
                            <table width="100%" cellpadding="1" cellspacing="1" border="1">
                                <tr>
                                    <th width="20%">Date:</th>
                                    <th width="20%">Viewing ID:</th>
                                    <th width="20%">Property ID:</th>
                                    <th width="20%">Confirmed:</th>
                                    <th width="20%">Cancelled:</th>
                                    <th width="20%">FollowUp:</th>
                                </tr>
                                <tr>
                                    <td>'.$viewing->DateTime.'</td>
                                    <td>'.$viewing->ID.'</td>
                                    <td>'.$viewing->Property->ID.'</td>
                                    <td>'.(($viewing->Confirmed)?'YES':'NO').'</td>
                                    <td>'.(($viewing->Cancelled)?'YES':'NO').'</td>
                                    <td>'.$viewing->FollowUp.'</td>
                                </tr>
                            </table>
                        </td>';
                echo '</tr>';
            }
        }
    //Test for Offers
    if(isset($applicant['offers']) && !empty($applicant['offers'])){
        foreach($applicant['offers'] as $offer){
            //Test if offer has been accepted
            if($offer->Status==="Offer Accepted"){
                //get the vendor for the property
                $vendor = false;
                //try
                try{
                    $vc['id'] = $offer->Property->ID;
                    $vendor = $client->__soapCall('GetVendor', $vc);
                }catch(Exception $e){}
                //test
                if($vendor){
                    //set display
                    $vDis = '
                            <table width="100%" border="1">
                                <tr>
                                    <th>Vendor ID</th>
                                    <th>Vendor Name</th>
                                </tr>
                                <tr>
                                    <td>'.$vendor->ID.'</td>
                                    <td>'.$vendor->Title.' '.$vendor->Initials.' '.$vendor->Surname.'</td>
                                </tr>
                            </table>
                    ';
                }else{
                    $vDis = false;
                }
            }else{
                $vDis = false;
            }
            //show table
            echo '<tr>';
            echo '<th>Offer:</th>';
            echo '<td colspan="7">
                            <table width="100%" cellpadding="1" cellspacing="1" border="1">
                                <tr>
                                    <th width="20%">Date:</th>
                                    <th width="20%">Offer ID:</th>
                                    <th width="20%">Status:</th>
                                    <th width="20%">Property ID:</th>
                                    <th width="20%">Price:</th>
                                </tr>
                                <tr>
                                    <td>'.$offer->Date.'</td>
                                    <td>'.$offer->ID.'</td>
                                    <td>'.$offer->Status.'</td>
                                    <td>'.$offer->Property->ID.'</td>
                                    <td>'.$offer->Price.'</td>
                                </tr>
                            </table>
                            '.(($vDis)?$vDis:'').'
                        </td>';
            echo '</tr>';
        }
    }
    echo '</table><br><p>&nbsp;</p>';
}
