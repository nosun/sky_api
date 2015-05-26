<?php defined('BASEPATH') or exit('No direct script access allowed');

class SClient {

    public $client;
    public function __construct(){
        $this->client = new swoole_client(SWOOLE_TCP);;
    }

    public function connect(){
        if($this->client->connect('127.0.0.1', 9999))
        {
            return 1;
        }
            return 0;
    }


    public function send($msg){
        $result = $this->client->send($msg);
        return $result;
    }

    public function close(){
        $this->client->close();
    }

}