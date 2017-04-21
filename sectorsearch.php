<?php
//error reporting
error_reporting(E_ALL);
ini_set("display_errors", 1);
//Bring in custom DB wrapper
require __DIR__.'/db_wrapper.php';
//Bring in Composer Libraries
require __DIR__ . '/vendor/autoload.php';
//---------------------------------------------------------------------------------
//Set DB wrapper
$db = new db_wrapper();
//Test
if(isset($_GET['search']) && !empty($_GET['search'])){
    //Collect
    $collect = array();
    //------------------------------------------------------------------------------------------------------------------
    //Get Cords by Post Code
    $sql = 'SELECT PostDist FROM amd_postcode_disrict_boundaries WHERE `PostDist` LIKE "'.$_GET['search'].'%" OR `Structurred_Dist` LIKE "'.$_GET['search'].'%" ORDER BY `PostDist` ASC';
    //get res
    $result = $db->conn->query($sql);
    //test rest
    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            //add to collection
            $collect[] = $row['PostDist'];
        }
    }
    //------------------------------------------------------------------------------------------------------------------
    //Get Cords by Town Name
    $sql = 'SELECT town_name FROM seamless_town WHERE `town_name` LIKE "'.$_GET['search'].'%" OR `u_townname` LIKE "'.$_GET['search'].'%" ORDER BY `town_name` ASC';
    //get res
    $result = $db->conn->query($sql);
    //test rest
    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            //add to collection
            $collect[] = $row['town_name'];
        }
    }
    //------------------------------------------------------------------------------------------------------------------
    //Output Json
    //arsort($collect);
    header('Content-type: application/json');
    print json_encode($collect);

    $db->conn->close();
}