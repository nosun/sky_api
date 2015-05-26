<?php
/**
 * Created by PhpStorm.
 * User: nosun
 * Date: 14-11-24
 * Time: 下午4:24
 */

function get_file($url)
{
    $ch = curl_init();
    $ip = "209.58." . rand(1, 255) . "." . rand(1, 255);
    $headers = array("X-FORWARDED-FOR:$ip");
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_REFERER, "http://baidu.com/ ");   //构造来路
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function file_force_contents($dir, $contents,$mode = FILE_APPEND )
{
    $parts = explode('/', $dir);
    $file = array_pop($parts);
    $dir = '';
    foreach ($parts as $part)
        if (!is_dir($dir .= "/$part")) mkdir($dir);
    file_put_contents("$dir/$file", $contents,$mode);
}
