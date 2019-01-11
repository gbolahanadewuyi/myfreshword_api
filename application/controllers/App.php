<?php

defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST_Controller.php';

require_once APPPATH . '/libraries/JWT.php';

require_once APPPATH . '/libraries/HubtelApi.php';

// use Cloudinary;
// use Stripe\Stripe;
// use \Firebase\JWT\JWT;

class App extends REST_Controller

{
	public function __construct()
	{
		// header('Access-Control-Allow-Origin: *');
    	// header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
		parent::__construct();
		$this->load->model('MyModel');
		$this->load->model('MerchantProductModel');
		$this->load->model('MerchantFeedModel');
		$this->load->model('MerchantPastorModel');
		$this->load->model('MerchantMembersModel');
		$this->load->library('hubtelApi');
		$this->load->library('cloudinarylib');
		$this->load->library('email');
	}

	public function isLoggedin_post()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);
		$response = $this->MyModel->auth($_POST['id'], $_POST['token']);
		if ($response['status'] == 200) {
			$this->response($response, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function login_post()
	{

		// get from json body

		$_POST = json_decode(file_get_contents('php://input'), true);
		$username = $_POST['username'];
		$password = $_POST['password'];
		$response = array(
			'success' => false,
			'messages' => array()
		);
		$this->form_validation->set_rules('username', 'Username', 'trim|required');
		$this->form_validation->set_rules('password', 'Password', 'trim|required');
		$this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
		if ($this->form_validation->run() === false) {
			foreach ($_POST as $key => $value) {
				$response['messages'][$key] = form_error($key);
			}
		} else {
			$response = $this->MyModel->login($username, $password);
		}

		$this->response($response, REST_Controller::HTTP_OK);
	}

	public function logout_post()
	{
		$response = $this->MyModel->logout();
		$this->response($response, REST_Controller::HTTP_OK);
	}

	// NEW LOGIN FOR MYFRESHWORD LOGIN  PAGE

	public function mobile_login_post()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);
		$user_mobile = $_POST['mobile'];
		$password = $_POST['password'];
		$response = array(
			'success' => false,
			'messages' => array()
		);
		$this->form_validation->set_rules('mobile', 'Mobile Number', 'trim|required');
		$this->form_validation->set_rules('password', 'Password', 'trim|required');
		$this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
		if ($this->form_validation->run() === false) {
			foreach ($_POST as $key => $value) {
				$response['messages'][$key] = form_error($key);
			}

			$this->response($response, REST_Controller::HTTP_OK);
			return false;
		} else {
			$response = $this->MyModel->mobile_login($user_mobile, $password);
			if ($response['status'] == 204) { //no content
				$this->response($response, REST_Controller::HTTP_OK);
				return false;
			}

			if ($response['status'] == 500) {
				$this->response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return false;
			}

			if ($response['status'] == 200) {
				$this->response($response, REST_Controller::HTTP_OK);
				return false;
			}
		}
	}

	// user forgot password resets the same process in themeportal web

	public function forgot_password_post()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);
		$data = array(
			'success' => false,
			'messages' => array()
		);
		$this->form_validation->set_rules('emailadd', 'Email', 'required');
		$this->form_validation->set_error_delimiters('<span>', '</span>');
		if ($this->form_validation->run() === false) {
			foreach ($_POST as $key => $value) {
				$data['messages'][$key] = form_error($key);
			}
		} else {
			$data = $this->MyModel->forgot_password_email($_POST['emailadd']);
			if ($data['status'] == 204) {
				$this->response($data, REST_Controller::HTTP_OK);
			} else {
				$q = $this->MyModel->generate_short_code();
				$updateData = array(
					'user_key' => $data['key'],
					'user_reset_code' => $q
				);
				$id = $data['id'];
				$data = array(
					'success' => $this->MyModel->update_key($id, $updateData),
					'number' => $data['link']
				);
				$this->MyModel->send_code($data['number'], $q);
			}
		}

		$this->response($data, REST_Controller::HTTP_OK);
	}

	// get registration details from the user post with default role key
	// pass user role from the server side
	// activation for user who signs up by using email verification
	//

	public function sign_up_post()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);
		$data = array(
			'success' => false,
			'messages' => array()
		);
		$this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[5]|is_unique[ts_user.user_uname]');
		$this->form_validation->set_rules('mobile', 'Mobile', 'trim|required|min_length[10]|is_unique[ts_user.user_mobile]');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|is_unique[ts_user.user_email]');
		$this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[8]');
		$this->form_validation->set_rules('church_id', 'church ID', 'trim|required');
		$this->form_validation->set_message('is_unique', 'The %s is already taken');
		$this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
		if ($this->form_validation->run() === false) {
			foreach ($_POST as $key => $value) {
				$data['messages'][$key] = form_error($key);
			}
		} else {
			$q = $this->MyModel->generate_short_code();
			$data = array(
				'user_uname' => $_POST['username'],
				'user_email' => $_POST['email'],
				'user_mobile' => $_POST['mobile'],
				'user_pwd' => md5($_POST['password']),
				'user_church_id' => $_POST['church_id'],
				'user_key' => $key = md5(date('his') . $_POST['email']),
				'user_accesslevel' => 2,
				'user_status' => 2,
				'user_activation_code' => $q,
				'image_url' => '../assets/img/user-img.png'
			);
			$data = $this->MyModel->create_user($data);
			$this->MyModel->sendEmail($_POST['email']);
			$this->MyModel->send_code($_POST['mobile'], $q);


			// $this->mail_user($_POST['email'], 'Registration', 'Click link to confirm and activate account thank you: ' .'http://192.168.1.3/themeportal/authenticate/login/'.$key);

		}

		$this->response($data, REST_Controller::HTTP_OK);
	}

	function confirmEmail_post($hashcode){
        if($this->MyModel->verifyEmail($hashcode)){
             echo "active";
            redirect('www.google.com'); 
        }else{
             echo "not active";
            redirect('www.facebook.co');

    }
    
	}

	public function activate_account_post()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);
		$data = array(
			'success' => false,
			'messages' => array()
		);
		$this->form_validation->set_rules('code', 'code', 'trim|required|min_length[4]|max_length[4]|numeric');
		$this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
		if ($this->form_validation->run() === false) {
			foreach ($_POST as $key => $value) {
				$data['messages'][$key] = form_error($key);
			}
		} else {
			$q = $this->MyModel->activate_account($_POST['code']);
			if ($q['status'] == 200) {
				$data = array(
					'status' => 200,
					'message' => 'Account activated successfully'
				);
			} else {
				$data = array(
					'status' => 204,
					'message' => 'Problem with activation code please resend'
				);
			}
		}

		$this->response($data, REST_Controller::HTTP_OK);
	}

	public function reset_password_post()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);
		$data = array(
			'success' => false,
			'messages' => array()
		);
		$this->form_validation->set_rules('code', 'code', 'trim|required|min_length[4]|max_length[4]|numeric');
		$this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
		if ($this->form_validation->run() === false) {
			foreach ($_POST as $key => $value) {
				$data['messages'][$key] = form_error($key);
			}
		} else {
			$q = $this->MyModel->reset_password_code($_POST['code']);
			if ($q['status'] == 200) {
				$data = array(
					'status' => 200,
					'message' => 'passed',
					'user_email' => $q['email']
				);
			} else {
				$data = array(
					'status' => 204,
					'message' => 'Wrong Pin..Type correct reset pin sent via sms'
				);
			}
		}

		$this->response($data, REST_Controller::HTTP_OK);
	}

	public function new_password_post()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);
		$data = array(
			'success' => false,
			'messages' => array()
		);
		$this->form_validation->set_rules('email', 'Email', 'trim|required');
		$this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[8]');
		$this->form_validation->set_rules('passwordAgain', 'Password', 'trim|required|matches[password]');
		$this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
		if ($this->form_validation->run() === false) {
			foreach ($_POST as $key => $value) {
				$data['messages'][$key] = form_error($key);
			}
		} else {
			$dataInsert = array(
				'user_pwd' => md5($_POST['password'])
			);
			$data = $this->MyModel->update_password($_POST['email'], $dataInsert);
		}

		$this->response($data, REST_Controller::HTTP_OK);
	}

	public function resend_post()
	{ //this will stored by default
		$_POST = json_decode(file_get_contents('php://input'), true);
		if ($_POST['mobile'] == null) {
			$data = array(
				'status' => 401,
				'message' => 'Unauthorized.'
			);
		} else {
			$pin = $this->MyModel->generate_short_code();
			$q = $this->MyModel->send_code($_POST['mobile'], $pin);
			$updateData = array(
				'user_activation_code' => $pin
			);
			$data = $this->MyModel->update_key_mobile($_POST['mobile'], $updateData);
		}

		$this->response($data, REST_Controller::HTTP_OK);
	}

	public function preachers_get()
	{
		$query = $this->MyModel->get_all_preachers();
		$this->response($query, REST_Controller::HTTP_OK);
	}

	protected function mail_user($toEmail, $subject, $message)
	{
		$data = array(
			'email' => 'admin@myfreshword.com',
			'name' => 'administrator',
			'toEmail' => $toEmail,
			'subject' => $subject,
			'message' => $message
		);
		$this->MyModel->send_data_mail($data);
	}

	// list all the number of churches user is registered on

	public function church_list_get()
	{
		$data = $this->MyModel->church_all_data();
		$this->response($data, REST_Controller::HTTP_OK);
	}

	public function feed_post()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);
		$data = array(
			'success' => false,
			'messages' => array()
		);
		$this->form_validation->set_rules('denomination', 'Denomination', 'trim|required');

		// $this->form_validation->set_rules('pastors[]', 'Preacher', 'count_array_check');
		// $this->form_validation->set_rules('sermon[]', 'Sermon Topics', 'count_array_check');

		$this->form_validation->set_rules('email', 'Email', 'trim|required');
		$this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
		if ($this->form_validation->run() === false) {
			foreach ($_POST as $key => $value) {
				$data['messages'][$key] = form_error($key);
			}
		} else {
			$dataInsert = array(
				'user_email' => $_POST['email'],
				'denomination' => $_POST['denomination'],
				'fav_preachers' => implode(", ", $_POST['pastors']),
				'sermon_topics' => implode(", ", $_POST['sermon']),
			);
			$data = $this->MyModel->feed_data($dataInsert);
		}

		$this->response($data, REST_Controller::HTTP_OK);
	}

	/*

	// THIS PART CALLS API FOR DATA AFTER USER HAS LOGGED IN SUCCESSFULLY

	 */
	public function change_password_post()
	{
	}

	public function facebook_login_post()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);
		$data = array(
			'fb_id' => $_POST['id'],
			'fb_email' => $_POST['email'],
			'user_email' => $_POST['email'],
			'fb_name' => $_POST['name'],
			'fb_gender' => $_POST['gender']
		);
		$query = $this->MyModel->facebook_data($data);
		$this->response($query, REST_Controller::HTTP_OK);
	}

	public function google_login_post()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);
		$data = array(
			'g_user_id' => $_POST['id'],
			'g_email' => $_POST['email'],
			'user_email' => $_POST['email'],
			'g_display_name' => $_POST['name']
		);
		$query = $this->MyModel->google_data($data);
		$this->response($query, REST_Controller::HTTP_OK);
	}

	// get user profile data by id and apikey

	public function user_profile_get()
	{
		$response = $this->MyModel->auth($this->get('userid'), $this->get('token'));
		if ($response['status'] == 200) { //if header is passed
			$resp = $this->MyModel->user_profile_data($response['id']);
			$this->response($resp, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	// //update user profile like password and the details
	// public function user_profile_post(){
	//
	// }

	public function all_product_get()
	{
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$resp = $this->MyModel->audio_all_data(); //this is pulling all data not just audio
			$this->response($resp, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function product_by_id_get()
	{
		// $response = $this->MyModel->auth($this->get('userid'), $this->get('token'));
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$id = (int)$this->get('p_id');
			$resp = $this->MyModel->product_id();
			$this->response($resp, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function mobile_money_post()
	{
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$_POST = json_decode(file_get_contents('php://input'), true);
			$data = array(
				'success' => false,
				'messages' => array()
			);
			$this->form_validation->set_rules('network', 'Mobile Network', 'trim|required');
			$this->form_validation->set_rules('number', 'Mobile Number', 'trim|required|min_length[10]|max_length[12]|is_unique[momo.payin_number]');
			$this->form_validation->set_message('is_unique', 'The %s is already taken');
			$this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
			if ($this->form_validation->run() === false) {
				foreach ($_POST as $key => $value) {
					$data['messages'][$key] = form_error($key);
				}
			} else { //if this return correct results then we need to show success message and store  data locally on phone
				$query = $this->MyModel->new_momo($_POST['number']);
				if ($query['valid'] === false) {
					$data['error'] = array(
						'status' => false,
						'message' => 'Invalid Mobile Money Number'
					);
				} else
					if ($query['valid'] === true) {
					$data = array(
						'success' => true,
						'message' => 'Valid Mobile Money Number',
						'results' => $query
					);

					// now save momo details into the table

					$dB = array(
						'network' => $_POST['network'],
						'payin_number' => $_POST['number'],
						'unique_acc' => $_POST['email']
					);
					$dBquery = $this->MyModel->insert_momo($dB);
				}

				// ends here for the numerify validation

			}

			$this->response($data, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
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

	public function momo_app_post()
	{
		$responseHead = $this->MyModel->header_auth();
		if ($responseHead['status'] == 200) {
			$data = json_decode(file_get_contents('php://input'), true);
			$query = $this->MyModel->momo_by_id($data['email']);
			if ($query == false) {

				// do nothing

				$response['success'] = false;
				$response['message'] = 'User has not set up mobile money';
			} else {
				$response['success'] = true;
				$response['message'] = 'User has set up mobile money';
				$response['results'] = $query;
			}

			$this->response($response, REST_Controller::HTTP_OK);
		} else {
			$this->response($responseHead, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function momo_default_post()
	{
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$_POST = json_decode(file_get_contents('php://input'), true);
			$q = $this->MyModel->set_momo_default($_POST);
			$this->response($q, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	// this should pull data

	public function get_momo_data_post()
	{
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$_POST = json_decode(file_get_contents('php://input'), true);
			$data = array(
				'email' => $_POST['email']
			);
			$q = $this->MyModel->user_momo_numbers($data);
			$this->response($q, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function credit_card_post()
	{
		$data = json_decode(file_get_contents('php://input'), true);
		$query = $this->MyModel->bin_checker($data['bin']);
		$this->response($query, REST_Controller::HTTP_OK);
	}

	public function head_post()
	{
		$query = $this->MyModel->check_auth_client();
		$this->response($query, REST_Controller::HTTP_OK);
	}

	public function cardAdd_post()
	{ //this to add products to carts
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$dataPost = json_decode(file_get_contents('php://input'), true);
			$data = array(
				'prod_uniqid' => $dataPost['prod_uniqid'],
				'prod_description' => $dataPost['prod_description'],
				'prod_name' => $dataPost['prod_name'],
				'prod_price' => $dataPost['prod_price'],
				'prod_quantity' => $dataPost['prod_quantity'],
				'prod_img_link' => $dataPost['prod_img_link'],
				'prod_purchase_by' => $dataPost['prod_purchase_by'],
				'paid' => $dataPost['paid'],
				'file_link' => $dataPost['file_link'],
				'prod_type' => $dataPost['type_list'],
				'transactionid' => $this->MyModel->trans_rotate($dataPost['prod_purchase_by']) //if it hasnt been paid dont generate new transaction id
			);
			$query['insert_query'] = $this->MyModel->addToCart($data);
			$query['item_in_cart'] = $this->MyModel->cartRowCount($data);
			$query['total_price'] = $this->MyModel->TotalCartSales($data);
			$this->response($query, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	// on page load post run and get necessary data from cart

	public function cart_status_post()
	{
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$dataPost = json_decode(file_get_contents('php://input'), true);
			$data = array(
				'prod_purchase_by' => $dataPost['prod_purchase_by'],
			);
			$query['cart_data'] = $this->MyModel->fetch_cart_data($data);
			$query['item_in_cart'] = $this->MyModel->cartRowCount($data);
			$query['total_price'] = $this->MyModel->TotalCartSales($data);
			$this->response($query, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function remove_cart_item_post()
	{
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$param = json_decode(file_get_contents('php://input'), true);
			$data = array(
				'id' => $param['id'],
				'prod_purchase_by' => $param['email']
			);
			$query = $this->MyModel->delete_cart_data($data);
			$this->response($query, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function library_post()
	{
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$param = json_decode(file_get_contents('php://input'), true);
			$data = array(
				'userid' => $response['id']
			);
			$query = $this->MyModel->library_data($data['userid']);
			$this->response($query, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function checkout_post()
	{
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$_POST = json_decode(file_get_contents('php://input'), true);

			// so here when you make payment
			// $this->db->insert_batch('ts_paid_prod', $_POST['cart_data']);

			foreach ($_POST['cart_data'] as $db_data) {
				$data = array(
					'prod_name' => $db_data['prod_name'],
					'prod_uniqid' => $db_data['prod_uniqid'],
					'file_link' => $db_data['file_link'],
					'type' => $db_data['prod_type'],
					'paid' => 1,
					'user_acc' => $db_data['prod_purchase_by'],
					'img_link' => $db_data['prod_img_link'],
					'prod_price' => $db_data['prod_price']
				);
				$this->db->insert('ts_paid_prod', $data);
			}

			$this->response($_POST['cart_data'], REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function checkout_all_free_post()
	{
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$_POST = json_decode(file_get_contents('php://input'), true);
			foreach ($_POST['cart_data'] as $db_data) {
				$data = array(
					'prod_name' => $db_data['prod_name'],
					'prod_uniqid' => $db_data['prod_uniqid'],
					'file_link' => $db_data['file_link'],
					'type' => $db_data['prod_type'],
					'paid' => 0,
					'free' => 1,
					'user_acc' => $db_data['prod_purchase_by'],
					'img_link' => $db_data['prod_img_link'],
					'prod_price' => $db_data['prod_price']
				);
				$this->db->insert('ts_paid_prod', $data);
			}

			// $data = array(
			//   'prod_purchase_by' => $param['email']
			// );

			$message = array(
				'status' => 201,
				'message' => 'Free items success'
			);
			$this->response($message, REST_Controller::HTTP_CREATED);

			// running a clear cart data from the mobile application side
			// $q = $this->MyModel->delete_all_cart($data);
			//
			// if($q['status'] == 200){
			//
			// }
			//
			//
			// $message = array('status'=>200, 'message'=>'Error updating cart after purchase');
			// $this->response($message, REST_Controller::HTTP_OK);//using these responses for nathan

			return false;
		}

		$this->response($response, REST_Controller::HTTP_NOT_FOUND);
	}

	public function subscribe_post()
	{
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$_POST = json_decode(file_get_contents('php://input'), true);
			$userid = $response['id'];
			$subscriptionPackage = $_POST['subscriptionType'];


			$query = $this->MyModel->subscribe($userid, $subscriptionPackage);
			$this->response($query, REST_Controller::HTTP_OK);

		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}


	public function isSubscribed_post()
	{
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$userid = $response['id'];

			$query = $this->MyModel->isSubscribed($userid);
			$this->response($query, REST_Controller::HTTP_OK);

		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}
	/*
	|--------------------------------------------------------------------------
	| Controller method for Adding An Item To Library
	|--------------------------------------------------------------------------
	|
	| Here is where you add a product to the users library once they tab add
	| Now create something great!
	|
	 */
	public function addto_library_post()
	{
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$dataPost = json_decode(file_get_contents('php://input'), true);

			// foreach($_POST['cart_data'] as $db_data){

			$data = array(
				'prod_uniqid' => $dataPost['prod_uniqid'],
				'userid' => $dataPost['userid']
			);
			$query['insert_query'] = $this->MyModel->addto_library($data);

			// }

			$message = array(
				'status' => 201,
				'message' => 'Items Added To Library Success'
			);
			$this->response($message, REST_Controller::HTTP_CREATED);
			return false;
		}

		$this->response($response, REST_Controller::HTTP_NOT_FOUND);
	}

	public function delete_file_delete()
	{
		$id = (int)$this->get('id');
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$query = $this->MyModel->delete_library_data($response['id']);
			if ($query['status'] == 204) {
				$this->response($query, REST_Controller::HTTP_OK);
			} else {
				$message = array(
					'status' => 400,
					'message' => 'error deleting product'
				);
				$this->response($message, REST_Controller::HTTP_OK);
			}
		} else {
			$this->response($data, REST_Controller::HTTP_OK);
		}
	}

	public function clear_library_post()
	{
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {

			// run this endpoint after user checked out and paid all

			$_POST = json_decode(file_get_contents('php://input'), true);
			$q = $this->MyModel->delete_library_data($_POST['email']);
			if ($q == true) {
				$data = array(
					'status' => 200,
					'message' => 'Cart data cleared'
				);
			} else {
				$data = array(
					'status' => 400,
					'message' => 'Error with cart data processing'
				);
			}

			$this->response($data, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function process_payment_post()
	{
	}

	public function sms_enable_post()
	{
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$param = json_decode(file_get_contents('php://input'), true);
			$data = array(
				'sms_notify' => $param['notify']

				// 'email'       => $param['email'],
				//  'id'         => $param['id'],//this will be the profile id
				//  'mobile'     => $param['mobile']

			);
			$query = $this->MyModel->sms_enable($data);
			$this->response($query, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function email_enable_post()
	{
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$param = json_decode(file_get_contents('php://input'), true);
			$data = array(
				'email_notify' => $param['notify']
			);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function profile_update_post()
	{
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$_POST = json_decode(file_get_contents('php://input'), true);
			$data = array(
				'success' => false,
				'messages' => array()
			);
			$this->form_validation->set_rules('id', 'User Profile ID', 'trim|required|numeric');
			$this->form_validation->set_rules('username', 'Username', 'trim|required');
			$this->form_validation->set_rules('mobile', 'Mobile Number', 'trim|required');
			$this->form_validation->set_rules('password', 'Password', 'trim|required');
			$this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
			if ($this->form_validation->run() === false) {
				foreach ($_POST as $key => $value) {
					$data['messages'][$key] = form_error($key);
				}
			} else {
				$data = array(
					'user_uname' => $_POST['username'],
					'user_mobile' => $_POST['mobile'],
					'user_pwd' => $_POST['password']
				);
				$id = $_POST['id'];
				$data = $this->MyModel->update_user_profile($id, $data);
			}

			$this->response($data, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function upload_profile_photo_post()
	{
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$sname = $response['id'];

			$my_bucket = "techloft-173609.appspot.com";
			$file_name = $_FILES['photo']['name'];
			$temp_name = $_FILES['photo']['tmp_name'];
	
			$image = file_get_contents($_FILES['photo']['tmp_name']);
			// echo $temp_name;
			// echo $file_name;
			// echo $image;
	
			$options = ['gs' => ['Content-Type' => 'image/jpeg']];
			$context = stream_context_create($options); 
			$fileName = "gs://${my_bucket}/$sname.jpg";
			file_put_contents($fileName, $image, 0, $context);
			// $publicUrl = CloudStorageTools::getPublicUrl($fileName, false);
              
			//  echo $publicUrl;
     
			//  $url = file_get_contents($fileName);

			// move_uploaded_file($temp_name, $file);
			 $publicurl = "https://storage.googleapis.com/techloft-173609.appspot.com/$sname.jpg";
			 $profilefeed = array(
			     'image_url' => $publicurl
			   );

				  $data['messages'] = $this->MyModel->update_user_profile($sname, $profilefeed);
                 	$success = ['status' => true, 'success' => $publicurl];
			 	$this->response($data, REST_Controller::HTTP_OK);

		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	//this shooud be the response for the payment

	public function do_upload_post()

	{
		// $id = $_POST['id'];

		// $this->load->libaray('cloudinarylib');

		// $file = $this->input->post('photo');


		// $data['image'] = "https://api.cloudinary.com/v1_1/techloft-company-ltd/image/upload";



		//  $imageurl = $data['image'];

		//  echo $imageurl;
		
            
        $my_bucket = "techloft-173609.appspot.com";
		$file_name = $_FILES['photo']['name'];
		$temp_name = $_FILES['photo']['tmp_name'];

		$image = file_get_contents($_FILES['photo']['tmp_name']);
		echo $temp_name;
		echo $file_name;
		echo $image;

		$options = ['gs' => ['Content-Type' => 'image/jpeg']];
		$context = stream_context_create($options); 
		$fileName = "gs://${my_bucket}/gman.jpg";
		$file = "gs://${my_bucket}/.jpg";
		// file_put_contents("gs://${my_bucket}/gman.jpg", $temp_name, 0 , $context);
		file_put_contents($fileName, $image, 0, $context);

		move_uploaded_file($temp_name, $file);
		// \Cloudinary\Uploader::upload($temp_name,
		// array( "overwrite" => TRUE,
		//  "resource_type" => "image"));



		// $my_bucket = "freshword-ci";
		// $options = ['gs' => ['Content-Type' => 'image/jpeg']];
		// $context = stream_context_create($options);


		// $config['upload_path'] = "gs://${my_bucket}/ ";



		// $config['overwrite'] = true;
		// $config['file_ext_tolower'] = true;
		// $config['allowed_types'] = 'gif|jpg|png|jpeg'; //allowing only images
		// $config['max_size'] = 0;
		// $this->load->library('upload', $config);

		// $this->upload->initialize($config);

		// if (!$this->upload->do_upload('photo')) {
		// 	$error = array(
		// 		'status' => false,
		// 		'uploadpath' => $config['upload_path'],
		// 		'error' => $this->upload->display_errors()
		// 	);

		// 		// echo json_encode($error);

		// 	$this->response($error, REST_Controller::HTTP_OK);
		// } else {
		// 	$data = $this->upload->data();
		// 	// $file = $data['file_name'];
		// 	$file = $data['file_name'];
		// 	$path = $data['file_path'];
		// 	echo $file;
		// 	$fileurl = "https://storage.cloud.google.com/${my_bucket}/$file";
		// 	//    echo $fileurl;



			//    $data['image']  =    \Cloudinary\Uploader::upload($file);

		// $collect = \Cloudinary\Uploader::upload("gs://${my_bucket}/$file");
		// print_r($collect);
	                //    $img = $collect['image'] ;



			// $img = "https://storage.cloud.google.com/${my_bucket}/$file?organizationId=96831556031&_ga=2.83358422.-1152930877.1539685883";
			// $success = ['status' => true, 'success' => $img];

	// $data['messages'] = $this->MyModel->update_user_profile($id, $profilefeed);



			$this->response($temp_name, REST_Controller::HTTP_OK);

	}













	public function payment_response_post()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);
		$data = array(
			'success' => false,
			'messages' => array()
		);

		// $this->form_validation->set_rules('status', 'Rest Status Code', 'trim|required|numeric');//preferred not to be passed

		$this->form_validation->set_rules('success', 'Success Boolean', 'trim|required');
		$this->form_validation->set_rules('message', 'Message', 'trim|required');
		$this->form_validation->set_rules('network', 'Mobile Money Network', 'trim|required');
		$this->form_validation->set_rules('phone_number', 'Phone Number', 'trim|required|numeric');
		$this->form_validation->set_rules('amount', 'Transaction Amount', 'trim|required|numeric');
		$this->form_validation->set_rules('freshword_transaction_id', 'My Freshword Transaction ID', 'trim|required'); //this should be tied to the array of products being purchased
		$this->form_validation->set_rules('payin_transaction_id', 'Payin Transaction ID', 'trim|required');
		$this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
		if ($this->form_validation->run() === false) {
			foreach ($_POST as $key => $value) {
				$data['messages'][$key] = form_error($key);
			}
		} else {
			$data = $this->MyModel->callback_response($_POST);
		}

		$this->response($data, REST_Controller::HTTP_OK);
	}

	// data passed here should just contain the following
	// transactionid
	//

	public function process_cart_payment_post()
	{
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$_POST = json_decode(file_get_contents('php://input'), true);
			$data = array(
				'success' => false,
				'messages' => array()
			);
			$this->form_validation->set_rules('success', 'Success Boolean', 'trim|required');
			$this->form_validation->set_rules('status', 'status', 'trim|required');
			$this->form_validation->set_rules('message', 'Message', 'trim|required');
			$this->form_validation->set_rules('network', 'network', 'trim|required');
			$this->form_validation->set_rules('phonenumber', 'Phone Number', 'trim|required|numeric');
			$this->form_validation->set_rules('amount', 'amount', 'trim|required|numeric');
			$this->form_validation->set_rules('freshword_transaction_id', 'Freshword Transaction Id', 'trim|required');
			$this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
			if ($this->form_validation->run() === false) {
				foreach ($_POST as $key => $value) {
					$data['messages'][$key] = form_error($key);
				}
			} else {
				$payData = array(
					'success' => $_POST['success'],
					'status' => $_POST['status'],
					'message' => $_POST['message'],
					'network' => $_POST['network'],
					'phone_number' => $_POST['phonenumber'],
					'amount' => $_POST['amount'],
					'freshword_transaction_id' => $_POST['freshword_transaction_id']
				);
				$data['success'] = true;
				$data['messages'] = $this->MyModel->payment_to_db($payData);
			}

			$this->response($data, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function comments_title_post()
	{
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$_POST = json_decode(file_get_contents('php://input'), true);
			$data = array(
				'prod_id' => $_POST['id'],
				'comment_title' => $_POST['title']
			);
			$q = $this->MyModel->get_comment_title_data($data);
			if ($q['status'] == 204) {
				$this->response($q, REST_Controller::HTTP_NOT_FOUND);
			} else {
				$this->response($q, REST_Controller::HTTP_OK);
			}
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function product_search_query_post()
	{
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$_POST = json_decode(file_get_contents('php://input'), true);
			$q = $this->MyModel->search_product($_POST['prod_search']);
			$this->response($q, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function feed_search_query_post()
	{
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$_POST = json_decode(file_get_contents('php://input'), true);
			$q = $this->MyModel->search_all_feed($_POST['feed_search']);
			$this->response($q, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function filter_audio_get()
	{
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$q = $this->MyModel->audio_fetch();
			if (count($q) > 0) {
				$this->response($q, REST_Controller::HTTP_OK);
			} else {
				$this->response($q, REST_Controller::HTTP_NO_CONTENT);
			}
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function filter_video_get()
	{
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$q = $this->MyModel->video_fetch();
			if (count($q) > 0) {
				$this->response($q, REST_Controller::HTTP_OK);
			} else {
				$this->response($q, REST_Controller::HTTP_NO_CONTENT);
			}
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function filter_book_get()
	{
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$q = $this->MyModel->book_fetch();
			if (count($q) > 0) {
				$this->response($q, REST_Controller::HTTP_OK);
			} else {
				$this->response($q, REST_Controller::HTTP_NO_CONTENT);
			}
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	/*
	 */
	public function filter_audio_search_post()
	{
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$_POST = json_decode(file_get_contents('php://input'), true);
			$q = $this->MyModel->audio_by_title($_POST['feed_search']);
			if (count($q) > 0) {
				$this->response($q, REST_Controller::HTTP_OK);
			} else {
				$this->response($q, REST_Controller::HTTP_NO_CONTENT);
			}
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function filter_video_search_post()
	{
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$_POST = json_decode(file_get_contents('php://input'), true);
			$q = $this->MyModel->video_by_title($_POST['feed_search']);
			if (count($q) > 0) {
				$this->response($q, REST_Controller::HTTP_OK);
			} else {
				$this->response($q, REST_Controller::HTTP_NO_CONTENT);
			}
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function filter_book_search_post()
	{
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$_POST = json_decode(file_get_contents('php://input'), true);
			$q = $this->MyModel->book_by_title($_POST['feed_search']);
			if (count($q) > 0) {
				$this->response($q, REST_Controller::HTTP_OK);
			} else {
				$this->response($q, REST_Controller::HTTP_NO_CONTENT);
			}
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
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
	public function web_products_get()
	{
		$resp = $this->MyModel->audio_all_data(); //this is pulling all data not just audio
		$this->response($resp, REST_Controller::HTTP_OK);
	}

	public function merchant_register_post()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);
		$data = array(
			'success' => false,
			'messages' => array()
		);
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
		if ($this->form_validation->run() === false) {
			foreach ($_POST as $key => $value) {
				$data['messages'][$key] = form_error($key);
			}
		} else {
			$regData = array(
				'first_name' => $_POST['firstname'],
				'last_name' => $_POST['lastname'],
				'email' => $_POST['email'],
				'mobile' => $_POST['mobile'],
				'password' => hash('sha256', $_POST['password']),
				'organisation' => $_POST['organisation'],
				'location' => $_POST['location'],
				'merchant_name' => $_POST['merchantname'],
				'approval_code' => $this->MyModel->generate_merchant_activation_code()
			);
			// $data['sms'] = $this->MyModel->send_code($regData['mobile'], $regData['approval_code']);
			$data['success'] = true;
			$data['messages'] = $this->MyModel->create_merchant($regData);
		}

		$this->response($data, REST_Controller::HTTP_OK);
	}

		/*
		|--------------------------------------------------------------------------
		| Send Bulk SMS Controller Method
		|--------------------------------------------------------------------------
		|
		| Here is where you can register web routes for your application. These
		|
	 */
	public function merchant_sendbulksms_post()
	{
		$response = $this->MyModel->merchant_auth();
		if ($response['status'] == 200) {
			$_POST = json_decode(file_get_contents('php://input'), true);
			$data = array(
				'success' => false,
				'messages' => array()
			);
			$this->form_validation->set_rules('mobile_number', 'Mobile Number', 'trim|required');
			$this->form_validation->set_rules('sender_id', 'Sender ID', 'trim|required');
			$this->form_validation->set_rules('message_content', 'Message Content', 'trim|required');
			$this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
			if ($this->form_validation->run() === false) {
				foreach ($_POST as $key => $value) {
					$data['messages'][$key] = form_error($key);
				}
			} else {
				$smsData = array(
					'mobile_number' => $_POST['mobile_number'],
					'sender_id' => $_POST['sender_id'],
					'message_content' => $_POST['message_content']
				);

				$smsData = array_shift($smsData);

				$data['Success'] = true;
				//Set Time Zone as this is very important to ensure your messages are delievered on time
				date_default_timezone_set('Africa/Accra');

				// Account details
				$clientId = '5ac33950c8e46';
				$applicationSecret = '5bcf90580c9db6811e8d3d1e49b9dd6d';

				// Prepare data for POST request
				$url = 'https://app.helliomessaging.com/api/v2/sms';
				$currentTime = date('YmdH');
				$hashedAuthKey = sha1($clientId . $applicationSecret . $currentTime);
				$senderId = $smsData['sender_id']; //Change this to your sender ID e.g. HellioSMS
				$msisdn = $smsData['mobile_number']; //Change this to the recipient you wish to send the message to
				$message = $smsData['message_content']; //The message to be send here
				$params = [
					'clientId' => $clientId,
					'authKey' => $hashedAuthKey,
					'senderId' => $senderId,
					'msisdn' => $msisdn,
					'message' => $message
				];

				// Send the POST request with cURL
				$ch = curl_init($url);
				$payload = json_encode($params);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-Type: application/json',
					'Content-Length: ' . strlen($payload)
				));

				// Process your response here
				$result = curl_exec($ch);
				echo var_export($result, true);
				curl_close($ch);

				// $data['Messages'] = $this->MyModel->sendbulksms_message($smsData);
			}

			$this->response($data, REST_Controller::HTTP_OK);
		}
	}

	//End Send Bulk SMS Block

	public function church_resident_post()
	{
		$response = $this->MyModel->merchant_auth();
		if ($response['status'] == 200) {
		// $_POST = json_decode(file_get_contents('php://input'), true);
		$data = array(
			'success' => false,
			'messages' => array()
		);
		$this->form_validation->set_rules('rfirst_name', 'First Name', 'trim|required');
		$this->form_validation->set_rules('rlast_name', 'Last Name', 'trim|required');
		$this->form_validation->set_rules('r_title', 'Title', 'trim|required');
		$this->form_validation->set_rules('org_id', 'Organization ID', 'trim|required');
		$this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
		if ($this->form_validation->run() === false) {
			foreach ($_POST as $key => $value) {
				$data['messages'][$key] = form_error($key);
			}
		} else {
			$churchResidentData = array(
				'lastname' => $_POST['rlast_name'],
				'Firstname' => $_POST['rfirst_name'],
				'Title' => $_POST['r_title'],
				'organization_ID' => $_POST['org_id']
			);

			$data['messages'] = $this->MyModel->create_resident($churchResidentData);
			$data = array(
				'success' => true,
				'message' => $data
			);
		}

		$this->response($data, REST_Controller::HTTP_OK);
	}else {
		$this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
	}
}



 //creating church group
public function church_group_name_post()
{
	// $_POST = json_decode(file_get_contents('php://input'), true);
	$response = $this->MyModel->merchant_auth();
	if ($response['status'] == 200) {
		$data = array(
			'success' => false,
			'messages' => array()
		);
		$this->form_validation->set_rules('group_name', 'group name', 'trim|required');
		$this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
		if ($this->form_validation->run() === false) {
			foreach ($_POST as $key => $value) {
				$data['messages'][$key] = form_error($key);
			}
		} else {
			$merchantgroup = array(
				'group_name' => $_POST['group_name'],
				'merchant_id' => $_POST['merchant_id']
			);
			$data['messages'] = $this->MyModel->create_merchant_group($merchantgroup);
			// $data = array(
			// 	'success' => true,
			// 	'message' => $data
			// );
		}

 $this->response($data, REST_Controller::HTTP_OK);
} else {
	$this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
}
}

	public function church_membership_register_post()
	{
		// $_POST = json_decode(file_get_contents('php://input'), true);
		$response = $this->MyModel->merchant_auth();
		if ($response['status'] == 200) {
			$data = array(
				'success' => false,
				'messages' => array()
			);
			$this->form_validation->set_rules('first_name', 'First Name', 'trim|required');
			$this->form_validation->set_rules('last_name', 'Last Name', 'trim|required');
			$this->form_validation->set_rules('email', 'Email', 'trim|required');
			$this->form_validation->set_rules('mobile_number', 'Mobile Number', 'trim|required');
			$this->form_validation->set_rules('date_of_birth', 'Date of Birth', 'trim|required');
			$this->form_validation->set_rules('gender', 'Gender', 'trim|required');
			$this->form_validation->set_rules('nationality', 'Nationality', 'trim|required');
			$this->form_validation->set_rules('marital_status', 'Marital Status', 'trim|required');
			$this->form_validation->set_rules('address', 'Address', 'trim|required');
			$this->form_validation->set_rules('marital_status', 'Marital Status', 'trim|required');
			$this->form_validation->set_rules('merchant_id', 'Merchant ID', 'trim|required');
			// $this->form_validation->set_rules('member_photo', 'Member Image Photo', 'trim|required');

			$this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
			if ($this->form_validation->run() === false) {
				foreach ($_POST as $key => $value) {
					$data['messages'][$key] = form_error($key);
				}
			} else {
 
                    //using merchant ID and member firstname to identify memeber images
				$firstid = $_POST['merchant_id'];
				$secondid = $_POST['first_name'];
;               $my_bucket = "freshword-ci/merchant_members";

             if(empty($_FILES)){
                    $error = array(
						   'status'=> false,
						   'error' => 'you did not select an image to upload'
					);
					$this->response($error, REST_Controller::HTTP_OK);
					return false;
		  } else {
			// $file_name = $_FILES['member_photo']['name'];
			$temp_name = $_FILES['member_photo']['tmp_name'];
	
			$image = file_get_contents($_FILES['member_photo']['tmp_name']);
	
			$options = ['gs' => ['Content-Type' => 'image/jpeg']];
			$context = stream_context_create($options); 
			$fileName = "gs://${my_bucket}/$firstid.$secondid.jpg";
			file_put_contents($fileName, $image, 0, $context);

			 $img = "https://storage.googleapis.com/freshword-ci/merchant_members/$firstid.$secondid.jpg";
				$churchMemberData = array(
					'first_name' => $_POST['first_name'],
					'last_name' => $_POST['last_name'],
					'email' => $_POST['email'],
					'mobile_number' => $_POST['mobile_number'],
					'date_of_birth' => $_POST['date_of_birth'],
					'gender' => $_POST['gender'],
					'nationality' => $_POST['nationality'],
					'marital_status' => $_POST['marital_status'],
					'address' => $_POST['address'],
					'member_photo' => $img,
					'church_id' => $_POST['merchant_id']
				);
		  }
			
				$data['messages'] = $this->MyModel->create_church_member($churchMemberData);
				$data = array(
					'success' => true,
					'message' => $data
				);
			
	 }
	 $this->response($data, REST_Controller::HTTP_OK);
  } else {
		$this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
	}
}

public function update_churchmember_post()
{
	$response = $this->MyModel->merchant_auth();
	if ($response['status'] == 200) {
		$data = array(
			'success' => false,
			'messages' => array()
		);
		$this->form_validation->set_rules('first_name', 'first name', 'trim|required');
		$this->form_validation->set_rules('last_name', 'last name', 'trim|required');
		$this->form_validation->set_rules('mobile_number', 'mobile number', 'trim|required');
		$this->form_validation->set_rules('date_of_birth', 'date of birth', 'trim|required');
		$this->form_validation->set_rules('gender', 'gender', 'trim|required');
		$this->form_validation->set_rules('nationality', 'nationality', 'trim|required');
		$this->form_validation->set_rules('address', 'address', 'trim|required');
		$this->form_validation->set_rules('marital_status', 'marital status', 'trim|required');
		$this->form_validation->set_rules('email', 'email', 'trim|required');
		
		$this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
		if ($this->form_validation->run() === false) {
			foreach ($_POST as $key => $value) {
				$data['messages'][$key] = form_error($key);
			}
		} else {
			$firstid = $response['id'];
			$secondid = $_POST['first_name'];
;           $my_bucket = "freshword-ci/merchant_members";

			if (empty($_FILES)) {
				$img = '';
				$data = $this->MyModel->update_churchmember($_POST['member_id'], $_POST, $img);
				$this->response($data, REST_Controller::HTTP_OK);
				return false; //script will end here
			}else{
				$temp_name = $_FILES['member_photo']['tmp_name'];
	
				$image = file_get_contents($_FILES['member_photo']['tmp_name']);
		
				$options = ['gs' => ['Content-Type' => 'image/jpeg']];
				$context = stream_context_create($options); 
				$fileName = "gs://${my_bucket}/$firstid.$secondid.jpg";
				file_put_contents($fileName, $image, 0, $context);
	
				 $img = "https://storage.googleapis.com/freshword-ci/merchant_members/$firstid.$secondid.jpg";
	
			}
			


			// so run insertion since the validation for the form has been passed correctly

			$data = $this->MyModel->update_churchmember($_POST['member_id'], $_POST, $img);
		}

		$this->response($data, REST_Controller::HTTP_OK);
	} else {
		$this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
	}
}

	// fetch membership bio data

	public function get_church_membership_data_get()
	{
		$query = $this->MyModel->get_all_church_members();
		$this->response($query, REST_Controller::HTTP_OK);
	}

	public function merchant_login_post()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);
		$data = array(
			'success' => false,
			'messages' => array()
		);
		$this->form_validation->set_rules('email', 'Email', 'trim|required');
		$this->form_validation->set_rules('password', 'Password', 'trim|required');
		$this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
		if ($this->form_validation->run() === false) {
			foreach ($_POST as $key => $value) {
				$data['messages'][$key] = form_error($key);
			}
		} else {
			$data['success'] = true;
			$data['messages'] = $this->MyModel->merchant_login($_POST['email'], $_POST['password']);
		}

		$this->response($data, REST_Controller::HTTP_OK);
	}

	// so here i am beginning session to control my rest client session pages

	public function merchant_session_start_post()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);
		$resp = $this->MyModel->merchant_session($_POST['id'], $_POST['token']);
		$this->response($resp, REST_Controller::HTTP_OK);
	}

	public function merchant_activate_account_post()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);
		$query = $this->MyModel->activate_merchant($_POST);
		$this->response($query, REST_Controller::HTTP_OK);
	}

	public function merchant_forgot_pass_email_post()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);
		$resp = $this->MyModel->check_merchant_email($_POST);
		$this->response($resp, REST_Controller::HTTP_OK);
	}

	public function merchant_confirm_reset_code_post()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);
		$resp = $this->MyModel->check_reset_code($_POST['mobile'], $_POST['resetcode']);
		$this->response($resp, REST_Controller::HTTP_OK);
	}

	public function merchant_profile_get()
	{
		$response = $this->MyModel->merchant_auth();
		if ($response['status'] == 200) {
			$query = $this->MyModel->get_merchant_profile($response['id']);
			$data = array(
				'res' => $query,
				'headerRes' => $response
			);
			$this->response($data, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_OK);
		}
	}

	// Get all Churches controller method
	public function church_data_get() {
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$query = $this->MyModel->get_church_data($response['id']);

			$this->response($query, REST_Controller::HTTP_OK);
		}
		else {
			$this->response($response, REST_Controller::HTTP_OK);
		}
	}


	public function subscription_packages_get() {
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$query = $this->MyModel->get_subscription_packages();

			$this->response($query, REST_Controller::HTTP_OK);
		}
		else {
			$this->response($response, REST_Controller::HTTP_OK);
		}
	}

	//get subsciption packages
	public function subscription_modules_get() {
		$response = $this->MyModel->header_auth();
		if ($response['status'] == 200) {
			$query = $this->MyModel->get_subscription_modules();

			$this->response($query, REST_Controller::HTTP_OK);
		}
		else {
			$this->response($response, REST_Controller::HTTP_OK);
		}
	}

	// this has to be sequential now we need to return values here to proceed to the next endpoint
	// this will be looped twice to the end point

	public function merchant_add_image_post()
	{
		$id = $_POST['id'];
		$my_bucket = "freshword-ci";
		$config['upload_path'] = "gs://${my_bucket}/";
		$config['allowed_types'] = 'gif|jpg|png|jpeg'; //allowing only images
		$config['max_size'] = 2024;
		$this->load->library('upload', $config);
		$this->upload->initialize($config);
		if (!$this->upload->do_upload('image_file')) {
			$error = array(
				'status' => false,
				'error' => $this->upload->display_errors()
			);

			// echo json_encode($error);

			$this->response($error, REST_Controller::HTTP_OK);
		} else {
			$data = $this->upload->data();
			$success = ['status' => true, 'success' => $data['file_name']];


			// echo json_encode($success);
			$file = $data['file_name'];



			$img = "https://storage.cloud.google.com/${my_bucket}/$file?organizationId=96831556031&_ga=2.83358422.-1152930877.1539685883";

			$imgData = array(
				'prod_image' => $data['file_name'],
				'img_link' => $img
			);
			$data['messages'] = $this->MyModel->update_image($id, $imgData);
			$this->response($success, REST_Controller::HTTP_OK);
		}
	}

	// we run this on the success response from the first push

	public function merchant_add_file_post()
	{

		$id = $_POST['id'];
		$query = $this->MyModel->upload_path($id);
		$config['upload_path'] = './public/images/uploads/prod_link' . $query . '/';
		if ($query == "audio") {
			$config['allowed_types'] = 'mp3';
		}
		if ($query == "video") {
			$config['allowed_types'] = 'mp4|avi';
		}
		if ($query == "book") {
			$config['allowed_types'] = 'pdf|doc';
		}
		$config['max_size'] = 0;

		$this->load->library('upload', $config);
		$this->upload->initialize($config);
		if (!$this->upload->do_upload('product_file')) {
			$error = array('status' => false, 'error' => $this->upload->display_errors());
				//echo json_encode($error);
			$this->response($error, REST_Controller::HTTP_OK);
		} else {
			$data = $this->upload->data();
			$success = ['status' => true, 'success' => $data['file_name']];
			$imgData = array(
				'file_link' => $data['file_name']
			);
			$this->MyModel->update_file($id, $imgData);
			$this->response($success, REST_Controller::HTTP_OK);
		}

	}

	public function merchant_products_post()
	{

		$_POST = json_decode(file_get_contents('php://input'), true);

		$response = $this->MyModel->merchant_auth();
		if ($response['status'] == 200) {
			$this->load->helper('url');
			$query = $this->MyModel->merchant_email($response['id']);
			$list = $this->MerchantProductModel->get_datatables($query->email);
			$data = array();
			$no = $_POST['start'];
			foreach ($list as $prod) {
				$no++;
				$row = array();
				// $row[] = '<img src="' . $prod->img_link . '"width: 50px;border-radius: 100%;margin-right: 10px;">';
				$row[] = $prod->prod_name;
				$row[] = $prod->prod_preacher;
				// $row[] = $prod->prod_church;
				$row[] = $prod->prod_tags;
				// $row[] = $prod->prod_uniqid;
				$row[] = $prod->prod_download_count;
				$row[] = $prod->prod_date;

				// if($payee->network == 'MTN'):

				$favicon = $this->MyModel->favicon_show($prod->prod_tags);
				$row[] = '<div class="btn-group" role="group" aria-label="action button group">
							<a href="javascript:void(0)" title="Preview" onclick="preview_product(' . "'" . $prod->prod_id . "'" . ')" class="btn btn-primary">View</a>
							<a class="btn btn-info" href="javascript:void(0)" title="Edit" onclick="edit_product(' . "'" . $prod->prod_id . "'" . ')">Edit</a>
							<a class="btn btn-danger" href="javascript:void(0)" title="Delete" onclick="delete_product(' . "'" . $prod->prod_id . "'" . ')">Delete</a>
						</div>';
				$data[] = $row;
			}

			$output = array(
				"draw" => $_POST['draw'],
				"recordsTotal" => $this->MerchantProductModel->count_all($query->email),
				"recordsFiltered" => $this->MerchantProductModel->count_filtered($query->email),
				"data" => $data,
			);

			// output to json format

			$this->response($output, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_OK);
		}
	}

	public function get_merchant_feeds_post()
	{

		$_POST = json_decode(file_get_contents('php://input'), true);

		$response = $this->MyModel->merchant_auth();
		if ($response['status'] == 200) {
			$this->load->helper('url');
			$query = $this->MyModel->merchant_email($response['id']);
			$list = $this->MerchantFeedModel->get_datatables($query->email);
			$data = array();
			$no = $_POST['start'];
			foreach ($list as $feed) {
				$no++;
				$row = array();
				$row[] = '<img src="' . $feed->image . '" height="75px">';
				$row[] = $feed->category;
				$row[] = $feed->title;
				$row[] = $feed->message;
				$row[] = $feed->likes_count;
				$row[] = $feed->comments_counts;


				// if($payee->network == 'MTN'):

				// $favicon = $this->MyModel->favicon_show($prod->prod_tags);
				$row[] = '
                        <a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_feed(' . "'" . $feed->id . "'" . ')"><i class="fa fa-edit"></i> </a>
                        <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Delete" onclick="delete_feed(' . "'" . $feed->id . "'" . ')"><i class="fa fa-trash"></i> </a>';
				$data[] = $row;
			}

			$output = array(
				"draw" => $_POST['draw'],
				"recordsTotal" => $this->MerchantFeedModel->count_all($query->email),
				"recordsFiltered" => $this->MerchantFeedModel->count_filtered($query->email),
				"data" => $data,
			);

			// output to json format

			$this->response($output, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_OK);
		}
	}

	public function get_pastorslisting_post()
	{

		$_POST = json_decode(file_get_contents('php://input'), true);

		$response = $this->MyModel->merchant_auth();
		if ($response['status'] == 200) {
			$this->load->helper('url');
			$query = $this->MyModel->merchant_email($response['id']);
			$list = $this->MerchantPastorModel->get_datatables($query->id);
			$data = array();
			$no = $_POST['start'];
			foreach ($list as $pastor) {
				$no++;
				$row = array();
				$row[] = '<img src="' . $pastor->photo . '" height="75px" . class="img-circle">';
				$row[] = $pastor->pastors_title;
				$row[] = $pastor->name;
				



				// if($payee->network == 'MTN'):

				// $favicon = $this->MyModel->favicon_show($prod->prod_tags);
				$row[] = '
                        <a class="btn  btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_pastor(' . "'" . $pastor->id . "'" . ');"><i class="fa fa-edit"></i>Edit</a>
                        <a class="btn  btn-danger" href="javascript:void(0)" title="Delete" onclick="delete_pastor(' . "'" . $pastor->id . "'" . ');"><i class="fa fa-trash"></i>Delete</a>';
				$data[] = $row;
			}

			$output = array(
				"draw" => $_POST['draw'],
				"recordsTotal" => $this->MerchantPastorModel->count_all($query->id),
				"recordsFiltered" => $this->MerchantPastorModel->count_filtered($query->id),
				"data" => $data,
			);

			// output to json format

			$this->response($output, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_OK);
		}
	}


	public function get_churchmemberslisting_post()
	{

		$_POST = json_decode(file_get_contents('php://input'), true);

		$response = $this->MyModel->merchant_auth();
		if ($response['status'] == 200) {
			$this->load->helper('url');
			$query = $this->MyModel->merchant_email($response['id']);
			$list = $this->MerchantMembersModel->get_datatables($query->id);
			$data = array();
			$no = $_POST['start'];
			foreach ($list as $member) {
				$no++;
				$row = array();
				$row[] = '<img src="' . $member->member_photo . '" height="75px">';
				$row[] = $member->first_name;
				$row[] = $member->last_name;
				$row[] = $member->email;
				$row[] = $member->mobile_number;
				$row[] = $member->date_of_birth;



				// if($payee->network == 'MTN'):

				// $favicon = $this->MyModel->favicon_show($prod->prod_tags);
				$row[] = '
                        <a class="btn  btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_member(' . "'" . $member->id . "'" . ')"><i class="fa fa-edit"></i>Edit </a>
                        <a class="btn  btn-danger" href="javascript:void(0)" title="Delete" onclick="delete_member(' . "'" . $member->id . "'" . ')"><i class="fa fa-trash"></i>Delete </a>';
				$data[] = $row;
			}

			$output = array(
				"draw" => $_POST['draw'],
				"recordsTotal" => $this->MerchantMembersModel->count_all($query->id),
				"recordsFiltered" => $this->MerchantMembersModel->count_filtered($query->id),
				"data" => $data,
			);

			// output to json format

			$this->response($output, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_OK);
		}
	}

	// and then we finally post the data needed as well
	// Here we will go through our form validaitons to avoid same data being posted twice

	public function merchant_add_product_data_post()
	{

		$response = $this->MyModel->merchant_auth();
		if ($response['status'] == 200) {

			// $_POST = json_decode(file_get_contents('php://input'), true);
			$data = array('success' => false, 'messages' => array());
			$this->form_validation->set_rules('prod_tags', 'Category', 'trim|required');//type
			$this->form_validation->set_rules('prod_name', 'Title', 'trim|required|is_unique[ts_products.prod_name]');
			$this->form_validation->set_rules('prod_preacher', 'Preacher / Speaker / Author', 'trim|required');
			$this->form_validation->set_rules('prod_description', 'Topic', 'trim|required|max_length[160]');//this is the theme
			$this->form_validation->set_rules('prod_essay', 'Description', 'trim|required');//and this is the essay
			$this->form_validation->set_rules('prod_church', 'Church Name', 'trim|required');//should be an hidden input
			$this->form_validation->set_rules('merchant_email', 'Merchant Email', 'trim|required');
			$this->form_validation->set_message('is_unique', 'The %s is already taken');
			$this->form_validation->set_message('max_length[160]', '%s: the maximum of 160 Characters allowed');
			$this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');

			if ($this->form_validation->run() === false) {
				foreach ($_POST as $key => $value) {
					$data['messages'][$key] = form_error($key);
				}
			} else {
				$firstid = $response['id'];
				$secondid = $_POST['prod_name'];
;               $my_bucket = "freshword-ci/merchant_products";

             if(empty($_FILES)){
                    $error = array(
						   'status'=> false,
						   'error' => 'you did not select an image to upload'
					);
					$this->response($error, REST_Controller::HTTP_OK);
					return false;
		  } else {
			// $file_name = $_FILES['member_photo']['name'];
			$temp_name = $_FILES['image_file']['tmp_name'];
	
			$image = file_get_contents($_FILES['image_file']['tmp_name']);
	
			$options = ['gs' => ['Content-Type' => 'image/jpeg']];
			$context = stream_context_create($options); 
			$fileName = "gs://${my_bucket}/$firstid.$secondid.jpg";
			file_put_contents($fileName, $image, 0, $context);

			 $img = "https://storage.googleapis.com/freshword-ci/merchant_products/$firstid.$secondid.jpg";
		
		  }

				$prodData = array(
					'prod_name' => $_POST['prod_name'],
					'prod_urlname' => $this->MyModel->replace_hyphens($_POST['prod_name']),
					'prod_preacher' => $_POST['prod_preacher'],
					'prod_church' => $_POST['prod_church'],
					'prod_tags' => $_POST['prod_tags'], //here we use value as the same for type_list
					'prod_description' => $_POST['prod_description'],
					'prod_essay' => $_POST['prod_essay'],
					'prod_demourl' => 'null',
					'prod_demoshow' => 1,
					'prod_cateid' => 1,
					'prod_subcateid' => 0,
					'prod_filename' => 0,
					'prod_plan' => 0,
					'prod_free' => 0,
					'prod_featured' => 0,
					'prod_status' => 1,
					'prod_uniqid' => $this->MyModel->generate_product_unique_code(),
					'prod_download_count' => 0,
					'prod_gallery' => 1,
					'prod_uid' => 1,
					'prod_type' => $this->MyModel->prod_type($_POST['prod_tags']),
					'type_list' => $_POST['prod_tags'],
					 'file_link'=> $_POST['product_file'],
					'merchant_email' => $_POST['merchant_email'],
					'prod_date' => date('Y-m-d H:i:s'),
					'img_link'=> $img,



				);
				$query = $this->MyModel->merchant_insert_product($prodData);
				$data = array('success' => true, 'message' => $query);
			}
			$this->response($data, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
		}
	}

	public function product_preview_get()
	{
		$response = $this->MyModel->merchant_auth();
		if ($response['status'] == 200) {
			$id = (int)$this->get('id');
			$query = $this->MyModel->product_preview($id);
			$this->response($query, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
		}
	}

	public function product_edit_get()
	{
		$response = $this->MyModel->merchant_auth();
		if ($response['status'] == 200) {
			$id = (int)$this->get('id');
			$query = $this->MyModel->edit_product($id);
			$this->response($query, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
		}
	}

	public function pastorslisting_edit_get()
	{
		$response = $this->MyModel->merchant_auth();
		if ($response['status'] == 200) {
			$id = (int)$this->get('id');
			$query = $this->MyModel->edit_pastors($id);
			$this->response($query, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
		}
	}

	public function churchmembers_edit_get()
	{
		$response = $this->MyModel->merchant_auth();
		if ($response['status'] == 200) {
			$id = (int)$this->get('id');
			$query = $this->MyModel->edit_members($id);
			$this->response($query, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
		}
	}

	public function delete_product_post()
	{
		$response = $this->MyModel->merchant_auth();
		if ($response['status'] == 200) {
			$_POST = json_decode(file_get_contents('php://input'), true);
			$query = $this->MyModel->delete_product($_POST['id'], $_POST['email']);
			$this->response($query, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
		}
	}

	public function delete_member_post()
	{
		$response = $this->MyModel->merchant_auth();
		if ($response['status'] == 200) {
			$_POST = json_decode(file_get_contents('php://input'), true);
			$query = $this->MyModel->delete_member($_POST['id']);
			$this->response($query, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
		}
	}

	public function delete_pastor_post()
	{
		$response = $this->MyModel->merchant_auth();
		if ($response['status'] == 200) {
			$_POST = json_decode(file_get_contents('php://input'), true);
			$query = $this->MyModel->delete_pastor($_POST['id']);
			$this->response($query, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
		}
	}

	public function update_product_post()
	{
		$response = $this->MyModel->merchant_auth();
		if ($response['status'] == 200) {

			// code beginss here

			$_POST = json_decode(file_get_contents('php://input'), true);
			$data = array(
				'success' => false,
				'messages' => array()
			);
			$this->form_validation->set_rules('prod_tags', 'Product Type', 'trim|required'); //type
			$this->form_validation->set_rules('prod_name', 'Product Name', 'trim|required|callback__is_unique2');
			$this->form_validation->set_rules('prod_preacher_id', 'Preacher / Speaker / Author', 'trim|required');
			$this->form_validation->set_rules('prod_preacher', 'Product Preacher', 'trim|required');
			// $this->form_validation->set_rules('prod_price', 'Product Price', 'trim|required');
			// $this->form_validation->set_rules('prod_currency', 'Product Currency', 'trim|required');
			$this->form_validation->set_rules('prod_description', 'Product Theme', 'trim|required|max_length[160]'); //this is the theme
			$this->form_validation->set_rules('prod_essay', 'Product Description', 'trim|required'); //and this is the essay

			// below are the custom reponse messages

			$this->form_validation->set_message('is_unique2', 'The %s is already taken');
			$this->form_validation->set_message('max_length[160]', '%s: the maximum of 160 Characters allowed');

			// push error response into delimiters

			$this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
			if ($this->form_validation->run() === false) {
				foreach ($_POST as $key => $value) {
					$data['messages'][$key] = form_error($key);
				}
			} else {
				$data['success'] = true;
				$data['message'] = $this->MyModel->update_ts_products($_POST);
			}

			$this->response($data, REST_Controller::HTTP_OK);

			// code ends here

		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
		}
	}

	// this is a call back

	public function _is_unique2($input)
	{
		$exclude_id = $_POST['prod_id'];
		if ($this->db->where('prod_name', $input)->where('prod_id !=', $exclude_id)->limit(1)->get('ts_products')->num_rows()) {
			$this->form_validation->set_message('_is_unique2', 'The product name already exists');
			return false;
		}

		return true;
	}

	public function dashboard_data_get()
	{
		$response = $this->MyModel->merchant_auth();
		if ($response['status'] == 200) {
			$email = $this->get('email');
			$data['free_products'] = $this->MyModel->count_free_products($email);
			$data['premium_products'] = $this->MyModel->count_premium_products($email);
			$data['total_product_views'] = $this->MyModel->count_product_views($email);
			$this->response($data, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
		}
	}

	public function merchant_feed_post()
	{
		$response = $this->MyModel->merchant_auth();
		if ($response['status'] == 200) {
			$data = array(
				'success' => false,
				'messages' => array()
			);
			$this->form_validation->set_rules('news_cat', 'Category', 'trim|required');
			$this->form_validation->set_rules('feed_title', 'Title', 'trim|required|is_unique[merchant_feed.title]');
			$this->form_validation->set_rules('feed_message', 'Message', 'trim|required');
			$this->form_validation->set_rules('merchantemail', 'Merchant Email', 'trim|required');
			$this->form_validation->set_rules('church_id', 'church id', 'trim|required');
			// $this->form_validation->set_rules('file', 'Merchant Image', 'required');
			$this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
			if ($this->form_validation->run() === false) {
				foreach ($_POST as $key => $value) {
					$data['messages'][$key] = form_error($key);
				}
			} else {

				$firstid = $_POST['church_id'];
				$secondid = $_POST['feed_title'];
;               $my_bucket = "freshword-ci/merchant_feeds";

             if(empty($_FILES)){
                    $error = array(
						   'status'=> false,
						   'error' => 'you did not select an image to upload'
					);
					$this->response($error, REST_Controller::HTTP_OK);
					return false;
		  } else {
			// $file_name = $_FILES['member_photo']['name'];
			$temp_name = $_FILES['newsfeed_img']['tmp_name'];
	
			$image = file_get_contents($_FILES['newsfeed_img']['tmp_name']);
	
			$options = ['gs' => ['Content-Type' => 'image/jpeg']];
			$context = stream_context_create($options); 
			$fileName = "gs://${my_bucket}/$firstid.$secondid.jpg";
			file_put_contents($fileName, $image, 0, $context);

			 $img = "https://storage.googleapis.com/freshword-ci/merchant_feeds/$firstid.$secondid.jpg";
			 $newFeed = array(
				'category' => $_POST['news_cat'],
				'title' => $_POST['feed_title'],
				'message' => $_POST['feed_message'],
				'image' => $img,
				'merchantemail' => $_POST['merchantemail'],
				'timestamp' => date('Y-m-d H:i:s'),
				'likes_count' => 0,
				'comments_counts' => 0,
				'churchid' => $_POST['church_id']
			);
		  }
				  
				$data['messages'] = $this->MyModel->insert_feed_data($newFeed);
			}

			$this->response($data, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
		}
	}


	//Pastors Listings
	public function pastors_listing_post()
	{

		$response = $this->MyModel->merchant_auth();
		if ($response['status'] == 200) {
			// $_POST = json_decode(file_get_contents('php://input'), true);
			$data = array(
				'success' => false,
				'messages' => array()
			);
			$this->form_validation->set_rules('pastors_title', 'Pastors Title', 'trim|required');
			$this->form_validation->set_rules('pastors_name', 'Pastors Fullname', 'trim|required');
			$this->form_validation->set_rules('pastors_bio', 'Pastors Bio', 'trim|required');
			$this->form_validation->set_rules('merchant_id', 'Merchant ID', 'trim|required');
			// $this->form_validation->set_rules('pastors_avatar_img', 'Pastors Image', 'required');
			$this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
			if ($this->form_validation->run() === false) {
				foreach ($_POST as $key => $value) {
					$data['messages'][$key] = form_error($key);
				}
			} else {
						  
				$firstid = $response['id'];
				$secondid = $_POST['pastors_name'];
;               $my_bucket = "freshword-ci/merchant_pastors";

             if(empty($_FILES)){
                    $error = array(
						   'status'=> false,
						   'error' => 'you did not select an image to upload'
					);
					$this->response($error, REST_Controller::HTTP_OK);
					return false;
		  } else {
			$temp_name = $_FILES['pastorsimg']['tmp_name'];
	
			$image = file_get_contents($_FILES['pastorsimg']['tmp_name']);
	
			$options = ['gs' => ['Content-Type' => 'image/jpeg']];
			$context = stream_context_create($options); 
			$fileName = "gs://${my_bucket}/$firstid.$secondid.jpg";
			file_put_contents($fileName, $image, 0, $context);

			 $img = "https://storage.googleapis.com/freshword-ci/merchant_pastors/$firstid.$secondid.jpg";
		
		  }

			
				$Pastors_listing = array(
					'pastors_title' => $_POST['pastors_title'],
					'name' => $_POST['pastors_name'],
					'bio' => $_POST['pastors_bio'],
					'photo' => $img,
					'merchant_id' => $response['id']
				);

				// so run insertion since the validation for the form has been passed correctly

				$data['messages'] = $this->MyModel->insert_pastors_bio_data($Pastors_listing);
			}

			$this->response($data, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
		}
	}



	public function update_pastor_post()
	{
		$response = $this->MyModel->merchant_auth();
		if ($response['status'] == 200) {
			$data = array(
				'success' => false,
				'messages' => array()
			);
			$this->form_validation->set_rules('pastors_title', 'Category', 'trim|required');
			$this->form_validation->set_rules('pastors_name', 'Title', 'trim|required');
			$this->form_validation->set_rules('pastors_bio', 'Message', 'trim|required');
			$this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
			if ($this->form_validation->run() === false) {
				foreach ($_POST as $key => $value) {
					$data['messages'][$key] = form_error($key);
				}
			} else {
				$firstid = $response['id'];
				$secondid = $_POST['pastors_name'];
;               $my_bucket = "freshword-ci/merchant_pastors";
				if (empty($_FILES)) {
					$img = '';
					$data = $this->MyModel->update_pastor($_POST['pastor_id'], $_POST, $img);
					$this->response($data, REST_Controller::HTTP_OK);
					return false; //script will end here
				}else{


					$temp_name = $_FILES['pastorsimg']['tmp_name'];
	
					$image = file_get_contents($_FILES['pastorsimg']['tmp_name']);
			
					$options = ['gs' => ['Content-Type' => 'image/jpeg']];
					$context = stream_context_create($options); 
					$fileName = "gs://${my_bucket}/$firstid.$secondid.jpg";
					file_put_contents($fileName, $image, 0, $context);
		
					 $img = "https://storage.googleapis.com/freshword-ci/merchant_pastors/$firstid.$secondid.jpg";


				}
				// so run insertion since the validation for the form has been passed correctly

				$data['messages'] = $this->MyModel->update_pastor($_POST['pastor_id'], $_POST, $img);
			}

			$this->response($data, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
		}
	}

	// so basically this gets the row data to dsiplay in the

	public function merchant_feed_get()
	{
		$response = $this->MyModel->merchant_auth();
		if ($response['status'] == 200) {
			$id = (int)$this->get('id');
			$data = $this->MyModel->get_merchant_feed_id($id);
			// $data = $this->MyModel->edit_merchantfeed($id);
			$this->response($data, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
		}
	}

	public function merchant_feed_update_post()
	{
		$response = $this->MyModel->merchant_auth();
		if ($response['status'] == 200) {

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

			$data = array(
				'success' => false,
				'messages' => array()
			);
			$this->form_validation->set_rules('news_cat', 'Category', 'trim|required');
			$this->form_validation->set_rules('feed_title', 'Title', 'trim|required');
			$this->form_validation->set_rules('feed_message', 'Message', 'trim|required');
			$this->form_validation->set_rules('merchantemail', 'Merchant Email', 'trim|required');
			// $this->form_validation->set_rules('newsfeed_img', 'Merchant Image', 'callback_update_file_check');
			$this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
			if ($this->form_validation->run() === false) {
				foreach ($_POST as $key => $value) {
					$data['messages'][$key] = form_error($key);
				}
			} else {
				if ($_FILES['newsfeed_img']['name'] == "") {
					$img = '';
					$data = $this->MyModel->update_merchant_feed($_POST['post_id'], $_POST, $_POST['merchantemail'], $img);
					$this->response($data, REST_Controller::HTTP_OK);
					return false; //script will end here
				}
				require_once 'google/appengine/api/cloud_storage/CloudStorageTools.php';
				$my_bucket = "freshword-ci";
				$config['upload_path'] = "gs://${my_bucket}/";
				$config['allowed_types'] = 'gif|jpg|png|jpeg'; //allowing only images
				$config['max_size'] = 3024;
				$this->load->library('upload', $config);
				$this->upload->initialize($config);
				if (!$this->upload->do_upload('newsfeed_img')) {
					$error = array(
						'status' => false,
						'error' => $this->upload->display_errors()
					);

					// echo json_encode($error);

					$this->response($error, REST_Controller::HTTP_OK);
					return false;
				} else {
					$data = $this->upload->data();
					$success = ['status' => true, 'success' => $data['file_name']];

					// echo json_encode($success);

					$file = $data['file_name'];



					$img = "https://storage.cloud.google.com/${my_bucket}/$file?organizationId=96831556031&_ga=2.83358422.-1152930877.1539685883";

					// so run insertion since the validation for the form has been passed correctly

					$data = $this->MyModel->update_merchant_feed($_POST['post_id'], $_POST, $_POST['merchantemail'], $img);
				}
			}

			$this->response($data, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
		}
	}

	public function merchant_feed_delete()
	{
		$response = $this->MyModel->merchant_auth();
		if ($response['status'] == 200) {
			$id = (int)$this->get('id');
			$data = $this->MyModel->delete_merchant_feed($id);
			$this->response($data, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
		}
	}

	public function update_file_check($str)
	{
		if ($_FILES['file']['name'] == "") { //so here we assume user decided to use the old file upload
			return true;
		} else {
			return $this->file_check($str);
		}
	}

	// call back for checking file directly into one

	public function file_check($str)
	{
		$allowed_mime_type_arr = array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png');
		$mime = get_mime_by_extension($_FILES['file']['name']);
		if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != "") {
			if (in_array($mime, $allowed_mime_type_arr)) {
				return true;
			} else {
				$this->form_validation->set_message('file_check', 'Please select only jpeg/jpg/png file.');
				return false;
			}
		} else {
			$this->form_validation->set_message('file_check', 'Please choose a file to upload.');
			return false;
		}
	}

	public function merchant_news_feed_get()
	{
		$response = $this->MyModel->merchant_auth();
		if ($response['status'] == 200) {
			$email = $this->get('email');  
			$config = array();
			$config["base_url"] = base_url() . "merchant/news_feed";
			$config["total_rows"] = $this->MyModel->count_merchant_feed($email);
			$config["per_page"] = 10;
			$config["uri_segment"] = 3;
			$this->pagination->initialize($config);
			$page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
			$data["results"] = $this->MyModel->get_merchant_feed_data($email); //here we need to take the pagination out for the time
			$data["links"] = $this->pagination->create_links();
			$data['entries'] = $this->MyModel->count_merchant_feed($email);
			$data['likes'] = $this->MyModel->count_merchant_likes($response['id']);
			$data['comments'] = $this->MyModel->count_merchant_comments($response['id']);
			$this->response($data, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
		}
	}

	public function merchant_search_feed_post()
	{
		$response = $this->MyModel->merchant_auth();
		if ($response['status'] == 200) {
			$_POST = json_decode(file_get_contents('php://input'), true);
			$data['results'] = $this->MyModel->search_merchant_feed($_POST['search'], $_POST['email']);
			$this->response($data, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
		}
	}

	public function merchant_update_profile_post()
	{
		$response = $this->MyModel->merchant_auth();
		if ($response['status'] == 200) {
			$data = array(
				'success' => false,
				'messages' => array()
			);

			$this->form_validation->set_rules('organisation', 'Organisation ', 'trim|required|is_unique[merchant_feed.title]');
			$this->form_validation->set_rules('merchant_name', 'Merchant Name', 'trim|required');
			$this->form_validation->set_rules('first_name', 'First Name', 'trim|required');
			$this->form_validation->set_rules('last_name', 'Last Name', 'trim|required');
			$this->form_validation->set_rules('email', 'Email', 'trim|required');
			$this->form_validation->set_rules('mobile', 'Mobile', 'trim|required');
			$this->form_validation->set_rules('password', 'Password', 'trim|required');
			$this->form_validation->set_rules('organisation_info', 'Organisation Summary', 'trim|required');
			$this->form_validation->set_rules('org_address', 'Address', 'trim|required');
			$this->form_validation->set_rules('org_country', 'Country', 'trim|required');
			// $this->form_validation->set_rules('location', 'Location', 'trim|required');
			// $this->form_validation->set_rules('merchant_display_picture', 'Your Profile Display  Image', 'callback_merchant_profile_check');
			$this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
			if ($this->form_validation->run() === false) {
				foreach ($_POST as $key => $value) {
					$data['messages'][$key] = form_error($key);
				}
			} else {
				$firstid = $response['id'];
			$secondid = $_POST['merchant_name'];
;           $my_bucket = "freshword-ci/merchant_profile";
				if(empty($_FILES)) {
					$q = $this->MyModel->photo_check($_POST['id']);
					$img = $q;
					$data = $this->MyModel->update_merchant_profile($_POST, $img);
					$this->response($data, REST_Controller::HTTP_OK);
					return false;
				}else {

                	$temp_name = $_FILES['merchant_display_picture']['tmp_name'];
	
				$image = file_get_contents($_FILES['merchant_display_picture']['tmp_name']);
		
				$options = ['gs' => ['Content-Type' => 'image/jpeg']];
				$context = stream_context_create($options); 
				$fileName = "gs://${my_bucket}/$firstid.$secondid.jpg";
				file_put_contents($fileName, $image, 0, $context);
	
				 $img = "https://storage.googleapis.com/freshword-ci/merchant_profile/$firstid.$secondid.jpg";

				}

				


				// so run insertion since the validation for the form has been passed correctly

				$data['messages'] = $this->MyModel->update_merchant_profile($_POST, $img);
			}

			$this->response($data, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	

	public function merchant_profile_check($str)
	{
		$allowed_mime_type_arr = array(
			'image/gif',
			'image/jpeg',
			'image/pjpeg',
			'image/png',
			'image/x-png'
		);
		$mime = get_mime_by_extension($_FILES['merchant_display_picture']['name']);
		if (isset($_FILES['merchant_display_picture']['name']) && $_FILES['merchant_display_picture']['name'] != "") {
			if (in_array($mime, $allowed_mime_type_arr)) {
				return true;
			} else {
				$this->form_validation->set_message('merchant_profile_check', 'Please select only jpeg/jpg/png file.');
				return false;
			}
		} else {

			// $this->form_validation->set_message('merchant_profile_check', 'Please choose a file to upload.');

			return true;
		}
	}

	function merchant_momo_add_post()
	{
		$response = $this->MyModel->merchant_auth();
		if ($response['status'] == 200) {
			$_POST = json_decode(file_get_contents('php://input'), true);
			$data = array(
				'success' => false,
				'messages' => array()
			);
			$this->form_validation->set_rules('network', 'Network ', 'trim|required');
			$this->form_validation->set_rules('mobile', 'Mobile Money Number', 'trim|required');
			$this->form_validation->set_error_delimiters('<span class=" text-danger">', '</span>');
			if ($this->form_validation->run() === false) {
				foreach ($_POST as $key => $value) {
					$data['messages'][$key] = form_error($key);
				}
			} else {
				$momoData = array(
					'merchant_id' => $response['id'],
					'network' => $_POST['network'],
					'mobile' => $_POST['mobile']
				);
				$data = $this->MyModel->save_momo_code($momoData);
				if ($data['status'] == 201) {
					$this->response($data, REST_Controller::HTTP_CREATED);
					return false;
				}

				if ($data['status'] == 204) {
					$this->response($data, REST_Controller::HTTP_NO_CONTENT);
					return false;
				}
			}

			$this->response($data, REST_Controller::HTTP_OK);
		} else {
			$this->response($response, REST_Controller::HTTP_NOT_FOUND); // BAD_REQUEST (400) being the HTTP response code
		}
	}


	//Stripe Processing For Billing
	public function stripe_billing_processing()
	{
		//check whether stripe token is not empty
		if (!empty($_POST['stripeToken'])) {
			//get token, card and user info from the form
			$token = $_POST['stripeToken'];
			$name = $_POST['name'];
			$email = $_POST['email'];
			$card_num = $_POST['card_num'];
			$card_cvc = $_POST['cvc'];
			$card_exp_month = $_POST['exp_month'];
			$card_exp_year = $_POST['exp_year'];

			//include Stripe PHP library
			require_once APPPATH . "third_party/stripe/init.php";

			//set api key
			$stripe = array(
				"secret_key" => "YOUR_SECRET_KEY",
				"publishable_key" => "YOUR_PUBLISHABLE_KEY"
			);

			\Stripe\Stripe::setApiKey($stripe['secret_key']);

			//add customer to stripe
			$customer = \Stripe\Customer::create(array(
				'email' => $email,
				'source' => $token
			));

			//item information
			$itemName = "Stripe Donation";
			$itemNumber = "PS123456";
			$itemPrice = 50;
			$currency = "usd";
			$orderID = "SKA92712382139";

			//charge a credit or a debit card
			$charge = \Stripe\Charge::create(array(
				'customer' => $customer->id,
				'amount' => $itemPrice,
				'currency' => $currency,
				'description' => $itemNumber,
				'metadata' => array(
					'item_id' => $itemNumber
				)
			));

			//retrieve charge details
			$chargeJson = $charge->jsonSerialize();
			//check whether the charge is successful
			if ($chargeJson['amount_refunded'] == 0 && empty($chargeJson['failure_code']) && $chargeJson['paid'] == 1 && $chargeJson['captured'] == 1) {
				//order details
				$amount = $chargeJson['amount'];
				$balance_transaction = $chargeJson['balance_transaction'];
				$currency = $chargeJson['currency'];
				$status = $chargeJson['status'];
				$date = date("Y-m-d H:i:s");


				//insert tansaction data into the database
				$dataDB = array(
					'name' => $name,
					'email' => $email,
					'card_num' => $card_num,
					'card_cvc' => $card_cvc,
					'card_exp_month' => $card_exp_month,
					'card_exp_year' => $card_exp_year,
					'item_name' => $itemName,
					'item_number' => $itemNumber,
					'item_price' => $itemPrice,
					'item_price_currency' => $currency,
					'paid_amount' => $amount,
					'paid_amount_currency' => $currency,
					'txn_id' => $balance_transaction,
					'payment_status' => $status,
					'created' => $date,
					'modified' => $date
				);
				if ($this->db->insert('orders', $dataDB)) {
					if ($this->db->insert_id() && $status == 'succeeded') {
						$data['insertID'] = $this->db->insert_id();
						$this->load->view('payment_success', $data);
						// redirect('Welcome/payment_success','refresh');
					} else {
						echo "Transaction has been failed";
					}
				} else {
					echo "not inserted. Transaction has been failed";
				}
			} else {
				echo "Invalid Token";
				$statusMsg = "";
			}
		}
	}
	public function payment_success()
	{
		$this->load->view('payment_success');
	}
	public function payment_error()
	{
		$this->load->view('payment_error');
	}
	public function help()
	{
		$this->load->view('help');
	}
} //end of class
