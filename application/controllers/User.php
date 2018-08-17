<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';
use \Firebase\JWT\JWT;

class User extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('MyModel');
        $this->load->model('Users_model');
    }



    public function data_post() {

        //if u pass this meaning you are are using x-wwww-form-urlencodede
        $username = $this->post('username');
        $password = $this->post('password');


        $invalidLogin = ['invalid' => $username];
        if(!$username || !$password) $this->response($invalidLogin, REST_Controller::HTTP_NOT_FOUND);
        $id = $this->Users_model->login($username,$password);
        if($id) {
            $token['id'] = $id;
            $token['username'] = $username;
            $date = new DateTime();
            $token['iat'] = $date->getTimestamp();
            $token['exp'] = $date->getTimestamp() + 60*60*5;

            $output['id_token'] = JWT::encode($token, "my Secret key!");
            $this->set_response($output, REST_Controller::HTTP_OK);

        }
        else {
            $this->set_response($invalidLogin, REST_Controller::HTTP_NOT_FOUND);
        }
    }



    public function data_get(){
      $response = $this->MyModel->header_auth();
      if($response['status']==200){
        $query = $this->Users_model->get_users();
        $this->set_response($query, REST_Controller::HTTP_OK);
      }
      else{
        $this->response($response,REST_Controller::HTTP_NOT_FOUND);
      }
    }


    public function user_get(){
      $response = $this->MyModel->header_auth();
      if($response['status']==200){
        $id = $this->get('id');
        $query = $this->Users_model->get_user_id($id);
        $this->set_response($query, REST_Controller::HTTP_OK);
      }
      else{
        $this->response($response,REST_Controller::HTTP_NOT_FOUND);
      }
    }



}
