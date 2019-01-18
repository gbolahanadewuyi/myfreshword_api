<?php
defined('BASEPATH') or exit('No direct script access allowed');
class MyModel extends CI_Model

{

	// var $client_service = "frontend-client";
	// var $auth_key       = "myfreshword";

	var $client_id = 'ihounyms';
	var $client_secret = 'icbvukgq';
	public function __construct()
	{
		parent::__construct();
	}

	public function check_auth_client()
	{

		// this is where i am returning the header  userid and authentication id as well

		$client_service = $this->input->get_request_header('User-ID', true);
		$auth_key = $this->input->get_request_header('Authorization', true);
		return array(
			'user-id' => $client_service,
			'auth' => $auth_key
		);
	}

	public function login($username, $password)
	{
		$q = $this->db->select('user_pwd,user_id,user_status,user_mobile,user_church_id,image_url')->from('ts_user')->where('user_uname', $username)->get()->row();
		if ($q == "") {
			return array( 
				'status' => 204,
				'message' => 'Username not found.'
			);
		} else
			if ($q->user_status == 2) { //means account needs activation
			return array(
				'status' => 204,
				'message' => 'Your account is inactive.',
				'mobile' => $q->user_mobile
			);
		} else {
			$hashed_password = $q->user_pwd;
			$id = $q->user_id;
			$image = $q->image_url;
			$churchId = $q->user_church_id;
			if ($hashed_password == md5($password)) {
				$last_login = date('Y-m-d H:i:s');
				$token_set = substr(md5(rand()), 0, 7);
				$token = hash('sha256', $token_set);
				$expired_at = date("Y-m-d H:i:s", strtotime('+12 hours'));
				$this->db->trans_start();
				$this->db->where('user_id', $id)->update('ts_user', array(
					'last_login' => $last_login
				));
				$this->db->insert('users_authentication', array(
					'users_id' => $id,
					'token' => $token,
					'expired_at' => $expired_at
				));
				if ($this->db->trans_status() === false) {
					$this->db->trans_rollback();
					return array(
						'status' => 500,
						'message' => 'Internal server error.'
					);
				} else {
					$this->db->trans_commit();
					return array(
						'status' => 200,
						'message' => 'Successfully login.',
						'id' => $id,
						'token' => $token,
						'churchId' => $churchId,
						'image_url' => $image
					);
				}
			} else {
				return array(
					'status' => 204,
					'message' => 'Wrong password.'
				);
			}
		}
	}

	// this authentication is for mobile phone login request

	public function mobile_login($user_mobile, $password)
	{
		$q = $this->db->select('user_pwd,user_id,user_status,user_uname,user_mobile,user_church_id,image_url')->from('ts_user')->where('user_mobile', $user_mobile)->get()->row();
		if ($q == "") {
			return array(
				'status' => 204,
				'message' => 'Mobile not found.'
			);
		} else
			if ($q->user_status == 2) { //means account needs activation
			return array(
				'status' => 204,
				'message' => 'Your account is inactive.',
				'mobile' => $q->user_mobile
			);
		} else {
			$hashed_password = $q->user_pwd;
			$id = $q->user_id;
			$churchId = $q->user_church_id;
			$image = $q->image_url;
			if ($hashed_password == md5($password)) {
				$last_login = date('Y-m-d H:i:s');
				$token_set = substr(md5(rand()), 0, 7);
				$token = hash('sha256', $token_set);
				$expired_at = date("Y-m-d H:i:s", strtotime('+12 hours'));
				$this->db->trans_start();
				$this->db->where('user_id', $id)->update('ts_user', array(
					'last_login' => $last_login
				));
				$this->db->insert('users_authentication', array(
					'users_id' => $id,
					'token' => $token,
					'expired_at' => $expired_at
				));
				if ($this->db->trans_status() === false) {
					$this->db->trans_rollback();
					return array(
						'status' => 500,
						'message' => 'Internal server error.'
					);
				} else {
					$this->db->trans_commit();
					return array(
						'status' => 200,
						'message' => 'Successfully login.',
						'id' => $id,
						'token' => $token,
						'churchId' => $churchId,
						'image_url'=> $image

					);
				}
			} else {
				return array(
					'status' => 204,
					'message' => 'Wrong password.'
				);
			}
		}
	}

	public function merchant_login($email, $password)
	{
		$q = $this->db->select('id, email, password, approved')->from('ts_merchant')->where('email', $email)->get()->row();
		if ($q == "") {
			return array(
				'status' => 204,
				'message' => 'Email not found.'
			);
		} else
			if ($q->approved == 0) { //means account needs activation
			return array(
				'status' => 204,
				'message' => 'Your merchant account is  pending approval.'
			);
		} else {
			$hashed_password = $q->password;
			$id = $q->id;
			if ($hashed_password == hash('sha256', $password)) {
				$last_login = date('Y-m-d H:i:s');
				$token_set = substr(md5(rand()), 0, 7);
				$token = hash('sha256', $token_set);
				$expired_at = date("Y-m-d H:i:s", strtotime('+12 hours'));
				$this->db->trans_start();
				$this->db->where('id', $id)->update('ts_merchant', array(
					'last_login' => $last_login
				));
				$this->db->insert('merchant_authentication', array(
					'merchant_id' => $id,
					'token' => $token,
					'expired_at' => $expired_at
				));
				if ($this->db->trans_status() === false) {
					$this->db->trans_rollback();
					return array(
						'status' => 500,
						'message' => 'Internal server error.'
					);
				} else {
					$this->db->trans_commit();
					return array(
						'status' => 200,
						'message' => 'Successfully login.',
						'id' => $id,
						'token' => $token
					);
				}
			} else {
				return array(
					'status' => 204,
					'message' => 'Wrong password.'
				);
			}
		}
	}

	public function merchant_logout()
	{
		$users_id = $this->input->get_request_header('User-ID', true);
		$token = $this->input->get_request_header('Authorization', true);
		$this->db->where('merchant_id', $users_id)->where('token', $token)->delete('merchant_authentication');
		return array(
			'status' => 200,
			'message' => 'Successfully logout.'
		);
	}

	public function merchant_auth()
	{
		$merchant_id = $this->input->get_request_header('User-ID', true);
		$token = $this->input->get_request_header('Authorization', true);
		$q = $this->db->select('expired_at')->from('merchant_authentication')->where('merchant_id', $merchant_id)->where('token', $token)->get()->row();
		if ($q == "") {
			return array(
				'status' => 401,
				'message' => 'Unauthorized.'
			);
		} else {
			if ($q->expired_at < date('Y-m-d H:i:s')) {
				return array(
					'status' => 401,
					'message' => 'Your session has been expired.'
				);
			} else {
				$updated_at = date('Y-m-d H:i:s');
				$expired_at = date("Y-m-d H:i:s", strtotime('+12 hours'));
				$this->db->where('merchant_id', $merchant_id)->where('token', $token)->update('merchant_authentication', array(
					'expired_at' => $expired_at,
					'updated_at' => $updated_at
				));
				return array(
					'status' => 200,
					'message' => 'Authorized.',
					'id' => $merchant_id
				);
			}
		}
	}

	public function merchant_session($id, $token_auth)
	{
		$merchant_id = $id;
		$token = $token_auth;
		$q = $this->db->select('expired_at')->from('merchant_authentication')->where('merchant_id', $merchant_id)->where('token', $token)->get()->row();
		if ($q == "") {
			return array(
				'status' => 401,
				'message' => 'Unauthorized.'
			);
		} else {
			if ($q->expired_at < date('Y-m-d H:i:s')) {
				return array(
					'status' => 401,
					'message' => 'Your session has been expired.'
				);
			} else {
				$updated_at = date('Y-m-d H:i:s');
				$expired_at = date("Y-m-d H:i:s", strtotime('+12 hours'));
				$this->db->where('merchant_id', $merchant_id)->where('token', $token)->update('merchant_authentication', array(
					'expired_at' => $expired_at,
					'updated_at' => $updated_at
				));
				return array(
					'status' => 200,
					'message' => 'Authorized.',
					'id' => $merchant_id
				);
			}
		}
	}

	public function merchant_email($id)
	{
		return $this->db->select('email, id')->from('ts_merchant')->where('id', $id)->get()->row();
	}

	public function logout()
	{
		$users_id = $this->input->get_request_header('User-ID', true);
		$token = $this->input->get_request_header('Authorization', true);
		$this->db->where('users_id', $users_id)->where('token', $token)->delete('users_authentication');
		return array(
			'status' => 200,
			'message' => 'Successfully logout.'
		);
	}

	// this is to check the connection with the api key once the user  has successfully logged in

