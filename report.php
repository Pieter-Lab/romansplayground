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
//------------------------------------------------------------------------------------------------------------------
    //get applicant Viewings
//    $vc['id'] = $_GET['appid'];
//    $viewings = $client->__soapCall('GetApplicantViewings', $vc);
//        echo '<table border="1" width="100%"><tr><th colspan="2">Viewings</th></tr>';
//        foreach($viewings as $vkey => $viewing){
//            foreach ($viewing as $key => $val){
//                if(is_array($val) || is_object($val)){
//                    $collect = '';
//                    foreach ($val as $k=>$v){
//                        if(is_array($v) || is_object($v)){
//                            ob_start();
//                            printer($v);
//                            $out = ob_get_contents();
//                            ob_end_clean();
//                            $v = $out;
//                        }
//                        $collect.= $k.':'.$v.'<br />';
//                    }
//                    $val = $collect;
//                }
//                echo '<tr><td>'.$key.'</td><td>'.$val.'</td></tr>';
//            }
//            echo '<tr><td colspan="2">&nbsp;</td></tr>';
//        }
//        echo '</table>';
//------------------------------------------------------------------------------------------------------------------
//get applicant offers
//        $vc['id'] = $_GET['appid'];
//        $viewings = $client->__soapCall('GetApplicantOffers', $vc);
//        echo '<table border="1" width="100%"><tr><th colspan="2">Offers</th></tr>';
//        foreach($viewings as $vkey => $viewing){
//            foreach ($viewing as $key => $val){
//                if(is_array($val) || is_object($val)){
//                    $collect = '';
//                    foreach ($val as $k=>$v){
//                        if(is_array($v) || is_object($v)){
//                            ob_start();
//                            printer($v);
//                            $out = ob_get_contents();
//                            ob_end_clean();
//                            $v = $out;
//                        }
//                        $collect.= $k.':'.$v.'<br />';
//                    }
//                    $val = $collect;
//                }
//                echo '<tr><td>'.$key.'</td><td>'.$val.'</td></tr>';
//            }
//            echo '<tr><td colspan="2">&nbsp;</td></tr>';
//        }
//        echo '</table>';
//------------------------------------------------------------------------------------------------------------------
$prop_id = ((isset($_GET['propid']))?$_GET['propid']:false);
if($prop_id && !empty($prop_id)){
    //------------------------------------------------------------------------------------------------------------------
    //Get the vendor
//        $vc['id'] = $prop_id;
//        $vendor = $client->__soapCall('GetVendor', $vc);
//        echo '<table border="1" width="100%"><tr><th colspan="2">Vendor</th></tr>';
//        foreach($vendor as $key => $val){
//            echo '<tr><td>'.$key.'</td><td>'.$val.'</td></tr>';
//        }
//        echo '</table>';
    //------------------------------------------------------------------------------------------------------------------
    //Get the property Viewings
//        $viewings = $client->__soapCall('GetViewings', $vc);
//        echo '<table border="1" width="100%"><tr><th colspan="2">Viewings</th></tr>';
//        foreach($viewings as $vkey => $viewing){
//            foreach ($viewing as $key => $val){
//                if(is_array($val) || is_object($val)){
//                    $collect = '';
//                    foreach ($val as $k=>$v){
//                        if(is_array($v) || is_object($v)){
//                            ob_start();
//                            printer($v);
//                            $out = ob_get_contents();
//                            ob_end_clean();
//                            $v = $out;
//                        }
//                        $collect.= $k.':'.$v.'<br />';
//                    }
//                    $val = $collect;
//                }
//                echo '<tr><td>'.$key.'</td><td>'.$val.'</td></tr>';
//            }
//            echo '<tr><td colspan="2">&nbsp;</td></tr>';
//        }
//        echo '</table>';
    //------------------------------------------------------------------------------------------------------------------
    //Get the Accepted Offer
//        $oc['id'] = $prop_id;
//        $offers = $client->__soapCall('GetOffers', $oc);
//        echo '<table border="1" width="100%"><tr><th colspan="2">Accepted Offer</th></tr>';
//        foreach($offers as $okey => $offer){
////            if($offer->Status==="Offer Accepted" || $offer->Status==="Offer Pending"){
//                foreach($offer as $key => $val){
//                    if(is_array($val) || is_object($val)){
//                        $collect = '';
//                        foreach ($val as $k=>$v){
//                            if(is_array($v) || is_object($v)){
//                                ob_start();
//                                printer($v);
//                                $out = ob_get_contents();
//                                ob_end_clean();
//                                $v = $out;
//                            }
//                            $collect.= $k.':'.$v.'<br />';
//                        }
//                        $val = $collect;
//                    }
//                    echo '<tr><td>'.$key.'</td><td>'.$val.'</td></tr>';
//                }
//            echo '<tr><td colspan="2">&nbsp;</td></tr>';
////            }
//        }
//        echo '</table>';
    //------------------------------------------------------------------------------------------------------------------
    //Get the property documents
//        $docs = false;
//        try{
//            $dc['id'] = $prop_id;
//            $docs = $client->__soapCall('GetVendorDocuments', $dc);
//        }catch(Exception $e){}
//        if($docs){
//            echo '<table border="1" width="100%"><tr><th colspan="2">Documents</th></tr>';
//            foreach($docs as $key => $val){
//                foreach($val as $i=>$f){
//                    echo '<tr><td>'.$i.'</td><td>'.$f.'</td></tr>';
//                }
//                echo '<tr><td colspan="2">&nbsp;</td></tr>';
//            }
//            echo '</table>';
//        }
    //------------------------------------------------------------------------------------------------------------------
    //Get the property data
    $pc['id'] = $prop_id;
    $pc['GetUnavailable'] = true;
    $property = $client->__soapCall('GetCommercialProperty', $pc);
    echo '<table border="1" width="100%"><tr><th colspan="2">Property Information</th></tr>';
    foreach($property as $key => $val){
        if(is_array($val) || is_object($val)){
            $collect = '';
            foreach ($val as $k=>$v){
                if(is_array($v) || is_object($v)){
                    $c = '';
                    foreach($v as $i=>$n){
                        if(is_array($n) || is_object($n)){
                            $d = '';
                            foreach ($n as $a=>$b){
                                if(is_array($b) || is_object($b)){
                                    $e = '';
                                    foreach ($b as $z=>$f){
                                        $e.= $z.':'.$f.'<br />';
                                    }
//                                        ob_start();
//                                        printer($b);
//                                        $out = ob_get_contents();
//                                        ob_end_clean();
                                    $d .= $e;
                                }else{
                                    $d.= $a.':'.$b.'<br />';
                                }
                            }
                            $c .= $d;
                        }else{
                            $c.= $i.':'.$n.'<br />';
                        }
                    }
                    $collect.= $c.'<br />';
                }else{
                    $collect.= $k.':'.$v.'<br />';
                }
            }
            $val = $collect;
        }
        echo '<tr><td>'.$key.'</td><td>'.$val.'</td></tr>';
    }
    echo '</table>';
//        $pc['id'] = $prop_id;
//        $property = $client->__soapCall('GetGeneralProperty', $pc);
//        echo '<table border="1" width="100%"><tr><th colspan="2">Property Information</th></tr>';
//        foreach($property as $key => $val){
//            if(is_array($val) || is_object($val)){
//                $collect = '';
//                foreach ($val as $k=>$v){
//                    if(is_array($v) || is_object($v)){
//                        $c = '';
//                        foreach($v as $i=>$n){
//                            if(is_array($n) || is_object($n)){
//                                $d = '';
//                                foreach ($n as $a=>$b){
//                                    if(is_array($b) || is_object($b)){
//                                        $e = '';
//                                        foreach ($b as $z=>$f){
//                                            $e.= $z.':'.$f.'<br />';
//                                        }
////                                        ob_start();
////                                        printer($b);
////                                        $out = ob_get_contents();
////                                        ob_end_clean();
//                                        $d .= $e;
//                                    }else{
//                                        $d.= $a.':'.$b.'<br />';
//                                    }
//                                }
//                                $c .= $d;
//                            }else{
//                                $c.= $i.':'.$n.'<br />';
//                            }
//                        }
//                        $collect.= $c.'<br />';
//                    }else{
//                        $collect.= $k.':'.$v.'<br />';
//                    }
//                }
//                $val = $collect;
//            }
//            echo '<tr><td>'.$key.'</td><td>'.$val.'</td></tr>';
//        }
//        echo '</table>';
}else{
    echo '<h3>Please supply a REAPIT property ID</h3>';
}