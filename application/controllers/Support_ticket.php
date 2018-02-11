<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';
use \Firebase\JWT\JWT;
class Support_ticket extends REST_Controller {

  public function __construct() {
      parent::__construct();
      $this->load->model('Ticket_model', 'ticket');

  }


  // call this from the fetch post
  public function sign_up_data_open_post($param,$status){

    $this->load->helper('url');
    $list = $this->ticket->get_datatables($param,$status);
    $data = array();
    $no = $_POST['start'];
    foreach ($list as $ticket) {
        $no++;
        $row = array();
        $row[] = $ticket->userTicket;
        $row[] = $ticket->ticketMode;
        $row[] = $ticket->timeStamp;
        $row[] = '<a class="btn btn-sm btn-success" href="javascript:void(0)"><i class="fa fa-folder-open-o"></i>'. $ticket->status.'</a>';
        $row[] = $ticket->type;
        //$row[] = $ticket->pageVisits;
        //$row[] = $profile->user_email;

        //if( == 'MTN'):
        $row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_person('."'".$ticket->id."'".')"><i class="fa fa-edit"></i> </a>
                  <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="delete_person('."'".$ticket->id."'".')"><i class="fa fa-trash"></i> </a>';

        $data[] = $row;
    }

    $output = array(
        "draw" => $_POST['draw'],
        "recordsTotal" => $this->ticket->count_all($param,$status),
        "recordsFiltered" => $this->ticket->count_filtered($param, $status),
        "data" => $data,
    );
    //output to json format
    //echo json_encode($output);
    //header content type pass json
  return  $this->response($output, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
  //}
  }


  // call this from the fetch post
  public function sign_up_data_pending_post($param,$status){

    $this->load->helper('url');
    $list = $this->ticket->get_datatables($param,$status);
    $data = array();
    $no = $_POST['start'];
    foreach ($list as $ticket) {
        $no++;
        $row = array();
        $row[] = $ticket->userTicket;
        $row[] = $ticket->ticketMode;
        $row[] = $ticket->timeStamp;
        $row[] = '<a class="btn btn-sm btn-warning" href="javascript:void(0)"><i class="fa fa-pencil"></i>'. $ticket->status.'</a>';
        $row[] = $ticket->type;
        //$row[] = $ticket->pageVisits;
        //$row[] = $profile->user_email;

        //if( == 'MTN'):
        $row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_person('."'".$ticket->id."'".')"><i class="fa fa-edit"></i> </a>
                  <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="delete_person('."'".$ticket->id."'".')"><i class="fa fa-trash"></i> </a>';

        $data[] = $row;
    }

    $output = array(
        "draw" => $_POST['draw'],
        "recordsTotal" => $this->ticket->count_all($param,$status),
        "recordsFiltered" => $this->ticket->count_filtered($param, $status),
        "data" => $data,
    );
    //output to json format
    //echo json_encode($output);
    //header content type pass json
  return  $this->response($output, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
  //}
  }



  // call this from the fetch post
  public function sign_up_data_closed_post($param,$status){

    $this->load->helper('url');
    $list = $this->ticket->get_datatables($param,$status);
    $data = array();
    $no = $_POST['start'];
    foreach ($list as $ticket) {
        $no++;
        $row = array();
        $row[] = $ticket->userTicket;
        $row[] = $ticket->ticketMode;
        $row[] = $ticket->timeStamp;
        $row[] = '<a class="btn btn-sm btn-danger" href="javascript:void(0)"><i class="fa fa-folder"></i>'. $ticket->status.'</a>';
        $row[] = $ticket->type;
        //$row[] = $ticket->pageVisits;
        //$row[] = $profile->user_email;

        //if( == 'MTN'):
        $row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_person('."'".$ticket->id."'".')"><i class="fa fa-edit"></i> </a>
                  <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="delete_person('."'".$ticket->id."'".')"><i class="fa fa-trash"></i> </a>';

        $data[] = $row;
    }

    $output = array(
        "draw" => $_POST['draw'],
        "recordsTotal" => $this->ticket->count_all($param,$status),
        "recordsFiltered" => $this->ticket->count_filtered($param, $status),
        "data" => $data,
    );
    //output to json format
    //echo json_encode($output);
    //header content type pass json
  return  $this->response($output, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
  //}
  }










  public function enquiries_data_open_post($param, $status){

    $this->load->helper('url');
    $list = $this->ticket->get_datatables($param,$status);
    $data = array();
    $no = $_POST['start'];
    foreach ($list as $ticket) {
        $no++;
        $row = array();
        $row[] = $ticket->userTicket;
        $row[] = $ticket->ticketMode;
        $row[] = $ticket->timeStamp;
        $row[] = '<a class="btn btn-sm btn-success" href="javascript:void(0)"><i class="fa fa-folder-open-o"></i>'. $ticket->status.'</a>';
        $row[] = $ticket->type;
        //$row[] = $ticket->pageVisits;
        //$row[] = $profile->user_email;

        //if( == 'MTN'):
        $row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_person('."'".$ticket->id."'".')"><i class="fa fa-edit"></i> </a>
                  <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="delete_person('."'".$ticket->id."'".')"><i class="fa fa-trash"></i> </a>';

        $data[] = $row;
    }

    $output = array(
        "draw" => $_POST['draw'],
        "recordsTotal" => $this->ticket->count_all($param,$status),
        "recordsFiltered" => $this->ticket->count_filtered($param, $status),
        "data" => $data,
    );
    //output to json format
    //echo json_encode($output);
    //header content type pass json
  return  $this->response($output, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
  //}
  }

  public function enquiries_data_pending_post($param, $status){

    $this->load->helper('url');
    $list = $this->ticket->get_datatables($param,$status);
    $data = array();
    $no = $_POST['start'];
    foreach ($list as $ticket) {
        $no++;
        $row = array();
        $row[] = $ticket->userTicket;
        $row[] = $ticket->ticketMode;
        $row[] = $ticket->timeStamp;
        $row[] = '<a class="btn btn-sm btn-success" href="javascript:void(0)"><i class="fa fa-folder-open-o"></i>'. $ticket->status.'</a>';
        $row[] = $ticket->type;
        //$row[] = $ticket->pageVisits;
        //$row[] = $profile->user_email;

        //if( == 'MTN'):
        $row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_person('."'".$ticket->id."'".')"><i class="fa fa-edit"></i> </a>
                  <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="delete_person('."'".$ticket->id."'".')"><i class="fa fa-trash"></i> </a>';

        $data[] = $row;
    }

    $output = array(
        "draw" => $_POST['draw'],
        "recordsTotal" => $this->ticket->count_all($param,$status),
        "recordsFiltered" => $this->ticket->count_filtered($param, $status),
        "data" => $data,
    );
    //output to json format
    //echo json_encode($output);
    //header content type pass json
  return  $this->response($output, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
  //}

  }

  public function enquiries_data_closed_post($param, $status){

    $this->load->helper('url');
    $list = $this->ticket->get_datatables($param,$status);
    $data = array();
    $no = $_POST['start'];
    foreach ($list as $ticket) {
        $no++;
        $row = array();
        $row[] = $ticket->userTicket;
        $row[] = $ticket->ticketMode;
        $row[] = $ticket->timeStamp;
        $row[] = '<a class="btn btn-sm btn-success" href="javascript:void(0)"><i class="fa fa-folder-open-o"></i>'. $ticket->status.'</a>';
        $row[] = $ticket->type;
        //$row[] = $ticket->pageVisits;
        //$row[] = $profile->user_email;

        //if( == 'MTN'):
        $row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_person('."'".$ticket->id."'".')"><i class="fa fa-edit"></i> </a>
                  <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="delete_person('."'".$ticket->id."'".')"><i class="fa fa-trash"></i> </a>';

        $data[] = $row;
    }

    $output = array(
        "draw" => $_POST['draw'],
        "recordsTotal" => $this->ticket->count_all($param,$status),
        "recordsFiltered" => $this->ticket->count_filtered($param, $status),
        "data" => $data,
    );
    //output to json format
    //echo json_encode($output);
    //header content type pass json
  return  $this->response($output, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
  //}
  }




  public function complaint_data_open_post($param, $status){

    $this->load->helper('url');
    $list = $this->ticket->get_datatables($param,$status);
    $data = array();
    $no = $_POST['start'];
    foreach ($list as $ticket) {
        $no++;
        $row = array();
        $row[] = $ticket->userTicket;
        $row[] = $ticket->ticketMode;
        $row[] = $ticket->timeStamp;
        $row[] = '<a class="btn btn-sm btn-success" href="javascript:void(0)"><i class="fa fa-folder-open-o"></i>'. $ticket->status.'</a>';
        $row[] = $ticket->type;
        //$row[] = $ticket->pageVisits;
        //$row[] = $profile->user_email;

        //if( == 'MTN'):
        $row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_person('."'".$ticket->id."'".')"><i class="fa fa-edit"></i> </a>
                  <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="delete_person('."'".$ticket->id."'".')"><i class="fa fa-trash"></i> </a>';

        $data[] = $row;
    }

    $output = array(
        "draw" => $_POST['draw'],
        "recordsTotal" => $this->ticket->count_all($param,$status),
        "recordsFiltered" => $this->ticket->count_filtered($param, $status),
        "data" => $data,
    );
    //output to json format
    //echo json_encode($output);
    //header content type pass json
  return  $this->response($output, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
  //}

  }


  public function complaint_data_pending_post($param, $status){

    $this->load->helper('url');
    $list = $this->ticket->get_datatables($param,$status);
    $data = array();
    $no = $_POST['start'];
    foreach ($list as $ticket) {
        $no++;
        $row = array();
        $row[] = $ticket->userTicket;
        $row[] = $ticket->ticketMode;
        $row[] = $ticket->timeStamp;
        $row[] = '<a class="btn btn-sm btn-warning" href="javascript:void(0)"><i class="fa fa-pencil"></i>'. $ticket->status.'</a>';
        $row[] = $ticket->type;
        //$row[] = $ticket->pageVisits;
        //$row[] = $profile->user_email;

        //if( == 'MTN'):
        $row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_person('."'".$ticket->id."'".')"><i class="fa fa-edit"></i> </a>
                  <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="delete_person('."'".$ticket->id."'".')"><i class="fa fa-trash"></i> </a>';

        $data[] = $row;
    }

    $output = array(
        "draw" => $_POST['draw'],
        "recordsTotal" => $this->ticket->count_all($param,$status),
        "recordsFiltered" => $this->ticket->count_filtered($param, $status),
        "data" => $data,
    );
    //output to json format
    //echo json_encode($output);
    //header content type pass json
  return  $this->response($output, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
  //}

  }



  public function complaint_data_closed_post($param, $status){

    $this->load->helper('url');
    $list = $this->ticket->get_datatables($param,$status);
    $data = array();
    $no = $_POST['start'];
    foreach ($list as $ticket) {
        $no++;
        $row = array();
        $row[] = $ticket->userTicket;
        $row[] = $ticket->ticketMode;
        $row[] = $ticket->timeStamp;
        $row[] = '<a class="btn btn-sm btn-danger" href="javascript:void(0)"><i class="fa fa-folder"></i>'. $ticket->status.'</a>';
        $row[] = $ticket->type;
        //$row[] = $ticket->pageVisits;
        //$row[] = $profile->user_email;

        //if( == 'MTN'):
        $row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_person('."'".$ticket->id."'".')"><i class="fa fa-edit"></i> </a>
                  <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="delete_person('."'".$ticket->id."'".')"><i class="fa fa-trash"></i> </a>';

        $data[] = $row;
    }

    $output = array(
        "draw" => $_POST['draw'],
        "recordsTotal" => $this->ticket->count_all($param,$status),
        "recordsFiltered" => $this->ticket->count_filtered($param, $status),
        "data" => $data,
    );
    //output to json format
    //echo json_encode($output);
    //header content type pass json
  return  $this->response($output, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
  //}

  }






  public function payment_data_open_post($param, $status){

    $this->load->helper('url');
    $list = $this->ticket->get_datatables($param,$status);
    $data = array();
    $no = $_POST['start'];
    foreach ($list as $ticket) {
        $no++;
        $row = array();
        $row[] = $ticket->userTicket;
        $row[] = $ticket->ticketMode;
        $row[] = $ticket->timeStamp;
        $row[] = '<a class="btn btn-sm btn-success" href="javascript:void(0)"><i class="fa fa-folder-open-o"></i>'. $ticket->status.'</a>';
        $row[] = $ticket->type;
        //$row[] = $ticket->pageVisits;
        //$row[] = $profile->user_email;

        //if( == 'MTN'):
        $row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_person('."'".$ticket->id."'".')"><i class="fa fa-edit"></i> </a>
                  <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="delete_person('."'".$ticket->id."'".')"><i class="fa fa-trash"></i> </a>';

        $data[] = $row;
    }

    $output = array(
        "draw" => $_POST['draw'],
        "recordsTotal" => $this->ticket->count_all($param,$status),
        "recordsFiltered" => $this->ticket->count_filtered($param, $status),
        "data" => $data,
    );
    //output to json format
    //echo json_encode($output);
    //header content type pass json
  return  $this->response($output, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
  //}

  }


  public function payment_data_pending_post($param, $status){

    $this->load->helper('url');
    $list = $this->ticket->get_datatables($param,$status);
    $data = array();
    $no = $_POST['start'];
    foreach ($list as $ticket) {
        $no++;
        $row = array();
        $row[] = $ticket->userTicket;
        $row[] = $ticket->ticketMode;
        $row[] = $ticket->timeStamp;
        $row[] = '<a class="btn btn-sm btn-warning" href="javascript:void(0)"><i class="fa fa-pencil"></i>'. $ticket->status.'</a>';
        $row[] = $ticket->type;
        //$row[] = $ticket->pageVisits;
        //$row[] = $profile->user_email;

        //if( == 'MTN'):
        $row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_person('."'".$ticket->id."'".')"><i class="fa fa-edit"></i> </a>
                  <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="delete_person('."'".$ticket->id."'".')"><i class="fa fa-trash"></i> </a>';

        $data[] = $row;
    }

    $output = array(
        "draw" => $_POST['draw'],
        "recordsTotal" => $this->ticket->count_all($param,$status),
        "recordsFiltered" => $this->ticket->count_filtered($param, $status),
        "data" => $data,
    );
    //output to json format
    //echo json_encode($output);
    //header content type pass json
  return  $this->response($output, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
  //}

  }



  public function payment_data_closed_post($param, $status){

    $this->load->helper('url');
    $list = $this->ticket->get_datatables($param,$status);
    $data = array();
    $no = $_POST['start'];
    foreach ($list as $ticket) {
        $no++;
        $row = array();
        $row[] = $ticket->userTicket;
        $row[] = $ticket->ticketMode;
        $row[] = $ticket->timeStamp;
        $row[] = '<a class="btn btn-sm btn-danger" href="javascript:void(0)"><i class="fa fa-folder"></i>'. $ticket->status.'</a>';
        $row[] = $ticket->type;
        //$row[] = $ticket->pageVisits;
        //$row[] = $profile->user_email;

        //if( == 'MTN'):
        $row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_person('."'".$ticket->id."'".')"><i class="fa fa-edit"></i> </a>
                  <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="delete_person('."'".$ticket->id."'".')"><i class="fa fa-trash"></i> </a>';

        $data[] = $row;
    }

    $output = array(
        "draw" => $_POST['draw'],
        "recordsTotal" => $this->ticket->count_all($param,$status),
        "recordsFiltered" => $this->ticket->count_filtered($param, $status),
        "data" => $data,
    );
    //output to json format
    //echo json_encode($output);
    //header content type pass json
  return  $this->response($output, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
  //}

  }


 // public function test_get($param, $status){
 //   $list = $this->ticket->get_datatables($param,$status);
 //   return  $this->response($list, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
 //   //}
 // }

}
