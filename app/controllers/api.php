<?php defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH.'/libraries/REST_Controller.php';

class Api extends REST_Controller
{
    private $key;
    private $token;
	function __construct()
    {
        parent::__construct();
        $this->load->helper('encrypt');
        $this->key=$this->config->item('aes_key');
        if($this->post('token')){
            $this->token=$this->post('token');
        }elseif($this->put('token')){
            $this->token=$this->put('token');
        }elseif($this->delete('token')){
            $this->token=$this->delete('token');
        }else{
            $this->token='';
        }
    }

    //检查用户是否存在，true 表示存在
    function login_id_get()
    {
        $this->load->model('user_model');
        $login_id=$this->uri->segment('3');
        if(empty($login_id)){
            if(empty($login_id) || empty($login_pwd)){
                $this->response(array('message'=>400),400);
            }
        }
        $user=array(
            'login_id'=>$login_id
        );
        $result=$this->user_model->getUser($user);
        if($result) {
            $this->response(array('message' => 200), 200);
        }else{
            $this->response(array('message' => 404), 200);
        }
    }

    //用户注册
    function user_post()
    {
        $this->load->model('user_model');
        $user['login_id'] = $this->post('login_id');
        $user['login_pwd'] = $this->post('login_pwd');
        $user['app_id'] = $this->post('app_id');
        $user['reg_time'] = time();
        $user['user_type'] = 1;
        $user['user_phone'] = $user['login_id'];

        if(empty($user['login_id']) || empty($user['login_pwd'])){
            $this->response(array('message'=>400),400);
        }

        $result=$this->user_model->addUser($user);

        if($result) {
            $message = array('login_id' => $this->post('login_id'), 'message' => 200);
            $this->response($message, 200);
        }else{
            $message = array('login_id' => $this->post('login_id'), 'message' => 500);
            $this->response($message, 200);
        }
    }

    //获取用户信息 需要登录才能获取用户信息，否则返回403
    function user_get()
    {
        $token   =$this->uri->segment('3');
        $user_id =decrypt($token,$this->key);
        $this->load->model('user_model');
        $user=array(
            'user_id'=>$user_id,
        );
        $result=$this->user_model->getUser($user);
        if($result) {
            $this->response(array('result' => $result, 'message' => 200), 200);
        }else{
            $this->response(array('message' => 404), 200);
        }
    }

    //修改用户信息
    function user_put()
    {
        $token   = $this->uri->segment('3');
        $user_id = decrypt($token,$this->key);
        $user=array();
        if ($this->put('user_name')) $user['user_name']=$this->put('user_name');
        if ($this->put('user_phone')) $user['user_phone']=$this->put('user_phone');
        if ($this->put('user_email')) $user['user_email']=$this->put('user_email');
        if ($this->put('user_img')) $user['user_img']=$this->put('user_img');
        if ($this->put('user_prefer')) $user['user_prefer']=$this->put('user_prefer');

        if (empty($user_id) or empty($user)){
            $this->response(array('message'=>400),400);
        }

        $this->load->model('user_model');
        $result=$this->user_model->updateUser($user,$user_id);

        if($result) {
            $this->response(array('message' => 200), 200);
        }else{
            $this->response(array('message' => 200), 200);
        }
    }

    //修改密码
    function  passwd_put(){
        $user = $this->getUserByToken($this->token);
        $passwd     = $this->put('login_pwd');
        $passwd_old = $this->put('login_pwd_old');
        $login_id   = $user['login_id'];


        if(empty($login_id) or empty($passwd) or empty($passwd_old)){
            $this->response(array('message'=>400),400);
        }

        $user=array(
            'login_id' => $login_id,
            'login_pwd'=> $passwd_old
        );

        $this->load->model('user_model');
        $res = $this->user_model->getToken($user);
        if($res){
            $user=array(
                'login_id' => $login_id,
                'login_pwd'=> $passwd
            );
            $result=$this->user_model->updatePasswd($user);
            if($result){
                $this->response(array('message' => 200), 200);
            }else{
                $this->response(array('message' => 500), 200);
            }
        }else{
            $this->response(array('message' => 404), 200);
        }
    }

