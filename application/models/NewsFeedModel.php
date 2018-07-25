<?php defined('BASEPATH') OR exit('No direct script access allowed');


Class NewsFeedModel extends CI_Model {

  protected $newsTable = "news_feed_category";
  protected $feedTable = "merchant_feed";

  public function __construct(){
    parent:: __construct();

  }

  function get_topic_category(){
    $q = $this->db->select('*')->from($this->newsTable)->get()->result();
    if($q == true){
      return array('status'=>200, 'result'=>$q);
    }
    return array('status'=>204, 'message'=> 'No Content found');
  }

  function get_all_feed_data(){
    $q  = $this->db->select('id, title, message, image, timestamp, likes_count, comments_counts')->from($this->feedTable)->order_by('id','desc')->get()->result_array();
    if($q == true){
      //return array('status'=>200, 'result'=>$q);
      return $q;
    }
    return array('status'=>204, 'message'=> 'No Content found');
  }


  function update_with_comments_likes_count($data){//data should have object for likes count  and comment counts
    return    $query = $this->db->where('id',$id)->update($this->feedTable,$data);
  }


  function array_search_x( $array, $name ){

    // foreach( $array as $item ){
    //     if ( is_array( $item ) && isset( $item['speaker_id'] )){
    //         if (strpos($item['speaker_id'], $name) !== false) { // changed this line
    //             return array('follow'=>true);
    //         }
    //     }
    // }
    // return  array('follow'=>false); // or whatever else you'd like


    foreach( $array as $item ){
        if ( is_array( $item ) && isset( $item['merchant_feed_id'] )){
            if ( $item['merchant_feed_id'] == $name ){ // or other string comparison
              $a = $this->db->select()->from('merchant_like_thread')->where('merchant_feed_id', $item['merchant_feed_id'])->get()->num_rows();//this counts the associated likes
              $b = $this->db->select()->from('merchant_comment_thread')->where('merchant_feed_id', $item['merchant_feed_id'])->get()->num_rows();//this counts the associated likes
              return array('likes_count'=>$a, 'comments_count'=>$b);
            }
        }
    }
    //return  array('follow'=>false);
  }

}
