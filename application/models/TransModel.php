<?php defined('BASEPATH') OR exit('No direct script access allowed');


Class TransModel extends CI_Model{

    protected $transTable     = "completedTrans";
    protected $withdrawTable  = "withdrawal_merchant";

    function __construct(){
     parent::__construct();
    }

   //this should be the list of the merchant transaction passed  and inserted containing
   //the list of the products purchased
   //this soughta represents the same data from the cart table
   function get_purchase_history($id){
      return $q = $this->db->select()->from($this->transTable)->where('merchant_id', $id)->get()->result();
   }


   // function get_transactions_history(){
   //   $q = $this->db->select('*')->from($this)->where()->get()->result();
   // }


   function get_withdrawal_history($id){
       return $q = $this->db->select()->from($this->withdrawTable)->where('merchant_id', $id)->get()->result();
   }



   //calculating for the difference for the actual balance / available balance
   function percent_deduction($amount){
     return $q = ($this->percent_value / 100) * $amount;
   }

   function date_range_filter($from, $to, $type, $id){
     if($type == "purchase"){
       $this->db->select('*');
       $this->db->from($this->transTable);
       $this->db->where('date <',$to);
       $this->db->where('date >',$from);
       $this->db->where('merchant_id', $id);
       return $result = $this->db->get()->result();
     }
     $this->db->select('*');
     $this->db->from($this->withdrawTable);
     $this->db->where('date <',$to);// highest
     $this->db->where('date >',$from);//lowest
     $this->db->where('merchant_id', $id);
     $result = $this->db->get()->result();
   }

   private function percent_value(){
     return 20;
   }

   //after deducation user can actuall withdraw
   function available_balance($id){
     $q = $this->actual_balance($id);
     $res = $this->percent_deduction($q);
     return $res;
   }


   //amount with deduction
   //this can also be used to represent total sales
   // function actual_balance($id, $pstAmt){
   //   if($pstAmt == ""){
   //     $this->db->select_sum('price');
   //     $this->db->where('merchant_id', $id);
   //     $result = $this->db->get($this->transTable)->row();
   //     return $result->price;
   //   }
   //   return $pstAmt;
   // }

   //calculating the total number of purchases for merchant made users
   function total_sales($id){
     $this->db->select_sum('price');
     $this->db->where('merchant_id', $id);
     $result = $this->db->get($this->transTable)->row();
     return $result->price;
   }

   function  actual_balance($id){
     $total_sales     = $this->total_sales($id);
     $total_balance   = $this->total_balance($id);
     $result = $total_sales - $total_balance;
     return $result;
   }

   function total_balance($id){
     $this->db->select_sum('debit_commission');
     $this->db->where('merchant_id', $id);
     $result = $this->db->get($this->withdrawTable)->row();
     return $result->debit_commission;
   }

}
