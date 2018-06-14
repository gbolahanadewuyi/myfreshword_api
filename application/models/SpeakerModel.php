<?php  defined('BASEPATH') OR exit('No direct script access allowed');

Class SpeakerModel extends CI_Model {

  protected $speakerTable = "speakers";

  function __construct(){
    parent:: __construct();
  }

  function get_speaker_data(){
    $q = $this->db->select('*')->from($this->speakerTable)->get()->result();
    if($q == true){
      return array('status'=>200, 'result'=>$q);
    }
    return array('status'=>204, 'message'=> 'No Content found');
  }

  function add_to_list($data){
    $q =$this->db->insert($this->speakerTable, $data);
  }
}