	public function auth($users_id, $token)
	{

		$users_id  = $this->input->get_request_header('User-ID', TRUE);
		 $token     = $this->input->get_request_header('Authorization', TRUE);
		// $users_id =  $this->get('userid', TRUE);
		// $token    =  $this->get('token', TRUE);

		$q = $this->db->select('expired_at')->from('users_authentication')->where('users_id', $users_id)->where('token', $token)->get()->row();
		if ($q == "") {
			return array(
				'status' => 401,
				'message' => 'Unauthorized.'
			);
		} else {
			if ($q->expired_at < date('Y-m-d H:i:s')) {
				return array(
					'status' => 401,
					'message' => 'Your session has been expired.'
				);
			} else {
				$updated_at = date('Y-m-d H:i:s');
				$expired_at = date("Y-m-d H:i:s", strtotime('+12 hours'));
				$this->db->where('users_id', $users_id)->where('token', $token)->update('users_authentication', array(
					'expired_at' => $expired_at,
					'updated_at' => $updated_at
				));
				return array(
					'status' => 200,
					'message' => 'Authorized.',
					'id' => $users_id
				);
			}
		}
	}

	public function header_auth()
	{
		$users_id = $this->input->get_request_header('User-ID', true);
		$token = $this->input->get_request_header('Authorization', true);
		//not selecting expired_at anymore but all the fields. 
		$q = $this->db->select()->from('users_authentication')->where('users_id', $users_id)->where('token', $token)->get()->row();
		if ($q == "") {
			return array(
				'status' => 401,
				'message' => 'Unauthorized.'
			);
		} else {

			return array(
						'status' => 200,
						'message' => 'Authorized.',
						'id' => $users_id
					);
			// if ($q->expired_at < date('Y-m-d H:i:s')) {
			// 	return array(
			// 		'status' => 401,
			// 		'message' => 'Your session has been expired.'
			// 	);
			// } else {
			// 	$updated_at = date('Y-m-d H:i:s');
			// 	$expired_at = date("Y-m-d H:i:s", strtotime('+12 hours'));
			// 	$this->db->where('users_id', $users_id)->where('token', $token)->update('users_authentication', array(
			// 		'expired_at' => $expired_at,
			// 		'updated_at' => $updated_at
			// 	));
			// 	return array(
			// 		'status' => 200,
			// 		'message' => 'Authorized.',
			// 		'id' => $users_id
			// 	);
			// }
		}
	}

	public function get_all_preachers()
	{
		$q = $this->db->select('id,name')->from('speakers')->order_by('id', 'desc')->get()->result();
		return array(
			'status' => 200,
			'result' => $q
		);
	}

	// fetch membership bio data from db

