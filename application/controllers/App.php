<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';
//require_once APPPATH . '/libraries/HubtelApi.php';

use \Firebase\JWT\JWT;

class App extends REST_Controller {


  public function __construct() {
      parent::__construct();
      $this->load->model('MyModel');
      $this->load->model('MerchantProductModel');
      $this->load->library('hubtelApi');
  }

  public function isLoggedin_post(){
    $_POST = json_decode(file_get_contents('php://input'), TRUE);
    $response = $this->MyModel->auth($_POST['id'],$_POST['token']);
    if($response['status'] == 200){
      $this->response($response, REST_Controller::HTTP_OK);
    }else{
      $this->response($response, REST_Controller::HTTP_NOT_FOUND);
    }
  }

  public function login_post(){
    //get from json body
    $_POST = json_decode(file_get_contents('php://input'), TRUE);
    $username = $_POST['username'];
    $password = $_POST['password'];
    $response= array('success'=> false, 'messages' => array());
		$this->form_validation->set_rules('username', 'Username', 'trim|required');
    $this->form_validation->set_rules('password', 'Password', 'trim|required');
		$this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
    if ($this->form_validation->run() === FALSE){
        foreach($_POST as $key =>$value){
             $response['messages'][$key] = form_error($key);
        }
    }
    else{
      $response = $this->MyModel->login($username,$password);
    }
    $this->response($response, REST_Controller::HTTP_OK);
  }

  public function logout_post(){
		  $response = $this->MyModel->logout();
			$this->response($response, REST_Controller::HTTP_OK);
  }

  //NEW LOGIN FOR MYFRESHWORD LOGIN  PAGE
  public function mobile_login_post(){
    $_POST = json_decode(file_get_contents('php://input'), TRUE);
    $user_mobile = $_POST['mobile'];
    $password = $_POST['password'];
    $response= array('success'=> false, 'messages' => array());
    $this->form_validation->set_rules('mobile', 'Mobile Number', 'trim|required');
    $this->form_validation->set_rules('password', 'Password', 'trim|required');
    $this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
    if ($this->form_validation->run() === FALSE){
        foreach($_POST as $key =>$value){
             $response['messages'][$key] = form_error($key);
        }
        $this->response($response, REST_Controller::HTTP_OK);
        return false;
    }
    else{
      $response = $this->MyModel->mobile_login($user_mobile,$password);
      if($response['status'] == 204){//no content
        $this->response($response, REST_Controller::HTTP_NO_CONTENT);
        return false;
      }

      if($response['status'] == 500){
        $this->response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        return false;
      }

      if($response['status'] == 200){
        $this->response($response, REST_Controller::HTTP_OK);
        return false;
      }
    }
  }


  //user forgot password resets the same process in themeportal web
  public function forgot_password_post(){
    $_POST = json_decode(file_get_contents('php://input'), TRUE);
    $data= array('success'=> false, 'messages' => array());
		$this->form_validation->set_rules('emailadd', 'Email', 'required');
		$this->form_validation->set_error_delimiters('<span>', '</span>');
    if ($this->form_validation->run() === FALSE){
        foreach($_POST as $key =>$value){
             $data['messages'][$key] = form_error($key);
        }
    }
    else{
            $data = $this->MyModel->forgot_password_email($_POST['emailadd']);
            if($data['status'] == 204){
              $this->response($data, REST_Controller::HTTP_OK);
            }
            else{
              $q = $this->MyModel->generate_short_code();
              $updateData =array(
                'user_key'    =>$data['key'],
                'user_reset_code'=>$q
              );
              $id = $data['id'];
              $data = array(
                'success'=>$this->MyModel->update_key($id,$updateData),
                'number'=>$data['link']
              );
               $this->MyModel->send_code($data['number'],$q);
      }
  }
  $this->response($data, REST_Controller::HTTP_OK);
}

  //get registration details from the user post with default role key
  //pass user role from the server side
  //activation for user who signs up by using email verification
  //
  public function sign_up_post(){
    $_POST = json_decode(file_get_contents('php://input'), TRUE);
    $data= array('success'=> false, 'messages' => array());
		$this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[5]|is_unique[ts_user.user_uname]');
    $this->form_validation->set_rules('mobile', 'Mobile', 'trim|required|min_length[10]|is_unique[ts_user.user_mobile]');
    $this->form_validation->set_rules('email', 'Email', 'trim|required|is_unique[ts_user.user_email]');
    $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[8]');
    $this->form_validation->set_message('is_unique', 'The %s is already taken');
		$this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');

		if ($this->form_validation->run() === FALSE){
        foreach($_POST as $key =>$value){
            $data['messages'][$key] = form_error($key);
        }
    }
    else{
            $q = $this->MyModel->generate_short_code();
            $data =array(
              'user_uname'  =>  $_POST['username'],
              'user_email'  =>  $_POST['email'],
              'user_mobile' =>  $_POST['mobile'],
              'user_pwd'    =>md5($_POST['password']),
              'user_key'    =>$key = md5(date('his').$_POST['email']),
              'user_accesslevel'=>2,
  						'user_status'=>2,
              'user_activation_code'=>$q
            );
            $data = $this->MyModel->create_user($data);
            $this->MyModel->send_code($_POST['mobile'],$q);
            //$this->mail_user($_POST['email'], 'Registration', 'Click link to confirm and activate account thank you: ' .'http://192.168.1.3/themeportal/authenticate/login/'.$key);
        }
        $this->response($data, REST_Controller::HTTP_OK);
  }


  public function activate_account_post(){
      $_POST = json_decode(file_get_contents('php://input'), TRUE);
      $data= array('success'=> false, 'messages' => array());
      $this->form_validation->set_rules('code', 'code', 'trim|required|min_length[4]|max_length[4]|numeric');
      $this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
      if ($this->form_validation->run() === FALSE){
          foreach($_POST as $key =>$value){
              $data['messages'][$key] = form_error($key);
          }
      }
      else{
        $q = $this->MyModel->activate_account($_POST['code']);
        if($q['status'] == 200){
          $data = array('status' => 200,'message' => 'Account activated successfully');
        }
        else{
          $data = array('status' => 204,'message' => 'Problem with activation code please resend');
        }
      }
      $this->response($data, REST_Controller::HTTP_OK);
  }

  public function reset_password_post(){
    $_POST = json_decode(file_get_contents('php://input'), TRUE);
    $data= array('success'=> false, 'messages' => array());
    $this->form_validation->set_rules('code', 'code', 'trim|required|min_length[4]|max_length[4]|numeric');
    $this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
    if ($this->form_validation->run() === FALSE){
        foreach($_POST as $key =>$value){
            $data['messages'][$key] = form_error($key);
        }
    }
    else{
      $q = $this->MyModel->reset_password_code($_POST['code']);
      if($q['status'] == 200){
        $data = array('status' => 200,'message' => 'passed','user_email'=>$q['email']);
      }
      else{
        $data = array('status' => 204,'message' => 'Wrong Pin..Type correct reset pin sent via sms');
      }
    }
    $this->response($data, REST_Controller::HTTP_OK);
  }

