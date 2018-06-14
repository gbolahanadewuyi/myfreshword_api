<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';

use \Firebase\JWT\JWT;

Class File extends REST_Controller{

  function __construct(){
    parent:: __construct();
    $this->load->model('FileModel', 'fi');
  }

  function index_get(){
    $q = $this->fi->get_file_category();
    if($q['status'] ==204){
      $this->response($q, REST_Controller::HTTP_NO_CONTENT);
      return false;
    }
    $this->response($q, REST_Controller::HTTP_OK);
  }

}
