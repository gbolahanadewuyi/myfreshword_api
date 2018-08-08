<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';

use \Firebase\JWT\JWT;

Class Speakers extends REST_Controller{

  function __construct(){
    parent:: __construct();
    $this->load->model('SpeakerModel', 'sp');
    $this->load->model('MyModel');

  }

  //this gets all the data on speaker list
  //this should return speakers who are being followed
  function index_get(){
    $response = $this->MyModel->header_auth();
    if($response['status']==200){
      $q = $this->sp->get_speaker_data($response['id']);
      if($q['status'] ==204){
        $this->response($q, REST_Controller::HTTP_NO_CONTENT);
        return false;
      }
      $this->response($q, REST_Controller::HTTP_OK);
    }
    else{
      $this->response($response,REST_Controller::HTTP_NOT_FOUND);
    }
  }



  function search_speaker_post(){
    $response = $this->MyModel->header_auth();
    if($response['status']==200){
      $_POST = json_decode(file_get_contents('php://input'), TRUE);
      $q = $this->sp->search_with_follow_value($_POST['feed_search']);
      if(count($q) > 0){
        $this->response($q, REST_Controller::HTTP_OK);
      }
      else {
        $this->response($q, REST_Controller::HTTP_NO_CONTENT);
      }
    }
    else{
      $this->response($response,REST_Controller::HTTP_NOT_FOUND);
    }
  }

 //this gets a particular speaker data
 function author_get(){
   $response = $this->MyModel->header_auth();
   if($response['status']==200){
     $id = (int) $this->get('id');
     $q = $this->sp->get_speaher_id($id);
     $j = $this->sp->get_followers($id);
     if($q == ""){
       $this->response($q, REST_Controller::HTTP_NO_CONTENT);
       return false;
     }
    $q['followers'] = $j['count(ts_users_id)'];
    $this->response($q, REST_Controller::HTTP_OK);
   }
   else{
     $this->response($response,REST_Controller::HTTP_NOT_FOUND);
   }

 }


 function speaker_followers_get(){
   $response = $this->MyModel->header_auth();
   if($response['status']==200){
     $q = $this->sp->get_follower_data($response['id']);
     if($q['status'] == 204){
       $this->response($q, REST_Controller::HTTP_NO_CONTENT);
       return false;
     }
     if($q['status'] ==200){
       $this->response($q, REST_Controller::HTTP_OK);
       return false;
     }
   }
   else{
     $this->response($response,REST_Controller::HTTP_NOT_FOUND);
   }
 }


 function follow_speaker_get(){
   $response = $this->MyModel->header_auth();
   if($response['status']==200){
     $id = (int) $this->get('id');
     $data['speaker_id'] = $id;
     $data['ts_users_id']= $response['id'];
     $q = $this->sp->avoid_duplicates($data);
     if($q == true){
       $message['status']   = 201;
       $message['message']  = "Author / Speaker followed successfully";
       $this->response($message, REST_Controller::HTTP_CREATED);
     }else{
       $message['status']   = 400;
       $message['message']  = "Already following author";
       $this->response($message, REST_Controller::HTTP_NOT_FOUND);
     }
   }else{
     $this->response($response,REST_Controller::HTTP_NOT_FOUND);
   }
 }



 function unfollow_speaker_get(){
   $response = $this->MyModel->header_auth();
   if($response['status']==200){
     $id = (int) $this->get('id');
     $data['speaker_id'] = $id;
     $data['ts_users_id']= $response['id'];
     $q = $this->sp->unfollow_speaker($data['ts_users_id'] , $data['speaker_id']);
     if($q['status'] ==204){
       $this->response($q, REST_Controller::HTTP_OK);
     }
   }else{
     $this->response($response,REST_Controller::HTTP_NOT_FOUND);
   }
 }


}
