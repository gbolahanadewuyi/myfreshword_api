<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';

use \Firebase\JWT\JWT;

Class Transactions extends REST_Controller{

   function __construct(){
     parent:: __construct();
     //$this->load->model();
     $this->load->model('TransModel', 'trans');
 }

}