  public function new_password_post(){
    $_POST = json_decode(file_get_contents('php://input'), TRUE);
    $data= array('success'=> false, 'messages' => array());
    $this->form_validation->set_rules('email', 'Email', 'trim|required');
    $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[8]');
    $this->form_validation->set_rules('passwordAgain', 'Password', 'trim|required|matches[password]');
    $this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
    if ($this->form_validation->run() === FALSE){
        foreach($_POST as $key =>$value){
            $data['messages'][$key] = form_error($key);
        }
    }
    else{
      $dataInsert = array(
        'user_pwd'=>md5($_POST['password'])
      );
      $data = $this->MyModel->update_password($_POST['email'],$dataInsert);
    }
    $this->response($data, REST_Controller::HTTP_OK);
  }


  public function resend_post(){//this will stored by default
    $_POST = json_decode(file_get_contents('php://input'), TRUE);
      if($_POST['mobile'] == NULL){
       $data =  array('status' => 401,'message' => 'Unauthorized.');
     }
     else{
      $pin  = $this->MyModel->generate_short_code();
      $q    = $this->MyModel->send_code($_POST['mobile'],$pin);
      $updateData  = array(
          'user_activation_code' => $pin
      );
      $data  = $this->MyModel->update_key_mobile($_POST['mobile'],$updateData);
     }
     $this->response($data, REST_Controller::HTTP_OK);
  }

  public function preachers_get(){
    $query = $this->MyModel->get_all_preachers();
    $this->response($query, REST_Controller::HTTP_OK);
  }


  protected function mail_user($toEmail, $subject, $message){
    $data=array(
      'email'=> 'admin@myfreshword.com',
      'name'=>'administrator',
      'toEmail'=>$toEmail,
      'subject'=>$subject,
      'message'=>$message
    );
    $this->MyModel->send_data_mail($data);

  }

  //list all the number of churches user is registered on
  public function church_list_get(){
    $data = $this->MyModel->church_all_data();
    $this->response($data, REST_Controller::HTTP_OK);
  }

  public function feed_post(){
    $_POST = json_decode(file_get_contents('php://input'), TRUE);
    $data= array('success'=> false, 'messages' => array());
    $this->form_validation->set_rules('denomination', 'Denomination', 'trim|required');
    // $this->form_validation->set_rules('pastors[]', 'Preacher', 'count_array_check');
    // $this->form_validation->set_rules('sermon[]', 'Sermon Topics', 'count_array_check');
    $this->form_validation->set_rules('email', 'Email', 'trim|required');
    $this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
    if ($this->form_validation->run() === FALSE){
        foreach($_POST as $key =>$value){
            $data['messages'][$key] = form_error($key);
        }
    }else {
      $dataInsert = array(
        'user_email'    => $_POST['email'],
        'denomination'  => $_POST['denomination'],
        'fav_preachers' => implode(", ", $_POST['pastors']),
        'sermon_topics' => implode(", ", $_POST['sermon']),
      );
      $data = $this->MyModel->feed_data($dataInsert);
    }
    $this->response($data, REST_Controller::HTTP_OK);
  }

  /*
  //THIS PART CALLS API FOR DATA AFTER USER HAS LOGGED IN SUCCESSFULLY
  */
  public function change_password_post(){

  }

  public function facebook_login_post(){
    $_POST = json_decode(file_get_contents('php://input'), TRUE);
    $data = array(
      'fb_id'     =>  $_POST['id'],
      'fb_email'  =>  $_POST['email'],
      'user_email'=>  $_POST['email'],
      'fb_name'   =>  $_POST['name'],
      'fb_gender' =>  $_POST['gender']
    );
    $query = $this->MyModel->facebook_data($data);
    $this->response($query, REST_Controller::HTTP_OK);
  }

  public function google_login_post(){
    $_POST = json_decode(file_get_contents('php://input'), TRUE);
    $data = array(
      'g_user_id'     =>  $_POST['id'],
      'g_email'       =>  $_POST['email'],
      'user_email'    =>  $_POST['email'],
      'g_display_name' =>  $_POST['name']
    );
    $query = $this->MyModel->google_data($data);
    $this->response($query, REST_Controller::HTTP_OK);
  }

  //get user profile data by id and apikey
  public function user_profile_get(){
    $response = $this->MyModel->auth($this->get('userid'),$this->get('token'));
    if($response['status'] == 200){//if header is passed
      $resp = $this->MyModel->user_profile_data($response['id']);
      $this->response($resp, REST_Controller::HTTP_OK);
    }else{
      $this->response($response, REST_Controller::HTTP_NOT_FOUND);
    }
  }

  // //update user profile like password and the details
  // public function user_profile_post(){
  //
  // }


  public function all_product_get(){
     $response = $this->MyModel->auth($this->get('userid'),$this->get('token'));
     if($response['status'] == 200){
       $resp = $this->MyModel->audio_all_data();//this is pulling all data not just audio
       $this->response($resp, REST_Controller::HTTP_OK);
     }else{
       $this->response($response, REST_Controller::HTTP_NOT_FOUND);
     }
  }

  public function product_by_id_get(){
    $response = $this->MyModel->auth($this->get('userid'),$this->get('token'));
    if($response['status'] == 200){
      $resp = $this->MyModel->product_id($this->get('p_id'));
      $this->response($resp, REST_Controller::HTTP_OK);
    }else{
      $this->response($response, REST_Controller::HTTP_NOT_FOUND);
    }
  }



  public function mobile_money_post(){
    $response = $this->MyModel->header_auth();
    if($response['status']==200){
      $_POST = json_decode(file_get_contents('php://input'), TRUE);
      $data= array('success'=> false, 'messages' => array());
      $this->form_validation->set_rules('network', 'Mobile Network', 'trim|required');
      $this->form_validation->set_rules('number', 'Mobile Number', 'trim|required|min_length[10]|max_length[12]|is_unique[momo.payin_number]');
      $this->form_validation->set_message('is_unique', 'The %s is already taken');
      $this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
      if ($this->form_validation->run() === FALSE){
          foreach($_POST as $key =>$value){
              $data['messages'][$key] = form_error($key);
          }
      }
      else {//if this return correct results then we need to show success message and store  data locally on phone

        $query = $this->MyModel->new_momo($_POST['number']);
        if($query['valid'] === false){
          $data['error'] = array(
            'status'=>false,
            'message'=> 'Invalid Mobile Money Number'
          );
        }

        else if($query['valid'] === true){

          $data= array(
            'success'=> true,
            'message'=>'Valid Mobile Money Number',
            'results'=>$query
          );

          //now save momo details into the table
          $dB = array(
            'network'       =>  $_POST['network'],
            'payin_number'  =>  $_POST['number'],
            'unique_acc'    =>  $_POST['email']
          );
          $dBquery = $this->MyModel->insert_momo($dB);
        }
        //ends here for the numerify validation
      }
      $this->response($data, REST_Controller::HTTP_OK);
    }
    else{
      $this->response($response,REST_Controller::HTTP_NOT_FOUND);
    }
  }

