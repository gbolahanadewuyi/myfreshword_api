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

  function index_get(){
    $q = $this->sp->get_speaker_data();
    if($q['status'] ==204){
      $this->response($q, REST_Controller::HTTP_NO_CONTENT);
      return false;
    }
    $this->response($q, REST_Controller::HTTP_OK);
  }

  function search_speaker_post(){
    $response = $this->MyModel->header_auth();
    if($response['status']==200){
      $_POST = json_decode(file_get_contents('php://input'), TRUE);
      $q = $this->MyModel->search_speaker($_POST['feed_search']);
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

 function author_get(){
   $id = (int) $this->get('id');
   $q = $this->sp->get_speaher_id($id);
   if($q == ""){
     $this->response($q, REST_Controller::HTTP_NO_CONTENT);
     return false;
   }
   $this->response($q, REST_Controller::HTTP_OK);
 }

}
