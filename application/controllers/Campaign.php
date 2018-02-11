<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';
use \Firebase\JWT\JWT;

Class Campaign extends REST_Controller {

  public function __construct(){
    parent::__construct();
    $this->load->model('Campaign_model', 'cam');
    $this->load->model('Email_model', 'em');


  }


  //this api is to fetch sms campaign logs
  public function sms_list_post(){

    $this->load->helper('url');
    $list = $this->cam->get_datatables();
    $data = array();
    $no = $_POST['start'];
    foreach ($list as $campaign) {
        $no++;
        $row = array();
        $row[] = $campaign->logName;
        $row[] = $campaign->group;
        $row[] = $campaign->LogMessage;
        $row[] = $campaign->logStamp;

        //$row[] = $profile->id_number;
        //$row[] = $profile->user_email;

        //if( == 'MTN'):
        //$row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_person('."'".$campaign->id."'".')"><i class="fa fa-edit"></i> </a>
        //<a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="delete_person('."'".$campaign->id."'".')"><i class="fa fa-trash"></i> </a>';

        $data[] = $row;
    }

    $output = array(
        "draw" => $_POST['draw'],
        "recordsTotal" => $this->cam->count_all(),
        "recordsFiltered" => $this->cam->count_filtered(),
        "data" => $data,
    );
    //output to json format
    //echo json_encode($output);
    //header content type pass json
    $this->response($output, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
  }


  //this api is to fetch sms campaign logs
  public function email_list_post(){

    $this->load->helper('url');
    $list = $this->em->get_datatables();
    $data = array();
    $no = $_POST['start'];
    foreach ($list as $em) {
        $no++;
        $row = array();
        $row[] = $em->LogName;
        $row[] = $em->group;
        $row[] = $em->emailMessage;
        //$row[] = $em->emailAdd;

        $row[] = $em->emailTime;


        //$row[] = $profile->user_email;

        //if( == 'MTN'):
        //$row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_person('."'".$campaign->id."'".')"><i class="fa fa-edit"></i> </a>
        //<a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="delete_person('."'".$campaign->id."'".')"><i class="fa fa-trash"></i> </a>';

        $data[] = $row;
    }

    $output = array(
        "draw" => $_POST['draw'],
        "recordsTotal" => $this->em->count_all(),
        "recordsFiltered" => $this->em->count_filtered(),
        "data" => $data,
    );
    //output to json format
    //echo json_encode($output);
    //header content type pass json
    $this->response($output, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
  }


}
