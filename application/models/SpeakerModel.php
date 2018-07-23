<?php  defined('BASEPATH') OR exit('No direct script access allowed');

Class SpeakerModel extends CI_Model {

  protected $speakerTable     = "speakers";
  protected $speakerFollowers = "speakers_followers";


  function __construct(){
    parent:: __construct();
  }

  function get_speaker_data(){
    $q = $this->db->select('*')->from($this->speakerTable)->get()->result_array();
    $a = [];
    $follow = array('follow'=>false);
    foreach($q as $b){
      $a = array_merge($b,$follow);
    }
    if($q != ""){
      return array('status'=>200, 'result'=>$a);
    }
    return array('status'=>204, 'message'=> 'No Content found');
  }


  function get_follower_data($ts_user_id){
    $q = $this->db->select('*')->from($this->speakerFollowers)->where('ts_users_id', $ts_user_id)->get()->result();
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
    $res=$query->result();//so basically we are going to return an array of the results
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
    $q  = $this->db->select('ts_users_id')->from($this->speakerFollowers)->where('ts_users_id',$user_id)->get()->row();
    if($q == ""){
      //so here you have to insert data into the database
       return $q = $this->new_follow_speaker($data);
    }
    return array('status'=>200, 'message'=> 'User is already following speaker');
  }


  //if a user decides to unfollow the speaker
  function unfollow_speaker($user_id, $speaker_id){
    $q = $this->db->where('ts_users_id',$user_id)->where('speaker_id', $speaker_id)->delete($this->speakerFollowers);
    if($q == true){
      return array('status'=>204, 'message'=> 'Speaker unfollowed successfully');
    }
  }
}