    //找回密码
    function  passwd_post(){

        $login_pwd  = $this->post('login_pwd');
        $login_id   = $this->post('login_id');

        if(empty($login_id) or empty($login_pwd)){
            $this->response(array('message'=>400),400);
        }

        $user=array(
            'login_id'  => $login_id,
            'login_pwd' => $login_pwd
        );

        $this->load->model('user_model');
        $result=$this->user_model->updatePasswd($user);

        if($result){
            $this->response(array('message' => 200), 200);
        }else{
            $this->response(array('message' => 500), 200);
        }
    }

    //登录操作,返回user_id
    function token_post(){
        $this->load->model('user_model');
        $login_id   = $this->post('login_id');
        $login_pwd  = $this->post('login_pwd');
        //$login_type = $this->post('login_type');

        if(empty($login_id) or empty($login_pwd)){
            $this->response(array('message'=>400),200);
        }

        $user=array(
            'login_id'=>$login_id
        );

        $res=$this->user_model->getUser($user);

        if($res){
            $result= $this->user_model->getToken($this->post());
            if($result) {
                $this->response(array('token'=>$result,'message' => 200), 200);
            }else{
                $this->response(array('message' => 500), 200);
            }
        }else{
            $this->response(array('message' => 404), 200);
        }
    }

    //上传用户头像
    function file_post(){
        $user = $this->getUserByToken($this->token);
        if(empty($user)){
            $this->response(array('message'=>400),200);
        }
        $config['allowed_types'] = 'gif|jpg|jpeg|png|jpe';
        $config['max_size'] = '512';
        $config['max_width'] = '2048';
        $config['max_height'] = '1500';
        $config['encrypt_name'] = TRUE;
        $config['remove_spaces'] = TRUE;

        $dir = 'uploads/'.date('Ym',time());
        $path = $_SERVER['DOCUMENT_ROOT'].'/'.$dir;
        if ( !is_dir($path)) //if the path not exist,create it.
        {
            if (!mkdir($path,0777,true)) {
                $this->response(array('message' => 501), 200);
            }
        }

        $config['upload_path'] = $path;
        $this->load->library('upload',$config);
        if ( ! $this->upload->do_upload('file'))
        {
            $error = array('error' => $this->upload->display_errors());
            $this->response($error, 200);
        }else{
            $data = $this->upload->data();
            $file =array(
                "file_name"=> $data['file_name'],
                "file_path"=> $dir,
                "file_class"=> 1,
                "file_size"=> $data['file_size'],
                "file_time"=> time(),
                "is_image"=> $data['is_image'],
                "image_width"=> $data['image_width'],
                "image_height"=> $data['image_height'],
                "orig_name"=> $data['orig_name']
            );
            $this->load->model('file_model');
            $result=$this->file_model->addFile($file);
            if($result) {
                $url= base_url().$dir.'/'.$data['file_name'];
                $this->response(array('url'=>$url,'message' => 200), 200);
            }else{
                $this->response(array('message' => 500), 200);
            }
        }
    }

    //上传用户头像
    function log_post(){
        $user = $this->getUserByToken($this->token);
        $path  = $this->post('path');
        if(empty($user)){
            $this->response(array('message'=>400),200);
        }
        $config['allowed_types'] = 'log';
        $config['max_size'] = '1024';

        $dir = 'uploads/log/'.$path;
        $path = $_SERVER['DOCUMENT_ROOT'].'/'.$dir;
        if ( !is_dir($path)) //if the path not exist,create it.
        {
            if (!mkdir($path,0777,true)) {
                $this->response(array('message' => 501), 200);
            }
        }

        $config['upload_path'] = $path;
        $this->load->library('upload',$config);
        if ( ! $this->upload->do_upload('file'))
        {
            $error = array('error' => $this->upload->display_errors());
            $this->response($error, 200);
        }else{
            $data = $this->upload->data();
            if($data) {
                $url= base_url().$dir.'/'.$data['file_name'];
                $this->response(array('url'=>$url,'message' => 200), 200);
            }else{
                $this->response(array('message' => 500), 200);
            }
        }
    }

