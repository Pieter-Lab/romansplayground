<?php
/**
 * Created by PhpStorm.
 * User: pieter
 * Date: 4/21/17
 * Time: 10:22 AM
 */

class db_wrapper{

    private $servername = "localhost";
    private $username = "root";
    private $password = "peter123";
    private $dbname = "locations";
    public $conn = false;

    function __construct()
    {
        // Create connection
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        // Check connection
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }
    /**
     * Prints out variables
     */
    public function printer($val){
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

    /**
     * http://stackoverflow.com/questions/10053358/measuring-the-distance-between-two-coordinates-in-php
     * Calculates the great-circle distance between two points, with
     * the Vincenty formula.
     * @param float $latitudeFrom Latitude of start point in [deg decimal]
     * @param float $longitudeFrom Longitude of start point in [deg decimal]
     * @param float $latitudeTo Latitude of target point in [deg decimal]
     * @param float $longitudeTo Longitude of target point in [deg decimal]
     * @param float $earthRadius Mean earth radius in [m]
     * @return float Distance between points in [m] (same as earthRadius)
     */
    public function vincentyGreatCircleDistance(
        $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
    {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }

    public function polyArea($polygon){

        $polygon = current($polygon);

        $NumPoints = count($polygon);

        if($polygon[$NumPoints-1] == $polygon[0]){
            $NumPoints--;
        }else{
            //Add the first point at the end of the array.
            $polygon[$NumPoints] = $polygon[0];
        }

        if ($NumPoints < 3) {
            return 0;
        } else {
            $area = 0;
            $lastPoint = $polygon[$NumPoints - 1];
            foreach ($polygon as $point) {
                $area += ($lastPoint[0] * $point[1] - $lastPoint[1] * $point[0]);
                $lastPoint = $point;
            }
            return ($area / 2.0);
        }
    }

    function getDistance($point1_lat, $point1_long, $point2_lat, $point2_long, $unit = 'km', $decimals = 2) {
        // Calculate the distance in degrees
        $degrees = rad2deg(acos((sin(deg2rad($point1_lat))*sin(deg2rad($point2_lat))) + (cos(deg2rad($point1_lat))*cos(deg2rad($point2_lat))*cos(deg2rad($point1_long-$point2_long)))));

        // Convert the distance in degrees to the chosen unit (kilometres, miles or nautical miles)
        switch($unit) {
            case 'km':
                $distance = $degrees * 111.13384; // 1 degree = 111.13384 km, based on the average diameter of the Earth (12,735 km)
                break;
            case 'mi':
                $distance = $degrees * 69.05482; // 1 degree = 69.05482 miles, based on the average diameter of the Earth (7,913.1 miles)
                break;
            case 'nmi':
                $distance =  $degrees * 59.97662; // 1 degree = 59.97662 nautic miles, based on the average diameter of the Earth (6,876.3 nautical miles)
        }
        return round($distance, $decimals);
    }
}