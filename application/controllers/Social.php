<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';
//require_once APPPATH . '/libraries/HubtelApi.php';

use \Firebase\JWT\JWT;

class Social extends REST_Controller {

  public function __construct() {
      parent::__construct();
      $this->load->model('MyModel');
      $this->load->model('MerchantProductModel');
      $this->load->library('hubtelApi');
      $this->load->model('SocialModel', 'soc');
  }


  //this will return the total number of likes from the table
  //this is just getting the count
  public function all_likes_get(){
    $response = $this->MyModel->header_auth();
    if($response['status']==200){
      $a = $this->soc->count_all_likes_on_feed();
      $message = array('status'=>200, 'results'=>$a);
      $this->response($message, REST_Controller::HTTP_OK);
    }
    else{
      $this->response($response,REST_Controller::HTTP_NOT_FOUND);
    }
  }

  public function like_post(){

  }

  public function unlike_post(){

  }


  public function all_comments_get(){

  }


  //this will allow you to post to one comment
  public function comment_post(){

  }


  //this will just get comment id
  public function comment_get(){

  }


  public function comment_delete(){

  }

  public function update_comment_post(){

  }


}
