<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';
use \Firebase\JWT\JWT;

Class Api_key extends REST_Controller {

  public function __construct(){
    parent:: __construct();
    $this->load->model('Api_model', 'api');
  }

  //post data into the database on the server side to store

  public function set_api_post(){
      // we need to expect it from a particular header to avoid people hacking it
      $params = json_decode(file_get_contents('php://input'), TRUE);
      $data = [
          'user_id'        => $params['user_id'],
          '_key'           => $params['api_key'],
          'created'        => $params['created_time'],
          'expiration'     => $params['exp_time']
      ];


      $query =  $this->api->store_api_key($data); //this should return true of false

        if($query == true){
          //then here we can show success with message
          $msg = array(
            'status' => true,
            'message'=> 'Api for transaction created'
          );
          $this->set_response($msg, REST_Controller::HTTP_OK);
        }





      // if($res->status != false){
      //
      //     $query =  $this->api->store_api_key($data); //this should return true of false
      //     if($query == true){
      //         //then here we can show success with message
      //         $msg = array(
      //           'status' => true,
      //           'message'=> 'Api for transaction created'
      //         );
      //         $this->set_response($msg, REST_Controller::HTTP_OK);
      //       }
      //
      // }


      //$this->set_response($data, REST_Controller::HTTP_OK);
      // if(empty($data)){
      //   $message = array(
      //     'msg' => 'error'
      //   );
      //   $this->set_response($message, REST_Controller::HTTP_NOT_FOUND);
      //
      // } else {
      //
      //   // $query =  $this->api->store_api_key($data); //this should return true of false
      //   //
      //   // if($query == true){
      //   //   //then here we can show success with message
      //   //   $msg = array(
      //   //     'status' => true,
      //   //     'message'=> 'Api for transaction created'
      //   //   );
      //   //   $this->set_response($msg, REST_Controller::HTTP_OK);
      //   // }
      // }
  }


  public function check_key(){
    //pass the data through json body
    $params = json_decode(file_get_contents('php://input'), TRUE);
    $query = $this->api->get_api_key($params['key']);
    if($query == true){
      //run the query again the endpoint
      $message = array(
        'status' => true,
        'message'=> 'Access granted'
      );
      $this->set_response($message, REST_Controller::HTTP_OK);

    }else {

      $message = array(
        'status' => false,
        'message'=> 'Unauthorized access'
      );
      $this->set_response($message, REST_Controller::HTTP_NOT_FOUND);
    }
  }


}
