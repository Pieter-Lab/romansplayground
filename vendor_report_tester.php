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
$fromDate = '2015-07-01';
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
//        echo '-------------------------------------------------------------------------------'."\r\n";
//        printer($fromDate);
//        printer($toDate);
//        printer(count($results));
        $res = array_merge($res,$results);
    }
    //------------------------------------------------------------------------------------------------------------------
    //set the new from to the current to date
    $fromDate = $toDate;
    //------------------------------------------------------------------------------------------------------------------
}
//echo '-------------------------------------------------------------------------------'."\r\n";
//printer("Total Count");
//printer(count($res));
//exit();
//reset
$results = $res;

//Test
if($results && is_array($results) && !empty($results)){
    $countLvl1 = 0;// All contacts
    $countLvl4 = 0;// All contacts that get a password back from curl service and get an access token back from vendor login and do not  have properties
    $countLvl5 = 0;// All contacts that get a password back from curl service and get an access token back from vendor login and have properties
    $whoHas = array();
    //Loop em
    foreach($results as $contact){
        //Set the majors
        $reapitID = false;
        $reapitPassword = false;
        //Make the Reapit ID -------------------------------------------------------------------------------
        $raw = explode('-',$contact->ID);
        $reapitID = $raw[1];
        //Set new criteria to get Vendor documents--------------------------------------------------
        $cat['id'] = $contact->ID;
        //try
        try{
            //make call to get the vendor props
            $vendorProps = $client->__soapCall('GetVendorProperties', $cat);
            //test if they have more than 1 property
            if(!empty($vendorProps)){
                //Who Has
                $whoHas[$reapitID] = array(
                    'id'=>$contact->ID,
                    'propCount'=>count($vendorProps)
                );
                //increment lvl4 count
                $countLvl5++;
            }
        }catch(Exception $e){
//                    printer($e->getMessage());
//                    printer($e->getLine());
//                    printer($e->getTrace());
            //increment lvl4 count
            $countLvl4++;
        }
        //increment lvl1 count
        $countLvl1++;

//        if($countLvl1==10){
//            break;
//        }
    }

}
//Styling---------------------------------------------------------------------------------------------------------------
echo '
    <style>
        body,th,td{
            font-size: 10px;
            font-family: Verdana, Arial, sans-serif;
        }
    </style>
';
//Display Table For Key Info--------------------------------------------------------------------------------------------
echo '
    <table width="100%" cellpadding="2" cellspacing="2" border="1">
        <thead>
            <tr>
                <th>How many Contacts</th>
                <th>How many Contacts that do not have properties</th>
                <th>How many Contacts that do have properties</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>'.$countLvl1.'</td>
                <td>'.$countLvl4.'</td>
                <td>'.$countLvl5.'</td>
            </tr>
        </tbody>
    </table>
';
//Display Table for who has---------------------------------------------------------------------------------------------
echo '<table width="100%" cellpadding="2" cellspacing="2" border="1">
    <tr>
        <th colspan="2">Who Has properties</th>
    </tr>
    <tr>
        <th>ReapIt ID</th>
        <th>Property Count</th>
    </tr>
';
ksort($whoHas);
foreach($whoHas as $person){
    echo '<tr><td>'.$person['id'].'</td><td>'.$person['propCount'].'</td></tr>';
}
echo '</table>';
