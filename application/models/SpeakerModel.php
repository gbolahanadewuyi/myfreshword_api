<?php  defined('BASEPATH') OR exit('No direct script access allowed');

Class SpeakerModel extends CI_Model {

  protected $speakerTable     = "speakers";
  protected $speakerFollowers = "speakers_followers";


  function __construct(){
    parent:: __construct();
  }

  function get_all_speakers($query){
        $q = $this->db->select('id,name')->from($this->speakerTable)->get()->result_array();//getting object array returns id of the speaker
        if($q == ""){
          return array('status'=>204, 'message'=> 'No Content found');
        }
        return array('status'=>200, 'result'=>$q);
    }

function get_speaker_data($query){
  $q = $this->db->select('pastors_listing.pastors_title, pastors_listing.pastors_name, pastors_listing.pastors_bio, pastors_listing.pastors_avatar_img, ts_merchant.organisation')->from('pastors_listing')->join('ts_merchant','pastors_listing.merchant_id = ts_merchant.id','Left')->get()->result_array();

      $que = $this->db->select('*')->from($this->speakerFollowers)->where('ts_users_id', $query)->get()->result_array();//returns speaker_id



      $arrObject = array();
      //$arr_2 = array();
      foreach($q as $res){
        //foreach($que as $due){

          // if($res['id'] === $due['speaker_id']){
          //   $var = array('follow'=>1);
          // }else{
          //   $var = array('follow'=>0);
          // }

          $a = $this->array_search_x($que, $res['id']);

        //}
        $arrObject[]= array_merge($res, $a);
      }
       //krsort($arrObject);
      return array('status'=>200, 'result'=> $arrObject);

      // if($q != ""){
      // }
      // return array('status'=>204, 'message'=> 'No Content found');
  }

  function search_with_follow_value($query){
    $q = $this->search_speaker($query);
    if(isset($q['status']) && $q['status'] == 400){
      return $q;
    }
    else{
      $que = $this->db->select('*')->from($this->speakerFollowers)->where('ts_users_id', $query)->get()->result_array();//returns speaker_id
      $arrObject = array();
      //$arr_2 = array();
      foreach($q as $res){
        $a = $this->array_search_x($que, $res['id']);

        $arrObject[]= array_merge($res, $a);
      }
      return array('status'=>200, 'result'=> $arrObject);
    }
  }


  function array_search_x( $array, $name ){
    foreach( $array as $item ){
        if ( is_array( $item ) && isset( $item['speaker_id'] )){
            if ( $item['speaker_id'] == $name ){ // or other string comparison
              return array('follow'=>true);
            }
        }
    }
    return  array('follow'=>false);
  }

  function get_follower_data($ts_user_id){//array is for the speaker id
    $q = $this->db->select('*')->from($this->speakerFollowers)->where('ts_users_id', $ts_user_id)->get()->result_array();
    if($q == ""){
      return array('status'=>204, 'message'=> 'No Content found');
    }
    return array('status'=>200, 'result'=>$q);
  }





  // //return follow true or return follow false
  // function follow_status($id){//this is the user id
  //   $query = $this->db->select('*')->from($this->speakerFollowers)->where('ts_users_id', $id)->get()->result();//this will only bring back the speakers user is following
  //   foreach($query as $res){
  //     if($res->speaker_id ){}
  //   }
  //
  // }
  //
  //
  // //select the match from the table
  // function following($id){//results returned from speaker followers
  //   $q = $this->db->select('*')->from($this->speakerTable)->where('id', $id)->get()->result();
  // }
  //
  // function get_followers($id){//this is the authentication id
  //   $query = $this->db->select('*')->from($this->speakerFollowers)->where('ts_users_id', $id)->get();//this will only bring back the speakers user is following
  //   if($query->num_rows() > 0){
  //     return array('count'   => $query->num_rows(),'result'  => $query->result());
  //   }
  //
  //   $q = $this->db->select('*')->from($this->speakerTable)->get();
  //   // return array(
  //   //
  //   // );
  //
  //
  // }

  function get_speaher_id($id){
    return $this->db->select('*')->from($this->speakerTable)->where('id',$id)->order_by('id','desc')->get()->row();
  }



  function add_to_list($data){
    $q =$this->db->insert($this->speakerTable, $data);
  }


  function search_speaker($search_term){
    $search_term="%".$search_term."%";
    $sql="SELECT * FROM $this->speakerTable WHERE name LIKE ? ";
    $query=$this->db->query($sql,array($search_term));
    $res=$query->result_array();//so basically we are going to return an array of the results
     if(count($res) > 0){
       return $res;
     }
     else {
       return array('status'=>400 , 'message'=> 'Sorry No results found');
     }
  }

  function new_follow_speaker($data){
    $query = $this->db->insert($this->speakerFollowers, $data);
    return $query;
  }

  function avoid_duplicates($data){
    $query = $this->db->select('ts_users_id, speaker_id')->from($this->speakerFollowers)->where('ts_users_id', $data['ts_users_id'])->where('speaker_id', $data['speaker_id'])->get()->row();
    if($query == ""){
      $this->new_follow_speaker($data);
      return true;
    }
    return false;
  }

  function adding_followers(){

  }



  //so if there isnt any followers or if there is
  function check_followers($user_id, $data){//user id will be passed by the token
    $q  = $this->db->select('ts_users_id')->from($this->speakerFollowers)->where('ts_users_id',$user_id)->get()->result_array();
    if($q == ""){
      //so here you have to insert data into the database
       return $q = $this->new_follow_speaker($data);
    }
    return array('status'=>200, 'message'=> 'User is already following speaker');
  }


  //so if there isnt any followers or if there is
  function get_followers($speaker_id){//user id will be passed by the token
    $q  = $this->db->select('count(ts_users_id)')->from($this->speakerFollowers)->where('speaker_id',$speaker_id)->get()->row(0);
    return $q;
  }

  //if a user decides to unfollow the speaker
  function unfollow_speaker($user_id, $speaker_id){
    $q = $this->db->where('ts_users_id',$user_id)->where('speaker_id', $speaker_id)->delete($this->speakerFollowers);
    if($q == true){
      return array('status'=>204, 'message'=> 'Speaker unfollowed successfully');
    }
  }
}
