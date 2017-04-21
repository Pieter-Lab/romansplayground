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
$sql = 'SELECT AsText(geom) AS CORDS FROM amd_postcode_area_boundaries WHERE `PostArea`="GU"';
//get res
$result = $db->conn->query($sql);
//test rest
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {


        $polygon = geoPHP::load($row['CORDS'],'wkt');

//        echo '<pre>';
//        print_r($polygon->out('google_geocode'));
//        echo '</pre>';
//        echo '<pre>';
//        print_r($polygon->out('json'));
//        echo '</pre>';
        echo '<pre>';
        print_r($polygon->out('kml'));
        echo '</pre>';
//        echo '<pre>';
//        print_r($polygon->getBBox());
//        echo '</pre>';
//        echo '<pre>';
//        print_r($polygon->envelope());
//        echo '</pre>';
//        echo '<pre>';
//        print_r($polygon->asArray());
//        echo '</pre>';
//        exit();

//        $area = $polygon->getArea();
//        $centroid = $polygon->getCentroid();
//        $centX = $centroid->getX();
//        $centY = $centroid->getY();
//
//        print "This polygon has an area of ".$area." and a centroid with X=".$centX." and Y=".$centY;

    }
} else {
    //echo "0 results";
}
//$db->conn->close();

