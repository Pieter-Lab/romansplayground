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
//Pretty Print array
function pp($arr){
    $retStr = '<table width="100%" border="1" cellpadding="1" cellspacing="1">';
    if (is_array($arr) || is_object($arr)){
        foreach ($arr as $key=>$val){
            if (is_array($val)  || is_object($val)){
                $retStr .= '<tr><td>' . $key . '</td><td> ' . pp($val) . '</td></tr>';
            }else{
                $retStr .= '<tr><td>' . $key . '</td><td> ' . $val . '</td></tr>';
            }
        }
    }
    $retStr .= '</table>';
    return $retStr;
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

//set the property filter criteria
//$criteria['PropertyField'] = array(
//    'ID','WeeklyRent', 'SalePrice','Status'
//);
//set offset
$criteria['Offset'] = ((isset($_GET['offset']))?$_GET['offset']:0);
//set limit
$criteria['Limit'] = ((isset($_GET['limit']))?$_GET['limit']:200);
//for method 2
$criteria['NewHomes'] = '1';
//set the property filter criteria
$criteria['PropertyStatus'] = array(
    'for sale','under offer'
);
//add search parameters to call to call
$params = array('Criteria' => $criteria);
//----------------------------------------------------------------------------------------------------------------------
//Get all properties for our criteria
$results = $client->__soapCall('GetGeneralProperties', $params);
//Loop and get general property information
if($results && !empty($results)){
    header('Content-Type: application/force-download');
    header('Content-Disposition: attachment;filename="new_homes_reapit_comparison_with_romans_db_07_09_2017.xls"');
    header('Cache-Control: max-age=0');
    foreach($results as $key => $prop){
        //Try and get the property information
        $property = false;
        //try
        try{
            $gpc['ID'] = $prop->ID;
            $property = $client->__soapCall('GetGeneralProperty', $gpc);
        }catch(Exception $e){
            echo $e->getMessage();
            exit();
        }
        //test
        if($property){
            //find property in db
            $sql = 'SELECT * FROM property where provider_property_id = "'.str_replace('romrps_test-','',$prop->ID).'"';
            //get res
            $res = $conn->query($sql);
            //test for record
            if ($res->num_rows > 0) {
                $row = $res->fetch_array(MYSQLI_ASSOC);
                //Table
                $t1 = '<table cellpadding="1" cellspacing="1" border="1" width="100%">';
                //loop and print values
                foreach($row as $pkey=>$pval){
                    if(is_array($pval) || is_object($pval)){
                        $pval = pp($pval);
                    }
                    $t1 .= '<tr><td>'.$pkey.'</td><td>'.$pval.'</td></tr>';
                }
                //------------------------------------------------------------------------------------------------------
                //AccommodationSummary
                $rsql = 'SELECT 
                                pf.property_features_value
                            FROM
                                property__property_features as pf
                            WHERE 
                                pf.entity_id = '.$row['id'].';';
                $rRes = $conn->query($rsql);
                $rows = $rRes->fetch_all();
                $t1 .= '<tr><td>Features / AccommodationSummary</td><td>'.pp($rows).'</td></tr>';
                //------------------------------------------------------------------------------------------------------
                //Images
                $rsql = 'SELECT 
                                fm.filename,pi.image_title,pi.image_width,pi.image_height
                            FROM
                                property__image AS pi
                            LEFT JOIN file_managed AS fm ON pi.image_target_id = fm.fid
                            WHERE
                                pi.entity_id = '.$row['id'].';';
                $rRes = $conn->query($rsql);
                $rows = $rRes->fetch_all();
                $t1 .= '<tr><td>Image</td><td>'.pp($rows).'</td></tr>';
                //------------------------------------------------------------------------------------------------------
                //Rooms
                $rsql = 'SELECT room.name, room.description__value, room.status 
                            FROM property__rooms AS pr 
                            LEFT JOIN room ON pr.rooms_target_id = room.id
                            where pr.entity_id = '.$row['id'].';';
                $rRes = $conn->query($rsql);
                $rows = $rRes->fetch_all();
                $t1 .= '<tr><td>Room</td><td>'.pp($rows).'</td></tr>';
                //------------------------------------------------------------------------------------------------------
                //Get the images form the property
                $t1 .= '</table>';

            }
            //Table
            $t2 = '<table cellpadding="1" cellspacing="1" border="1" width="100%">';
            //loop and print values
            foreach($property as $pkey=>$pval){
                if(is_array($pval) || is_object($pval)){
                    $pval = pp($pval);
                }
                $t2 .= '<tr><td>'.$pkey.'</td><td>'.$pval.'</td></tr>';
            }
            $t2 .= '</table>';
            //Holder table
            echo '<table cellpadding="1" cellspacing="1" border="1" width="100%">';
                echo '<tr><th colspan="2" valign="top">New Home ReapIt ID: '.$prop->ID.'</th></tr>';
                echo '<tr><th valign="top">Our DB</th><th valign="top">From Reapit</th></tr>';
                echo '<tr><td valign="top">'.$t1.'</td><td valign="top">'.$t2.'</td></tr>';
            echo '</table>';
        }
    }
}else{
    echo '<h3>No New Homes Found</h3>';
}