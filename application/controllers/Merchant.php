<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';

use \Firebase\JWT\JWT;

Class Merchant extends REST_Controller{


  function __construct(){
    parent:: __construct();
    $this->load->model('MyModel');
    $this->load->model('PayModel', 'pay');
    $this->load->model('NotificationModel', 'notify');
  }

  function setPaymentDefault_post(){
    $response = $this->MyModel->merchant_auth();
    if($response['status']==200){
      $_POST = json_decode(file_get_contents('php://input'), TRUE);
        if($_POST['type'] == "momoMerchant"){
          $query = $this->pay->setMomoDefault($response['id'], $_POST);


          return false;
        }

        if($_POST['type'] == "bankMerchant"){
          $query = $this->pay->setBankDefault($response['id'], $_POST);

          return false;
        }
    }
    $this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
  }


  //sp here you should join from both tables
  function showAdded_payments_get(){
    $id = (int) $this->get('id');
    $response = $this->MyModel->merchant_auth();
    if($response['status']==200){
      $q['momoRes'] = $this->pay->get_checkMomoPayments($id);
      $q['bankRes'] = $this->pay->get_checkBankPayments($id);
      $this->response($q,REST_Controller::HTTP_OK);
    }
    $this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
  }


  function ghBanks_get(){
    $response = $this->MyModel->merchant_auth();
    if($response['status']==200){
        $q = $this->pay->get_ghBanks();
        $this->response($q,REST_Controller::HTTP_OK);
    }
    $this->response($response, REST_Controller::HTTP_NOT_FOUND);
  }


  function addBankChannel_post(){
    $response = $this->MyModel->merchant_auth();
    if($response['status']==200){
      $_POST = json_decode(file_get_contents('php://input'), TRUE);
      $data= array('success'=> false, 'messages' => array());
      $this->form_validation->set_rules('bankName', 'Bank Name', 'trim|required');
      $this->form_validation->set_rules('accountName', 'Account Name', 'trim|required');
      $this->form_validation->set_rules('accountNumber', 'Account Number', 'trim|required');
      $this->form_validation->set_rules('swiftCode', 'Swift Code / BAC', 'trim|required');
      $this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
      if ($this->form_validation->run() === FALSE){
          foreach($_POST as $key =>$value){
              $data['messages'][$key] = form_error($key);
          }
          $this->response($data, REST_Controller::HTTP_OK);
          return false;
      }
      $pData = array(
        'bankName'              =>    $_POST['bankName'],
        'bankAccountName'       =>    $_POST['accountName'],
        'bankAccountNumber'     =>    $_POST['accountNumber'],
        'swiftCode'             =>    $_POST['swiftCode'],
        'merchant_id'           =>    $response['id']
      );
      $q = $this->pay->add_Bank_details($response['id'], $pData);
      $this->response($q, REST_Controller::HTTP_OK);
      return false;
    }
    $this->response($response, REST_Controller::HTTP_NOT_FOUND);
  }



  function addMomoChannel_post(){
    $response = $this->MyModel->merchant_auth();
    if($response['status']==200){
      $_POST = json_decode(file_get_contents('php://input'), TRUE);
      $data= array('success'=> false, 'messages' => array());
      $this->form_validation->set_rules('network', 'Network', 'trim|required');
      $this->form_validation->set_rules('mobile', 'Mobile Number', 'trim|required');
      $this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
      if ($this->form_validation->run() === FALSE){
          foreach($_POST as $key =>$value){
              $data['messages'][$key] = form_error($key);
          }
          $this->response($data, REST_Controller::HTTP_OK);
          return false;
      }
      $q = $this->pay->add_Momo_details($response['id'], $_POST);
      if($q['status'] == 201){
        $this->response($q, REST_Controller::HTTP_CREATED);
        return false;
      }
      $this->response($q, REST_Controller::HTTP_NOT_FOUND);
      return false;
    }
    $this->response($response, REST_Controller::HTTP_NOT_FOUND);
  }

  //user will select at least of the payments added to receive payment
  function defaultPayChannel_post(){
    $response = $this->MyModel->merchant_auth();
    if($response['status']==200){
      $_POST = json_decode(file_get_contents('php://input'), TRUE);
      $data= array('success'=> false, 'messages' => array());
      $this->form_validation->set_rules('defaultPay', 'Default Channel', 'trim|required');
      $this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
      if ($this->form_validation->run() === FALSE){
          foreach($_POST as $key =>$value){
              $data['messages'][$key] = form_error($key);
          }
          $this->response($data, REST_Controller::HTTP_OK);
          return false;
      }
      $q = $this->pay->setDefaultPaymentMerchant($reponse['id'], $data);
      if($q['status'] ==201){
        $this->response($q, REST_Controller::HTTP_OK);
        return false;
      }
      $this->response($q, REST_Controller::HTTP_NOT_FOUND);
      return false;
    }
    $this->response($response, REST_Controller::HTTP_NOT_FOUND);
  }



  function bankDetails_delete(){
    $response = $this->MyModel->merchant_auth();
    if($response['status']==200){
        $q = $this->pay->deleteBankData($response['id']);
        if($q['status'] == 202){
          $this->response($q, REST_Controller::HTTP_ACCEPTED);
          return false;
        }
        $this->response($q,REST_Controller::HTTP_NOT_FOUND);
        return false;
    }
    $this->response($response, REST_Controller::HTTP_NOT_FOUND);
  }

  function momoDetails_delete(){
    $response = $this->MyModel->merchant_auth();
    if($response['status']==200){
      $q = $this->pay->deleteMomoData($response['id']);
      if($q['status'] == 202){
        $this->response($q, REST_Controller::HTTP_ACCEPTED);
        return false;
      }
      $this->response($q,REST_Controller::HTTP_NOT_FOUND);
      return false;
    }
    $this->response($response, REST_Controller::HTTP_NOT_FOUND);
  }

  //this should be part of the data  that has to be run
  function defaultPaychannel_get(){
    $response = $this->MyModel->merchant_auth();
    if($response['status']==200){
      $q = $this->pay->getMerchantDefault($response['id']);
      if($q['status'] == 200){
        $this->response($q, REST_Controller::HTTP_OK);
        return false;
      }
      $this->response($q, REST_Controller::HTTP_NOT_FOUND);
      return false;
    }
    $this->response($response, REST_Controller::HTTP_NOT_FOUND);
  }


  function all_notification_get(){
    $response = $this->MyModel->merchant_auth();
    if($response['status']==200){
      $q = $this->notify->getNotificationStatus($response['id']);
      if($q == false){
        $message = array('status'=> 204, 'message'=> 'all notifications are turned off');
        $this->response($message, REST_Controller::HTTP_NO_CONTENT);
        return false;
      }
      $this->response($q, REST_Controller::HTTP_OK);
      return false;
    }
    $this->response($response, REST_Controller::HTTP_NOT_FOUND);
  }


  function setAppNotify_get(){
    $id = (int) $this->get('id');
    $response = $this->MyModel->merchant_auth();
    if($response['status']==200){
      if($id == 0){
        $q = $this->notify->setNotificationApp($response['id'], $id);
        if($q['status']==201){
          $mess = array('status'=>201, 'message'=>'Notification disabled successfully');
          $this->response($mess, REST_Controller::HTTP_CREATED);
          return false;
        }
        $this->response($q, REST_Controller::HTTP_OK);
        return false;
      }
      $q = $this->notify->setNotificationApp($response['id'], $id);
      if($q['status']==201){
        $mess = array('status'=>201, 'message'=>'Notification enabled successfully');
        $this->response($mess, REST_Controller::HTTP_CREATED);
        return false;
      }
      $this->response($q, REST_Controller::HTTP_OK);
      return false;
    }
    $this->response($response, REST_Controller::HTTP_NOT_FOUND);
  }

  function setEmailNotify_get(){
    $id = (int) $this->get('id');
    $response = $this->MyModel->merchant_auth();
    if($response['status']==200){
      if($id == 0){
        $q = $this->notify->setNotificationEmail($response['id'], $id);
        if($q['status']==201){
          $mess = array('status'=>201, 'message'=>'Notification disabled successfully');
          $this->response($mess, REST_Controller::HTTP_CREATED);
          return false;
        }
        $this->response($q, REST_Controller::HTTP_OK);
        return false;
      }
      $q = $this->notify->setNotificationEmail($response['id'], $id);
      if($q['status']==201){
        $mess = array('status'=>201, 'message'=>'Notification enabled successfully');
        $this->response($mess, REST_Controller::HTTP_CREATED);
        return false;
      }
      $this->response($q, REST_Controller::HTTP_OK);
      return false;
    }
    $this->response($response, REST_Controller::HTTP_NOT_FOUND);
  }

  function setPushNotify_get(){
    $id = (int) $this->get('id');
    $response = $this->MyModel->merchant_auth();
    if($response['status']==200){
      if($id == 0){
        $q = $this->notify->setPushNotification($response['id'], $id);
        if($q['status']==201){
          $mess = array('status'=>201, 'message'=>'Notification disabled successfully');
          $this->response($mess, REST_Controller::HTTP_CREATED);
          return false;
        }
        $this->response($q, REST_Controller::HTTP_OK);
        return false;
      }
      $q = $this->notify->setPushNotification($response['id'], $id);
      if($q['status']==201){
        $mess = array('status'=>201, 'message'=>'Notification enabled successfully');
        $this->response($mess, REST_Controller::HTTP_CREATED);
        return false;
      }
      $this->response($q, REST_Controller::HTTP_OK);
      return false;
    }
    $this->response($response, REST_Controller::HTTP_NOT_FOUND);
  }

  function setPurchaseNotify_get(){
    $id = (int) $this->get('id');
    $response = $this->MyModel->merchant_auth();
    if($response['status']==200){
      if($id == 0){
        $q = $this->notify->setPurchaseNotification($response['id'], $id);
        if($q['status']==201){
          $mess = array('status'=>201, 'message'=>'Notification disabled successfully');
          $this->response($mess, REST_Controller::HTTP_CREATED);
          return false;
        }
        $this->response($q, REST_Controller::HTTP_OK);
        return false;
      }
      $q = $this->notify->setPurchaseNotification($response['id'], $id);
      if($q['status']==201){
        $mess = array('status'=>201, 'message'=>'Notification enabled successfully');
        $this->response($mess, REST_Controller::HTTP_CREATED);
        return false;
      }
      $this->response($q, REST_Controller::HTTP_OK);
      return false;
    }
    $this->response($response, REST_Controller::HTTP_NOT_FOUND);
  }

  function setCommentNotify_get(){
    $id = (int) $this->get('id');
    $response = $this->MyModel->merchant_auth();
    if($response['status']==200){
      if($id == 0){
        $q = $this->notify->setCommentNotification($response['id'], $id);
        if($q['status']==201){
          $mess = array('status'=>201, 'message'=>'Notification disabled successfully');
          $this->response($mess, REST_Controller::HTTP_CREATED);
          return false;
        }
        $this->response($q, REST_Controller::HTTP_OK);
        return false;
      }
      $q = $this->notify->setCommentNotification($response['id'], $id);
      if($q['status']==201){
        $mess = array('status'=>201, 'message'=>'Notification enabled successfully');
        $this->response($mess, REST_Controller::HTTP_CREATED);
        return false;
      }
      $this->response($q, REST_Controller::HTTP_OK);
      return false;
    }
    $this->response($response, REST_Controller::HTTP_NOT_FOUND);
  }


}//end of class