	public function get_all_church_members()
	{
		$query = $this->db->select('
			first_name,last_name,
			email,mobile_number,
			date_of_birth,
			gender,
			nationality,
			marital_status,
			address,
			member_photo')->from('mfw_church_membership')->order_by('id', 'desc')->get()->result();
		return array(
			'status' => 200,
			'church_members_result' => $query
		);
	}

	// End fetch membership bio data from db

	public function audio_all_data()
	{
	   return $this->db->select()->from('ts_products')->order_by('prod_id', 'desc')->where('img_link IS NOT NULL', NULL, false)->where('file_link IS NOT NULL',NULL,false)->get()->result();
		//  if ($q == true) {
		// 	return array(
		// 		'status' => 201,
		// 		'message' => 'Success fetching data',
		// 		'result'=> $q
		// 	);
		// } else {
		// 	return array(
		// 		'status' => 204,
		// 		'message' => 'error fetching data',
				
		// 	);
		// }
	}


	public function book_all_data()
	{
		return $this->db->select('id,title,author')->from('books')->order_by('id', 'desc')->get()->result();
	}

	public function book_detail_data($id)
	{
		return $this->db->select('id,title,author')->from('books')->where('id', $id)->order_by('id', 'desc')->get()->row();
	}

	public function create_user($data)
	{
		$q = $this->db->insert('ts_user', $data);
		if ($q == true) {
			return array(
				'status' => 201,
				'message' => 'User has been created.'
			);
		} else {
			return array(
				'status' => 204,
				'message' => 'User could not be created.'
			);
		}
	}

	public function book_create_data($data)
	{
		$q = $this->db->insert('books', $data);
		return array(
			'status' => 201,
			'message' => 'Data has been created.'
		);
	}

	public function book_update_data($id, $data)
	{
		$this->db->where('id', $id)->update('books', $data);
		return array(
			'status' => 200,
			'message' => 'Data has been updated.'
		);
	}

	public function book_delete_data($id)
	{
		$this->db->where('id', $id)->delete('books');
		return array(
			'status' => 200,
			'message' => 'Data has been deleted.'
		);
	}

	public function send_data_mail($data = array())
	{
		$this->load->library('email');
		$this->email->initialize(array(
			'protocol' => 'smtp',
			'smtp_host' => 'smtp.sendgrid.net',
			'smtp_user' => 'peniel.armah',
			'smtp_pass' => 'Welcome1#0545',
			'smtp_port' => 587,
			'crlf' => "\r\n",
			'newline' => "\r\n"
		));
		$this->email->from($data['email'], $data['name']);
		$this->email->to($data['toEmail']);

		// $this->email->cc('another@another-example.com');
		// $this->email->bcc('them@their-example.com');

		$this->email->subject($data['subject']);
		$this->email->message($data['message']);
		$this->email->send();
		$this->email->print_debugger();
	}

	public function forgot_password_email($data)
	{
		$q = $this->db->select()->from('ts_user')->where('user_email', $data)->get()->row();
		if ($q == "") {
			return array(
				'status' => 204,
				'message' => 'Email not found.'
			);
		} else {
			$uid = $q->user_id;
			$mobile = $q->user_mobile;
			$key = md5(date('Ymdhis') . $uid);
			$data = array(
				'status' => 200,
				'id' => $uid,
				'key' => $key,
				'link' => $mobile
			);
			return $data;
		}
	}

	public function update_key($id, $data)
	{
		$this->db->where('user_id', $id)->update('ts_user', $data);
		return array(
			'status' => 200,
			'message' => 'Sending reset code to mobile.'
		);
	}

	public function update_key_mobile($mobile, $data)
	{
		$this->db->where('user_mobile', $mobile)->update('ts_user', $data);
		return array(
			'status' => 200,
			'message' => 'Reset code sent.'
		);
	}

	public function church_all_data()
	{
		return $this->db->select('id, church_name')->from('ts_church')->order_by('id', 'desc')->get()->result();
	}

	public function generate_short_code($digits = 4)
	{
		$i = 0; //counter
		$pin = ""; //our default pin is blank.
		while ($i < $digits) {

			// generate a random number between 0 and 9.

			$pin .= mt_rand(0, 9);
			$i++;
		}

		return $pin;
	}

	public function sendEmail($receiver){
        $from = "gwopz4adz@gmail.com";    //senders email address
        $subject = 'Email Verification';  //email subject
        
        //sending confirmEmail($receiver) function calling link to the user, inside message body
        $message = 'Dear User,<br><br> Please click on the below activation link to verify your email address<br><br>
        <a href=\'https://myfreshword-dot-techloft-173609.appspot.com/App/confirmEmail/'.md5(date('his').($receiver)).'\'>https://myfreshword-dot-techloft-173609.appspot.com/App/confirmEmail/'. md5(date('his').($receiver)) .'</a><br><br>Thanks';
        
        //config email settings
        $config['protocol'] = 'smtp';
        $config['smtp_host'] = 'ssl://smtp.gmail.com';
        $config['smtp_port'] = '465';
        $config['smtp_user'] = $from;
        $config['smtp_pass'] = 'gman2014';  //sender's password
        $config['mailtype'] = 'html';
        $config['charset'] = 'iso-8859-1';
        $config['wordwrap'] = 'TRUE';
        $config['newline'] = "\r\n"; 
        
        $this->load->library('email', $config);
		$this->email->initialize($config);
        //send email
        $this->email->from($from);
        $this->email->to($receiver);
        $this->email->subject($subject);
        $this->email->message($message);
        
        if($this->email->send()){
			//for testing
            echo "sent to: ".$receiver."<br>";
			echo "from: ".$from. "<br>";
			echo "protocol: ". $config['protocol']."<br>";
			echo "message: ".$message;
            return true;
        }else{
            echo "email send failed";
            return false;
        }
        
       
	}
	
	function verifyEmail($key){
		$q = $this->db->select('user_id,user_status')->from('ts_user')->where('user_key', $key)->get()->row();
		if ($q == "") {
			return array(
				'status' => 204,
				'message' => 'Error activating account'
			);
		} else
			if ($q->user_status == 1) {
			return array(
				'status' => 200,
				'message' => 'Account already active..'
			);
		} else {
			$data = array('user_status' => 1);
			$this->db->where('user_id', $q->user_id)->update('ts_user', $data);
			return array(
				'status' => 200,
				'message' => 'Account activated successfully.'
			);
		}
       
    }
    


	public function send_code($phone, $pin)
	{

		$pin = $this->generate_short_code();

		$url = "https://api.hubtel.com/v1/messages/send?" . "From=MyFreshWord" . "&To=$phone" . "&Content=" . urlencode("myFreshWord secret code $pin") . "&ClientId=dgsfkiil" . "&ClientSecret=czywtkzd" . "&RegisteredDelivery=true";
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => "GET",
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		if ($err) {
			return "cURL Error #:" . $err;
		} else {
			return $response;
		}
	}

	public function activate_account($auth_code)
	{
		$q = $this->db->select('user_id,user_status')->from('ts_user')->where('user_activation_code', $auth_code)->get()->row();
		if ($q == "") {
			return array(
				'status' => 204,
				'message' => 'Invalid activation pin.'
			);
		} else
			if ($q->user_status == 1) {
			return array(
				'status' => 200,
				'message' => 'Account already active..'
			);
		} else {
			$data = array(
				'user_status' => 1
			);
			$this->db->where('user_id', $q->user_id)->update('ts_user', $data);
			return array(
				'status' => 200,
				'message' => 'Account activated successfully.'
			);
		}
	}

	public function reset_password_code($auth_code)
	{
		$q = $this->db->select()->from('ts_user')->where('user_reset_code', $auth_code)->get()->row();
		if ($q == "") {
			return array(
				'status' => 204,
				'message' => 'Invalid reset pin.'
			);
		} else
			if ($q->user_reset_code == $auth_code) {
			return array(
				'status' => 200,
				'message' => 'Passed',
				'email' => $q->user_email
			);
		}
	}

	public function update_password($email, $data)
	{
		$q = $this->db->where('user_email', $email)->update('ts_user', $data);
		return array(
			'status' => 200,
			'message' => 'Password updated successfully',
			'email' => $email,
			'pass' => $data,
			'query_res' => $q
		);
	}

	public function feed_data($data)
	{
		$q = $this->db->insert('ts_feed', $data);
		if ($q === true) {
			return array(
				'status' => 201,
				'message' => 'Data has been created.',
				'query' => $q
			);
		} else {
			return array(
				'status' => 204,
				'message' => 'Error inserting data.',
				'query' => $q
			);
		}
	}

	private
		function generate_auth_key($email)
	{
		$q = $this->db->select()->from('ts_user')->where('user_email', $email)->get()->row();
		if ($q != "") {
			$last_login = date('Y-m-d H:i:s');
			$token_set = substr(md5(rand()), 0, 7);
			$token = hash('sha256', $token_set);
			$expired_at = date("Y-m-d H:i:s", strtotime('+12 hours'));
			$this->db->trans_start();
			$this->db->where('user_id', $q->user_id)->update('ts_user', array(
				'last_login' => $q->last_login
			));
			$this->db->insert('users_authentication', array(
				'users_id' => $q->user_id,
				'token' => $token,
				'expired_at' => $expired_at
			));
			if ($this->db->trans_status() === false) {
				$this->db->trans_rollback();
				return array(
					'status' => 500,
					'message' => 'Internal server error.'
				);
			} else {
				$this->db->trans_commit();
				return array(
					'status' => 200,
					'message' => 'Successfully login.',
					'id' => $q->user_id,
					'token' => $token
				);
			}
		}
	}

	public function facebook_data($data = array())
	{
		$q = $this->db->select()->from('ts_user')->where('fb_id', $data['fb_id'])->get()->row();
		if ($q == "") { //user is new member
			$query = $this->db->insert('ts_user', $data);
			$access = $this->generate_auth_key($data['fb_email']);
			return array(
				'query' => $query,
				'API_ACCESS' => $access
			);
		} else { //user already exist //
			$query = $this->generate_auth_key($q->user_email);
			return $query;
		}
	}

	public function google_data($data = array())
	{
		$q = $this->db->select()->from('ts_user')->where('g_user_id', $data['g_user_id'])->get()->row();
		if ($q == "") { //user is new member
			$query = $this->db->insert('ts_user', $data);
			$access = $this->generate_auth_key($data['g_email']);
			return array(
				'query' => $query,
				'API_ACCESS' => $access
			);
		} else { //user already exist //
			$query = $this->generate_auth_key($q->user_email);
			return $query;
		}
	}

	public function user_profile_data($id)
	{
		$q = $this->db->select()->from('ts_user')->where('user_id', $id)->get()->row();
		if ($q == "") {
			return array(
				'status' => 404,
				'message' => 'Unauthorized access',
				'query' => $q
			);
		} else {
			return array(
				'status' => 200,
				'message' => 'Profile data fetched',
				'query' => $q
			);
		}
	}

	public	function product_id($id)
	{
		$q = $this->db->select('*')->from('ts_products')->where('prod_id', $id)->get()->row();
		if ($q == true) {
			return array(
				'status' => 200,
				'message' => 'success fetching data',
				'result' => $q
			);
		} else {
			return array(
				'status' => 404,
				'message' => 'error fetching data',
				
			);
		}
	}


	public function church_details($id)
{
	return $this->db->select()->from('ts_church')->where('id', $id)->get()->row();
	
}


	public

		function phone_momo($mobile)
	{

		// set API Access Key

		$access_key = 'a41000a45a900d1ba598be3e977fc387';

		// set phone number

		$phone_number = $mobile;

		// Initialize CURL:

		$ch = curl_init('http://apilayer.net/api/validate?access_key=' . $access_key . '&number=' . $phone_number . '');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// Store the data:

		$json = curl_exec($ch);
		curl_close($ch);

		// Decode JSON response:

		return $validationResult = json_decode($json, true);

		// Access and use your preferred validation result objects
		// $validationResult['valid'];
		// $validationResult['country_code'];
		// $validationResult['carrier'];

	}

	public function new_momo($mobile)
	{

		// set API Access Key

		$access_key = 'a88d6294fe6ae7ded65c1f7fc7911c8e';

		// set phone number

		$phone_number = $mobile;

		// Initialize CURL:

		$ch = curl_init('http://apilayer.net/api/validate?access_key=' . $access_key . '&number=' . $phone_number . '');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// Store the data:

		$json = curl_exec($ch);
		curl_close($ch);

		// Decode JSON response:

		return $validationResult = json_decode($json, true);

		// Access and use your preferred validation result objects
		// $validationResult['valid'];
		// $validationResult['country_code'];
		// $validationResult['carrier'];

	}

	public function insert_momo($data)
	{
		$query = $this->db->insert('momo', $data);
		if ($query === true) {
			return array(
				'status' => 200,
				'message' => 'Momo saved',
				'query' => $query
			);
		} else {
			return array(
				'status' => 400,
				'message' => 'Error saving momo',
				'query' => $query
			);
		}
	}

	public function check_momo_exist($data = array())
	{
		$mobile = $data['number'];
		$q = $this->db->select()->from('momo')->where('number', $mobile)->get()->row();
		if ($q->number != $mobile) { //if there are not duplicates
			return true;
		} else { // if there are duplicates
			return array(
				'status' => 404,
				'message' => 'Mobile Money Number already used on another account'
			);
		}
	}

	public function momo_by_id($email)
	{
		$q = $this->db->select()->from('momo')->where('unique_acc', $email)->get()->row();
		if ($q->unique_acc == " ") { //if email doesnt exist
			return false;
		} else {
			return $q;
		}
	}

	public function bin_checker($bin)
	{
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://api.freebinchecker.com/bin/" . $bin,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"cache-control: no-cache"

				// "postman-token: 8f957757-094d-6903-f2e2-f0dbb9d1ee06"

			),
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		if ($err) {
			echo "cURL Error #:" . $err;
		} else {
			return $responseResult = json_decode($response, true);
		}
	}

	// when a user is adding new data that hasnt been bought

	public function addToCart($data = array())
	{
		$checkLib = $this->check_if_item_is_purchased($data);
		if ($checkLib['success'] == true) {
			$query = $this->db->select()->from('ts_cart')->where('prod_uniqid', $data['prod_uniqid'])->where('prod_purchase_by', $data['prod_purchase_by'])->where('paid', 0)->get()->row();
			if ($query == "") { //if query didnt bring back anything
				$insertDB = $this->db->insert('ts_cart', $data);
				if ($insertDB == true) {
					return array(
						'success' => true,
						'message' => 'Product added successfully',
						'db_query' => $insertDB
					);
				} else {
					return array(
						'success' => false,
						'message' => 'Error adding product to cart',
						'db_query' => $insertDB
					);
				}
			} else {
				return array(
					'success' => false,
					'message' => 'Product already added'
				);
			}
		} else {
			return $checkLib;
		}
	}

	/*
	|--------------------------------------------------------------------------
	| Model for Adding An Item To Library
	|--------------------------------------------------------------------------
	|
	| Here is where you add a product to the users library once they tab add
	| Now create something great!
	|
	 */
	public function addto_library($data = array())
	{
		$checkLib = $this->confirm_item_library($data);
		if ($checkLib['success'] == true) {

			// $query = $this->db->select()->from('ts_Library')->where('prod_uniqid',$data['prod_uniqid'])->where('prod_purchase_by',$data['userid'])->get()->row();
			// if($query == ""){//if query didnt bring back anything

			$insertDB = $this->db->insert('ts_Library', $data);
			if ($insertDB == true) {
				return array(
					'success' => true,
					'message' => 'Product added successfully to Library',
					'db_query' => $insertDB
				);
			} else {
				return array(
					'success' => false,
					'message' => 'Error adding product to library',
					'db_query' => $insertDB
				);
			}
		} else {
			return $checkLib;
		}
	}