  // public function momo_data_post(){
  //   $data = json_decode(file_get_contents('php://input'), TRUE);
  //   // $query = $this->MyModel->momo_by_id($data['email']);
  //   // if($query === false){
  //   //   //do nothing
  //   //   $response['success'] = false;
  //   //   $response['message'] = 'User has not set up mobile money';
  //   // }else
  //   //   $response['success'] = true;
  //   //   $response['message'] = 'User has set up mobile money';
  //   //   $response['results'] = $query;
  //   // }
  //   $this->response($data,REST_Controller::HTTP_OK);
  // }

  public function momo_app_post(){
      $responseHead = $this->MyModel->header_auth();
      if($responseHead['status']==200){
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $query = $this->MyModel->momo_by_id($data['email']);

        if($query == false){
          //do nothing
          $response['success'] = false;
          $response['message'] = 'User has not set up mobile money';
        }else{
          $response['success'] = true;
          $response['message'] = 'User has set up mobile money';
          $response['results'] = $query;
        }
        $this->response($response,REST_Controller::HTTP_OK);
      }
      else{
        $this->response($responseHead,REST_Controller::HTTP_NOT_FOUND);
      }

  }


  public function momo_default_post(){
    $response = $this->MyModel->header_auth();
    if($response['status']==200){
      $_POST = json_decode(file_get_contents('php://input'), TRUE);
      $q = $this->MyModel->set_momo_default($_POST);
      $this->response($q,REST_Controller::HTTP_OK);
    }
    else{
      $this->response($response,REST_Controller::HTTP_NOT_FOUND);

    }
  }

  //this should pull data
  public function get_momo_data_post(){
    $response = $this->MyModel->header_auth();
    if($response['status']==200){
      $_POST = json_decode(file_get_contents('php://input'), TRUE);
      $data = array(
        'email'=>$_POST['email']
      );
      $q = $this->MyModel->user_momo_numbers($data);
      $this->response($q,REST_Controller::HTTP_OK);
    }else{
      $this->response($response,REST_Controller::HTTP_NOT_FOUND);
    }
  }

  public function credit_card_post(){
    $data = json_decode(file_get_contents('php://input'), TRUE);
    $query = $this->MyModel->bin_checker($data['bin']);
    $this->response($query, REST_Controller::HTTP_OK);
  }

  public function head_post(){
    $query = $this->MyModel->check_auth_client();
    $this->response($query, REST_Controller::HTTP_OK);
  }

  public function cardAdd_post(){//this to add products to carts
    $response = $this->MyModel->header_auth();
    if($response['status']==200){
        $dataPost = json_decode(file_get_contents('php://input'), TRUE);

         $data = array(
           'prod_uniqid'     =>  $dataPost['prod_uniqid'],
           'prod_description'=>  $dataPost['prod_description'],
           'prod_name'       =>  $dataPost['prod_name'],
           'prod_price'      =>  $dataPost['prod_price'],
           'prod_quantity'   =>  $dataPost['prod_quantity'],
           'prod_img_link'   =>  $dataPost['prod_img_link'],
           'prod_purchase_by'=>  $dataPost['prod_purchase_by'],
           'paid'            =>  $dataPost['paid'],
           'file_link'       =>  $dataPost['file_link'],
           'prod_type'       =>  $dataPost['type_list'],
           'transactionid'   =>  $this->MyModel->trans_rotate($dataPost['prod_purchase_by'])//if it hasnt been paid dont generate new transaction id
         );
         $query['insert_query'] = $this->MyModel->addToCart($data);
         $query['item_in_cart'] = $this->MyModel->cartRowCount($data);
         $query['total_price'] = $this->MyModel->TotalCartSales($data);
         $this->response($query,REST_Controller::HTTP_OK);

     }
     else{
       $this->response($response,REST_Controller::HTTP_NOT_FOUND);
     }

  }

  //on page load post run and get necessary data from cart
  public function cart_status_post(){
    $response = $this->MyModel->header_auth();
    if($response['status']==200){
      $dataPost = json_decode(file_get_contents('php://input'), TRUE);
      $data = array(
         'prod_purchase_by'=>  $dataPost['prod_purchase_by'],
       );
      $query['cart_data'] = $this->MyModel->fetch_cart_data($data);
      $query['item_in_cart'] = $this->MyModel->cartRowCount($data);
      $query['total_price'] = $this->MyModel->TotalCartSales($data);
      $this->response($query,REST_Controller::HTTP_OK);
    }
    else{
      $this->response($response,REST_Controller::HTTP_NOT_FOUND);
    }
  }

  public function remove_cart_item_post(){
    $response = $this->MyModel->header_auth();
    if($response['status']==200){
      $param = json_decode(file_get_contents('php://input'), TRUE);
      $data = array(
        'id'               => $param['id'],
        'prod_purchase_by' => $param['email']
      );
      $query = $this->MyModel->delete_cart_data($data);
      $this->response($query,REST_Controller::HTTP_OK);
    }
    else{
      $this->response($response,REST_Controller::HTTP_NOT_FOUND);
    }
  }

  public function library_post(){
    $response = $this->MyModel->header_auth();
    if($response['status']==200){
      $param = json_decode(file_get_contents('php://input'), TRUE);
      $data = array(
        'email'               => $param['email']
      );
      $query = $this->MyModel->library_data($data['email']);
      $this->response($query,REST_Controller::HTTP_OK);
    }
    else{
      $this->response($response,REST_Controller::HTTP_NOT_FOUND);
    }
  }



  public function checkout_post(){
    $response = $this->MyModel->header_auth();
    if($response['status']==200){
      $_POST = json_decode(file_get_contents('php://input'), TRUE);

      //so here when you make payment
      //$this->db->insert_batch('ts_paid_prod', $_POST['cart_data']);
      foreach($_POST['cart_data'] as $db_data){
        $data = array(
          'prod_name'   =>  $db_data['prod_name'],
          'prod_uniqid' =>  $db_data['prod_uniqid'],
          'file_link'   =>  $db_data['file_link'],
          'type'        =>  $db_data['prod_type'],
          'paid'        =>  1,
          'user_acc'    =>  $db_data['prod_purchase_by'],
          'img_link'    =>  $db_data['prod_img_link'],
          'prod_price'  =>  $db_data['prod_price']
        );
        $this->db->insert('ts_paid_prod', $data);
      }
      $this->response($_POST['cart_data'],REST_Controller::HTTP_OK);
    }
    else{
      $this->response($response,REST_Controller::HTTP_NOT_FOUND);
    }
  }

