<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';

use \Firebase\JWT\JWT;

Class News extends REST_Controller{

  function __construct(){
    parent:: __construct();
    $this->load->model('NewsFeedModel', 'news');
    $this->load->model('MyModel', 'my');
  }

  function index_get(){
    $q = $this->news->get_topic_category();
    if($q['status'] ==204){
      $this->response($q, REST_Controller::HTTP_NO_CONTENT);
      return false;
    }
    $this->response($q, REST_Controller::HTTP_OK);
  }


  //get user feed from this endpoint
  function feed_get(){
    $r = $this->my->header_auth();
    if($r['status']==200){
      $q = $this->news->get_all_feed_data();
      if(isset($q['status']) && $q['status'] == 204 ){
        $this->response($q, REST_Controller::HTTP_NO_CONTENT);
        return false;
      }
      $this->response($q, REST_Controller::HTTP_OK);
      return false;
    }
    $this->response($r,REST_Controller::HTTP_NOT_FOUND);
  }

}