	public function confirm_item_library($data = array())
	{
		$query = $this->db->select()->from('ts_Library')->where('prod_uniqid', $data['prod_uniqid'])->where('userid', $data['userid'])->get()->row();
		if ($query == "") {

			// move to next function

			return array(
				'success' => true,
				'message' => 'Product not purchased to library yet'
			);
		} else {
			return array(
				'success' => false,
				'message' => 'Product already purchased to library'
			);
		}
	}

	public function check_if_item_is_purchased($data = array())
	{
		$query = $this->db->select()->from('ts_paid_prod')->where('prod_uniqid', $data['prod_uniqid'])->where('user_acc', $data['prod_purchase_by'])->get()->row();
		if ($query == "") {

			// move to next function

			return array(
				'success' => true,
				'message' => 'Product not purchased to library yet'
			);
		} else {
			return array(
				'success' => false,
				'message' => 'Product already purchased to library'
			);
		}
	}

	public function cartRowCount($data = array())
	{ //counting for cart items that havent been paid
		$this->db->select('*')->from('ts_cart')->where('prod_purchase_by', $data['prod_purchase_by'])->where('paid', 0);
		$q = $this->db->get();
		return $q->num_rows();
	}

	public function TotalCartSales($data)
	{ //total price of items in the cart not paid for yet
		$this->db->select_sum('prod_price');
		$this->db->from('ts_cart');
		$this->db->where('prod_purchase_by', $data['prod_purchase_by']); //by email
		$this->db->where('paid', 0); //where product hasnt been paid yet
		$query = $this->db->get();
		if ($query->row()->prod_price === null) {
			return $data = '0';
		} else {
			return $query->row()->prod_price;
		}
	}

	public function fetch_cart_data($data)
	{
		$query = $this->db->select()->from('ts_cart')->where('prod_purchase_by', $data['prod_purchase_by'])->where('paid', 0)->order_by('id', 'desc')->get()->result();
		return $query;
	}

	public function delete_cart_data($data)
	{
		$query = $this->db->where('prod_purchase_by', $data['prod_purchase_by'])->where('paid', 0)->where('id', $data['id'])->delete('ts_cart');
		if ($query == true) {
			return array(
				'status' => 200,
				'message' => 'Data has been deleted.',
				'Query' => $query
			);
		} else {
			return array(
				'status' => 400,
				'message' => 'Data delete error.',
				'Query' => $query
			);
		}
	}

	public function delete_all_cart($data)
	{
		$query = $this->db->where('prod_purchase_by', $data['prod_purchase_by'])->delete('ts_cart'); //this will take the email of the user and empty the cart
		return array(
			'status' => 200,
			'message' => 'Empty Cart Data'
		);
	}

	// trying to merge arrays here

	public function library_data($userid)
	{
		$query = $this->db->select('prod_uniqid')->from('ts_Library')->where('userid', $userid)->order_by('id', 'desc')->get()->result();
		return array(
			'results' => $query
		);
	}

	public function free_library_data()
	{
		return $this->db->select('*')->from('ts_paid_prod')->where('user_acc', 'admin@techloftgh.com')->where('free', 1)->order_by('id', 'desc')->get()->result();
	}

	public function checkout_data($data)
	{
		return $query = $this->db->insert('ts_paid_prod', $data);
	}

	// should contain email and id param of the product to be deleted

	public function delete_library_data($data)
	{
		$query = $this->db->select('user_email')->from('ts_user')->where('user_id', $data['id'])->get()->row();
		$this->db->where('user_acc', $query['email'])->where('id', $data['id'])->delete('ts_paid_prod');
		return array(
			'status' => 204,
			'message' => 'Library removed from library'
		);
	}

	public function payIN_endpoint($phoneNumber, $payAmount, $churchAccount)
	{
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => "http://api.techloftgh.com/api/Transactions/buyAPI",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => "{\n    \"service\": \"mobilemoney\",\n    \"account\": \"233545057185\",\n    \"channel\": \"mobilemoney\",\n    \"network\": \"mtn\",\n    \"amount\": \"0.10\",\n    \"servicedetails\": {\n      \"account\": \"myfreshword\",\n      \"type\": \"myfreshword\",\n      \"network\": \"icgc\",\n      \"description\": \"myFreshword Payment\",\n      \"amount\": \"1\"\n    }\n  }",
			CURLOPT_HTTPHEADER => array(
				"cache-control: no-cache",
				"content-type: application/json",
				"postman-token: 6399befd-a47a-5b0f-6bde-fdff7e8caafd"
			),
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		if ($err) {
			echo "cURL Error #:" . $err;
		} else {
			echo $response;
		}
	}


	  

	// user subscriptions management

	public function subscribe($sub_id , $userid)
	{
		$q = $this->db->select('sub_type, sub_price')->from('subscription_modules')->where('id',$sub_id)->get()->row();
		if( $q == ""){
		 return array(
			 'status' => 204,
			 'message' => 'subscription type not recognized'
		 );
		}else{
			$a = $q->sub_type;
			$b = $q->sub_price;

		// check if user has no valid subscription

		$hasValidSubscription = $this->isSubscribed($userid);
		if ($hasValidSubscription['status'] == 204) {

			// subscribe user here

			switch ($a) {
				case 'BRONZE':

				// code...

				
					$amountPaid = $b;
					$purchaseDate = date('Y-m-d H:i:s');
					$expired = date("Y-m-d H:i:s", strtotime('+1 day'));
					break;

				case 'SILVER':

				// code...

					$amountPaid = $b;
					$purchaseDate = date('Y-m-d H:i:s');
					$expired = date("Y-m-d H:i:s", strtotime('+1 week'));
					break;

				case 'GOLD':

				// code...

					$amountPaid = $b;
					$purchaseDate = date('Y-m-d H:i:s');
					$expired = date("Y-m-d H:i:s", strtotime('+1 month'));
					break;

				case 'PLATINUM':

				// code...

					$amountPaid = $b;
					$purchaseDate = date('Y-m-d H:i:s');
					$expired = date("Y-m-d H:i:s", strtotime('+1 month'));
					break;

				case 'CONFERENCE':

				// code...

					$amountPaid = $b;
					$purchaseDate = date('Y-m-d H:i:s');
					$expired = date("Y-m-d H:i:s", strtotime('+1 week'));
					break;

				default:

				// code...

					return array(
						'status' => 404,
						'message' => 'Error enrolling user to Subscription'
					);
					break;
			}

			$query = $this->db->insert('ts_subscription', array(
				'userid' => $userid,
				'subscriptionType' => $a,
				'amountPaid' => $b,
				'purchaseDate' => $purchaseDate,
				'expired' => $expired
			));
			return array(
				'status' => 200,
				'message' => 'Subscription completed successfully.',
				'paid' => 'true'
			);
		} else {
			return array(
				'status' => 204,
				'message' => 'Subscription process failed because user already has a valid subscription'
			);
		}
	}
}

	public function isSubscribed($userid)
	{

		// Using MySQL NOW() in Codeigniter
		// $this->db->select('field_name', 'NOW()', FALSE);
		// The FALSE in the set method prevents the NOW() being escaped

		$now = date('Y-m-d H:i:s');
		$query = $this->db->select('*')->from('ts_subscription')->where('userid', $userid)->where('expired >', $now)->get()->row();
		if ($query == true) {
			return array(
				'status' => 200,
				'message' => 'valid subscription found',
				'results' => $query
			);
		} else {
			return array(
				'status' => 204,
				'message' => 'No valid Subscription package found'
			);
		}
	}

	public function email_enable($data, $param)
	{

		// enable email alerts  ===  Id from user profile details

		$this->db->where('id', $param['id'])->update('ts_user', $data);
		return array(
			'status' => 200,
			'message' => 'Email Notification Enabled.'
		);
	}

	public function sms_enable($data, $param)
	{

		// this is saying send alerts if == Id from user profile details
		// so here we need to check first if user phone number is set

		$query = $this->mobile($data, $param);
		if ($query['status'] == 400) {
			return $query;
		} else {
			$this->db->where('id', $param['id'])->update('ts_user', $data);
			return array(
				'status' => 200,
				'message' => 'SMS Notification Enabled.'
			);
		}
	}

	private
		function mobile_exist($data, $param)
	{
		$query = $this->db->select('user_mobile')->from('ts_user')->where('user_mobile', $param['mobile'])->where('user_email', $param['email'])->get()->row();
		if ($query->user_mobile == "") {
			return array(
				'status' => 400,
				'message' => 'Update profile info with mobile'
			);
		} else {
			return array(
				'status' => 200,
				'message' => 'mobile number set'
			);
		}
	}

	public function update_user_profile($id, $data)
	{
		$query = $this->db->where('user_id', $id)->update('ts_user', $data);
		if ($query == true) {
			return array(
				'status' => 200,
				'message' => 'Profile data has been updated.'
			);
		} else {
			return array(
				'status' => 400,
				'message' => 'Error updating profile data.'
			);
		}
	}

