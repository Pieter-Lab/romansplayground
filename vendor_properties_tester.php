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
//----------------------------------------------------------------------------------------------------------------------
//Styling---------------------------------------------------------------------------------------------------------------
echo '
    <style>
        body,th,td{
            font-size: 10px;
            font-family: Verdana, Arial, sans-serif;
        }
    </style>
';
//Test For Vendor ID
if(isset($_GET) && isset($_GET['vendorId'])){
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
    //Set the majors
    $reapitID = false;
    $reapitPassword = false;
    //Make the Reapit ID -------------------------------------------------------------------------------
    $raw = explode('-',$_GET['vendorId']);
    $reapitID = $raw[1];
    //set the Vendors password
    $reapitPassword = get_password($reapitID);
    //Test that we have a ID and Pass-------------------------------------------------------------------
    if($reapitID && $reapitPassword){
        //create the criteria to log the Vendor in on the Reapit side so we can get the Access Token----
        //for the Vendor--------------------------------------------------------------------------------
        $Criteria['id'] = $_GET['vendorId'];
        $Criteria['password'] = $reapitPassword;
        //Attempt to get a valid access token
        $attempt = $client->__soapCall('VendorLogin', $Criteria);
        //Check for the access token
        if($attempt && !empty($attempt)) {
            //set Vendor access token
            $acTk = trim($attempt);
            //Set new criteria to get Vendor documents--------------------------------------------------
            $cat['id'] = $_GET['vendorId'];
            $cat['AccessToken'] = $acTk;
            //make call to get the vendor props
            $vendorProps = $client->__soapCall('GetVendorProperties', $cat);
            //test if they have more than 1 property
            if(!empty($vendorProps)){
                //holder for props
                $props = array();
                //loop em
                foreach($vendorProps as $prop){
                    //unique id
                    $uniId = str_replace('romrps_test-','',$prop->ID);
                    //set in holder
                    $props[$uniId] = array(
                        'id'=>$prop->ID,
                        'salePrice'=>$prop->SalePriceString,
                        'salesStatus'=>@$prop->SaleStatus,
                        'status'=>@$prop->Status,
                        'area'=>$prop->Area,
                        'postCode'=>$prop->Postcode
                    );
                    //Get the Offers for the property
                    $poc['id'] = $prop->ID;
                    $poc['AccessToken'] = $acTk;
                    //make the call to get properties
                    $offers = $vendorProps = $client->__soapCall('GetOffers', $poc);
                    //Test
                    if(!empty($offers)){
                        //Set holder
                        $props[$uniId]['offers'] = array();
                        //loop and add
                        foreach ($offers as $offer){
                            //add
                            $props[$uniId]['offers'][strtotime($offer->Date)] = $offer;
                            //Check for the Chain
                            if(isset($offer->Chain) && !empty($offer->Chain) && count($offer->Chain)>1){
                                //Holder
                                $props[$uniId]['offers'][strtotime($offer->Date)]->chain = array();
                                //Loop the change
                                foreach ($offer->Chain as $chain){
                                    //uni
                                    $cUni = str_replace('romrps_test-','',$chain->ID);
                                    //set
                                    $props[$uniId]['offers'][strtotime($offer->Date)]->chain[$chain->ID] = array();
                                    //Get the Chain Offer
                                    $co['id'] = $chain->ID;
                                    $co['AccessToken'] = $acTk;
                                    //try
                                    try{
                                        $chainOffer = $client->__soapCall('GetOffer', $co);
                                        $props[$uniId]['offers'][strtotime($offer->Date)]->chain[$chain->ID] = $chainOffer;
//                                        printer($chainOffer);
                                    }catch (Exception $e){
                                        $props[$uniId]['offers'][strtotime($offer->Date)]->chain[$chain->ID]['error'] = $e->getMessage();
//                                        printer($co);
//                                        printer($e->getMessage());
                                    }
                                }
                            }
                        }
                    }
                }
//                printer($props);
//                printer($vendorProps);

                foreach($props as $prop){
                    //Display Table for props
                    echo '<table width="100%" cellpadding="2" cellspacing="2" border="1">';
                    //Prop info
                    echo '
                        <tr><th colspan="6" align="center">Vendor ID: '.$_GET['vendorId'].'</th></tr>
                        <tr><th colspan="6" align="left">Property '.$prop['id'].'</th></tr>
                        <tr>
                            <th width="16%">Property ID</th>
                            <th width="16%">Sale Price</th>
                            <th width="16%">Sales Status</th>
                            <th width="16%">Status</th>
                            <th width="16%">Area</th>
                            <th width="16%">Postcode</th>
                        </tr>
                        <tr>
                            <td>'.$prop['id'].'</td>
                            <td>'.$prop['salePrice'].'</td>
                            <td>'.$prop['salesStatus'].'</td>
                            <td>'.$prop['status'].'</td>
                            <td>'.$prop['area'].'</td>
                            <td>'.$prop['postCode'].'</td>
                        </tr>';
                    //check for offers
                    if(isset($prop['offers']) && !empty($prop['offers'])){
                        //sor
                        ksort($prop['offers']);
                        //Prop Offers
                        echo '<tr><td colspan="6">
                                <table width="100%" cellpadding="1" cellspacing="1" border="1">
                                    <tr><th colspan="7" align="left">Offers</th></tr>
                                    <tr>
                                        <th width="14%">Date</th>
                                        <th width="14%">Offer ID</th>
                                        <th width="14%">Status</th>
                                        <th width="14%">Vendor Chain Closed</th>
                                        <th width="14%">BuyerChainClosed</th>
                                        <th width="14%">Applicant</th>
                                        <th width="14%">Price</th>
                                        
                                    </tr>
                                ';
                            //loop offers
                            foreach ($prop['offers'] as $offer){
                                echo '<tr>
                                    <td>'.$offer->Date.'</td>
                                    <td>'.$offer->ID.'</td>
                                    <td>'.$offer->Status.'</td>
                                    <td>'.(($offer->VendorChainClosed)?'yes':'no').'</td>
                                    <td>'.(($offer->BuyerChainClosed)?'yes':'no').'</td>
                                    <td>'.$offer->Applicant->ID.'</td>
                                    <td>'.$offer->Price.'</td>
                                </tr>';
                                //loop for chain
                                if(isset($offer->chain) && !empty($offer->chain)){
                                    echo '<tr><td colspan="7">
                                        <table width="100%" cellpadding="1" cellspacing="1" border="1">
                                            <tr>
                                                <th>Chain Offer Id</th>
                                                <th>Chain Offer Status</th>
                                            </tr>
                                        ';
                                    foreach($offer->chain as $cId=>$cOffer){
                                        if(isset($cOffer['error']) && !empty($cOffer['error'])){
                                            echo '<tr>
                                                <td>'.$cId.'</td>
                                                <td>'.$cOffer['error'].'</td>
                                            </tr>';
                                        }else{
                                            echo '<tr>
                                                <td>'.$cId.'</td>
                                                <td>'.$cOffer->Status.'</td>
                                            </tr>';
                                        }
                                    }

                                    echo '</table>
                                            </td></tr>';
                                }
                            }
                            //close table
                            echo '</table>';
                        echo '</td></tr>';
                    }
                    //close table
                    echo '</table><br />';
                }
            }
        }
    }
}else{
    echo '<h3>Please Supply a Vendor Id</h3>';
}

