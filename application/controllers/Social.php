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
    $id = (int) $this->get('id');
    $response = $this->MyModel->header_auth();
    if($response['status']==200){
      $q = $this->soc->comments_all_data($id);
      $message['status'] =  200;
      $message['results'] = $q;
      $this->response($message, REST_Controller::HTTP_OK);
    }
    else{
      $this->response($response,REST_Controller::HTTP_NOT_FOUND);
    }
  }
  //this will take the loop
  public function user_detail_get(){
    $id = (int) $this->get('id');
    $response = $this->MyModel->header_auth();
    if($response['status']==200){
      $q = $this->soc->user_detail($id);
      $message['status'] =  200;
      $message['results'] = $q;
      $this->response($message, REST_Controller::HTTP_OK);
    }
    else{
      $this->response($response,REST_Controller::HTTP_NOT_FOUND);
    }


  }

  //this will allow you to post to one comment
  public function create_comment_post(){
    $response = $this->MyModel->header_auth();
    if($response['status']==200){
      $_POST = json_decode(file_get_contents('php://input'), TRUE);
      $data= array('success'=> false, 'messages' => array());
  		$this->form_validation->set_rules('comment_data', 'Comment', 'required');
  		$this->form_validation->set_error_delimiters('<span>', '</span>');
      if ($this->form_validation->run() === FALSE){
          foreach($_POST as $key =>$value){
               $data['messages'][$key] = form_error($key);
          }
          $this->response($data, REST_Controller::HTTP_OK);
          return false;

      }
      else{
        $comment_info = array(
          'merchant_feed_id'    =>  $_POST['feed_id'],//feed id in the loop
          'ts_user_id'          =>  $response['id'],//authentication id
          'message'             =>  $_POST['comment_data']
        );

        $q = $this->soc->create_comment($comment_info);
        if($q == true){
          $message['status'] =  201;
          $message['message'] = 'Comment created successfully';
          $this->response($message, REST_Controller::HTTP_OK);
          return false;
        }
        $message['status'] =  400;
        $message['message'] = 'Error creating comment';
        $this->response($message, REST_Controller::HTTP_OK);

      }
    }
    else{
      $this->response($response,REST_Controller::HTTP_NOT_FOUND);
    }
  }


  //this will just get comment id
  public function comment_get(){

  }


  public function comment_delete(){

  }

  public function update_comment_post(){

  }


}