	public function update_user($id, $data, $img)
	{
		if ($img == "") {
			$updateData = array(
				'user_uname' => $data['username'],
				'user_mobile' => $data['mobile'],
				'user_email' => $data['email']

				// 'image'             => $img,
			);
		} else {
			$updateData = array(
				'user_uname' => $data['username'],
				'user_mobile' => $data['mobile'],
				'user_email' => $data['email'],
				'img_url' => $img

			);
		}

		$query = $this->db->where('user_id', $id)->update('ts_user', $updateData);
		if ($query == false) {
			return array(
				'status' => 404,
				'message' => 'Error updating user data'
			);
		}

		return array(
			'status' => 201,
			'message' => 'user data updated successfully'
		);
	}

	

	public function check_db_with_rest_client($data)
	{
		$query = $this->db->select('*')->from('ts_user')->where('user_id', $data['id'])->get()->row();

		// check values returned here with the post data being passed from the rest client

		if ($query->user_uname == $data['username'] && $query->user_mobile == $data['mobile'] && $query->user_pwd == $data['password']) {
			return array(
				'status' => 401,
				'message' => 'No Profile Changes made'
			);
		} else {
			return array(
				'status' => 200,
				'message' => 'Data ready to update'
			);
		}
	}

	public function callback_response($data)
	{
		return $q = $this->check_if_freshword_transaction_id_exist($data);

		// if exist complete payment processing with response

	}

	private
		function check_if_freshword_transaction_id_exist($data)
	{
		$query = $this->db->select()->from('payment_response')->where('freshword_transaction_id', $data['freshword_transaction_id'])->get()->row();
		if ($query != "") {
			return $q = $this->complete_payment($data);
		} else {
			return array(
				'status' => 400,
				'message' => 'Freshword transaction id is invalid'
			);
		}
	}

	private
		function complete_payment($data)
	{ //this will be an update statement
		$query = $this->db->where('freshword_transaction_id', $data['freshword_transaction_id'])->update('payment_response', $data);
		if ($query == true) {
			return array(
				'status' => 200,
				'message' => 'payment process completed'
			);
		} else {
			return array(
				'status' => 400,
				'message' => 'There was an issue processing your payment'
			);
		}
	}

	public function user_momo_numbers($data)
	{ //here we check the unique account using the email address of the user
		$query = $this->db->select('*')->from('momo')->where('unique_acc', $data['email'])->order_by('id', 'desc')->get()->result();
		if ($query == "") {
			return array(
				'status' => 204,
				'message' => 'Add a mobile money number'
			);
		} else {

			// return array('status'=>200, 'message'=> 'User has set mobile money number', 'query'=>$query);//query will return all array with email address

			return $query;
		}
	}

	public function delete_momo_number($data)
	{
		$query = $this->db->where('id', $data['id'])->where('unique_acc', $data['email'])->delete('momo');
		if ($query == true) {
			return array(
				'status' => 200,
				'message' => 'Mobile money number removed successfully'
			);
		} else {
			return array(
				'status' => 400,
				'message' => 'Error removing mobile money number'
			);
		}
	}

	public function set_momo_default($data)
	{ //turn all off and set one as set_momo_default
		$q = $this->user_momo_active($data);
		if ($q['status'] == 200) {

			// set default = 1 to set as active

			$query = $this->db->where('payin_number', $data['payin_number'])->update('momo', $data);
			if ($query == true) {
				return array(
					'status' => 200,
					'message' => 'Number set as default'
				);
			}
		} else {
			return $q;
		}
	}

	private
		function user_momo_active($data)
	{
		$query = $this->db->select('*')->from('momo')->where('unique_acc', $data['email'])->where('set_default', 1)->get()->row();
		if ($query == "") { //if no number is active
			$q = $this->db->where('payin_number', $data['payin_number'])->update('momo', $data); //here the only data to set is set_default to 1
			if ($q == true) {
				return array(
					'status' => 201,
					'message' => 'Mobile money number set as default'
				);
			}
		} else {

			// turn off the already set Number

			$offdata = array(
				'payin_number' => $query->payin_number,
				'set_default' => 0
			);
			$res = $this->db->where('payin_number', $offdata['payin_number'])->update('momo', $offdata);
			if ($res == true) {
				return array(
					'status' => 200,
					'message' => 'Default number changed to zero'
				);
			} else {
				return array(
					'status' => 400,
					'message' => 'Default number could not be changed'
				);
			}
		}
	}

	// this should generate the transaction id stored inside the database

	public function RandomString($length = 39)
	{
		$randstr = "";
		srand((double)microtime(true) * 1000000);

		// our array add all letters and numbers if you wish

		$chars = array(
			'a',
			'b',
			'c',
			'd',
			'e',
			'f',
			'g',
			'h',
			'i',
			'j',
			'k',
			'l',
			'm',
			'n',
			'p',
			'q',
			'r',
			's',
			't',
			'u',
			'v',
			'w',
			'x',
			'y',
			'z',
			'1',
			'2',
			'3',
			'4',
			'5',
			'6',
			'7',
			'8',
			'9',
			'A',
			'B',
			'C',
			'D',
			'E',
			'F',
			'G',
			'H',
			'I',
			'J',
			'K',
			'L',
			'M',
			'N',
			'O',
			'P',
			'Q',
			'R',
			'S',
			'T',
			'U',
			'V',
			'W',
			'X',
			'Y',
			'Z'
		);
		for ($rand = 0; $rand <= $length; $rand++) {
			$random = rand(0, count($chars) - 1);
			$randstr .= $chars[$random];
		}

		// echo $randstr;
		// echo number_format($randstr,0,"","-");

		return wordwrap($randstr, 10, '-', true);
	}

	public function trans_rotate($email)
	{
		$q = $this->db->select('transactionid')->from('ts_cart')->where('prod_purchase_by', $email)->get()->row();
		if ($q == "") {
			return $this->RandomString();
		} else {
			return $q->transactionid; //if there is one it will always return this
		}
	}

	// this model is to get just the product comment title of the product

	public function get_comment_title_data($data)
	{ //now this should be associated with the comment title
		if ($data['prod_id'] == "" && $data['comment_title'] == "") {
			return array(
				'status' => 204,
				'message' => 'There is an error with the comment title'
			);
		} else {
			$q = $this->db->select('*')->from('comments')->where('prod_id', $data['prod_id'])->where('comment_title', $data['comment_title'])->get()->row();
			if ($q == true) {
				return array(
					'status' => 200,
					'query' => $q
				);
			}
		}
	}

	// process payment
	// dont allow users to enter when

	public function payment_to_db($data)
	{
		$q = $this->db->select('*')->from('payment_response')->where('freshword_transaction_id', $data['freshword_transaction_id'])->get()->row();
		if ($q == "") { //here if it shows nothing
			$this->db->insert('payment_response', $data);
			return array(
				'status' => 201,
				'message' => 'New payment data inserted'
			);
		} else {
			return array(
				'status' => 202,
				'message' => 'payment data already exist'
			);
		}
	}

	// public function delete_library_data($email){
	//   return $this->db->where('prod_purchase_by',$email)->delete('ts_cart');
	// }
	// need to check this data

	public function search_product($search_term)
	{

		// $search_term=$this->input->post('textboxName');

		$search_term = "%" . $search_term . "%";
		$sql = "SELECT * FROM ts_products WHERE prod_name LIKE ? ";
		$query = $this->db->query($sql, array(
			$search_term
		));
		$res = $query->result(); //so basically we are going to return an array of the results
		if (count($res) > 0) {
			return $res;
		} else {
			return array(
				'status' => 400,
				'message' => 'Sorry No Data found'
			);
		}
	}

	// this will arrange products on the home page based the type of data to be fetched

	public function arrange_by_category()
	{
	}

	public function audio_fetch()
	{
		return $this->db->select('*')->from('ts_products')->where('type_list', 'Audio')->order_by('prod_id', 'desc')->get()->result();
	}

	public function video_fetch()
	{
		return $this->db->select('*')->from('ts_products')->where('type_list', 'Video')->order_by('prod_id', 'desc')->get()->result();
	}

	public function book_fetch()
	{
		return $this->db->select('*')->from('ts_products')->where('type_list', 'Book')->order_by('prod_id', 'desc')->get()->result();
	}

	// where like statement for the queries below search filter

	public function audio_by_title($search_term)
	{
		$this->db->select('*');
		$this->db->from('ts_products');
		$this->db->like('prod_name', $search_term);
		$this->db->where('type_list', 'Audio');
		$this->db->order_by('prod_id', 'desc');
		$query = $this->db->get();
		return $res = $query->result();
	}

	public function video_by_title($search_term)
	{
		$this->db->select('*');
		$this->db->from('ts_products');
		$this->db->like('prod_name', $search_term);
		$this->db->where('type_list', 'Video');
		$this->db->order_by('prod_id', 'desc');
		$query = $this->db->get();
		return $res = $query->result();
	}

