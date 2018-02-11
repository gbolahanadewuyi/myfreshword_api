<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';
use \Firebase\JWT\JWT;

class SMS_campaign extends REST_Controller {

  public function __construct() {
      parent::__construct();
      $this->load->model('Database_model');
      $this->load->model('Sms_model');
  }


  // private function check_post_array($str){
  //   if (is_array($str)) {
  //     return TRUE;
  //   }else {
  //     return FALSE;
  //   }
  //
  // }
  //
  // private function get_count($str){
  //   return count($str);
  // }

  // public function data_post(){
  //       //fetch data from db
  //       //keys are going to be
  //       //sms camp name
  //       //send from called from db by post
  //       //select group called from db
  //       //message body from input
  //       $query = $this->check_post_array($this->post('group'));
  //       if($query === TRUE){//if is array
  //
  //                 $groupArray = array();
  //                 $groupArray = $this->post('group');
  //                 // $data = [
  //                 //           "campaign_name" => $this->post('campaign'),
  //                 //           //"send_from"     => $this->post('campaign'),//will not be determined by a post//this will have to be ommitted
  //                 //           "group"         => $groupArray,//this an array
  //                 //           "message"       => $this->post('message')
  //                 //         ];
  //
  //
  //
  //               //so therefore count array for input submission and loop through it to store data in database
  //                 $loopCount = $this->count($groupArray);
  //
  //                 for ($i = 1; $i <= $loopCount; $i++) {
  //                       $postData[] = array(
  //                           'campaign_name' => $this->post('campaign'),
  //                         //  'cust_id' => $cust_id,
  //                         //  'date_ordered' => $date,
  //                           'item_name'     => $groupArray["group{$i}"],
  //                           //'item_quantity' => $order_data["item_quantity_{$i}"],
  //                           //'item_price' => $order_data["item_price_{$i}"],
  //                           //'payment_method' => $_POST['payment_method'],
  //                           'message'       => $this->post('message')
  //                       );
  //                   }
  //
  //                   //echo "<pre>";print_r($item);echo "</pre>";die;   uncomment to see the array structure
  //                   $multipleInsert    =  $this->db->insert_batch('sms_logs', $postData);
  //                   $selectGroup       =  $this->Database_model->select_group($groupArray);
  //                   //$sendSms           =  $this->
  //
  //
  //
  //                   $message = [
  //                     "status" => TRUE,
  //                     "msg"=> "Campaign started"
  //                   ];
  //
  //                   if($multipleInsert){
  //                     $this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
  //                   }
  //                   else
  //                   {
  //                     $this->response([
  //                         'status' => FALSE,
  //                         'message' => 'Error starting campaignt'
  //                     ], REST_Controller::HTTP_NOT_FOUND);
  //                   }
  //
  //
  //       } else {//if not array
  //
  //
  //         $data = [
  //                   "campaign_name" => $this->post('campaign'),
  //                   //"send_from"     => $this->post('campaign'),//will not be determined by a post//this will have to be ommitted
  //                   "group"         => $this->post('group'),//this an array
  //                   "message"       => $this->post('message')
  //         ];
  //
  //         //insert into db
  //         $query = $this->Database_model->create_sms_campaign($data);
  //
  //
  //         //send sms around
  //         //loop through this function
  //         //number must be array fertched from the database
  //         $numbers = $this->Database_model->select_group($this->post('group'));
  //         foreach($numbers as $number){
  //           $sendSms = $this->Sms_model->campaign_send($number, $this->post('message'));
  //         }
  //
  //
  //
  //         $message = [
  //           "status" => TRUE,
  //           "msg"=> "Single group campaign started"
  //         ];
  //
  //        //run the insert model here
  //          if($query === true){
  //
  //            $this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
  //          }
  //          else
  //          {
  //            $this->response([
  //                'status' => FALSE,
  //                'message' => 'Error starting single campaign'
  //            ], REST_Controller::HTTP_NOT_FOUND);
  //          }
  //
  //
  //       }
  //
  //
  // }

   //sending bulk messages
   public function data_post(){

        $params = json_decode(file_get_contents('php://input'), TRUE);
        //we will do validation here
        $query = $this->Database_model->select_group($params['group']);//should fetch array
        foreach($query as $number){
           $this->Sms_model->campaign_send($number->mobile,$params['message']);
         }
     }

    


}