  public function clear_library_post(){
    $response = $this->MyModel->header_auth();
    if($response['status']==200){
      //run this endpoint after user checked out and paid all
      $_POST = json_decode(file_get_contents('php://input'), TRUE);
      $q = $this->MyModel->delete_library_data($_POST['email']);
      if($q == true){
        $data = array('status'=>200, 'message'=>'Cart data cleared');
      }else{
        $data = array('status'=>400, 'message'=> 'Error with cart data processing');
      }
      $this->response($data,REST_Controller::HTTP_OK);
    }
    else{
      $this->response($response,REST_Controller::HTTP_NOT_FOUND);
    }
  }

  public function process_payment_post(){

  }

  public function sms_enable_post(){
    $response = $this->MyModel->header_auth();
    if($response['status']==200){
      $param = json_decode(file_get_contents('php://input'), TRUE);
      $data  = array(
          'sms_notify'  => $param['notify']
          // 'email'       => $param['email'],
          //  'id'         => $param['id'],//this will be the profile id
          //  'mobile'     => $param['mobile']
      );
      $query = $this->MyModel->sms_enable($data);
      $this->response($query,REST_Controller::HTTP_OK);
    }
    else{
      $this->response($response,REST_Controller::HTTP_NOT_FOUND);
    }
  }

  public function email_enable_post(){
    $response = $this->MyModel->header_auth();
    if($response['status']==200){
      $param = json_decode(file_get_contents('php://input'), TRUE);
      $data = array(
        'email_notify'  => $param['notify']
      );
    }
    else{
      $this->response($response,REST_Controller::HTTP_NOT_FOUND);
    }

  }

  public function profile_update_post(){
    $response = $this->MyModel->header_auth();
    if($response['status']==200){
      $_POST = json_decode(file_get_contents('php://input'), TRUE);
      $data= array('success'=> false, 'messages' => array());
      $this->form_validation->set_rules('id', 'User Profile ID', 'trim|required|numeric');
      $this->form_validation->set_rules('username', 'Username', 'trim|required');
      $this->form_validation->set_rules('mobile', 'Mobile Number', 'trim|required');
      $this->form_validation->set_rules('password', 'Password', 'trim|required');
      $this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
      if ($this->form_validation->run() === FALSE){
          foreach($_POST as $key =>$value){
              $data['messages'][$key] = form_error($key);
          }
      }
      else{
        $data = array(
          'user_uname'  => $_POST['username'],
          'user_mobile' => $_POST['mobile'],
          'user_pwd'    => $_POST['password']
        );
        $id = $_POST['id'];
        $data = $this->MyModel->update_user_profile($id,$data);
      }
      $this->response($data,REST_Controller::HTTP_OK);
    }
    else{
      $this->response($response,REST_Controller::HTTP_NOT_FOUND);
    }
  }


  //this shooud be the response for the payment
  public function payment_response_post(){

    $_POST = json_decode(file_get_contents('php://input'), TRUE);
    $data= array('success'=> false, 'messages' => array());
    //$this->form_validation->set_rules('status', 'Rest Status Code', 'trim|required|numeric');//preferred not to be passed
    $this->form_validation->set_rules('success', 'Success Boolean', 'trim|required');
    $this->form_validation->set_rules('message', 'Message', 'trim|required');
    $this->form_validation->set_rules('network', 'Mobile Money Network', 'trim|required');
    $this->form_validation->set_rules('phone_number', 'Phone Number', 'trim|required|numeric');
    $this->form_validation->set_rules('amount', 'Transaction Amount', 'trim|required|numeric');
    $this->form_validation->set_rules('freshword_transaction_id', 'My Freshword Transaction ID', 'trim|required');//this should be tied to the array of products being purchased
    $this->form_validation->set_rules('payin_transaction_id', 'Payin Transaction ID', 'trim|required');
    $this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');

    if ($this->form_validation->run() === FALSE){
        foreach($_POST as $key =>$value){
            $data['messages'][$key] = form_error($key);
        }
    }
    else{
      $data = $this->MyModel->callback_response($_POST);
    }
    $this->response($data,REST_Controller::HTTP_OK);
  }

  //data passed here should just contain the following
  //transactionid
  //
  public function process_cart_payment_post(){
    $response = $this->MyModel->header_auth();
    if($response['status']==200){
      $_POST = json_decode(file_get_contents('php://input'), TRUE);
      $data= array('success'=> false, 'messages' => array());
      $this->form_validation->set_rules('success', 'Success Boolean', 'trim|required');
      $this->form_validation->set_rules('status', 'status', 'trim|required');
      $this->form_validation->set_rules('message', 'Message', 'trim|required');
      $this->form_validation->set_rules('network', 'network', 'trim|required');
      $this->form_validation->set_rules('phonenumber', 'Phone Number', 'trim|required|numeric');
      $this->form_validation->set_rules('amount', 'amount', 'trim|required|numeric');
      $this->form_validation->set_rules('freshword_transaction_id', 'Freshword Transaction Id', 'trim|required');
      $this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
        if ($this->form_validation->run() === FALSE){
            foreach($_POST as $key =>$value){
                $data['messages'][$key] = form_error($key);
            }
        }
        else{

          $payData = array(
            'success'                   =>    $_POST['success'],
            'status'                    =>    $_POST['status'],
            'message'                   =>    $_POST['message'],
            'network'                   =>    $_POST['network'],
            'phone_number'              =>    $_POST['phonenumber'],
            'amount'                    =>    $_POST['amount'],
            'freshword_transaction_id'  =>    $_POST['freshword_transaction_id']
          );
          $data['success']  = true;
          $data['messages'] = $this->MyModel->payment_to_db($payData);

        }
        $this->response($data, REST_Controller::HTTP_OK);
    }
    else{
      $this->response($response,REST_Controller::HTTP_NOT_FOUND);
    }
  }






  public function comments_title_post(){
    $response = $this->MyModel->header_auth();
    if($response['status']==200){
      $_POST = json_decode(file_get_contents('php://input'), TRUE);
      $data = array(
        'prod_id'              =>  $_POST['id'],
        'comment_title'   =>  $_POST['title']
      );

      $q = $this->MyModel->get_comment_title_data($data);
      if($q['status'] == 204){
        $this->response($q,REST_Controller::HTTP_NOT_FOUND);
      }else{
        $this->response($q,REST_Controller::HTTP_OK);
      }
    }
    else{
      $this->response($response,REST_Controller::HTTP_NOT_FOUND);
    }
  }


  public function product_search_query_post(){
    $response = $this->MyModel->header_auth();
    if($response['status']==200){
      $_POST = json_decode(file_get_contents('php://input'), TRUE);
      $q = $this->MyModel->search_product($_POST['prod_search']);
      $this->response($q,REST_Controller::HTTP_OK);
    }
    else{
      $this->response($response,REST_Controller::HTTP_NOT_FOUND);
    }
  }

