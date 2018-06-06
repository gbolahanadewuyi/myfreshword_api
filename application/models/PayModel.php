<?php defined('BASEPATH') OR exit('No direct script access allowed');


Class PayModel extends CI_Model {

  protected $momoTable = "merchant_momo";
  protected $bankTable = "merchant_bank";
  protected $ghBank    =  "ghBanks";
  function __construct(){
    parent:: __construct();
  }


  function get_checkMomoPayments($merchant_id){
    return $query = $this->db->select()->from($this->momoTable)->where('merchant_id', $merchant_id)->get()->row();
  }

  function get_checkBankPayments($merchant_id){
    return $query = $this->db->select()->from($this->bankTable)->where('merchant_id', $merchant_id)->get()->row();
  }


  function setMomoDefault($merchant_id, $data){
    $data = array('defaultSet'=>1);
    $query =$this->db->where('merchant_id',$merchant_id)->update($this->momoTable,$data);
    if($query == true){
      return array('status'=>201, 'message'=>'Mobile Money set as default payment channel');
    }
    return array('status'=>400, 'message'=> 'Error setting mobile money as default payment channel');
  }


  function setBankDefault($merchant_id, $data){
    $data = array('defaultSet'=>1);
    $query =$this->db->where('merchant_id',$merchant_id)->update($this->bankTable,$data);
    if($query == true){
      return array('status'=>201, 'message'=>'Bank Account set as default payment channel');
    }
    return array('status'=>400, 'message'=> 'Error setting bank account as default payment channel');
  }

  function get_ghBanks(){
    return $query = $this->db->select('*')->from($this->ghBank)->get()->result();
  }

}
