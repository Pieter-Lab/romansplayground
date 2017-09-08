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
//--------------------------------------------------------------------------------------------------------------
//Make db connection
$servername = "localhost";
$username = "root";
$password = "peter123";
// Create connection
$conn = new mysqli($servername, $username, $password,'romans_custom');
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//Carry on ---------------------------------------------------------------------
//Load the xml
$success = array();
$fail = array();
$row = 1;
if (($handle = fopen("output.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        //isolate column headers
        if($data[0]!=='location_id' && count($data)>1){
            $sql = "INSERT INTO `romans_custom_seamless_town` (`town_name`,`long_wgs`,`lat_wgs`,`geom`) VALUES ('".$data[2]."','".$data[6]."','".$data[5]."',St_GeomFromWKB(X'".$data[8]."', 4326));";
            $res = $conn->query($sql);
            if($res){
                $success[$data[0]] = $sql;
            }else{
                $fail[$data[0]] = $sql;
            }
        }
    }
    fclose($handle);
}
echo '<h3>Success</h3>';
printer($success);
var_export($success);
echo '<hr>';
echo '<h3>Fail</h3>';
printer($fail);
echo '<hr>';