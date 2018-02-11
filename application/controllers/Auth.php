<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';
use \Firebase\JWT\JWT;

class Auth extends REST_Controller {

  public function __construct(){
    parent:: __construct();
    $this->load->model('Database_model');

  }


  public function login_post(){

     $params = json_decode(file_get_contents('php://input'), TRUE);


       $data = [
           'user_id'        => $params['user_id'],
           'exp_time'       => $params['exp_time'],
           'created_time'   => $params['created_time'],
           'api_key'        => $params['api_key']
       ];

       $query = $this->Database_model->initiate_key($data);
       if($query === true){
         $message = [
           "status" => TRUE,
           "message"=> "Key Initiated Successfully"
         ];
         $this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code

       } else {

         $this->response([
             'status' => FALSE,
             'message' => 'Error initiating key'
         ], REST_Controller::HTTP_NOT_FOUND);

       }

  }


}
