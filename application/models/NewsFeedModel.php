<?php defined('BASEPATH') OR exit('No direct script access allowed');


Class NewsFeedModel extends CI_Model {

  protected $newsTable = "news_feed_category";
  public function __construct(){
    parent:: __construct();

  }

  function get_topic_category(){
    $q = $this->db->select('*')->from($this->newsTable)->get()->result();
    if($q == true){
      return array('status'=>200, 'result'=>$q);
    }
    return array('status'=>>204, 'message'=> 'No Content found');
  }
}
