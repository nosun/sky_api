<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Redis_Model  extends CI_Model {

    private $redis;
    static  $prefix = "yun_";
    static  $fd ='f_';
    static  $mac ='m_';
    static  $online ='o_'; //online status
    static  $dv_data ='d_';  //device data

    public function __construct($host = '127.0.0.1', $port = 6379, $timeout = 0.0){
        $redis = new \redis;
        $redis->connect($host, $port, $timeout);
        $this->redis = $redis;
    }

    public function checkKey($key){
        return $this->redis->exists($key);
    }

    public function getDevice($key,$field){
        return $this->redis->hGetAll(self::$prefix.self::$dv_data.$key);
    }

    public function setDevice($key,$value){
        $this->redis->hMset(self::$prefix.self::$dv_data.$key,$value);
    }

    public function setOnline($key,$value){
        $this->redis->set(self::$prefix.self::$online.$key,$value);
    }

    public function getOnline($key){
        return $this->redis->get(self::$prefix.self::$online.$key);
    }

    public function setMac($key,$value){
        $this->redis->set(self::$prefix.self::$mac.$key,$value);
    }

    public function getMac($key){
        return $this->redis->get(self::$prefix.self::$mac.$key);
    }

    public function delMac($key){
        return $this->redis->del(self::$prefix.self::$mac.$key);
    }

    public function setFd($key,$value){
        $this->redis->set(self::$prefix.self::$fd.$key,$value);
    }

    public function getFd($key){
        return $this->redis->get(self::$prefix.self::$fd.$key);
    }

    public function delFd($key){
        return $this->redis->del(self::$prefix.self::$fd.$key);
    }

}