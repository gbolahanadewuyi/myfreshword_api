<?php defined('BASEPATH') OR exit('No direct script access allowed');

 Class Database_model extends CI_Model {

    public function __construct(){
      parent:: __construct();
    }


    /*
    *Crud for Groups Model
    *Fetch all from database
    *Fetch by id from Database
    *Delete by id from Database
    *Code will be repeated for all the crud models on IDMA
    */

    public function all_groups(){
      return $this->db->select()->from('groups')->order_by('id','desc')->get()->result();
    }

    public function new_group($data= array()){
      $this->db->insert('groups', $data);
      return true;
    }

    public function get_group_id($id){
      return $this->db->select()->from('groups')->where('id',$id)->order_by('id','desc')->get()->row();
    }

    public function update_group_id($id,$data){
      $this->db->where('id',$id)->update('groups',$data);
      return true;
    }

    public function delete_group_id($id){
       $this->db->where('id',$id)->delete('groups');
       return true;
    }

    public function check_group_id($id){
      return $ql = $this->db->select('id')->from('groups')->where('id',$id)->get();
    }

    /*
    *Crud for Contacts Model
    *Fetch all from database
    *Fetch by id from Database
    *Delete by id from Database
    *Code will be repeated for all the crud models on IDMA
    */
    public function all_contacts(){
      return $this->db->select()->from('info_data')->order_by('id','desc')->get()->result();
    }

    public function new_contact($data= array()){
      $this->db->insert('info_data', $data);
      return true;
    }

    public function get_contact_id($id){
      return $this->db->select()->from('info_data')->where('id',$id)->order_by('id','desc')->get()->row();
    }

    public function update_contact_id($id,$data){
      $this->db->where('id',$id)->update('info_data',$data);
      return true;
    }

    public function delete_contact_id($id){
       $this->db->where('id',$id)->delete('info_data');
       return true;
    }

    public function check_contact_id($id){
      return $ql = $this->db->select('id')->from('info_data')->where('id',$id)->get();
    }


    /*
    *Crud for SMS Model
    *Fetch all from database
    *Fetch by id from Database
    *Delete by id from Database
    *Code will be repeated for all the crud models on IDMA
    */
    public function select_group($data = array()){
      return  $this->db->select('mobile')->from('info_data')->where_in('group', $data)->order_by('id','desc')->get()->result();//this will fetch just phone numbers from the group
    }

    public function select_email_group($data = array()){
      return  $this->db->select('email')->from('info_data')->where_in('group', $data)->order_by('id','desc')->get()->result();//this will fetch just phone numbers from the group
    }


    //run loop for this function since multiple inserts will go into the database
    public function create_sms_campaign($data){
      $query = $this->db->insert('sms_logs', $data);
      if($query){
        return TRUE;
      } else {
        return FALSE;
      }
    }


    //visitors model
    public function all_visits(){
      return $this->db->select()->from('visit')->order_by('id','desc')->get()->result();
    }

    public function get_visit_id($id){
      return $this->db->select()->from('visit')->where('id',$id)->order_by('id','desc')->get()->row();
    }

    // "id": "162",
    // "visitNumber": "",
    // "visitName": "",
    // "visitStamp": "2017-03-18 23:24:17",
    // "ipAdd": "66.249.93.138",
    // "pageVisits": "2",
    // "visitEmail": "",
    // "visitDirect": "user253988"

    //direct visitors models
    public function direct_visit(){
      $val = '';
      return $this->db->select('id, visitDirect, visitStamp, ipAdd, pageVisits')->from('visit')->where('visitDirect !=', $val)->order_by('id','desc')->get()->result();
    }

    public function direct_visit_id($id){
      $val = '';
      return $this->db->select('id, visitDirect, visitStamp, ipAdd, pageVisits')->from('visit')->where('id',$id)->where('visitDirect !=', $val)->order_by('id','desc')->get()->row();
    }

    //sms visitors Model
    public function sms_visit(){
      $val = '';
      return $this->db->select('id, visitName, visitNumber, visitStamp, ipAdd, pageVisits')->from('visit')->where('visitNumber !=', $val)->order_by('id','desc')->get()->result();
    }

    public function sms_visit_id($id){
      $val = '';
      return $this->db->select('id, visitName, visitNumber, visitStamp, ipAdd, pageVisits')->from('visit')->where('id',$id)->where('visitNumber !=', $val)->order_by('id','desc')->get()->row();
    }

    //email visitors model
    public function email_visit(){
      $val = '';
      return $this->db->select('id, visitName, visitEmail, visitStamp, ipAdd, pageVisits')->from('visit')->where('visitEmail !=', $val)->order_by('id','desc')->get()->result();
    }

    public function email_visit_id($id){
      $val = '';
      return $this->db->select('id, visitName, visitEmail, visitStamp, ipAdd, pageVisits')->from('visit')->where('id',$id)->where('visitEmail !=', $val)->order_by('id','desc')->get()->row();
    }

    //referral model
    public function all_referrals(){
      return $this->db->select()->from('referrals')->order_by('id','desc')->get()->result();
    }

    public function get_referral_id($id){
      return $this->db->select()->from('referrals')->where('id',$id)->order_by('id','desc')->get()->row();
    }

    //referral model email
    public function all_email_referrals(){
      $val = '';
      return $this->db->select('id, refDetails, refereeEmail, refStamp, refTimes')->from('referrals')->where('refereeEmail !=', $val)->order_by('id','desc')->get()->result();
    }

    public function get_email_referral_id($id){
      $val = '';
      return $this->db->select('id, refDetails, refereeEmail, refStamp, refTimes')->from('referrals')->where('id',$id)->where('refereeEmail !=', $val)->order_by('id','desc')->get()->row();
    }

    //sms referral models
    public function all_sms_referrals(){
      $val = '';
      return $this->db->select('id, refDetails, refereeCon, refStamp, refTimes')->from('referrals')->where('refereeCon !=', $val)->order_by('id','desc')->get()->result();
    }

    public function get_sms_referral_id($id){
      $val = '';
      return $this->db->select('id, refDetails, refereeCon, refStamp, refTimes')->from('referrals')->where('id',$id)->where('refereeCon !=', $val)->order_by('id','desc')->get()->row();
    }

    //insert api_keys
    public function initiate_key($data){
      return $this->db->insert('con_key', $data);
    }


    public function check_api($api_key){
      return $this->db->select()->from('con_key')->where('api_key',$api_key)->order_by('id','desc')->get()->row();
    }

    /*
    // Here we loading just the data from the page visitors and referrals
    */
    // public function sms_visit(){
    //   return $this->db->select('id, visitNumber, visitName, visitStamp, ipAdd, pageVisits')->from('visit')->order_by('id','desc')->get()->result();
    // }
    //
    // public function email_visit(){
    //   return $this->db->select('id, visitEmail, visitName, visitStamp, ipAdd, pageVisits')->from('visit')->order_by('id','desc')->get()->result();
    // }
    //
    // public function  direct_visit(){
    //   return $this->db->select('id, visitDirect, visitEmail, visitStamp, ipAdd, pageVisits')->from('visit')->order_by('id','desc')->get()->result();
    // }


    //this is fetch data from the referral page
    public function sms_ref(){

    }

    public function email_ref(){

    }

    public function  direct_ref(){

    }
 }
