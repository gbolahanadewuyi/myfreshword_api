<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';

use \Firebase\JWT\JWT;

Class Merchant extends REST_Controller{


  function __construct(){
    parent:: __construct();
    $this->load->model('MyModel');
    $this->load->model('PayModel', 'pay');
  }

  function setPaymentDefault_post(){
    $response = $this->MyModel->merchant_auth();
    if($response['status']==200){
      $_POST = json_decode(file_get_contents('php://input'), TRUE);
        if($_POST['type'] == "momoMerchant"){
          $query = $this->pay->setMomoDefault($response['id'], $_POST);


          return false;
        }

        if($_POST['type'] == "bankMerchant"){
          $query = $this->pay->setBankDefault($response['id'], $_POST);

          return false;
        }
    }
    $this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
  }


  //sp here you should join from both tables
  function showAdded_payments_get(){
    $id = (int) $this->get('id');
    $response = $this->MyModel->merchant_auth();
    if($response['status']==200){
      $q['momoRes'] = $this->pay->get_checkMomoPayments($id);
      $q['bankRes'] = $this->pay->get_checkBankPayments($id);
      $this->response($q,REST_Controller::HTTP_OK);
    }
    $this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
  }


  function ghBanks_post(){
    $response = $this->MyModel->merchant_auth();
    if($response['status']==200){
        $q = $this->pay->get_ghBanks();
        $this->response($q,REST_Controller::HTTP_OK);
    }
    $this->response($response, REST_Controller::HTTP_NOT_FOUND);
  }

}//end of class
