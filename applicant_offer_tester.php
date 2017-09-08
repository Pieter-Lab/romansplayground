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
if(isset($_GET['appId']) && !empty($_GET['appId'])){
    try{
        //Set criteria to get Applicant
        $c['id'] = $_GET['appId'];
        //Get the applicant info
        $applicant = $client->__soapCall('GetApplicant', $c);
    }catch(Exception $e){
        exit($e->getMessage());
    }
    //Test
    if($applicant && !empty($applicant)){
        printer($applicant);
        //set
        $content = '<table cellpadding="1" cellspacing="1" width="100%" border="1">
                        <tr>
                            <th width="35%">ID</th>
                            <th width="35%">NAME</th>
                            <th width="35%">Type</th>
                        </tr>
                        <tr>
                            <td>'.$applicant->ID.'</td>
                            <td>'.$applicant->Title.' '.$applicant->Initials.' '.$applicant->Surname.'</td>
                            <td>'.$applicant->Type.'</td>
                        </tr>';
        //Close table
        $content .= '</table>';
        //Get the offers
        try{
            $ao['id'] = $applicant->ID;
            $offers = $client->__soapCall('GetApplicantOffers', $ao);
        }catch(Exception $e){}
        //test
        if(isset($offers) && !empty($offers)){
            //loop em
            foreach($offers as $offer){

                printer($offer);

                $content .= '<table width="100%" cellpadding="1" cellspacing="1" border="1">
                                    <tr>
                                        <th colspan="6">Offer</th>
                                    </tr>
                                    <tr>
                                        <th>Offer ID</th>
                                        <th>Date</th>
                                        <th>Property ID</th>
                                        <th>Property</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                    </tr>';
                //get the property
                try{
                    //get prop
                    if(isset($offer->Property)){
                        $vpc['id'] = $offer->Property->ID;
                        $vpc['FieldList'] = array();
                        $property = $client->__soapCall('GetGeneralProperty', $vpc);
                    }
                }catch(Exception $e){}
                if(isset($property)){
                    ///set content
                    $content .= '
                        <tr>
                            <td>'.$offer->ID.'</td>
                            <td>'.$offer->Date.'</td>
                            <td>'.$offer->Property->ID.'</td>
                            <td>'.((isset($property->HouseNumber))?$property->HouseNumber.' ':'').((isset($property->Address1))?$property->Address1.' ':'').((isset($property->Address2))?$property->Address2.' ':'').((isset($property->Area))?$property->Area.' ':'').((isset($property->Postcode))?$property->Postcode.' ':'').'</td>
                            <td>'.$offer->Price.'</td>
                            <td>'.$offer->Status.'</td>
                        </tr>
                    ';
                }else{
                    ///set content
                    $content .= '
                        <tr>
                            <td>'.$offer->ID.'</td>
                            <td>'.$offer->Date.'</td>
                            <td>Unavailable</td>
                            <td>Unavailable</td>
                            <td>'.$offer->Price.'</td>
                            <td>'.$offer->Status.'</td>
                        </tr>
                    ';
                }
                $content .= '</table>';
                //check for the chain
                if(isset($offer->Chain) && !empty($offer->Chain)){
                    //open
                    $content .= '<table width="100%" cellpadding="1" cellspacing="1" border="1">
                                    <tr><th>Offer Chain</th></tr>
                                    <tr>
                                        <th>Offer Chain ID</th>
                                    </tr>
                                ';
                    //loop the chain
                    foreach ($offer->Chain as $Chain){
                        //Get the offer for the chain
                        try{
                            $oc['id'] = $Chain->ID;
                            $chainOffer = $client->__soapCall('GetOffer', $oc);
                        }catch(Exception $e){}
                        //get the offer in the chain
                        $content .= '<tr><td>'.$Chain->ID.'</td></tr>';
                        if(isset($chainOffer) && !empty($chainOffer)){
                            //set
                            $content .= '<tr><td><table width="100%" cellpadding="1" cellspacing="1" border="1">';
                            //get the property
                            try{
                                //get prop
                                if(isset($chainOffer->Property)){
                                    $vpc['id'] = $chainOffer->Property->ID;
                                    $vpc['FieldList'] = array();
                                    $property = $client->__soapCall('GetGeneralProperty', $vpc);
                                }
                            }catch(Exception $e){}
                            if(isset($property)){
                                ///set content
                                $content .= '
                                            <tr>
                                                <td>'.$chainOffer->ID.'</td>
                                                <td>'.$chainOffer->Date.'</td>
                                                <td>'.$chainOffer->Property->ID.'</td>
                                                <td>'.((isset($property->HouseNumber))?$property->HouseNumber.' ':'').((isset($property->Address1))?$property->Address1.' ':'').((isset($property->Address2))?$property->Address2.' ':'').((isset($property->Area))?$property->Area.' ':'').((isset($property->Postcode))?$property->Postcode.' ':'').'</td>
                                                <td>'.$chainOffer->Price.'</td>
                                                <td>'.$chainOffer->Status.'</td>
                                            </tr>
                                        ';
                            }else{
                                ///set content
                                $content .= '
                                        <tr>
                                            <td>'.$chainOffer->ID.'</td>
                                            <td>'.$chainOffer->Date.'</td>
                                            <td>Unavailable</td>
                                            <td>Unavailable</td>
                                            <td>'.$chainOffer->Price.'</td>
                                            <td>'.$chainOffer->Status.'</td>
                                        </tr>
                                    ';
                            }

                            //close
                            $content .= '</table></td></tr>';
                        }else{
                            $content .= '<tr><td>Unavailable</td></tr>';
                        }
                    }
                    //close
                    $content .= '</table>';
//                    printer($offer->Chain);

                }
            }

        }

        //print out
        print $content;
    }
}else{
    echo '<h3>Please supply asn Applicant ID</h3>';
}