	public function book_by_title($title_data)
	{
		$this->db->select('*');
		$this->db->from('ts_products');
		$this->db->like('prod_name', $search_term);
		$this->db->where('type_list', 'Book');
		$this->db->order_by('prod_id', 'desc');
		$query = $this->db->get();
		return $res = $query->result();
	}

	public function create_merchant($data)
	{
		$query = $this->db->insert('ts_merchant', $data);
		if ($query == true) {
			return array(
				'status' => 200,
				'message' => 'Merchant account created successfully'
			);
		} else {
			return array(
				'status' => 400,
				'message' => 'Error creating merchant account'
			);
		}
	}

	public function create_resident($data)
	{

		$query = $this->db->insert('ts_residentpastor', $data);
		if ($query == true) {
			return array('status' => 200, 'message' => 'Resident created successfully');
		} else {
			return array('status' => 400, 'message' => 'Error adding Residents');
		}
	} 

	// Create Church Mmembership

	public function create_church_member($data)
	{
		$query = $this->db->insert('mfw_church_membership', $data);
		if ($query == true) {
			return array(
				'status' => 200,
				'message' => 'Member created successfully'
			);
		} else {
			return array(
				'status' => 400,
				'message' => 'Error adding membership data'
			);
		}
	}


	public function create_merchant_group($data)
	{
		$query = $this->db->insert('merchant_group', $data);
		if ($query == true) {
			return array(
				'status' => 200,
				'message' => 'Group created successfully'
			);
		} else {
			return array(
				'status' => 400,
				'message' => 'Error adding Group'
			);
		}
	}
	



	public function merchant_insert_product($data)
	{
		$query = $query = $this->db->insert('ts_products', $data);
		$insert_id = $this->db->insert_id();
		if ($query == true) {
			return array(
				'status' => 200,
				'message' => 'Product added successfully',
				'last_insert_row' => $insert_id
			);
		} else {
			return array(
				'status' => 400,
				'message' => 'Error adding product details'
			);
		}
	}

	// add this is to file name and insert data

	public function imgPlus($data)
	{
		return "https://myfreshword-dot-techloft-173609.appspot.com/public/images/products/" . $data;
	}

	public function replace_hyphens($string)
	{
		return str_replace(' ', '-', $string);
	}

	public function prod_type($data)
	{
		if ($data == 'Audio') {
			return 'microphone';
		} else
			if ($data == 'Video') {
			return 'videocam';
		} else
			if ($data == 'Book') {
			return 'book';
		}
	}

	public function generate_product_unique_code($length = 12)
	{
		$randstr = "";
		srand((double)microtime(true) * 1000000);

		// our array add all letters and numbers if you wish

		$chars = array(
			'a',
			'b',
			'c',
			'd',
			'e',
			'f',
			'g',
			'h',
			'i',
			'j',
			'k',
			'l',
			'm',
			'n',
			'p',
			'q',
			'r',
			's',
			't',
			'u',
			'v',
			'w',
			'x',
			'y',
			'z',
			'1',
			'2',
			'3',
			'4',
			'5',
			'6',
			'7',
			'8',
			'9',
			'A',
			'B',
			'C',
			'D',
			'E',
			'F',
			'G',
			'H',
			'I',
			'J',
			'K',
			'L',
			'M',
			'N',
			'O',
			'P',
			'Q',
			'R',
			'S',
			'T',
			'U',
			'V',
			'W',
			'X',
			'Y',
			'Z'
		);
		for ($rand = 0; $rand <= $length; $rand++) {
			$random = rand(0, count($chars) - 1);
			$randstr .= $chars[$random];
		}

		return $randstr;

		// echo number_format($randstr,0,"","-");
		// return wordwrap($randstr, 10, '-', true);

	}

	public function generate_merchant_activation_code($length = 6)
	{
		$randstr = "";
		srand((double)microtime(true) * 1000000);

		// our array add all letters and numbers if you wish

		$chars = array(
			'a',
			'b',
			'c',
			'd',
			'e',
			'f',
			'g',
			'h',
			'i',
			'j',
			'k',
			'l',
			'm',
			'n',
			'p',
			'q',
			'r',
			's',
			't',
			'u',
			'v',
			'w',
			'x',
			'y',
			'z',
			'1',
			'2',
			'3',
			'4',
			'5',
			'6',
			'7',
			'8',
			'9',
			'A',
			'B',
			'C',
			'D',
			'E',
			'F',
			'G',
			'H',
			'I',
			'J',
			'K',
			'L',
			'M',
			'N',
			'O',
			'P',
			'Q',
			'R',
			'S',
			'T',
			'U',
			'V',
			'W',
			'X',
			'Y',
			'Z'
		);
		for ($rand = 0; $rand <= $length; $rand++) {
			$random = rand(0, count($chars) - 1);
			$randstr .= $chars[$random];
		}

		return $randstr;
	}

	public function activate_merchant($data)
	{
		$query = $this->db->select('mobile,approval_code')->from('ts_merchant')->where('mobile', $data['mobile'])->where('approval_code', $data['approval_code'])->get()->row();
		if ($query == "") {
			return array(
				'status' => 400,
				'message' => 'Invalid activation code'
			);
		} else {
			$update = array(
				'approved' => 1
			);
			$q = $this->db->where('mobile', $data['mobile'])->update('ts_merchant', $update);
			return array(
				'status' => 200,
				'message' => 'Merchant account successfully activated',
				'approval_status' => $q
			);
		}
	}

	public function check_merchant_email($data)
	{
		$query = $this->db->select('email,mobile')->from('ts_merchant')->where('email', $data['email'])->get()->row();
		if ($query == "") {
			return array(
				'status' => 400,
				'message' => 'Sorry email address does not exist'
			);
		} else {
			$q = $this->create_reset_code($query->mobile);
			return array(
				'status' => 200,
				'message' => 'Email address is present',
				'mobile' => $query->mobile,
				'resetSms' => $q
			);
		}
	}

	public function create_reset_code($mobile)
	{
		$resetcode = $this->generate_merchant_activation_code($length = 6);
		$reset_code = array(
			'reset_code' => $resetcode
		);
		$sms = $this->send_reset_code($mobile, $reset_code['reset_code']);
		$q = $this->db->where('mobile', $mobile)->update('ts_merchant', $reset_code);
		return array(
			'dbquery' => $q,
			'sms' => $sms
		);
	}

	public function check_reset_code($mobile, $resetcode)
	{
		$query = $this->db->select('mobile, reset_code')->from('ts_merchant')->where('reset_code', $resetcode)->get()->row();
		if ($query == "") {
			return array(
				'status' => 400,
				'message' => 'Invalid reset code'
			);
		} else {

			// now send new password

			$newpass = $this->generate_merchant_activation_code($length = 8);
			$query = $this->temp_merchant_password($newpass, $mobile);
			return array(
				'status' => 200,
				'message' => 'Sending temporary password to your mobile',
				'query' => $query
			);
		}
	}

	public function temp_merchant_password($newpassword, $mobile)
	{
		$updatepass = array(
			'password' => hash('sha256', $newpassword)
		);
		$q = $this->db->where('mobile', $mobile)->update('ts_merchant', $updatepass);
		$sms = $this->send_new_pass($mobile, $newpassword);
		return array(
			'newpass' => $newpassword,
			'updatestatus' => $q,
			'sms' => $sms
		);
	}

	public function send_new_pass($phone, $newpass)
	{

		// $pin = $this->generate_short_code();

		$url = "https://api.hubtel.com/v1/messages/send?" . "From=MyFreshWord" . "&To=$phone" . "&Content=" . urlencode("Your temporary password : $newpass , please do well to change your password after logging in .Thank You") . "&ClientId=dgsfkiil" . "&ClientSecret=czywtkzd" . "&RegisteredDelivery=true";
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => "GET",
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		if ($err) {
			return "cURL Error #:" . $err;
		} else {
			return $response;
		}
	}

	public function send_reset_code($phone, $resetcode)
	{
		$url = "https://api.hubtel.com/v1/messages/send?" . "From=MyFreshWord" . "&To=$phone" . "&Content=" . urlencode("Your account reset code : $resetcode") . "&ClientId=dgsfkiil" . "&ClientSecret=czywtkzd" . "&RegisteredDelivery=true";
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => "GET",
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		if ($err) {
			return "cURL Error #:" . $err;
		} else {
			return $response;
		}
	}

	// sdearch data by email

	public function get_merchant_profile($id)
	{
		$query = $this->db->select('id,first_name,last_name,email,mobile,organisation,location,merchant_name, address, country, org_info, facebook, twitter, youtube, display_image')->from('ts_merchant')->where('id', $id)->get()->row();
		if ($query == "") {
			return array(
				'status' => 400,
				'message' => 'Error fetching merchant profile data'
			);
		} else {
			return array(
				'status' => 200,
				'message' => $query
			);
		}
	}

	
	public function get_subscription_packages()
	{
		$query = $this->db->select('')->from('subscription_modules')->get()->row();
		if ($query == "") {
			return array(
				'status' => 400,
				'message' => 'Error fetching subscription  data'
			);
		} else {
			return array(
				'status' => 200,
				'message' => $query
			);
		}
	}

