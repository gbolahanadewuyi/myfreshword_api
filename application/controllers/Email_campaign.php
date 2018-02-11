<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';
use \Firebase\JWT\JWT;

class Email_campaign extends REST_Controller {

  public function __construct() {
      parent::__construct();
      $this->load->model('Database_model');
      $this->load->model('Sms_model');
  }


  public function data_post(){

       $params = json_decode(file_get_contents('php://input'), TRUE);
       //we will do validation here
       $query = $this->Database_model->select_email_group($params['group']);//should fetch array
       foreach($query as $email){
          $this->Sms_model->email_campaign_send($email->email,$params['message']);
        }
    }



}
