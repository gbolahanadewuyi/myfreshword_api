<?php defined('BASEPATH') OR exit('No direct script access allowed');


Class PayModel extends CI_Model {

  protected $momoTable      = "merchant_momo";
  protected $bankTable      = "merchant_bank";
  protected $ghBank         = "ghBanks";
  protected $payDefault     = "defaultPayMerchant";
  public    $bankDefault    = 1;
  public    $momoDeafault   = 2;

  function __construct(){
    parent:: __construct();
  }



  //base on this result we decide to insert new or update
  function checkDefaultPayTable($id){
    $q = $this->db->select('*')->from($this->payDefault)->where('merchant_id', $id)->get()->row();
    if($q == ""){
      return false;
    }
    return true;
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

  function add_Bank_details($id, $data){
    $a = $this->get_checkBankPayments($id);
    if($a == ""){
      $q = $this->db->insert($this->bankTable, $data);
      if($q == true){
        return array('status'=> 201, 'message'=> 'Your bank details has successfully being added');
      }
      return array('status'=>404, 'message'=> 'Error adding bank details');
    }
    $b = $this->db->where('merchant_id',$id)->update($this->bankTable,$data);
    if($b == true){
      return array('status'=> 201, 'message'=> 'Your bank details has successfully being updated');
    }
    return array('status'=>404, 'message'=> 'Error updating bank details');
  }

  function add_Momo_details($id,$data){
    $a = $this->get_checkMomoPayments($id);
    if($a == ""){
      $q = $this->db->insert($this->momoTable, $data);
      if($q == true){
        return array('status'=> 201, 'message'=> 'Your mobile money details has  successfully being added');
      }
      return array('status'=>404, 'message'=> 'Error mobile money details');
    }
    $b = $this->db->where('merchant_id',$id)->update($this->momoTable,$data);
    if($b == true){
      return array('status'=> 201, 'message'=> 'Your mobile money details has successfully being updated');
    }
    return array('status'=>404, 'message'=> 'Error updating momo  details');
  }

  function chooseMerchantDefaultPay($id, $data){
    $a = $this->checkDefaultPayTable($id);
    if($a == false){
      //now insert
      $b = $this->db->insert($this->payDefault, $data);
      if($b == true){
        return array('status'=>201, 'message'=>'bank default set successfully');
      }
      return array('status'=>404, 'message'=> 'error setting bank default');
    }
    //now update
    $q = $this->db->where('merchant_id', $id)->update($this->payDefault, $data);
    if($q == true){
      return array('status'=>200, 'message'=> 'bank default updated successfullyy');
    }
    return array('status'=>404, 'message'=> 'error updating bank details');
  }


  function getMerchantDefault($id){
    $a = $this->db->select('*')->from($this->$payDefault)->where('merchant_id', $id)->limit(1)->get()->row();
    if($a == ""){
      return array('status'=>404, 'message'=> 'default payment has not been set by merchant');
    }
    return array('status'=>200, 'message'=> 'payment default has been set by merchant');
  }

  function deleteBankData($id){
    $q =   $this->db->where('merchant_id',$id)->delete($this->bankTable);
    if($q == true){
      return array('status' => 202, 'message'=> 'Bank Details deleted successfully'  );
    }
    return array('status'=>404, 'message'=> 'Error deleting bank details');
  }

  function deleteMomoData($id){
    $q =   $this->db->where('merchant_id',$id)->delete($this->momoTable);
    if($q == true){
      return array('status' => 202, 'message'=> 'Mobile Money Details deleted successfully'  );
    }
    return array('status'=>404, 'message'=> 'Error deleting Momo details');
  }

  function setDefaultPaymentMerchant($id, $data){
    $q = $this->db->select()->from($this->payDefault)->where('merchant_id', $id)->limit(1)->get()->row();
    if($q == ""){
      $a  = $this->db->insert($this->payDefault, $data);
      if($a == true){
        return array('status'=> 201, 'message'=> 'Default payment has been created');
      }
      return array('status'=>404, 'message'=> 'Error creating default payment');
    }
    $b = $this->db->where('merchant_id', $id)->update($this->payDefault, $data);
    if($b == true){
      return array('status'=>201, 'message'=> 'Default payment has been updated successfully');
    }
    return array('status'=>404, 'message'=> 'Error updating default payment');
  }
}
