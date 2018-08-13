<?php  defined('BASEPATH') OR exit('No direct script access allowed');

Class SocialModel extends CI_Model {

    protected $comment_table     = "merchant_comment_thread";
    protected $like_table  = "merchant_like_thread";
    protected $user_table  = "ts_user";
    protected $feedTable = "merchant_feed";

    function __construct(){
      parent:: __construct();
    }


    //get all comments
    function comments_all_data($id){
      return $this->db->select()->from($this->comment_table)->where('merchant_feed_id', $id)->order_by('id','desc')->get()->result();
    }

    //this will count comments on  a particular thread
    function count_comments($data){
      $query = $this->db->select()->from($this->comment_table)->where('merchant_feed_id', $data['id'])->get();
      return $query->num_rows();//basically this tells you the number of comments associated to a particular merchant thread
    }

    //get just one comment
    //fetch just one data from the comment
    function comment_one_data($id){
      $query  = $this->db->select()->from($this->comment_table)->where('id', $id)->get()->row();
      return $query;
    }


    //function delete one comment
    function delete_comment($data){
      $query = $this->db->where('id',$data['id'])->where('ts_user_id',$data['user_id'])->delete($this->comment_table);
      return $query;
    }



    function create_comment($data){
      $query = $this->db->insert($this->comment_table, $data);
      $feeditem = $this->db->select()->from($this->feedTable)->where('id',$data['merchant_feed_id'])->get()->result_array();
      print_r($feeditem);
      $feeditem['comments_counts'] = $feeditem['comments_counts'] + 1;
      $updatecommentcount = $this->db->where('id',$feeditem['id'])->update($this->feedTable,$feeditem);
      return $query;
    }


    function update_comment($id, $data){
      $query = $this->db->where('id',$id)->update($this->comment_table,$data);
      return $query;
    }


    //get all the likes data from the table
    function likes_all_data(){
      return $this->db->select()->from($this->like_table)->order_by('id','desc')->get()->result();
    }

    function count_all_likes_on_feed(){
      return $this->db->select()->from($this->like_table)->get()->num_rows();
    }


    //so basically you will runnning this through a loop
    function  count_likes_by_merchant_feed($id){
      return $query = $this->db->select()->from($this->like_table)->where('merchant_feed_id', $id)->get()->num_rows();
    }

    //will also  have to run this through a loop as well
    function count_comments_by_merchant_feed($id){
      return $query = $this->db->select()->from($this->comment_table)->where('merchant_feed_id', $id)->get()->num_rows();
    }


    function get_one_like($id){
      return $query = $this->db->select()->from($this->like_table)->where('id', $id)->get()->row();
    }

    //function should avoid users from liking more than once
    function avoid_like_duplicates($data){
      $query = $this->get_one_like($data['id']);
      if($query == ""){
        $a = $this->like_post_data($data);
        if($a == true){
          return array('status'=>201, 'message'=>'feed liked successfully');
        }
      }
      return array('status'=>400, 'message'=> 'Feed thread already liked');
    }


    //this should just insert data into the database one
    function like_post_data($data){
      $query = $this->db->insert($this->like_table, $data);
      $feeditem = $this->db->select()->from($this->feedTable)->where('id',$data['merchant_feed_id'])->get()->row();
      $feeditem['likes_count'] += 1;
      $updatelikescount = $this->db->where('id',$feeditem['id'])->update($this->feedTable,$feeditem);
      return $query;
    }


    function  user_detail($id){
      $query = $this->db->select()->from($this->user_table)->where('user_id', $id)->get()->row();
      return $query;
    }
}
