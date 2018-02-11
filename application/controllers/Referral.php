<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';
use \Firebase\JWT\JWT;

class Referral extends REST_Controller {

  public function __construct() {
      parent::__construct();
      $this->load->model('Database_model');
      $this->load->model('Sms_model');
  }

  public function data_get(){

      //here fetch all rows from the database
      $query  = $this->Database_model->all_referrals();

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
                  'message' => 'Referrals not found'
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
      $query = $this->Database_model->get_referral_id($id);
      if($query){
        $this->response($query, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
      }
      else
      {
          // Set the response and exit
          $this->response([
              'status' => FALSE,
              'message' => 'Referral id not found'
          ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
      }
  }

  public function data_email_get(){
        //here fetch all rows from the database
        $query  = $this->Database_model->all_email_referrals();

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
                    'message' => 'Email Referrals not found'
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
        $query = $this->Database_model->get_email_referral_id($id);
        if($query){
          $this->response($query, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
        else
        {
            // Set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'Referral email id not found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }


  public function data_sms_get(){
    //here fetch all rows from the database
    $query  = $this->Database_model->all_sms_referrals();

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
                'message' => '`Sms Referrals not found'
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
    $query = $this->Database_model->get_sms_referral_id($id);
    if($query){
      $this->response($query, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }
    else
    {
        // Set the response and exit
        $this->response([
            'status' => FALSE,
            'message' => 'Referral Sms id not found'
        ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
    }
  }

}
