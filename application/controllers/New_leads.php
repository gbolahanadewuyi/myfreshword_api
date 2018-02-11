<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';
use \Firebase\JWT\JWT;

Class New_leads extends REST_Controller {

    public function __construct(){
      parent::__construct();
      $this->load->model('Database_model');
      $this->load->model('PhoneNumber_model', 'contact');
      $this->load->model('Emailadd_model', 'app');
    }


    public function ajax_list_post(){

      $this->load->helper('url');
      $list = $this->contact->get_datatables();
      $data = array();
      $no = $_POST['start'];
      foreach ($list as $contact) {
          $no++;
          $row = array();
          $row[] = $contact->mobile;
          $row[] = $contact->name;
          //$row[] = $contact->email;
          $row[] = $contact->group;
          //$row[] = $profile->id_number;
          //$row[] = $profile->user_email;

          //if( == 'MTN'):
          $row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_person('."'".$contact->id."'".')"><i class="fa fa-edit"></i> </a>
                    <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="delete_person('."'".$contact->id."'".')"><i class="fa fa-trash"></i> </a>';

          $data[] = $row;
      }

      $output = array(
          "draw" => $_POST['draw'],
          "recordsTotal" => $this->contact->count_all(),
          "recordsFiltered" => $this->contact->count_filtered(),
          "data" => $data,
      );
      //output to json format
      //echo json_encode($output);
      //header content type pass json
      $this->response($output, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }


    public function email_list_post(){

      $this->load->helper('url');
      $list = $this->app->get_datatables();
      $data = array();
      $no = $_POST['start'];
      foreach ($list as $app) {
          $no++;
          $row = array();
          $row[] = $app->email;
          $row[] = $app->name;
          //$row[] = $contact->email;
          $row[] = $app->group;
          //$row[] = $profile->id_number;
          //$row[] = $profile->user_email;

          //if( == 'MTN'):
          $row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_person('."'".$app->id."'".')"><i class="fa fa-edit"></i> </a>
                    <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="delete_person('."'".$app->id."'".')"><i class="fa fa-trash"></i> </a>';

          $data[] = $row;
      }

      $output = array(
          "draw" => $_POST['draw'],
          "recordsTotal" => $this->app->count_all(),
          "recordsFiltered" => $this->app->count_filtered(),
          "data" => $data,
      );
      //output to json format
      //echo json_encode($output);
      //header content type pass json
      $this->response($output, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }


}
