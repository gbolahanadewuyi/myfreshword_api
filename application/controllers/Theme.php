<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';

use \Firebase\JWT\JWT;

Class Theme extends REST_Controller{

  function __construct(){
    parent:: __construct();
    $this->load->model('ThemeModel', 'th');
    $this->load->model('MyModel', 'my');
  }



  //these are the product topics
  function index_get(){

      $q = $this->th->get_themes_data();
      if($q['status'] ==204){
        $this->response($q, REST_Controller::HTTP_NO_CONTENT);
        return false;
      }
      $this->response($q, REST_Controller::HTTP_OK);
  }

}
