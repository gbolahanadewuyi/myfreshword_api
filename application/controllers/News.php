<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';

use \Firebase\JWT\JWT;

Class News extends REST_Controller{

  function __construct(){
    parent:: __construct();
    $this->load->model('NewsFeedModel', 'news');
    $this->load->model('MyModel', 'my');
    $this->load->model('SocialModel', 'soc');
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
    // print_r($r);
    if($r['status']==200){
      $q = $this->news->get_all_feed_data();
      if(isset($q['status']) && $q['status'] == 204 ){
        $this->response($q, REST_Controller::HTTP_NO_CONTENT);
        return false;
      }
      $result = array();
      foreach ($q as $res) {
        $feedliked = $this->soc->get_one_like($res['id'],$r['id']);
        // print_r($feedliked);
        if (count($feedliked)>0)
        {$result[]= array_merge($res, ['liked' => !is_null($feedliked['0']['like'])]);}
        else {
          $result[]= array_merge($res, ['liked' => false]);
        }
      }
      $this->response($result, REST_Controller::HTTP_OK);
      return false;
    }
    $this->response($r,REST_Controller::HTTP_NOT_FOUND);
  }



}