  public function feed_search_query_post(){
    $response = $this->MyModel->header_auth();
    if($response['status']==200){
      $_POST = json_decode(file_get_contents('php://input'), TRUE);
      $q = $this->MyModel->search_all_feed($_POST['feed_search']);
      $this->response($q,REST_Controller::HTTP_OK);
    }
    else{
      $this->response($response,REST_Controller::HTTP_NOT_FOUND);
    }
  }

  // public function author_search_query_post(){
  //
  // }


/*================================================================================================================
==================================================================================================================
==================================================================================================================
*MERCHANT ENDPOINTS STARTS FROM HERE
*/
  public function web_products_get(){
    $resp = $this->MyModel->audio_all_data();//this is pulling all data not just audio
    $this->response($resp, REST_Controller::HTTP_OK);
  }

  public function merchant_register_post(){
    $_POST = json_decode(file_get_contents('php://input'), TRUE);

    $data= array('success'=> false, 'messages' => array());
    $this->form_validation->set_rules('location', 'Location', 'trim|required');
    $this->form_validation->set_rules('firstname', 'First Name', 'trim|required');
    $this->form_validation->set_rules('lastname', 'Last Name', 'trim|required');
    $this->form_validation->set_rules('email', 'Email', 'trim|required|is_unique[ts_merchant.email]');
    $this->form_validation->set_rules('mobile', 'Mobile', 'trim|required|is_unique[ts_merchant.mobile]');
    $this->form_validation->set_rules('password', 'Password', 'trim|required');
    $this->form_validation->set_rules('organisation', 'Organisation', 'trim|required');
    $this->form_validation->set_rules('merchantname', 'Merchant Name', 'trim|required|is_unique[ts_merchant.merchant_name]');
    $this->form_validation->set_message('is_unique', 'The %s is already taken');
    $this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');

    if ($this->form_validation->run() === FALSE){
        foreach($_POST as $key =>$value){
            $data['messages'][$key] = form_error($key);
        }
    }
    else{
      $regData = array(
        'first_name'          =>  $_POST['firstname'],
        'last_name'           =>  $_POST['lastname'],
        'email'               =>  $_POST['email'],
        'mobile'              =>  $_POST['mobile'],
        'password'            =>  hash('sha256', $_POST['password']),
        'organisation'        =>  $_POST['organisation'],
        'location'            =>  $_POST['location'],
        'merchant_name'       =>  $_POST['merchantname'],
        'approval_code'       =>  $this->MyModel->generate_merchant_activation_code()
      );
      $data['sms']        = $this->MyModel->send_code( $regData['mobile'], $regData['approval_code']);
      $data['success']    = true;
      $data['messages']   = $this->MyModel->create_merchant($regData);
    }

    $this->response($data, REST_Controller::HTTP_OK);
  }



  public function merchant_login_post(){
    $_POST = json_decode(file_get_contents('php://input'), TRUE);

    $data= array('success'=> false, 'messages' => array());
    $this->form_validation->set_rules('email', 'Email', 'trim|required');
    $this->form_validation->set_rules('password', 'Password', 'trim|required');
    $this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');

    if ($this->form_validation->run() === FALSE){
        foreach($_POST as $key =>$value){
            $data['messages'][$key] = form_error($key);
        }
    }
    else{
      $data['success'] = true;
      $data['messages'] = $this->MyModel->merchant_login($_POST['email'], $_POST['password']);
    }
    $this->response($data, REST_Controller::HTTP_OK);
  }

  //so here i am beginning session to control my rest client session pages
  public function merchant_session_start_post(){
    $_POST = json_decode(file_get_contents('php://input'), TRUE);
    $resp = $this->MyModel->merchant_session($_POST['id'],$_POST['token']);
    $this->response($resp, REST_Controller::HTTP_OK);
  }


  public function merchant_activate_account_post(){
    $_POST = json_decode(file_get_contents('php://input'), TRUE);
    $query = $this->MyModel->activate_merchant($_POST);
    $this->response($query, REST_Controller::HTTP_OK);
  }



  public function merchant_forgot_pass_email_post(){
    $_POST = json_decode(file_get_contents('php://input'), TRUE);
    $resp   = $this->MyModel->check_merchant_email($_POST);
    $this->response($resp, REST_Controller::HTTP_OK);
  }

  public function merchant_confirm_reset_code_post(){
    $_POST = json_decode(file_get_contents('php://input'), TRUE);
    $resp   = $this->MyModel->check_reset_code($_POST['mobile'],$_POST['resetcode']);
    $this->response($resp, REST_Controller::HTTP_OK);
  }

  public function merchant_profile_get(){
    $response = $this->MyModel->merchant_auth();
    if($response['status']==200){
      $query = $this->MyModel->get_merchant_profile($response['id']);
      $data = array('res'=>$query, 'headerRes'=> $response);
      $this->response($data, REST_Controller::HTTP_OK);
    }else{
      $this->response($response, REST_Controller::HTTP_OK);
    }
  }


  //this has to be sequential now we need to return values here to proceed to the next endpoint
  //this will be looped twice to the end point
  public function merchant_add_image_post(){

        $id = $_POST['id'];
        $config['upload_path']   = './public/images/products/';
        $config['allowed_types'] = 'gif|jpg|png';//allowing only images
        $config['max_size']      = 1024;
        $this->load->library('upload', $config);

        if ( ! $this->upload->do_upload('image_file')) {
           $error = array('status'=>false, 'error' => $this->upload->display_errors());
           //echo json_encode($error);
           $this->response($error, REST_Controller::HTTP_OK);
        }else {
           $data = $this->upload->data();
           $success = ['status'=>true,'success'=>$data['file_name']];
           //echo json_encode($success);
           $imgData = array(
             'prod_image'   =>  $data['file_name'],
             'img_link'     =>  'http://myfreshword.com/myfreshword/api/public/images/products/'.$data['file_name']
           );
           $this->MyModel->update_image($id, $imgData);
           $this->response($success, REST_Controller::HTTP_OK);
        }

}

  //we run this on the success response from the first push
  public function merchant_add_file_post(){

      $id = $_POST['id'];
      $query = $this->MyModel->upload_path($id);
      $config['upload_path']   = './prod_link/'.$query.'/';
      if($query == "audio"){
        $config['allowed_types'] = 'mp3';
      }
      if($query == "video"){
        $config['allowed_types'] = 'mp4|avi';
      }
      if($query == "book"){
        $config['allowed_types'] = 'pdf|doc';
      }
      $config['max_size']      = 2024;
      $this->load->library('upload', $config);

      if ( ! $this->upload->do_upload('image')) {
         $error = array('status'=>false, 'error' => $this->upload->display_errors());
         //echo json_encode($error);
         $this->response($error, REST_Controller::HTTP_OK);
      }else {
         $data = $this->upload->data();
         $success = ['status'=>true,'success'=>$data['file_name']];
         $imgData = array(
           'file_link'     =>  $data['file_name']
         );
         $this->MyModel->update_file($id, $imgData);
         $this->response($success, REST_Controller::HTTP_OK);
      }

  }


