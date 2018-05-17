<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';
use \Firebase\JWT\JWT;

class App extends REST_Controller {


  public function __construct() {
      parent::__construct();
      $this->load->model('MyModel');
      $this->load->model('MerchantProductModel');

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
    $this->form_validation->set_rules('freshword_transaction_id', 'My Freshword Transaction ID', 'trim|required');
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



  //merchant endpoint starts from here
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
        $_POST = json_decode(file_get_contents('php://input'), TRUE);

        $config['upload_path']   = './uploads/';
        $config['allowed_types'] = 'gif|jpg|png';
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
           $this->response($success, REST_Controller::HTTP_OK);
        }

  }


  //we run this on the success response from the first push
  public function merchant_add_file_post(){

    $config['upload_path']   = './uploads/';
    $config['allowed_types'] = 'mp3|mp4|avi';
    $config['max_size']      = 2024;
    $this->load->library('upload', $config);

    if ( ! $this->upload->do_upload('image')) {
       $error = array('status'=>false, 'error' => $this->upload->display_errors());
       //echo json_encode($error);
       $this->response($error, REST_Controller::HTTP_OK);
    }else {
       $data = $this->upload->data();
       $success = ['status'=>true,'success'=>$data['file_name']];
       //echo json_encode($success);
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
              $row[] = $prod->prod_name;
              $row[] = $prod->prod_preacher;
              $row[] = $prod->prod_church;
              $row[] = $prod->prod_price;
              $row[] = $prod->currency;
              $row[] =  '<img src="'.$prod->img_link.'" height="100px">';
              $row[] = $prod->prod_tags;
              $row[] = $prod->prod_uniqid;
              $row[] = $prod->prod_download_count;
              $row[] = $prod->prod_date;
             	//if($payee->network == 'MTN'):
              $row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Preview" onclick="preview_product('."'".$prod->prod_id."'".')"><i class="fa fa-play"></i> </a>
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
    $_POST = json_decode(file_get_contents('php://input'), TRUE);
    $data= array('success'=> false, 'messages' => array());
    $this->form_validation->set_rules('prod_tags', 'Product Type', 'trim|required');//type
    $this->form_validation->set_rules('prod_name', 'Product Name', 'trim|required|is_unique[ts_products.prod_name]');
    $this->form_validation->set_rules('prod_preacher', 'Product Preacher', 'trim|required');
    $this->form_validation->set_rules('prod_price', 'Product Price', 'trim|required');
    $this->form_validation->set_rules('prod_currency', 'Product Currency', 'trim|required');
    $this->form_validation->set_rules('prod_description', 'Product Theme', 'trim|required|max_length[160]');//this is the theme
    $this->form_validation->set_rules('prod_essay', 'Product Description', 'trim|required');//and this is the essay
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
        'prod_description'      =>      $_POST['prod_theme'],
        'prod_essay'            =>      $_POST['prod_description'],
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
        'currency'              =>      $_POST['prod_currency']

      );
      $query = $this->MyModel->merchant_insert_product($prodData);
      $data = array('success'=>true,'message'=>$query);
    }
    $this->response($data, REST_Controller::HTTP_OK);
  }



}//end of class
