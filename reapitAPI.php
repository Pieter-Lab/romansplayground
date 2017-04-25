<?php
/**
 * Created by PhpStorm.
 * User: pieter
 * Date: 4/24/17
 * Time: 5:51 PM
 */
//Error reporting
error_reporting(E_ALL);
ini_set("display_errors", 1);
//Set return holder
$holder = false;
//Check for Search
if(isset($_GET) && isset($_GET['search'])){
    //----------------------------------------------------------------------------------------------------------------------
    //Create the new SOAP Cllient instance
            $client = new SoapClient('http://romans.reapitcloud.com/dev-webservice/?wsdl', array('cache_wsdl' => WSDL_CACHE_NONE));
        //Set the Authentication Headers array
            $authHeaders = array(
                new SoapHeader('http://soapinterop.org/echoheader/', 'ClientID', 'lab'),
                new SoapHeader('http://soapinterop.org/echoheader/', 'Password', 'fa6d7d15e3fe38a')
            );
        //set headers
            $client->__setSoapHeaders($authHeaders);
    //----------------------------------------------------------------------------------------------------------------------
    //State Search criteria
        //$criteria['SearchType'] = 'sales'; // 'for sale' or 'to let'
            $criteria['Area'] = $_GET['search'];
        //Set the proiperty fields we wish to get back from reapit
            $criteria['PropertyField'] = array(
                'ID',
                'HouseName',
                'SalePriceString',
                'RentString',
                'Description',
                'Latitude',
                'Longitude',
                'Bedrooms',
                'Polygon',
                'Address1',
                'Address2',
                'Address3',
                'Address4',
                'Postcode',
                'Area',
                'Country',
                'Negotiator',
                'PrimaryDevelopment',
                'AgencyType',
                'Office',
                'IsDevelopment',
                'Developer',
                'Image'
            );
        //set offset
            $criteria['Offset'] = 0;
        //set limit
            $criteria['Limit'] = 10;
        //add seach parameters to call to call
            $params = array('Criteria' => $criteria);
    //----------------------------------------------------------------------------------------------------------------------
    //Get all properties for our criteria
        $results = $client->__soapCall('GetGeneralProperties', $params);
    //Test
    if($results && is_array($results) && !empty($results)){
        //assign to holder
        $holder = $results;
    }
}
//Kil Client
unset($client);
//output as json
header('Content-type: application/json');
print json_encode($holder);
?>