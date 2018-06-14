<?php defined('BASEPATH') OR exit('No direct script access allowed');
//this will be used in all controllers that have to make queries on tables

Class QueryModel extends CI_Model {

  protected $productTable     = "ts_products";
  protected $userTable        = "ts_user";
  protected $merchantTable    = "ts_merchant";

  function  __construct(){
    parent:: __construct();
  }

  function get_product($id){
    return $this->db->select()->from($this->productTable)->where('prod_id', $id)->get()->row();
  }

  function get_user($id){
    return $this->db->select()->from($this->userTable)->where('user_id', $id)->get()->row();
  }

  function get_merchant(){
    return $this->db->select()->from($this->merchantTable)->where('merchant_id', $id)->get()->row();
  }


}
