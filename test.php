<?php

//$str = "skyware4"."skyware2007";
//echo md5($str);


//$key = 'a-z 0-9~%.:_\-|=+';
//$str = '%E8%BF%99%E4%B8%8D%E6%98%AF%E4%BD%A0%E5%AE%B6WiFi10211006';
//$str1 =  preg_quote($key, '-');
//$str2 = str_replace(array('\\-', '\-'), '-',$str1);
//
//if ( ! preg_match("|^[".$str2."]+$|i", $str))
//{
//    echo 1;
//}else
//{echo 2;}


$t = array();
$push = array("xx:xx","bb::vb");
if(true){
    $t['t1']=time();
    array_push($push,'t2::'.$t['t1']);
}

if(true){
    $t['t2']=time();
    array_push($push,'t2::'.$t['t2']);
}

if(true){
    $t['t3']=time();
    array_push($push,'t2::'.$t['t3']);
}

var_dump($t);
var_dump(json_encode($push));