    //检查设备是否存在，true 表示存在，可以注册
    function device_get()
    {
        $token     = $this->uri->segment('3');
        $user_id   = decrypt($token,$this->key);
        $type    = $this->uri->segment('4');
        $value   = $this->uri->segment('5');

        if(empty($user_id)|| empty($type) || empty($value)){
            $this->response(array('message'=>400),200);
        }

        if(!in_array($type,array('mac','sn','id','link'))){
            $this->response(array('message'=>400),200);
        }
        $type = 'device_'.$type;

//        if($type == "link"){
//            $value = urlencode($value);
//        }

        $condition=array( $type => $value );

        $this->load->model('device_model');
        $result=$this->device_model->getDevice($condition);
        if($result) {
            $this->response(array('result'=>$result,'message' => 200), 200);
        }else{
            $this->response(array('message' => 404), 200);
        }
    }

    //获取设备列表
    function devices_get(){
        $token=$this->uri->segment('3');
        $user_id = decrypt($token,$this->key);
        if(empty($user_id)){
            $this->response(array('message'=>400),200);
        }
        $this->load->model('device_model');
        $result=$this->device_model->getDeviceByUser($user_id);

        if($result){
            $this->response(array('result' => $result,'message' => 200), 200);
        }else{
            $this->response(array('message' => 404), 200);
        }
    }


    //修改设备
    function device_put()
    {
        $user_id    = $this->getUserByToken($this->token)['user_id'];
        $device_mac = $this->uri->segment('3');

        if (empty($user_id) || empty($device_mac)){
            $this->response(array('message'=>400), 200);
        }

        $condition = array('device_mac'=>$device_mac);
        $device    = array();

        $device_lock = $this->put('device_lock');
        $longitude = $this->put('longitude');
        $latitude = $this->put('latitude');

        if($this->put('province'))      $device['province']     = $this->put('province');
        if($this->put('city'))          $device['city']         = $this->put('city');
        if($this->put('district'))      $device['district']     = $this->put('district');
        if($this->put('device_name'))   $device['device_name']  = $this->put('device_name');
        if($this->put('device_sn'))     $device['device_sn']    = $this->put('device_sn');
        if(isset($device_lock)) $device['device_lock']  = $this->put('device_lock');
        if(isset($longitude)) $device['longitude']    = $this->put('longitude');
        if(isset($latitude)) $device['latitude']      = $this->put('latitude');
        if($this->put('radius'))        $device['radius']       = $this->put('radius');
        if($this->put('area_id'))       $device['area_id']      = $this->put('area_id');
        if($this->put('device_address'))$device['device_address'] = $this->put('device_address');
        if($this->put('pm_id'))         $device['pm_id']        = $this->put('pm_id');

        $device['update_time']   = time();

        $this->load->model('device_model');
        $result=$this->device_model->updateDevice($device,$condition);

        if($result){
            $device = $this->device_model->getDevice($condition);
            $device_id = $device['device_id'];
            $this->response(array('result'=>$device_id,'message' => 200), 200);
        }else{
            $this->response(array('message' => 500), 200);
        }
    }


//    public function devices_put(){
//        $user_id    = $this->getUserByToken($this->token)['user_id'];
//        $device_id  = $this->put('device_id');// may more than one devices
//
//        if (empty($user_id) || empty($device_id)){
//            $this->response(array('message'=>400), 200);
//        }
//
//        $device    = array();
//
//        if($this->put('pmv')) $device['pmv']  = $this->put('pmv');
//        $device['update_time']   = time();
//
//        $this->load->model('device_model');
//        $result=$this->device_model->updateDevices($device,$device_id);
//
//        if($result){
//            $this->response(array('result'=>$result,'message' => 200), 200);
//        }else{
//            $this->response(array('message' => 500), 200);
//        }
//    }

