<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';
use \Firebase\JWT\JWT;
class Page extends REST_Controller {

  public function __construct() {
      parent::__construct();
      $this->load->model('Database_model');
      $this->load->model('Group_model', 'group');
      $this->load->model('Page_visitors_model', 'pgx');
      $this->load->model('Page_ref_model', 'ref');

  }

  public function visitors_post($type = NULL){

    //each param will be based on type
    if($type == 'sms'){
      $this->sms_data('sms');
    }

    if($type == 'email'){
      $this->email_data();
    }

    if($type == 'direct'){

    }


  }


  public function sms_data_post($param){

    $this->load->helper('url');
    $list = $this->pgx->get_datatables($param);
    $data = array();
    $no = $_POST['start'];
    foreach ($list as $visit) {
        $no++;
        $row = array();
        $row[] = $visit->visitName;
        $row[] = $visit->visitNumber;
        $row[] = $visit->visitStamp;
        $row[] = $visit->ipAdd;
        $row[] = $visit->pageVisits;
        //$row[] = $profile->user_email;

        //if( == 'MTN'):
        $row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_person('."'".$visit->id."'".')"><i class="fa fa-edit"></i> </a>
                  <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="delete_person('."'".$visit->id."'".')"><i class="fa fa-trash"></i> </a>';

        $data[] = $row;
    }

    $output = array(
        "draw" => $_POST['draw'],
        "recordsTotal" => $this->pgx->count_all($param),
        "recordsFiltered" => $this->pgx->count_filtered($param),
        "data" => $data,
    );
    //output to json format
    //echo json_encode($output);
    //header content type pass json
  return  $this->response($output, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
  //}
  }




  public function email_data_post($param){

    $this->load->helper('url');
    $list = $this->pgx->get_datatables($param);
    $data = array();
    $no = $_POST['start'];
    foreach ($list as $visit) {
        $no++;
        $row = array();
        $row[] = $visit->visitName;
        $row[] = $visit->visitEmail;
        $row[] = $visit->visitStamp;
        $row[] = $visit->ipAdd;
        $row[] = $visit->pageVisits;
        //$row[] = $profile->user_email;

        //if( == 'MTN'):
        $row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_person('."'".$visit->id."'".')"><i class="fa fa-edit"></i> </a>
                  <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="delete_person('."'".$visit->id."'".')"><i class="fa fa-trash"></i> </a>';

        $data[] = $row;
    }

    $output = array(
        "draw" => $_POST['draw'],
        "recordsTotal" => $this->pgx->count_all($param),
        "recordsFiltered" => $this->pgx->count_filtered($param),
        "data" => $data,
    );
    //output to json format
    //echo json_encode($output);
    //header content type pass json
  return  $this->response($output, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code

  }





  public function direct_data_post($param){

    $this->load->helper('url');
    $list = $this->pgx->get_datatables($param);
    $data = array();
    $no = $_POST['start'];
    foreach ($list as $visit) {
        $no++;
        $row = array();
      //  $row[] = $visit->visitName;
        $row[] = $visit->visitDirect;
        $row[] = $visit->visitStamp;
        $row[] = $visit->ipAdd;
        $row[] = $visit->pageVisits;
        //$row[] = $profile->user_email;

        //if( == 'MTN'):
        $row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_person('."'".$visit->id."'".')"><i class="fa fa-edit"></i> </a>
                  <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="delete_person('."'".$visit->id."'".')"><i class="fa fa-trash"></i> </a>';

        $data[] = $row;
    }

    $output = array(
        "draw" => $_POST['draw'],
        "recordsTotal" => $this->pgx->count_all($param),
        "recordsFiltered" => $this->pgx->count_filtered($param),
        "data" => $data,
    );
    //output to json format
    //echo json_encode($output);
    //header content type pass json
  return  $this->response($output, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code

  }



  public function sms_ref_data_post($param){

      $this->load->helper('url');
      $list = $this->ref->get_datatables($param);
      $data = array();
      $no = $_POST['start'];
      foreach ($list as $referral) {
          $no++;
          $row = array();
          $row[] = $referral->refDetails;
          $row[] = $referral->refereeCon;
          $row[] = $referral->refStamp;
          $row[] = $referral->refTimes;
          //$row[] = $visit->pageVisits;
          //$row[] = $profile->user_email;

          //if( == 'MTN'):
          $row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_person('."'".$referral->id."'".')"><i class="fa fa-edit"></i> </a>
                    <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="delete_person('."'".$referral->id."'".')"><i class="fa fa-trash"></i> </a>';

          $data[] = $row;
      }

      $output = array(
          "draw" => $_POST['draw'],
          "recordsTotal" => $this->ref->count_all($param),
          "recordsFiltered" => $this->ref->count_filtered($param),
          "data" => $data,
      );
      //output to json format
      //echo json_encode($output);
      //header content type pass json
    return  $this->response($output, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    //}
  }
  




  public function email_ref_data_post($param){

    $this->load->helper('url');
    $list = $this->ref->get_datatables($param);
    $data = array();
    $no = $_POST['start'];
    foreach ($list as $referral) {
        $no++;
        $row = array();
        $row[] = $referral->refDetails;
        $row[] = $referral->refereeEmail;
        $row[] = $referral->refStamp;
        $row[] = $referral->refTimes;
        //$row[] = $visit->pageVisits;
        //$row[] = $profile->user_email;

        //if( == 'MTN'):
        $row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_person('."'".$referral->id."'".')"><i class="fa fa-edit"></i> </a>
                  <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="delete_person('."'".$referral->id."'".')"><i class="fa fa-trash"></i> </a>';

        $data[] = $row;
    }

    $output = array(
        "draw" => $_POST['draw'],
        "recordsTotal" => $this->ref->count_all($param),
        "recordsFiltered" => $this->ref->count_filtered($param),
        "data" => $data,
    );
    //output to json format
    //echo json_encode($output);
    //header content type pass json
  return  $this->response($output, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
  //}
  }
}
