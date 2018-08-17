<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Users_model extends CI_Model {
    public function __construct() {
        $this->load->database();
    }

    public function login($username, $password) {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('username', $username);
        $this->db->where('password', $password);
        $query = $this->db->get();
              if ($query->num_rows() == 1) {
                  $result = $query->result();
                  return $result[0]->id;
              }
              return false;
    }

    public function get_users(){

        $this->db->select('user_id,user_uname');
        //$this->db->where('user_email', $user_email  );
        $this->db->from('ts_user');
        return $this->db->get()->result();
    }

    public function get_user_id($id){
      print_r("running get user id. requested user_id is\n");
      print_r($id);
      $query = $this->db->select('user_id,user_uname')->from('ts_user')->where('user_id', $id)->get()->result_array();//
      // print_r($query);

      if(isset($query[0])){
        return array("status"=>201,"message"=>"no user with specified id found");
      }
      else {
        return $query[0];
      }

    }
}
