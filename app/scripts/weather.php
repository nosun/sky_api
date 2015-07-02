<?php

/*
 * 采集weather.com.cn实况天气脚本
 * author:  nosun@nosun.cn
 * date:    2014-11-21 16:48:19
 *
 */

include_once('config.php');
include_once('function.php');

$config['private_key'] = "4f1175_SmartWeatherAPI_c0658a1";
$config['appid'] = "0bd4a721194e81d3";
$config['url'] = "http://open.weather.com.cn/data/";

$config['tb_weather_log'] = 'api_weather_log';
$config['tb_weather_area'] = 'api_weather_area';
$config['log_path']='/data/log/weather_cj_1.log';
$config['fail_path']='/data/log/weather_cj_fail.log';
$config['mysql_fail_path']='/data/log/weather_mysql_fail.log';
$config['step'] = 2600;
$config['safe'] = 10;
$config['page'] = 1;



caiji();
//
//$fail =file_get_contents('D:/'.$config['fail_path']);
//caiji($fail,1);

function caiji($bu = null,$cishu = 0){
    global $db,$config;

    $mysqli = new mysqli($db['hostname'], $db['username'],$db['password'], $db['database']);
    $mysqli->set_charset($db['char_set']);

    if (mysqli_connect_error()) {
        die('Connect Error (' . mysqli_connect_errno() . ')'
            . mysqli_connect_error());
    }
    $page = $config['page'];
    $start = 0;
    $k = 0;
    while ($k < $page) {

        if($cishu == 0){
            $sql = 'select weather_api_key,weather_api_name from '.$config['tb_weather_area'].' limit ' . $start . ',' . $config['step'];
        }else{
            if(!empty($bu)){
                $sql = 'select weather_api_key,weather_api_name from '.$config['tb_weather_area'].' where weather_api_key in ('.$bu.')';
            }else{
                exit;
            }
        }
        //var_dump($sql);
        $result = $mysqli->query($sql);
        $insert = '';
        $sign = '';
        $fail = '';
        while ($area = $result->fetch_array(MYSQLI_ASSOC)) {
            $date = date('YmdHi', time());
            $public_key = ''.$config['url'].'?areaid='.$area['weather_api_key'].'&type=observe&date='.$date.'&appid='.$config['appid'].'';
            $key = urlencode(base64_encode(hash_hmac('sha1',$public_key,$config['private_key'],TRUE)));
            $url = ''.$config['url'].'?areaid='.$area['weather_api_key'].'&type=observe&date='.$date.'&appid=0bd4a7&key='.$key.'';

            $json = json_decode(get_file($url),true);


            if (!empty($json)) {
                $json  = array_values($json);
                $temperature = array_values($json[0])[0];
                $wind_direct = array_values($json[0])[2];
                $wind_power = array_values($json[0])[3];
                $humidity = array_values($json[0])[1];
                $time = array_values($json[0])[4];
                $settime = time();
                $area_name = $area['weather_api_name'];
                $area_id = (int)$area['weather_api_key'];
                if($time >= '23:00' && $time <= '23:59' && date('H:i', time()) >='00:00' && date('H:i', time()) <='01:00'){
                    $updatetime = strtotime(date('Y-m-d', time()-86400) . ' ' . $time) ? strtotime(date('Y-m-d', time()-86400) . ' ' . $time) : time();
                }else{
                    $updatetime = strtotime(date('Y-m-d', time()) . ' ' . $time) ? strtotime(date('Y-m-d', time()) . ' ' . $time) : time();
                }
                $insert .= '("' . $area_name . '",' . $area_id . ',"' . $temperature . '","' . $wind_direct . '","' . $wind_power . '","' . $humidity . '","' . $updatetime . '","'.$settime.'"),';
                $sign .= '+';
            } else {
                $sign .= '-';
                $fail .= $area['weather_api_key'].',';
            }
        }

        $insert{strlen($insert) - 1} = ';';
        $sql = 'insert into '.$config['tb_weather_log'].' (area_name,area_id,temperature,wind_direct,wind_power,humidity,updatetime,settime)
      values ' . $insert;

        $r = $mysqli->query($sql);
        if ($r) {
            $log = $sign . ' ' . date('Y-m-d h:i:s', time()) . ' ' . ($k) . "page finished\n";
            file_force_contents($config['log_path'], $log);
        }else {
            $log = date('Y-m-d h:i:s', time()) . ' '.$sql;
            file_force_contents($config['mysql_fail_path'], $log);
        }

        if ($fail){
            if(empty($cishu)){
                $cishu = 0;
            }
            if($cishu > 3){
                $fail = substr($fail,0,-1);
                file_force_contents($config['fail_path'], $fail,FILE_USE_INCLUDE_PATH);
                exit;
            }else{
                $cishu++;
                //echo "第 $cishu 次";
                $fail = substr($fail,0,-1);
                file_force_contents($config['fail_path'], $fail,FILE_USE_INCLUDE_PATH);
                sleep($config['safe']);
                caiji($fail,$cishu);
            }
        }
        $k++;
        $start = $start + $config['step'] * $k;
        sleep($config['safe']);

    }

    file_force_contents($config['log_path'], "\n");
    mysqli_close($mysqli);
}