  public function merchant_products_post(){
        //$_POST = json_decode(file_get_contents('php://input'), TRUE);
        $response = $this->MyModel->merchant_auth();
        if($response['status']==200){
          $this->load->helper('url');
          $query = $this->MyModel->merchant_email($response['id']);
          $list = $this->MerchantProductModel->get_datatables($query->email);
          $data = array();
          $no = $_POST['start'];
          foreach ($list as $prod) {
              $no++;
              $row = array();
              $row[] =  '<img src="'.$prod->img_link.'" height="75px">';
              $row[] = $prod->prod_name;
              $row[] = $prod->prod_preacher;
              $row[] = $prod->prod_church;
              $row[] = $prod->prod_price;
              $row[] = $prod->currency;
              $row[] = $prod->prod_tags;
              $row[] = $prod->prod_uniqid;
              $row[] = $prod->prod_download_count;
              $row[] = $prod->prod_date;
             	//if($payee->network == 'MTN'):
              $favicon = $this->MyModel->favicon_show($prod->prod_tags);
              $row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Preview" onclick="preview_product('."'".$prod->prod_id."'".')"><i class="'.$favicon.'"></i> </a>
                        <a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_product('."'".$prod->prod_id."'".')"><i class="fa fa-edit"></i> </a>
                        <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Delete" onclick="delete_product('."'".$prod->prod_id."'".')"><i class="fa fa-trash"></i> </a>';
              $data[] = $row;
          }

          $output = array(
              "draw" => $_POST['draw'],
              "recordsTotal" => $this->MerchantProductModel->count_all($query->email),
              "recordsFiltered" => $this->MerchantProductModel->count_filtered($query->email),
              "data" => $data,
          );
          //output to json format
          $this->response($output, REST_Controller::HTTP_OK);
        }else{
          $this->response($response, REST_Controller::HTTP_OK);
        }

  }


  //and then we finally post the data needed as well
  // Here we will go through our form validaitons to avoid same data being posted twice
  public function merchant_add_product_data_post(){

    $response = $this->MyModel->merchant_auth();
    if($response['status']==200){

      $_POST = json_decode(file_get_contents('php://input'), TRUE);
      $data= array('success'=> false, 'messages' => array());
      $this->form_validation->set_rules('prod_tags', 'Category', 'trim|required');//type
      $this->form_validation->set_rules('prod_name', 'Title', 'trim|required|is_unique[ts_products.prod_name]');
      $this->form_validation->set_rules('prod_preacher', 'Preacher / Speaker / Author', 'trim|required');
      $this->form_validation->set_rules('prod_price', 'Price', 'trim|required');
      $this->form_validation->set_rules('prod_currency', 'Currency', 'trim|required');
      $this->form_validation->set_rules('prod_description', 'Topic', 'trim|required|max_length[160]');//this is the theme
      $this->form_validation->set_rules('prod_essay', 'Description', 'trim|required');//and this is the essay
      $this->form_validation->set_rules('prod_church', 'Church Name', 'trim|required');//should be an hidden input
      $this->form_validation->set_rules('merchant_email', 'Merchant Email', 'trim|required');
      $this->form_validation->set_message('is_unique', 'The %s is already taken');
      $this->form_validation->set_message('max_length[160]', '%s: the maximum of 160 Characters allowed');
      $this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');

      if ($this->form_validation->run() === FALSE){
          foreach($_POST as $key =>$value){
              $data['messages'][$key] = form_error($key);
          }
      }
      else{
        $prodData = array(
          'prod_name'             =>      $_POST['prod_name'],
          'prod_urlname'          =>      $this->MyModel->replace_hyphens($_POST['prod_name']),
          'prod_preacher'         =>      $_POST['prod_preacher'],
          'prod_church'           =>      $_POST['prod_church'],
          //'prod_image'            =>      $_POST['prod_image'],
          //'img_link'              =>      $this->MyModel->imgPlus($_POST['prod_image']),
          'prod_tags'             =>      $_POST['prod_tags'], //here we use value as the same for type_list
          'prod_description'      =>      $_POST['prod_description'],
          'prod_essay'            =>      $_POST['prod_essay'],
          'prod_demourl'          =>      'null',
          'prod_demoshow'         =>      1,
          'prod_cateid'           =>      1,
          'prod_subcateid'        =>      0,
          'prod_filename'         =>      0,
          'prod_price'            =>      $_POST['prod_price'],
          'prod_plan'             =>      0,
          'prod_free'             =>      0,
          'prod_featured'         =>      0,
          'prod_status'           =>      1,
          'prod_uniqid'           =>      $this->MyModel->generate_product_unique_code(),
          'prod_download_count'   =>      0,
          'prod_gallery'          =>      1,
          'prod_uid'              =>      1,
          'prod_type'             =>      $this->MyModel->prod_type($_POST['prod_tags']),
          'type_list'             =>      $_POST['prod_tags'],
          //'file_link'             =>      $_POST['file_link'],
          'merchant_email'        =>      $_POST['merchant_email'],
          'currency'              =>      $_POST['prod_currency'],
          'prod_date'             =>      date('Y-m-d H:i:s')
        );
        $query = $this->MyModel->merchant_insert_product($prodData);
        $data = array('success'=>true,'message'=>$query);
      }
      $this->response($data, REST_Controller::HTTP_OK);
    }else{
      $this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
    }
  }

  public function product_preview_get(){
    $response = $this->MyModel->merchant_auth();
    if($response['status']==200){
      $id = (int) $this->get('id');
      $query = $this->MyModel->product_preview($id);
      $this->response($query, REST_Controller::HTTP_OK);
    }else{
      $this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
    }
  }


  public function product_edit_get(){
    $response = $this->MyModel->merchant_auth();
    if($response['status']==200){
      $id = (int) $this->get('id');
      $query = $this->MyModel->edit_product($id);
      $this->response($query, REST_Controller::HTTP_OK);
    }else{
      $this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
    }
  }


  public function delete_product_post(){
    $response = $this->MyModel->merchant_auth();
    if($response['status']==200){
      $_POST = json_decode(file_get_contents('php://input'), TRUE);
      $query = $this->MyModel->delete_product($_POST['id'], $_POST['email']);
      $this->response($query, REST_Controller::HTTP_OK);
    }else{
      $this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
    }
  }