    function bind_post(){
        $user_id    = $this->getUserByToken($this->token)['user_id'];
        $device_id  = $this->post('device_id');
        $device_mac = $this->post('device_mac');

        if(empty($user_id) or (empty($device_id) and empty($device_mac))){
            $this->response(array('message'=>400), 200);
        }

        if($device_mac){
            $condition = array(
                'device_mac'=>$device_mac
            );
        }else{
            $condition = array(
                'device_id'=>$device_id
            );
        }

        $this->load->model('device_model');
        $device = $this->device_model->getDevice($condition);
        if($device){
            $bind= array(
                'device_id'=>$device['device_id'],
                'user_id'  =>$user_id,
                'bind_time'=>time()
            );
            $result=$this->device_model->addBind($bind);
            if($result) {
                $res = $this->device_model->getBind(array('user_id'=>$user_id));
                $num = count($res);
                $this->response(array('result'=>$num,'message' => 200), 200);
            }else{
                $this->response(array('message' => 500), 200);
            }
        }else{
            $this->response(array('message' => 404), 200);
        }
    }

    function bind_delete(){
        $token     = $this->uri->segment('3');
        $user_id   = decrypt($token,$this->key);
        $device_id = $this->uri->segment('4');

        if(empty($user_id) or empty($device_id)){
            $this->response(array('message'=>400), 200);
        }

        $this->load->model('device_model');
        //增加判断，设备的主人数量
        $binds = $this->device_model->getBind(array('device_id'=>$device_id));

        if(count($binds) == 1){
            $this->device_model->updateDevice(array('device_lock' => 1),array('device_id'=>$device_id));
        }
        $result=$this->device_model->delBind($user_id,$device_id);
        if($result) {
            $this->response(array('result'=>$result,'message' => 200), 200);
        }else{
            $this->response(array('message' => 500), 200);
        }
    }

    public function app_get(){
        $app_id = $this->uri->segment('3');
        if(empty($app_id)){
            $this->response(array('message'=>400), 200);
        }

        $this->load->model('service_model');
        $result=$this->service_model->getLatestApp(array('app_id'=>$app_id));

        if($result) {
            $this->response(array('result'=>$result[0],'message' => 200), 200);
        }else{
            $this->response(array('message' => 404), 200);
        }
    }

    public function company_get(){
        $company_id = $this->uri->segment('3');
        if(empty($company_id)){
            $this->response(array('message'=>400), 200);
        }

        $this->load->model('service_model');
        $result=$this->service_model->getCompany(array('company_id'=>$company_id));

        if($result) {
            $this->response(array('result'=>$result[0],'message' => 200), 200);
        }else{
            $this->response(array('message' => 404), 200);
        }
    }

    public function cmd_post(){
        $user_id   = $this->getUserByToken($this->token)['user_id'];
        $device_id = $this->post('device_id');
        $cmd = $this->post('commandv');

        if(empty($user_id) or empty($device_id) or empty($cmd)){
            $this->response(array('message'=>400), 200);
        }
        $this->load->model('device_model');
        $this->load->model('redis_model');
        $check = $this->device_model->getBind(array('user_id'=>$user_id,'device_id'=>$device_id));

        if(!empty($check)){
            $result = $this->device_model->pushMsgToDevice($device_id,$cmd);
            $this->response(array('message' => $result), 200);
        }else{
            $this->response(array('message'=>404), 200);
        }
    }

