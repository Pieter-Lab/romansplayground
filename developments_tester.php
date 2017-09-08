<?php
//Error reporting
error_reporting(E_ALL);
ini_set("display_errors", 1);
//Set the pdf headers
header("Content-type:application/pdf");
header("Content-Disposition:attachment;filename=developments_from_new_homes_criteria_used_by_romans.pdf");
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
                $retStr .= '<tr><td width="10%">' . $key . '</td><td> ' . pp($val) . '</td></tr>';
            }else{
                $retStr .= '<tr><td width="10%">' . $key . '</td><td> ' . $val . '</td></tr>';
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
//Styling ----------------------------------------------------------------------
?>
<style>
    table {
        font-size: 9px;
        font-family: Verdana, Arial;
    }
    table th {
        background-color: #00b3ee;
        padding: 5px;
    }
    table h3 {
        background-color: #1b809e;
        padding: 5px;
    }
    hr {
        height: 15px;
    }
</style>
<?php
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
    //Show which Filter and methods were used
    echo '<table width="100%">';
    echo '<tr><th>ReapIt API Method</th><th>Search Criteria</th></tr>';
    echo '<tr><th>GetGeneralProperties</th><th>'.pp($criteria).'</th></tr>';
    echo '</table>';
    //Set Primary Developments Holder
    $developments = array();
    $unavailable = array();
    //loop through res
    foreach($results as $ReapProp){
        //Try and get the property information
        $property = false;
        //try
        try{
            $gpc['ID'] = $ReapProp->ID;
            $property = $client->__soapCall('GetGeneralProperty', $gpc);
        }catch(Exception $e){
            $unavailable[] = array(
                'propID'=>$ReapProp->ID,
                'msg'=>$e->getMessage(),
                'type'=>'development'
            );
        }
        //test
        if($property){
            //add to developments holder
            if(isset($property->PrimaryDevelopment)){
                $developments[$property->PrimaryDevelopment->ID][] = $property->ID;
            }
        }
    }
    //print out the developments
    foreach($developments as $mDevId => $subplots){
        //Get the Master development
        $masterDevelopment = false;
        try{
            $md['id'] = $mDevId;
            $masterDevelopment = $client->__soapCall('GetGeneralProperty', $md);
        }catch(Exception $e){
            $msg = $e->getMessage();
            if(strstr($msg,'\'GetUnavailable\' must be set to retrieve unavalilable')){
                try{
                    $md['id'] = $mDevId;
                    $md['GetUnavailable'] = true;
                    $masterDevelopment = $client->__soapCall('GetGeneralProperty', $md);
                }catch(Exception $a){
                    $unavailable[] = array(
                        'propID'=>$mDevId,
                        'msg'=>$a->getMessage(),
                        'type'=>'development'
                    );
                }
            }else{
                $unavailable[] = array(
                    'propID'=>$mDevId,
                    'msg'=>$e->getMessage(),
                    'type'=>'development'
                );
            }
        }
        if($masterDevelopment){
            //display Master Development
            echo '<table width="100%">
                    <tr><th colspan="2">Master Development</th></tr>';
                echo '<tr>
                        <td valign="top" width="400">';
                        if(!empty($masterDevelopment->Image)){
                            echo '<h3>Development Images</h3>';
                            foreach($masterDevelopment->Image as $image){
                                echo '<img src="'.$image->Filepath.'" title="'.$image->Caption.'" width="200" style="display: inline;" />';
                            }
                        }else{
                            echo '<h3>No Development Images</h3>';
                        }
                        if(!empty($masterDevelopment->Room)){
                            echo '<h3>Development Room Images</h3>';
                            foreach($masterDevelopment->Room as $rKey=>$room){
                                if(!empty($room->Image)){
                                    foreach($room->Image as $image){
                                        echo '<img src="'.$image->Filepath.'" title="'.$image->Caption.'" width="200" style="display: inline;" />';
                                    }
                                }
                            }
                        }else{
                            echo '<h3>No Development Room Images</h3>';
                        }
                echo   '</td>
                        <td valign="top">';
                        echo '<table width="100%">
                                <tr><th colspan="2">Development Information</th></tr>';
                                foreach($masterDevelopment as $key=>$val){
                                    if($key!=="Image" && $key!=="Room"){
                                        if(is_array($val) || is_object($val)){
                                            $val = pp($val);
                                        }
                                        echo '<tr><td>'.$key.'</td><td>'.$val.'</td></tr>';
                                    }
                                }
                        echo '</table>';
                echo '  </td>
                    </tr>';
                echo '<tr><td colspan="2" valign="top"><h3>Sub Plots</h3>';
                    foreach($subplots as $subplotID){
                        //get the subplot
                        $plot = false;
                        try{
                            $sp['id'] = $subplotID;
                            $plot = $client->__soapCall('GetGeneralProperty', $sp);
                        }catch(Exception $e){
                            $unavailable[] = array(
                                'propID'=>$subplotID,
                                'msg'=>$e->getMessage(),
                                'type'=>'Subplot'
                            );
                        }
                        if($plot){
                            echo '<table width="100%">';
                            echo '<tr>
                                    <td valign="top">
                                        <table width="100%"><tr><th colspan="2">Sub Plot Information</th></tr>';
                                            foreach($plot as $key=>$val){
                                                if($key!=="Image" && $key!=="Room"){
                                                    if(is_array($val) || is_object($val)){
                                                        $val = pp($val);
                                                    }
                                                    echo '<tr><td>'.$key.'</td><td>'.$val.'</td></tr>';
                                                }
                                            }
                                    echo '</table>
                                    </td>
                                    <td valign="top" width="600">';
                                        if(!empty($plot->Image)){
                                            echo '<h3>Property Images</h3>';
                                            foreach($plot->Image as $image){
                                                echo '<img src="'.$image->Filepath.'" title="'.$image->Caption.'" width="200" style="display: inline;" />';
                                            }
                                        }else{
                                            echo '<h3>No Property Images</h3>';
                                        }
                                        if(!empty($plot->Room)){
                                            echo '<h3>Room Images</h3>';
                                            foreach($plot->Room as $rKey=>$room){
                                                if(!empty($room->Image)){
                                                    foreach($room->Image as $image){
                                                        echo '<img src="'.$image->Filepath.'" title="'.$image->Caption.'" width="200" style="display: inline;" />';
                                                    }
                                                }
                                            }
                                        }else{
                                            echo '<h3>No Property Room Images</h3>';
                                        }
                            echo   '</td>
                                </tr>';
                            echo '</table>';
                        }
                    }
                echo '</td></tr>';
            echo '</table>';
            echo '<hr />';
        }
    }
    if(!empty($unavailable)){
        echo '<h3><Properties Marked as Unavailable by the ReapIt API</h3>';
        echo '<table width="100%" cellpadding="1" cellspacing="1">';
        echo '<tr><th colspan="2">Properties Marked as Unavailable by the ReapIt API</th></tr>';
        echo '<tr><th>Property ID</th><th>ReapIt API Response</th></tr>';
        foreach($unavailable as $item){
            echo '<tr><td>'.$item['propID'].'</td><td>'.$item['msg'].'</td></tr>';
        }
        echo '</table>';
    }
}else{
    echo '<h3>No Properties found</h3>';
}