<?php

$str = "这不是你家WiFi"."10211006";
echo md5($str);


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