	public function get_subscription_modules()
	{
		$query = $this->db->select('ID, sub_type, sub_price')->from('subscription_modules')->get()->result();
		if ($query == "") {
			return array(
				'status' => 400,
				'message' => 'Error fetching subscription packages'
			);
		} else {
			return array(
				'status' => 200,
				'message' => $query
			);
		}
	}

	// Get all churches and their ids
	public function get_church_data($id)
	{
		$query = $this->db->select('user_church_id')->from('ts_user')->where('user_id', $id)->get()->row();
		if ($query == "") {
			return array('status' => 400, 'message' => 'Error fetching all churches data');
		} else {
			
			$churchid = $query->user_church_id;
			$find = $this->db->select()->from('ts_church')->where('id', $churchid)->get()->row();
			
			return  array('status' => 200, 'message' => $find);
		}
	}

	public function update_image($id, $data)
	{
			return $this->db->where('prod_id', $id)->update('ts_products', $data);
			
	}

	

	// upload and update photo for mobile endpoint

	public function upload_profile_image($id, $data)
	{
		$query = $this->db->where('user_id', $id)->update('ts_users', $data);
	}

	public function update_file($id, $data)
	{
		return $this->db->where('prod_id', $id)->update('ts_products', $data);
	}

	public function upload_path($id)
	{
		$query = $this->db->select('*')->from('ts_products')->where('prod_id', $id)->get()->row();
		if ($query->type_list == "Audio") {
			return "audio";
		} else
			if ($query->type_list == "Video") {
			return "video";
		} else
			if ($query->type_list == "Book") {
			return "book";
		}
	}

	public function favicon_show($data)
	{
		if ($data == "Book") {
			return "fa fa-book";
		} else
			if ($data == "Audio") {
			return "fa fa-music";
		} else
			if ($data == "Video") {
			return "fa fa-video-camera";
		}
	}

	// play a particular product / audio / book / video

	public function product_preview($id)
	{
		return $query = $this->db->select('prod_tags, file_link')->from('ts_products')->where('prod_id', $id)->get()->row();
	}

	public function edit_product($id)
	{
		return $query = $this->db->select('*')->from('ts_products')->where('prod_id', $id)->get()->row();
	}

	

	public function edit_members($id)
	{
		return $query = $this->db->select('*')->from('mfw_church_membership')->where('id', $id)->get()->row();
	}


	

	public function update_ts_products($data)
	{
		$update = array(
			'prod_name' => $data['prod_name'],
			'prod_preacher' => $data['prod_preacher'],
			'prod_tags' => $data['prod_tags'],
			'prod_description' => $data['prod_description'],
			'prod_essay' => $data['prod_essay'],
			// 'prod_price' => $data['prod_price'],
			// 'currency' => $data['prod_currency'],
			'prod_urlname' => $this->replace_hyphens($data['prod_name']),
			'prod_type' => $this->prod_type($data['prod_tags']),
			'type_list' => $data['prod_tags']
		);
		$query = $this->db->where('prod_id', $data['prod_id'])->update('ts_products', $update);
		if ($query == true) {
			return array(
				'status' => 201,
				'message' => 'Product has been updated successfully',
				'last_insert_row' => $data['prod_id']
			);
		} else {
			return array(
				'status' => 404,
				'message' => 'Error updating your products'
			);
		}
	}

	public function delete_product($id, $email)
	{
		$query = $this->db->where('prod_id', $id)->where('merchant_email', $email)->delete('ts_products');
		if ($query == true) {
			return array(
				'status' => 200,
				'message' => 'product item deleted successfully'
			);
		} else {
			return array(
				'status' => 404,
				'message' => 'error deleting product details'
			);
		}
	}

	public function delete_pastor($id)
	{
		$query = $this->db->where('id', $id)->delete('pastors_listing');
		if ($query == true) {
			return array(
				'status' => 200,
				'message' => 'pastor data deleted successfully'
			);
		} else {
			return array(
				'status' => 404,
				'message' => 'error deleting pastor data'
			);
		}
	}

	public function delete_member($id)
	{
		$query = $this->db->where('id', $id)->delete('mfw_church_membership');
		if ($query == true) {
			return array(
				'status' => 200,
				'message' => 'Member data deleted successfully'
			);
		} else {
			return array(
				'status' => 404,
				'message' => 'error deleting Member data'
			);
		}
	}

	// we will be using email to search

	public function count_free_products($email)
	{
		$this->db->select('*')->from('ts_products')->where('merchant_email', $email)->where('prod_price', 0);
		$q = $this->db->get();
		return $q->num_rows();
	}

	public function total_members($id)
	{
		$this->db->select('*')->from('mfw_church_membership')->where('church_id', $id);
		$q = $this->db->get();
		return $q->num_rows();
	}


	public function total_likes($id)
	{
		$this->db->select_sum('likes_count')->from('merchant_feed')->where('churchid', $id);
		$q = $this->db->get();
		return $q->row()->likes_count;
	}

	public function total_comments($id)
	{
		$this->db->select_sum('comments_counts')->from('merchant_feed')->where('churchid', $id);
		$q = $this->db->get();
		return $q->row()->comments_counts;
	}


	// using email to count qyert

	public function count_premium_products($email)
	{
		$this->db->select('*')->from('ts_products')->where('merchant_email', $email)->where('prod_price !=', 0);
		$q = $this->db->get();
		return $q->num_rows();
	}

	// so this counts a total number of product views

	public function count_product_views($query)
	{ //this is by email
		$this->db->select('*')->from('product_view')->where('merchantemail', $query);
		$q = $this->db->get();
		return $q->num_rows();
	}

	// merchant feed

	public function insert_feed_data($data)
	{
		$query = $query = $this->db->insert('merchant_feed', $data);
		$insert_id = $this->db->insert_id();
		if ($query != true) {
			return array(
				'status' => 404,
				'message' => 'Error creating your merchant feed'
			);
		}else{
			return array(
				'status' => 200,
				'message' => 'merchant feed created successfully',
				'last_insert_id' => $insert_id
			);
		}

		
	}

	// Insertion of Pastors listing to DB
	public function insert_pastors_bio_data($data)
	{
		$query = $query = $this->db->insert('pastors_listing', $data);
		$insert_id = $this->db->insert_id();
		if ($query != true) {
			return array(
				'status' => 404,
				'message' => 'Error creating pastor bio data'
			);
		}else{
			return array(
				'status' => 200,
				'message' => 'Pastor bio data added successfully',
				'last_insert_id' => $insert_id
			);
		}
		
	}


	public function update_pastor($id, $data, $img)
	{
		if ($img == "") {
			$updateData = array(
				'pastors_title' => $data['pastors_title'],
				'name' => $data['pastors_name'],
				'bio' => $data['pastors_bio']

				// 'image'             => $img,
			);
		} else {
			$updateData = array(
				'pastors_title' => $data['pastors_title'],
				'name' => $data['pastors_name'],
				'pastors_bio' => $data['pastors_bio'],
				'photo' => $img,

			);
		}

		$query = $this->db->where('id', $id)->update('pastors_listing', $updateData);
		if ($query == false) {
			return array(
				'status' => 404,
				'message' => 'Error updating updating pastor data'
			);
		}

		return array(
			'status' => 201,
			'message' => 'pastor data updated successfully'
		);
	}

	public function update_churchmember($id, $data, $img)
	{
		if ($img == "") {
			$updateData = array(
				'first_name' => $data['first_name'],
				'last_name' => $data['last_name'],
				'mobile_number' => $data['mobile_number'],
				'date_of_birth' => $data['date_of_birth'],
				'gender' => $data['gender'],
				'nationality' => $data['nationality'],
				'address' => $data['address'],
				'marital_status' => $data['marital_status'],
				'email' => $data['email'],
				'member_group' => $data['group']
				// 'member_group'=>$data['group_name']

			
				

				


				// 'image'             => $img,
			);
		} else {
			$updateData = array(
				'first_name' => $data['first_name'],
				'last_name' => $data['last_name'],
				'mobile_number' => $data['mobile_number'],
				'date_of_birth' => $data['date_of_birth'],
				'gender' => $data['gender'],
				'nationality' => $data['nationality'],
				'address' => $data['address'],
				'marital_status' => $data['marital_status'],
				'email' => $data['email'],
				// 'member_group'=>$data['group_name'],
				'member_photo' => $img,
				'member_group' => $data['group']

			);
		}

		$query = $this->db->where('id', $id)->update('mfw_church_membership', $updateData);
		if ($query == false) {
			return array(
				'status' => 404,
				'message' => 'Error updating updating member data'
			);
		}

		return array(
			'status' => 201,
			'message' => 'member data updated successfully'
		);
	}


	public function edit_pastors($id)
	{
		return $query = $this->db->select('*')->from('pastors_listing')->where('id', $id)->get()->row();
	}

