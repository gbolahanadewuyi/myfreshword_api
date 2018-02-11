<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';
use \Firebase\JWT\JWT;

Class Ticket  extends REST_Controller {
  public function __construct(){
    parent:: __construct();
    $this->load->model('Database_model');
    $this->load->model('Sms_model');
    $this->load->model('Transaction_model');
  }

  //here were are going to get all tickets
  public function data_get(){
        //here fetch all rows from the database
        $query  = $this->Transaction_model->get_all_ticket_type();

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
                    'message' => 'Tickets not found'
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
        $query = $this->Transaction_model->get_ticket_by_id($id);
        if($query){
          $this->response($query, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
        else
        {
            // Set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'Ticket id not found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
  }

  public function signup_ticket_get(){
      //here fetch all rows from the database
      $query  = $this->Transaction_model->get_signup_ticket();

      $id = $this->get('id'); //we should  pass this through the json body
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
                  'message' => 'SignUp Tickets not found'
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
      $query = $this->Transaction_model->get_signup_ticket_by_id($id);
      if($query){
        $this->response($query, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
      }
      else
      {
          // Set the response and exit
          $this->response([
              'status' => FALSE,
              'message' => 'SignUp Ticket id not found'
          ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
      }
  }

  public function payment_ticket_get(){
    //here fetch all rows from the database
    $query  = $this->Transaction_model->get_payment_ticket();

    $id = $this->get('id'); //we should  pass this through the json body
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
                'message' => 'Payment Tickets not found'
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
    $query = $this->Transaction_model->get_payment_ticket_by_id($id);
    if($query){
      $this->response($query, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }
    else
    {
        // Set the response and exit
        $this->response([
            'status' => FALSE,
            'message' => 'Payment Ticket id not found'
        ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
    }
  }



  public function complaint_ticket_get(){
    //here fetch all rows from the database
    $query  = $this->Transaction_model->get_complaint_ticket();

    $id = $this->get('id'); //we should  pass this through the json body
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
                'message' => 'Complaint Tickets not found'
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
    $query = $this->Transaction_model->get_complaint_ticket_by_id($id);
    if($query){
      $this->response($query, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }
    else
    {
        // Set the response and exit
        $this->response([
            'status' => FALSE,
            'message' => 'Complaint Ticket id not found'
        ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
    }
  }

  public function enquiry_ticket_get(){
    //here fetch all rows from the database
    $query  = $this->Transaction_model->get_enquiry_ticket();

    $id = $this->get('id'); //we should  pass this through the json body
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
                'message' => 'Enquiry Tickets not found'
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
    $query = $this->Transaction_model->get_enquiry_ticket_by_id($id);
    if($query){
      $this->response($query, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }
    else
    {
        // Set the response and exit
        $this->response([
            'status' => FALSE,
            'message' => 'Enquiry Ticket id not found'
        ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
    }
  }


  public function data_ticket_post(){
        //so basically we are going to check jsonbody to show which type of ticket to insert
        $_POST = json_decode(file_get_contents('php://input'), TRUE);

          //here we begin running validation here
          $data= array('success'=> false, 'messages' => array());
          $this->form_validation->set_rules('ticket_code', 'Ticket Number', 'required');
          $this->form_validation->set_rules('created_by', 'Created By', 'required');
          $this->form_validation->set_rules('cust_name', 'Customer Name', 'required');
          $this->form_validation->set_rules('cust_mobile', 'Customer Mobile', 'required');
          $this->form_validation->set_rules('ticket_status', 'Status', 'required');
          $this->form_validation->set_rules('ticket_package', 'Package', 'required');
          $this->form_validation->set_rules('ticket_type', 'Type', 'required');
          $this->form_validation->set_rules('ticket_comment', 'Comment', 'required');
          $this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');

            if($this->form_validation->run() === FALSE)
            {
                //if validation returns false
                foreach($_POST as $key =>$value)
                {
                    $data['messages'][$key] = form_error($key);
                }
            }else{


                    $dataSend = [
                      'userTicket'    =>  $_POST['ticket_code'],
                      'ticketMode'    =>  $_POST['created_by'],
                      'custName'      =>  $_POST['cust_name'],
                      'custNumber'    =>  $_POST['cust_mobile'],
                      'status'        =>  $_POST['ticket_status'],
                      'type'          =>  $_POST['ticket_type'],
                      'comments'      =>  $_POST['ticket_comment']
                    ];
                    //run insert table hebrev

                    $data['success']  = true;
                    $this->Transaction_model->create_ticket($dataSend);

            }

      $this->response($data, REST_Controller::HTTP_OK);

  }



      public function payment_data_post(){

          $_POST = json_decode(file_get_contents('php://input'), TRUE);

          $data= array('success'=> false, 'messages' => array());
          $this->form_validation->set_rules('ticket_code', 'Ticket Number', 'required');
          $this->form_validation->set_rules('created_by', 'Created By', 'required');
          $this->form_validation->set_rules('cust_name', 'Customer Name', 'required');
          $this->form_validation->set_rules('cust_mobile', 'Customer Mobile', 'required');
          $this->form_validation->set_rules('ticket_status', 'Status', 'required');
          $this->form_validation->set_rules('ticket_package', 'Package', 'required');
          $this->form_validation->set_rules('ticket_type', 'Type', 'required');
          $this->form_validation->set_rules('amount', 'Amount Paid', 'required');
          $this->form_validation->set_rules('ticket_comment', 'Comment', 'required');
          $this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
          if($this->form_validation->run() === FALSE)
          {
              //if validation returns false
              foreach($_POST as $key =>$value)
              {
                  $data['messages'][$key] = form_error($key);
              }
          }else{

                  $dataSend = [
                    'userTicket'    =>  $_POST['ticket_code'],//ticket will be generated from the frontend
                    'ticketMode'    =>  $_POST['created_by'],
                    'custName'      =>  $_POST['cust_name'],
                    'custNumber'    =>  $_POST['cust_mobile'],
                    'status'        =>  $_POST['ticket_status'],
                    'type'          =>  $_POST['ticket_type'],
                    'payAmount'     =>  $_POST['amount'],
                    'comments'      =>  $_POST['ticket_comment']
                  ];
                  //run insert table hebrev

                  $data['success']  = true;
                  $this->Transaction_model->create_ticket($dataSend);

          }
          $this->response($data, REST_Controller::HTTP_OK);

    }




      public function data_ticket_put(){//here if user wants to update by ticket by passing the id as well

        $id = $this->get('id');

        $_POST = json_decode(file_get_contents('php://input'), TRUE);
        //echo json_encode($_POST);
      //  so if type is a payment ticket do the following
        if($_POST['ticket_type'] == "payment"){
          $data = [
            'userTicket'    =>  $_POST['ticket_code'],
            'ticketMode'    =>  $_POST['created_by'],
            'custName'      =>  $_POST['cust_name'],
            'custNumber'    =>  $_POST['cust_mobile'],
            'status'        =>  $_POST['ticket_status'],
            'type'          =>  $_POST['ticket_type'],
            'payAmount'     =>  $_POST['amount'],
            'comments'      =>  $_POST['ticket_comment']
          ];
        } else {
          $data = [
            'userTicket'    =>  $_POST['ticket_code'],
            'ticketMode'    =>  $_POST['created_by'],
            'custName'      =>  $_POST['cust_name'],
            'custNumber'    =>  $_POST['cust_mobile'],
            'status'        =>  $_POST['ticket_status'],
            'type'          =>  $_POST['ticket_type'],
            //'payAmount'     =>  $_POST['amount'],
            'comments'      =>  $_POST['ticket_comment']
          ];
        }

        // $data = [
        //
        //   'userTicket'    =>  $_POST['ticket_code'],
        //   'ticketMode'    =>  $_POST['created_by'],
        //   'custName'      =>  $_POST['cust_name'],
        //   'custNumber'    =>  $_POST['cust_mobile'],
        //   'status'        =>  $_POST['ticket_status'],
        //   'type'          =>  $_POST['ticket_type'],
        //   'payAmount'     =>  $_POST['amount'],
        //   'comments'      =>  $_POST['ticket_comment']
        //
        // ];

        //echo json_encode($data);
        //now we check if the id exist first before running it through the Database
        //$query = $this->Database_model->check_id($id);


        if ($id != NULL)//if id is present
        {
           $query = $this->Transaction_model->update_ticket_id($id,$data);
            // this is where we run the sms model to send details the customers phone number
            
            // Check if the users data store contains users (in case the database result returns NULL)
            if ($query === TRUE)

                // Set the response and exit
                $this->response([
                    'status' => TRUE,
                    'message' => 'Ticket Update Success'
                ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }

            else
            {
                // Set the response and exit
                $this->response([
                    'status' => FALSE,
                    'message' => 'Ticket Update failed'
                ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
            }
          }

}
