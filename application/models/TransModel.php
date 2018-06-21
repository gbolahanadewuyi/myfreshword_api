<?php defined('BASEPATH') OR exit('No direct script access allowed');


Class TransModel extends CI_Model{

    protected $transTable     = "completedTrans";
    protected $withdrawTable  = "withdrawal_merchant";

    protected $momoTable      = "merchant_momo";
    protected $bankTable      = "merchant_bank";


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




   /*
   **Will work on the functions correctly later because i am already calling them
   */
   function get_checkMomoPayments($merchant_id){
     return $query = $this->db->select()->from($this->momoTable)->where('merchant_id', $merchant_id)->get()->row();
   }

   function get_checkBankPayments($merchant_id){
     return $query = $this->db->select()->from($this->bankTable)->where('merchant_id', $merchant_id)->get()->row();
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

   function calcPercent($num){
     return ($num/100)*20;
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

   function curl_command_send_money($param_0, $param_1){

   }



   //this logs data into database if the query is successful
   function log_withdrawal($payLoad){
     $data = array(
       'merchant_id'        =>  $payLoad['id'],
       'debit'              =>  $payLoad['debit'],
       'balance'            =>  $payLoad['balance'],
       'status'             =>  $payLoad['status'],
       'creditAcc'          =>  $payLoad['creditAcc'],
       'commission_amt'     =>  $payLoad['commission_amt'],
       'debit_commission'   =>  $payLoad['debit_commission']
     );
     $q = $this->db->insert($this->withdraw, $data);
     if($q == true){
       return array('status'=>201, 'message'=> 'payment successful');
     }
     return array('status'=>400, 'message'=> 'Error logging transaction withdrawal');
   }


   // run this and send the money to the user selected account
   function net_amount_transfer($amount, $id, $channel){

      if($amount > $this->actual_balance($id)){
        return array('status'=>false, 'message'=> 'Insufficient Funds');
      }
      $q = $this->calcPercent($amount);//gets the amount after percentage deduction
      $res = $amount - $q;//send this money to the merchant designated account


      //this will run get on bank db
      if($channel == "bank"){
        $m = $this->get_checkBankPayments($id);
        if($m == ""){
          return array('status'=>204, 'message'=> );
        }

        //so basically run this query  and get feedback data and store it in the database
        return $this->curl_command_send_money($res, $bank);
      }

      //this will run the get on momo db
      if($channel == "momo"){
        $b = $this->get_checkMomoPayments($id);
        if($b == ""){
          return array('status'=>204, 'message'=> 'no momo account has been set up');
        }


        return $this->curl_command_send_money($res, $momo); // res is the amount to be sent , momo is the number recieving the funds
      }

   }

}