  public function update_product_post(){
    $response = $this->MyModel->merchant_auth();
    if($response['status']==200){

          //code beginss here
          $_POST = json_decode(file_get_contents('php://input'), TRUE);
          $data= array('success'=> false, 'messages' => array());
          $this->form_validation->set_rules('prod_tags', 'Product Type', 'trim|required');//type
          $this->form_validation->set_rules('prod_name', 'Product Name', 'trim|required|callback__is_unique2');
          $this->form_validation->set_rules('prod_preacher', 'Product Preacher', 'trim|required');
          $this->form_validation->set_rules('prod_price', 'Product Price', 'trim|required');
          $this->form_validation->set_rules('prod_currency', 'Product Currency', 'trim|required');
          $this->form_validation->set_rules('prod_description', 'Product Theme', 'trim|required|max_length[160]');//this is the theme
          $this->form_validation->set_rules('prod_essay', 'Product Description', 'trim|required');//and this is the essay
          //below are the custom reponse messages
          $this->form_validation->set_message('is_unique2', 'The %s is already taken');
          $this->form_validation->set_message('max_length[160]', '%s: the maximum of 160 Characters allowed');
          //push error response into delimiters
          $this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
          if ($this->form_validation->run() === FALSE){
              foreach($_POST as $key =>$value){
                  $data['messages'][$key] = form_error($key);
              }
          }
          else{
            $data['success'] = true;
            $data['message'] = $this->MyModel->update_ts_products($_POST);
          }
          $this->response($data, REST_Controller::HTTP_OK);
          //code ends here
    }else{
      $this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
    }

  }


  //this is a call back
  public function _is_unique2($input) {
        $exclude_id = $_POST['prod_id'];
        if( $this->db->where('prod_name', $input)->where('prod_id !=', $exclude_id)
            ->limit(1)->get('ts_products')->num_rows())
        {
            $this->form_validation->set_message('_is_unique2', 'The product name already exists');
            return FALSE;
        }
        return TRUE;
  }


  public function dashboard_data_get(){
    $response = $this->MyModel->merchant_auth();
    if($response['status']==200){

      $email                            = $this->get('email');
      $data['free_products']            = $this->MyModel->count_free_products($email);
      $data['premium_products']         = $this->MyModel->count_premium_products($email);
      $data['total_product_views']      = $this->MyModel->count_product_views($email);
      $this->response($data, REST_Controller::HTTP_OK);

    }else{
      $this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
    }

  }


  public function merchant_feed_post(){
    $response = $this->MyModel->merchant_auth();
    if($response['status']==200){

      $data= array('success'=> false, 'messages' => array());
      $this->form_validation->set_rules('news_cat', 'Category', 'trim|required');
      $this->form_validation->set_rules('feed_title', 'Title', 'trim|required|is_unique[merchant_feed.title]');
      $this->form_validation->set_rules('feed_message', 'Message', 'trim|required');
      $this->form_validation->set_rules('merchantemail', 'Merchant Email', 'trim|required');
      $this->form_validation->set_rules('file', 'Merchant Image', 'callback_file_check');

      $this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
      if ($this->form_validation->run() === FALSE){
          foreach($_POST as $key =>$value){
              $data['messages'][$key] = form_error($key);
          }
      }
      else{

        //this is where i upload the image for the merchant feed
        $config['upload_path']   = './feed_upload/';
        $config['allowed_types'] = 'gif|jpg|png';//allowing only images
        $config['max_size']      = 1024;
        $this->load->library('upload', $config);

        if ( ! $this->upload->do_upload('file')) {
           $error = array('status'=>false, 'error' => $this->upload->display_errors());
           //echo json_encode($error);
           $this->response($error, REST_Controller::HTTP_OK);
           return false;
        }
        else{

          $data = $this->upload->data();
          $success = ['status'=>true,'success'=>$data['file_name']];
          //echo json_encode($success);
          $img =   'http://myfreshword.com/myfreshword/api/feed_upload/'.$data['file_name'];

          //so run insertion since the validation for the form has been passed correctly
          $data = $this->MyModel->insert_feed_data($_POST,$img);
        }
      }
      $this->response($data, REST_Controller::HTTP_OK);
    }
    else{
      $this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
    }
  }

  //so basically this gets the row data to dsiplay in the
  public function merchant_feed_get(){
    $response = $this->MyModel->merchant_auth();
    if($response['status']==200){
      $id = (int) $this->get('id');
      $data = $this->MyModel->get_merchant_feed_id($id);
      $this->response($data, REST_Controller::HTTP_OK);
    }
    else{
      $this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
    }
  }



  public function merchant_feed_update_post(){
    $response = $this->MyModel->merchant_auth();
    if($response['status']==200){

      // $id = (int) $this->get('id');
      // if ($id <= 0)
      // {
      //     // Invalid id, set the response and exit.
      //     $data['status']  = 404;
      //     $data['message'] = 'Id is invalid';
      //     $this->response($data, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
      //     return false;
      // }
      //
      // if($id == ""){
      //   // Invalid id, set the response and exit.
      //   $data['status']  = 404;
      //   $data['message'] = 'Id can not be empty';
      //   $this->response($data, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
      //   return false;
      // }

      $data= array('success'=> false, 'messages' => array());
      $this->form_validation->set_rules('news_cat', 'Category', 'trim|required');
      $this->form_validation->set_rules('feed_title', 'Title', 'trim|required');
      $this->form_validation->set_rules('feed_message', 'Message', 'trim|required');
      $this->form_validation->set_rules('merchantemail', 'Merchant Email', 'trim|required');
      $this->form_validation->set_rules('file', 'Merchant Image', 'callback_update_file_check');

      $this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
      if ($this->form_validation->run() === FALSE){
          foreach($_POST as $key =>$value){
              $data['messages'][$key] = form_error($key);
          }
      }
      else{
              if($_FILES['file']['name']=="" ){
                $img = '';
                $data = $this->MyModel->update_merchant_feed($_POST['post_id'], $_POST, $_POST['merchantemail'], $img);
                $this->response($data, REST_Controller::HTTP_OK);
                return false;//script will end here
              }

              $config['upload_path']   = './feed_upload/';
              $config['allowed_types'] = 'gif|jpg|png';//allowing only images
              $config['max_size']      = 1024;
              $this->load->library('upload', $config);

              if ( ! $this->upload->do_upload('file')) {
                 $error = array('status'=>false, 'error' => $this->upload->display_errors());
                 //echo json_encode($error);
                 $this->response($error, REST_Controller::HTTP_OK);
                 return false;
              }
              else{

                $data = $this->upload->data();
                $success = ['status'=>true,'success'=>$data['file_name']];
                //echo json_encode($success);
                $img =   'http://myfreshword.com/myfreshword/api/feed_upload/'.$data['file_name'];
                //so run insertion since the validation for the form has been passed correctly
                $data = $this->MyModel->update_merchant_feed($_POST['post_id'], $_POST, $_POST['merchantemail'], $img);
              }
      }
      $this->response($data, REST_Controller::HTTP_OK);
    }
    else{
      $this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
    }
  }

  public function merchant_feed_delete(){
    $response = $this->MyModel->merchant_auth();
    if($response['status']==200){
      $id = (int) $this->get('id');
      $data = $this->MyModel->delete_merchant_feed($id);
      $this->response($data, REST_Controller::HTTP_OK);
    }
    else{
      $this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
    }
  }

  public function update_file_check($str){
    if($_FILES['file']['name']==""){//so here we assume user decided to use the old file upload
      return true;
    }else{
      return $this->file_check($str);
    }
  }

