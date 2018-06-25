<?php defined('BASEPATH') OR exit('No direct script access allowed');


Class Notifications extends CI_Controller {


    function __construct(){
      parent:: __construct();
      $this->load->model('MyModel');
      $this->load->model('NotificationModel', 'note');
    }



    //here we check if id exist if not then return all the  recent notification messages
    function index_get(){
      $response = $this->MyModel->merchant_auth();
      if($response['status']==200){
        $id = $this->get('id');//serves as the primary key
        $id = (int) $id;

        if($id == ""){
          $q = $this->note->get_all_notifications($response['id']);
          if($q['status'] == 200){
            $this->response($q, REST_Controller::HTTP_OK);
            return false;
          }
          $this->reponse($q, REST_Controller::HTTP_NOT_FOUND);
          return false;
        }
        $b = $this->note->get_notification_id($response['id'], $id);
        if($b['status'] == 200){
          $this->response($b, REST_Controller::HTTP_OK);
          return false;
        }
        $this->response($b, REST_Controller::HTTP_NOT_FOUND);
        return false;
      }
      $this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
    }











}
