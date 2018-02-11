<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';
use \Firebase\JWT\JWT;

class Visit extends REST_Controller {

  public function __construct() {
      parent::__construct();
      $this->load->model('Database_model');
      $this->load->model('Sms_model');
  }

  // this gets all visitors
  public function data_get(){
          //here fetch all rows from the database
          $query  = $this->Database_model->all_visits();

          $id = $this->get('id');
          // If the id parameter doesn't exist return all the users

          if ($id === NULL)
          {
              // Check if the users data store contains users (in case the database result returns NULL)
              if ($query)
              {
                  // Set the response and exit
                  $this->response($query, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
              }
              else
              {
                  // Set the response and exit
                  $this->response([
                      'status' => FALSE,
                      'message' => 'Visitors not found'
                   ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
              }
          }



          $id = (int) $id;

          // Validate the id.
          if ($id <= 0)
          {
              // Invalid id, set the response and exit.
              $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
          }


          // Get the user from the array, using the id as key for retrieval.
          // Usually a model is to be used for this.
          $query = $this->Database_model->get_visit_id($id);
          if($query){
            $this->response($query, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
          }
          else
          {
              // Set the response and exit
              $this->response([
                  'status' => FALSE,
                  'message' => 'Visit id not found'
              ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
          }
    }

    //gets only direct visitors and their id
    public function direct_visit_get(){
      //here fetch all rows from the database
      $query  = $this->Database_model->direct_visit();

      $id = $this->get('id');
      // If the id parameter doesn't exist return all the users

      if ($id === NULL)
      {
          // Check if the users data store contains users (in case the database result returns NULL)
          if ($query)
          {
              // Set the response and exit
              $this->response($query, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
          }
          else
          {
              // Set the response and exit
              $this->response([
                  'status' => FALSE,
                  'message' => 'Direct visitors not found'
               ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
          }
      }



      $id = (int) $id;

      // Validate the id.
      if ($id <= 0)
      {
          // Invalid id, set the response and exit.
          $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
      }


      // Get the user from the array, using the id as key for retrieval.
      // Usually a model is to be used for this.
      $query = $this->Database_model->direct_visit_id($id);
      if($query){
        $this->response($query, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
      }
      else
      {
          // Set the response and exit
          $this->response([
              'status' => FALSE,
              'message' => 'Direct visitor id not found'
          ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
      }
    }

    //gets only SMS Link Visitors
    public function sms_visit_get(){
      //here fetch all rows from the database
      $query  = $this->Database_model->sms_visit();

      $id = $this->get('id');
      // If the id parameter doesn't exist return all the users

      if ($id === NULL)
      {
          // Check if the users data store contains users (in case the database result returns NULL)
          if ($query)
          {
              // Set the response and exit
              $this->response($query, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
          }
          else
          {
              // Set the response and exit
              $this->response([
                  'status' => FALSE,
                  'message' => 'Sms visitors not found'
               ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
          }
      }



      $id = (int) $id;

      // Validate the id.
      if ($id <= 0)
      {
          // Invalid id, set the response and exit.
          $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
      }


      // Get the user from the array, using the id as key for retrieval.
      // Usually a model is to be used for this.
      $query = $this->Database_model->sms_visit_id($id);
      if($query){
        $this->response($query, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
      }
      else
      {
          // Set the response and exit
          $this->response([
              'status' => FALSE,
              'message' => 'Sms visitor id not found'
          ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
      }
    }

    //gets only email Link visitors
    public function email_visit_get(){
      //here fetch all rows from the database
      $query  = $this->Database_model->email_visit();

      $id = $this->get('id');
      // If the id parameter doesn't exist return all the users

      if ($id === NULL)
      {
          // Check if the users data store contains users (in case the database result returns NULL)
          if ($query)
          {
              // Set the response and exit
              $this->response($query, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
          }
          else
          {
              // Set the response and exit
              $this->response([
                  'status' => FALSE,
                  'message' => 'Email visitors not found'
               ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
          }
      }



      $id = (int) $id;

      // Validate the id.
      if ($id <= 0)
      {
          // Invalid id, set the response and exit.
          $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
      }


      // Get the user from the array, using the id as key for retrieval.
      // Usually a model is to be used for this.
      $query = $this->Database_model->email_visit_id($id);
      if($query){
        $this->response($query, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
      }
      else
      {
          // Set the response and exit
          $this->response([
              'status' => FALSE,
              'message' => 'Email visitor id not found'
          ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
      }
    }
}
