<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);
//Bring in custom DB wrapper
require __DIR__.'/db_wrapper.php';
//Bring in Composer Libraries
require __DIR__ . '/vendor/autoload.php';
//---------------------------------------------------------------------------------
//Set DB wrapper
$db = new db_wrapper();
//Get Cords by Post Code
$sql = 'SELECT AsText(geom) AS CORDS FROM amd_postcode_area_boundaries WHERE `PostArea`="'.((isset($_GET['postcode']))?$_GET['postcode']:'GU').'"';
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
        //output as json
        header('Content-type: application/json');
        print json_encode($holder);
    }
}
$db->conn->close();

