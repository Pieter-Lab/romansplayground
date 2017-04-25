<?php
//error reporting
error_reporting(E_ALL);
ini_set("display_errors", 1);
//Bring in custom DB wrapper
require __DIR__.'/db_wrapper.php';
//Bring in Composer Libraries
require __DIR__ . '/vendor/autoload.php';
//---------------------------------------------------------------------------------
//Check for Search
if(isset($_GET) && isset($_GET['postcode'])){
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
    $criteria['Area'] = $_GET['postcode'];
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
    $propResults = $client->__soapCall('GetGeneralProperties', $params);
}
//Kil Client
unset($client);
//Set DB wrapper
$db = new db_wrapper();
//Get Cords by Post Code
$sql = 'SELECT AsText(geom) AS CORDS FROM amd_postcode_disrict_boundaries WHERE `PostDist`="'.$_GET['postcode'].'" OR `Structurred_Dist`="'.$_GET['postcode'].'" ORDER BY `PostDist` ASC';
//get res
$result = $db->conn->query($sql);
//test rest
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        //Load cords into polygon
        $polygon = geoPHP::load($row['CORDS'],'wkt');
        //get centers
        $centroid = $polygon->getCentroid();
        $centX = $centroid->getX();
        $centY = $centroid->getY();
        //get poly as json
        $jsonAr = json_decode($polygon->out('json'));
        //set the container
        $holder = array();
        $holder['center'] = array('lat'=>$centY,'lng'=>$centX);
        $holder['polygon'] = $jsonAr;
        //Props
        if($propResults && !empty($propResults)){
            foreach ($propResults as $prop){
                //http://stackoverflow.com/questions/22649239/geophp-point-in-polygon
                //https://github.com/sookoll/geoPHP
                $lon = $prop->Longitude;
                $lat = $prop->Latitude;
                $point = geoPHP::load('POINT ('.$lon.' '.$lat.')','wkt');
                $point_is_in_polygon = $polygon->pointInPolygon($point);
                //test if in polygon
                if($point_is_in_polygon){
                    $holder['properties'][] = $prop;
                }
            }
        }
        //output as json
        header('Content-type: application/json');
        print json_encode($holder);
    }
}
//Get Cords by Town Name
$sql = 'SELECT AsText(geom) AS CORDS FROM seamless_town WHERE `town_name`="'.$_GET['postcode'].'" OR `u_townname`="'.$_GET['postcode'].'" ORDER BY `town_name` ASC';
//get res
$result = $db->conn->query($sql);
//test rest
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        //Load cords into polygon
        $polygon = geoPHP::load($row['CORDS'],'wkt');
        //get centers
        $centroid = $polygon->getCentroid();
        $centX = $centroid->getX();
        $centY = $centroid->getY();
        //get poly as json
        $jsonAr = json_decode($polygon->out('json'));
        //set the container
        $holder = array();
        $holder['center'] = array('lat'=>$centY,'lng'=>$centX);
        $holder['polygon'] = $jsonAr;
        //Props
        if($propResults && !empty($propResults)){
            foreach ($propResults as $prop){
                //http://stackoverflow.com/questions/22649239/geophp-point-in-polygon
                //https://github.com/sookoll/geoPHP
                $lon = $prop->Longitude;
                $lat = $prop->Latitude;
                $point = geoPHP::load('POINT ('.$lon.' '.$lat.')','wkt');
                $point_is_in_polygon = $polygon->pointInPolygon($point);
                //test if in polygon
                if($point_is_in_polygon){
                    $holder['properties'][] = $prop;
                }
            }
        }
        //output as json
        header('Content-type: application/json');
        print json_encode($holder);
    }
}
$db->conn->close();

