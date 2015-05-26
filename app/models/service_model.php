<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

Class Service_model extends CI_Model{

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->tb_order=$this->db->dbprefix('order');
        $this->tb_app='app';
        $this->tb_app_version='app_version';
        $this->tb_company='company';
    }
    public function getLatestApp($condition){
        $this->db->select('*');
        $query=$this->db->order_by('version_code','desc')->limit(1,0)->get_where($this->tb_app_version,$condition);
        $result=$query->result();
        return $result;
    }

    public function getCompany($condition){
        $this->db->select('*');
        $query=$this->db->get_where($this->tb_company,$condition);
        $result=$query->result();
        return $result;
    }


}