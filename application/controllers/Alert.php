<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';
use \Firebase\JWT\JWT;

Class Alert  extends REST_Controller {
  public function __construct(){
    parent:: __construct();
  }

  public function alert_enable_put(){

  }

  private function time_line($day, $week, $month){

  }
}
