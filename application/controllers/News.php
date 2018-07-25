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
    if($r['status']==200){
      $q = $this->news->get_all_feed_data();
      if(isset($q['status']) && $q['status'] == 204 ){
        $this->response($q, REST_Controller::HTTP_NO_CONTENT);
        return false;
      }
      //add like counts
      //add comment counts
      $obj = array();
      $b = $this->db->select()->from('merchant_comment_thread')->get()->result_array();
      foreach($q as $res){
        //foreach($que as $due){

          // if($res['id'] === $due['speaker_id']){
          //   $var = array('follow'=>1);
          // }else{
          //   $var = array('follow'=>0);
          // }

          $a = $this->news->array_search_x($b, $res['id']);

        //}
        $obj[]= array_merge($res, $a);
      }



      $arr = array();
      $c = $this->db->select()->from('merchant_like_thread')->get()->result_array();
      foreach($q as $resi){

        $d= $this->news->array_search_x($c, $resi['id']);

        //}
        $arr[]= array_merge($obj, $a);
      }

       //krsort($arrObject);
      return array('status'=>200, 'result'=> $arr);

      $this->response($q, REST_Controller::HTTP_OK);
      return false;
    }
    $this->response($r,REST_Controller::HTTP_NOT_FOUND);
  }



}