	public function insert_feed_image($data)
	{
		$image = array(
			'image' => $data['file_name']
		);
		return $this->db->where('id', $id)->update('merchant_feed', $image);
	}

	public function count_merchant_feed($email)
	{

		// return $this->db->where('merchantemail', $email)->count_all("merchant_feed");

		$this->db->select('*')->from('merchant_feed')->where('merchantemail', $email);
		$q = $this->db->get();
		return $q->num_rows();
	}

	public function get_merchant_feed_data($email)
	{

		// $this->db->limit($limit, $start);

		$query = $this->db->where('merchantemail', $email)->order_by('id', 'desc')->get("merchant_feed");
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$data[] = $row;
			}

			return $data;
		}

		return false;
	}

	public function get_merchant_feed_no_pagination($email, $id)
	{
		$query['results'] = $this->db->select('*')->from('merchant_feed')->where('merchantemail', $email)->order_by('id', 'desc')->get()->result();
		$query['assoc_likes'] = $this->count_merchant_likes($id);
		$query['assoc_comments'] = $this->count_merchant_comments($id);
		return $query;
	}

	// count the number of likes in the comment thread

	public function count_merchant_likes($id)
	{

		// return $this->db->where('merchant_feed_id', $id)->where('likes', 1)->count_all('merchant_comment_thread');

		$this->db->select('*')->from('merchant_comment_thread')->where('merchant_feed_id', $id)->where('likes', 1);
		$q = $this->db->get();
		return $q->num_rows();
	}

	// count the number of comments in the comment thread

	public function count_merchant_comments($id)
	{

		// return $this->db->where('merchant_feed_id', $id)->count_all('merchant_comment_thread');

		$this->db->select('*')->from('merchant_comment_thread')->where('merchant_feed_id', $id);
		$q = $this->db->get();
		return $q->num_rows();
	}

	// fetch all merchant comments data associated to merchant feed-->git

	public function fetch_merchant_comments($id)
	{
		return $query = $this->db->select('*')->from('merchant_comment_thread')->where('merchant_feed_id', $id)->get()->result();
	}

	public function get_merchant_feed_id($id)
	{
		$q = $this->db->select()->from('merchant_feed')->where('id', $id)->limit(1)->get()->row();
		if ($q == "") {
			return array(
				'status' => 404,
				'message' => 'feed row data does not exit'
			);
		}

		return array(
			'status' => 200,
			'results' => $q
		);
	}

	public function update_merchant_feed($id, $data, $email, $img)
	{
		if ($img == "") {
			$updateData = array(
				'category' => $data['news_cat'],
				'title' => $data['feed_title'],
				'message' => $data['feed_message'],

				// 'image'             => $img,

				'merchantemail' => $data['merchantemail'],
				'timestamp' => date('Y-m-d H:i:s')
			);
		} else {
			$updateData = array(
				'category' => $data['news_cat'],
				'title' => $data['feed_title'],
				'message' => $data['feed_message'],
				'image' => $img,
				'merchantemail' => $data['merchantemail'],
				'timestamp' => date('Y-m-d H:i:s')
			);
		}

		$query = $this->db->where('id', $id)->where('merchantemail', $email)->update('merchant_feed', $updateData);
		if ($query == false) {
			return array(
				'status' => 404,
				'message' => 'Error updating merchant post'
			);
		}

		return array(
			'status' => 201,
			'message' => 'Merchant feed post updated successfully'
		);
	}

	public function delete_merchant_feed($id)
	{
		$query = $this->db->where('id', $id)->delete('merchant_feed');
		if ($query == false) {
			return array(
				'status' => 404,
				'message' => 'error deleting merchant feed'
			);
		}

		return array(
			'status' => 200,
			'message' => 'feed post deleted successfully'
		);
	}

	public function search_merchant_feed($search_term, $email)
	{
		$this->db->select('*');
		$this->db->from('merchant_feed');
		$this->db->like('title', $search_term);
		$this->db->where('merchantemail', $email);
		$this->db->order_by('id', 'desc');
		$query = $this->db->get();
		$res = $query->result(); //so basically we are going to return an array of the results
		if (count($res) > 0) {
			return $res;
		} else {
			return array(
				'status' => 404,
				'message' => 'No news post feed with that title'
			);
		}
	}

	// public function search feed data from user

	public function search_all_feed($search_term)
	{
		$this->db->select('*');
		$this->db->from('merchant_feed');
		$this->db->like('title', $search_term);
		$this->db->order_by('id', 'desc');
		$query = $this->db->get();
		$res = $query->result(); //so basically we are going to return an array of the results
		if (count($res) > 0) {
			return $res;
		} else {
			return array(
				'status' => 404,
				'message' => 'No news post feed with that title'
			);
		}
	}

	// public function search_all_author(){
	//
	// }

	public function update_merchant_profile($updateData, $img)
	{
		$result = $this->check_password($updateData['password']);
		if ($result == false) {
			$data = array(
				'first_name' => $updateData['first_name'],
				'last_name' => $updateData['last_name'],
				'email' => $updateData['email'],
				'mobile' => $updateData['mobile'],

				// 'password'        => $updateData['password'],//hashing password

				'organisation' => $updateData['organisation'],
				'merchant_name' => $updateData['merchant_name'],
				'address' => $updateData['org_address'],
				'country' => $updateData['org_country'],
				'org_info' => $updateData['organisation_info'],
				 'display_image' => $img
			);
		} else {
			$data = array(
				'first_name' => $updateData['first_name'],
				'last_name' => $updateData['last_name'],
				'email' => $updateData['email'],
				'mobile' => $updateData['mobile'],
				'password' => $result, //hashing password
				'organisation' => $updateData['organisation'],
				'merchant_name' => $updateData['merchant_name'],
				'address' => $updateData['org_address'],
				'country' => $updateData['org_country'],
				'org_info' => $updateData['organisation_info'],
				'display_image' => $img
			);
		}

		$query = $this->db->where('id', $updateData['id'])->update('ts_merchant', $data);
		if ($query == true) {
			return array(
				'status' => 201,
				'message' => 'Merchant profile updated successfully'
			);
		}

		return array(
			'status' => 404,
			'message' => 'Error updating merchant profile'
		);
	}

	public function check_password($str)
	{
		if ($str != "********") { //chars 8
			return hash('sha256', $str);
		}

		return false;
	}

	function photo_check($id)
	{
		$query = $this->db->select('display_image')->where('id', $id)->from('ts_merchant')->get()->row();
		if ($query == "") {
			return $img = 'http://www.top-madagascar.com/assets/images/admin/user-admin.png';
		} else {
			return $query->display_image;
		}
	}

	function generate_short_code_($x)
	{
		return $randomNum = substr(str_shuffle("0123456789"), 0, $x);
	}

	function confirm_merchant_momo()
	{
	}

	function save_momo_code($data_)
	{
		$data = array(
			'merchant_id' => $data_['merchant_id'],
			'network' => $data_['network'],
			'mobile' => $data_['mobile'],
			'code' => $this->generate_short_code_(4)
		);
		$q = $this->avoid_momo_duplicates($data_['merchant_id']);
		if ($q == false) {
			$query = $this->db->insert('merchant_momo', $data);
			if ($query == true) {
				$q = $this->send_message_($data['mobile'], $this->merchant_momo_message_content($data['code']));
				return array(
					'status' => 201,
					'message' => 'Merchant momo account created',
					'smsStatus' => $q
				);
			}

			return array(
				'status' => 204,
				'message' => 'Error adding merchant momo number'
			);
		}

		$data = array(

			// 'merchant_id' => $data_['merchant_id'],

			'network' => $data_['network'],
			'mobile' => $data_['mobile'],
			'code' => $this->generate_short_code_(4)
		);
		$a = $this->db->where('merchant_id', $data_['merchant_id'])->update('merchant_momo', $data);
		if ($a == true) {
			$q = $this->send_message_($data['mobile'], $this->merchant_momo_message_content($data['code']));
			return array(
				'status' => 201,
				'message' => 'Merchant account updated successfully',
				'smsStatus' => $q
			);
		}

		return array(
			'status' => 204,
			'message' => 'Error updating merchant momo number'
		);
	}

	function avoid_momo_duplicates($id)
	{
		$query = $this->db->select()->from('merchant_momo')->where('merchant_id', $id)->limit(1)->get()->row();
		if ($query == "") {
			return false;
		}

		return true;
	}

	function merchant_momo_message_content($pin)
	{
		return "Merchant mobile money confirmation code: " . $pin;
	}

	function send_message_($phone, $message)
	{
		$url = "https://api.hubtel.com/v1/messages/send?" . "From=MyFreshWord"

		// dynamic

		. "&To=$phone" //dynamic
		. "&Content=" . urlencode("$message") //dynamic
		. "&ClientId=dgsfkiil" //dynamic
		. "&ClientSecret=czywtkzd" //dynamic
		. "&RegisteredDelivery=true";
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => "GET",
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		if ($err) {
			return "cURL Error #:" . json_decode($err);
		} else {
			return json_decode($response);
		}
	}
} //end of class