    public function wpm_post(){
        $province  = $this->post('province');
        $city      = $this->post('city');
        $district  = $this->post('district');
        $this->load->model('api_model');

        if(empty($province) || empty($city) ){
            $this->response(array('message'=>400),200);
        }

        //根据城市查询pm
        $area = $this->api_model->chaxun_air_log('round(avg(aqi)) as pm',"area_name = '$city'");
        //上边没有查出来时，对城市名称处理后进行查询
        if(!empty($area)){
            $array = array('省','市','特别行政区','自治区','区','县',);
            $str=str_replace($array,'',$city);
            $area_del = $this->api_model->chaxun_air_log('round(avg(aqi)) as pm',"area_name like '$str'");
        }

        //根据地区查询天气
        $count ='';
        $area_sk = $this->api_model->chaxun_log_sk('temperature,wind_direct,wind_power,humidity',isset($district)?"area_name = '$district'":"area_name ='$city'");
        //没查出结果，就对地区名处理后查询，并在错误地区中插入一条不重复记录
        if(!$area_sk){
            $array = array('省','市','特别行政区','自治区','区','县',);
            $strcunty=str_replace($array,'',$district);
            $strcity=str_replace($array,'',$city);
            $area_sk_del = $this->api_model->chaxun_log_sk('temperature,wind_direct,wind_power,humidity',isset($strcunty)?"area_name like '%$strcunty%'":"area_name like '%$strcity%'");
            $count = count($area_sk_del);

            if(!empty($district)){
                $area_error = $this->api_model->chaxun_area_error("area_name = '$district'");
                if(empty($area_error)){
                    $this->api_model->charu(array('area_name'=>$district,'district_name'=>$city,'province_name'=>$province));
                }
            }
        }

        //如果查出的记录数多于两条，再根据城市和省市名称判断，都一样就用第一条。
        if(count($area_sk) >= 2 ||  $count>= 2){
            if(isset($strcunty) || isset($strcity)){
                $area_v2 = $this->api_model->chaxun_area_v2('area_id,area_name,district_name,province_name',isset($strcunty)?"area_name like '%$strcunty%'":"area_name like '%$strcity%'");
            }else{
                $area_v2 = $this->api_model->chaxun_area_v2('area_id,area_name,district_name,province_name',isset($district)?"area_name = '$district'":"area_name = '$city'");
            }

            foreach($area_v2 as $v){
                if(($v['district_name'] == $city || $v['district_name'] == str_replace($array,'',$city)) && ($v['province_name'] == $province || $v['province_name'] == str_replace($array,'',$province))){
                    $id = $v['area_id'];
                    $area_id = $this->api_model->chaxun_log_sk('temperature,wind_direct,wind_power,humidity',"area_id = '$id'");
                }
            }
        }

        //将查出的结果放入数组中
        if(!empty($area_id)){
            $data = array('temperature' =>$area_id[0]['temperature'],
                //'wind_direct' => $area_id[0]['wind_direct'],
                //'wind_power' => $area_id[0]['wind_power'],
                'humidity' => $area_id[0]['humidity']
            );
        }else if(!empty($area_sk_del)){
            $data = array('temperature' =>$area_sk_del[0]['temperature'],
                //'wind_direct' => $area_sk_del[0]['wind_direct'],
                //'wind_power' => $area_sk_del[0]['wind_power'],
                'humidity' => $area_sk_del[0]['humidity']
            );
        }else if(!empty($area_sk)){
            $data = array('temperature' =>$area_sk[0]['temperature'],
                //'wind_direct' => $area_sk[0]['wind_direct'],
                //'wind_power' => $area_sk[0]['wind_power'],
                'humidity' => $area_sk[0]['humidity']
            );
        }else{
            //天气信息查询失败
            $this->response(array('message' => 401), 200);
        }

        if(isset($area_del)){
            $data['pm'] = $area_del[0]['pm'];
        }else if(isset($area)){
            $data['pm'] = $area[0]['pm'];
        }else{
            //pm信息查询失败
            $this->response(array('message' => 402), 200);
        }

        //返回值
        if($data){
            $this->response(array('result'=>$data,'message' => 200), 200);
        }else{
            $this->response(array('message' => 404), 200);
        }
    }

    //just a redis test
    public function testSpeed_get(){
        $num = $this->uri->segment('3');
        $num = empty($num)?1:$num;
        $str =str_repeat(1,$num*1024);
        $this->response(array('result'=>$str), 200);
    }

    //just a redis test
    public function testHttp_get(){
        $code = $this->uri->segment('3');
        //返回值
        if($code){
            $this->response(array('result'=>'ok'), $code);
        }
    }

    //just a redis test
    public function testDelay_get(){
        $time = $this->uri->segment('3');
        sleep($time);
        $this->response(array('result'=>'ok'), 200);
    }

    private function getUserByToken($token){
        if($token){
            $user_id = decrypt($this->token,$this->key);
            $this->load->model('user_model');
            $user = $this->user_model->getUser(array('user_id'=>$user_id));
            return $user[0];
        }else{
            return 0;
        }
    }


}