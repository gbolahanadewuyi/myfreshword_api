<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';

use \Firebase\JWT\JWT;

Class Image extends REST_Controller{

  public function __construct(){
    parent:: __construct();
  }

  public function Home_resize_post(){

  }

  public function Feed_resize_post(){

  }

  public function author_followers_resize_post(){

  }

  public function library_resize_post(){

  }

  
}