  // call back for checking file directly into one
  public function file_check($str){
      $allowed_mime_type_arr = array('image/gif','image/jpeg','image/pjpeg','image/png','image/x-png');
      $mime = get_mime_by_extension($_FILES['file']['name']);
      if(isset($_FILES['file']['name']) && $_FILES['file']['name']!=""){
          if(in_array($mime, $allowed_mime_type_arr)){
              return true;
          }else{
              $this->form_validation->set_message('file_check', 'Please select only jpeg/jpg/png file.');
              return false;
          }
      }else{
          $this->form_validation->set_message('file_check', 'Please choose a file to upload.');
          return false;
      }
  }



public function merchant_news_feed_get(){
  $response = $this->MyModel->merchant_auth();
  if($response['status']==200){
    $email= $this->get('email');
    $config = array();
    $config["base_url"] = base_url() . "merchant/news_feed";
    $config["total_rows"] = $this->MyModel->count_merchant_feed($email);
    $config["per_page"] = 10;
    $config["uri_segment"] = 3;
    $this->pagination->initialize($config);
    $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

    $data["results"]  = $this->MyModel->get_merchant_feed_data($email);//here we need to take the pagination out for the time
    $data["links"]    = $this->pagination->create_links();
    $data['entries']  = $this->MyModel->count_merchant_feed($email);
    $data['likes']    = $this->MyModel->count_merchant_likes($response['id']);
    $data['comments'] = $this->MyModel->count_merchant_comments($response['id']);
    $this->response($data, REST_Controller::HTTP_OK);
  }
  else{
    $this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
  }
}

public function merchant_search_feed_post(){
  $response = $this->MyModel->merchant_auth();
  if($response['status']==200){
    $_POST = json_decode(file_get_contents('php://input'), TRUE);
    $data['results'] = $this->MyModel->search_merchant_feed($_POST['search'], $_POST['email']);
    $this->response($data, REST_Controller::HTTP_OK);
  }else{
    $this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
  }
}


public function merchant_update_profile_post(){

  $response = $this->MyModel->merchant_auth();
  if($response['status']==200){

    $data= array('success'=> false, 'messages' => array());

    $this->form_validation->set_rules('organisation', 'Organisation ', 'trim|required|is_unique[merchant_feed.title]');
    $this->form_validation->set_rules('merchant_name', 'Merchant Name', 'trim|required');
    $this->form_validation->set_rules('first_name', 'First Name', 'trim|required');
    $this->form_validation->set_rules('last_name', 'Last Name', 'trim|required');
    $this->form_validation->set_rules('email', 'Email', 'trim|required');
    $this->form_validation->set_rules('mobile', 'Mobile', 'trim|required');
    $this->form_validation->set_rules('password', 'Password', 'trim|required');
    $this->form_validation->set_rules('facebook', 'Facebook', 'trim|required');
    $this->form_validation->set_rules('twitter', 'Twitter', 'trim|required');
    $this->form_validation->set_rules('youtube', 'Youtube', 'trim|required');
    $this->form_validation->set_rules('organisation_info', 'Organisation Summary', 'trim|required');
    $this->form_validation->set_rules('org_address', 'Address', 'trim|required');
    $this->form_validation->set_rules('org_country', 'Country', 'trim|required');
    $this->form_validation->set_rules('location', 'Location', 'trim|required');
    $this->form_validation->set_rules('merchant_display_picture', 'Your Profile Display  Image', 'callback_merchant_profile_check');

    $this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
    if ($this->form_validation->run() === FALSE){
        foreach($_POST as $key =>$value){
            $data['messages'][$key] = form_error($key);
        }
    }
    else{

        if($_FILES['merchant_display_picture']['name']==""){
          $q = $this->MyModel->photo_check($_POST['id']);
          $img = $q;
          $data = $this->MyModel->update_merchant_profile($_POST,$img);
          $this->response($data, REST_Controller::HTTP_OK);
          return false;
        }


        //this is where i upload the image for the merchant feed
        $config['upload_path']   = './profile_photos/';
        $config['allowed_types'] = 'gif|jpg|png';//allowing only images
        $config['max_size']      = 1024;
        $this->load->library('upload', $config);

            if ( ! $this->upload->do_upload('merchant_display_picture')) {
               $error = array('status'=>false, 'error' => $this->upload->display_errors());
               //echo json_encode($error);
               $this->response($error, REST_Controller::HTTP_OK);
               return false;
            }
            else{

              $data = $this->upload->data();
              $success = ['status'=>true,'success'=>$data['file_name']];
              //echo json_encode($success);
              $img =   'http://myfreshword.com/myfreshword/api/profile_photos/'.$data['file_name'];

              //so run insertion since the validation for the form has been passed correctly
              $data = $this->MyModel->update_merchant_profile($_POST,$img);
              $this->response($data, REST_Controller::HTTP_OK);
              return false;
            }
    }
    $this->response($data, REST_Controller::HTTP_OK);
  }else{
    $this->response($response, REST_Controller::HTTP_NOT_FOUND);
  }
}

public function merchant_profile_check($str){
    $allowed_mime_type_arr = array('image/gif','image/jpeg','image/pjpeg','image/png','image/x-png');
    $mime = get_mime_by_extension($_FILES['merchant_display_picture']['name']);
    if(isset($_FILES['merchant_display_picture']['name']) && $_FILES['merchant_display_picture']['name']!=""){
        if(in_array($mime, $allowed_mime_type_arr)){
            return true;
        }else{
            $this->form_validation->set_message('merchant_profile_check', 'Please select only jpeg/jpg/png file.');
            return false;
        }
    }else{
        //$this->form_validation->set_message('merchant_profile_check', 'Please choose a file to upload.');
        return true;
    }
}


function merchant_momo_add_post(){
  $response = $this->MyModel->merchant_auth();
  if($response['status']==200){
    $_POST = json_decode(file_get_contents('php://input'), TRUE);
    $data= array('success'=> false, 'messages' => array());
    $this->form_validation->set_rules('network', 'Network ', 'trim|required');
    $this->form_validation->set_rules('mobile', 'Mobile Money Number', 'trim|required');
    $this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
    if ($this->form_validation->run() === FALSE){
        foreach($_POST as $key =>$value){
            $data['messages'][$key] = form_error($key);
        }
    }
    else{
      $momoData = array(
        'merchant_id' => $response['id'],
        'network'     => $_POST['network'],
        'mobile'      => $_POST['mobile']
      );

      $data =  $this->MyModel->save_momo_code($momoData);

      if($data['status'] ==201){
        $this->response($data, REST_Controller::HTTP_CREATED);
        return false;
      }

      if($data['status'] == 204){
        $this->response($data, REST_Controller::HTTP_NO_CONTENT);
        return false;
      }
    }
    $this->response($data, REST_Controller::HTTP_OK);
  }
  else{
    $this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
  }
}


}//end of class
