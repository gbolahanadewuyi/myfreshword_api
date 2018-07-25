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
  }


  public function all_likes_get(){

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
