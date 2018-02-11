<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MyModel extends CI_Model {

  var $client_service = "frontend-client";
  var $auth_key       = "myfreshword";

  var $client_id = 'ihounyms';
  var $client_secret='icbvukgq';

  public function __construct(){
    parent:: __construct();

  }


  public function check_auth_client(){
        $client_service = $this->input->get_request_header('Client-Service', TRUE);
        $auth_key  = $this->input->get_request_header('Auth-Key', TRUE);
        if($client_service == $this->client_service && $auth_key == $this->auth_key){
            return true;
        } else {
            //return json_output(401,array('status' => 401,'message' => 'Unauthorized.'));
            //instead return the rest keyword
            return array('status' => 401,'message' => 'Unauthorized.');
        }
    }


    public function login($username,$password)
    {
        $q  = $this->db->select('user_pwd,user_id,user_status,user_mobile')->from('ts_user')->where('user_uname',$username)->get()->row();
        if($q == ""){
            return array('status' => 204,'message' => 'Username not found.');
        }else if($q->user_status == 2){//means account needs activation
            return array('status' => 204,'message' => 'Your account is inactive.', 'mobile'=>$q->user_mobile);
        }
        else {

            $hashed_password = $q->user_pwd;
            $id              = $q->user_id;

            if ($hashed_password == md5($password)) {

               $last_login = date('Y-m-d H:i:s');
               $token_set = substr( md5(rand()), 0, 7);
               $token = hash('sha256', $token_set);
               $expired_at = date("Y-m-d H:i:s", strtotime('+12 hours'));
               $this->db->trans_start();
               $this->db->where('user_id',$id)->update('ts_user',array('last_login' => $last_login));
               $this->db->insert('users_authentication',array('users_id' => $id,'token' => $token,'expired_at' => $expired_at));
               if ($this->db->trans_status() === FALSE){
                  $this->db->trans_rollback();
                  return array('status' => 500,'message' => 'Internal server error.');
               } else {
                  $this->db->trans_commit();
                  return array('status' => 200,'message' => 'Successfully login.','id' => $id, 'token' => $token);
               }

            } else {
               return array('status' => 204,'message' => 'Wrong password.');
            }
        }
    }

    public function logout()
    {
        $users_id  = $this->input->get_request_header('User-ID', TRUE);
        $token     = $this->input->get_request_header('Authorization', TRUE);
        $this->db->where('users_id',$users_id)->where('token',$token)->delete('users_authentication');
        return array('status' => 200,'message' => 'Successfully logout.');
    }



    //this is to check the connection with the api key once the user  has successfully logged in
    public function auth($users_id, $token)
    {
        // $users_id  = $this->input->get_request_header('User-ID', TRUE);
        // $token     = $this->input->get_request_header('Authorization', TRUE);

        // $users_id =  $this->get('userid', TRUE);
        // $token    =  $this->get('token', TRUE);

        $q  = $this->db->select('expired_at')->from('users_authentication')->where('users_id',$users_id)->where('token',$token)->get()->row();
        if($q == ""){
            return array('status' => 401,'message' => 'Unauthorized.');
        } else {
            if($q->expired_at < date('Y-m-d H:i:s')){
                return array('status' => 401,'message' => 'Your session has been expired.');
            } else {
                $updated_at = date('Y-m-d H:i:s');
                $expired_at = date("Y-m-d H:i:s", strtotime('+12 hours'));
                $this->db->where('users_id',$users_id)->where('token',$token)->update('users_authentication',array('expired_at' => $expired_at,'updated_at' => $updated_at));
                return array('status' => 200,'message' => 'Authorized.', 'id'=>$users_id);
            }
        }
    }


    public function get_all_preachers(){
      // $query = $this->check_auth_client();
      // if($query === true){
      //
      // }else{
      //   return $query;
      // }

        return $this->db->select('preacher_name')->from('ts_preachers')->order_by('id','desc')->get()->result();
    }

    public function audio_all_data()
    {
      return $this->db->select()->from('ts_products')->order_by('prod_id','desc')->get()->result();
    }

    public function book_all_data()
    {
        return $this->db->select('id,title,author')->from('books')->order_by('id','desc')->get()->result();
    }

    public function book_detail_data($id)
    {
        return $this->db->select('id,title,author')->from('books')->where('id',$id)->order_by('id','desc')->get()->row();
    }

    public function create_user($data){
      $q = $this->db->insert('ts_user',$data);
      if($q == true){
        return array('status' => 201,'message' => 'User has been created.');
      }else{
        return array('status' => 204,'message' => 'User could not be created.');
      }
    }

    public function book_create_data($data)
    {
        $q = $this->db->insert('books',$data);
        return array('status' => 201,'message' => 'Data has been created.');
    }

    public function book_update_data($id,$data)
    {
        $this->db->where('id',$id)->update('books',$data);
        return array('status' => 200,'message' => 'Data has been updated.');
    }

    public function book_delete_data($id)
    {
        $this->db->where('id',$id)->delete('books');
        return array('status' => 200,'message' => 'Data has been deleted.');
    }

    public function send_data_mail($data=array()){
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
      //$this->email->cc('another@another-example.com');
      //$this->email->bcc('them@their-example.com');
      $this->email->subject($data['subject']);
      $this->email->message($data['message']);
      $this->email->send();
      $this->email->print_debugger();

    }

    public function forgot_password_email($data){
      $q  = $this->db->select()->from('ts_user')->where('user_email',$data)->get()->row();
      if($q == ""){
          return array('status' => 204,'message' => 'Email not found.');
      }
      else{
          $uid    = $q->user_id;
          $mobile = $q->user_mobile;
          $key = md5(date('Ymdhis').$uid);
          $data = array(
            'status' =>200,
            'id' => $uid,
            'key'=> $key,
            'link'=>$mobile
          );
          return  $data;
      }
    }

    public function update_key($id,$data){
      $this->db->where('user_id',$id)->update('ts_user',$data);
      return array('status' => 200,'message' => 'Sending reset code to mobile.');
    }

    public function update_key_mobile($mobile,$data){
      $this->db->where('user_mobile',$mobile)->update('ts_user',$data);
      return array('status' => 200,'message' => 'Reset code sent.');
    }

    public function church_all_data(){
        return $this->db->select('church_name')->from('ts_church')->order_by('id','desc')->get()->result();
    }


    public function generate_short_code($digits = 4){
         $i = 0; //counter
         $pin = ""; //our default pin is blank.
         while($i < $digits){
             //generate a random number between 0 and 9.
             $pin .= mt_rand(0, 9);
             $i++;
         }
         return $pin;
    }

    public function send_code($phone,$pin){
        //$pin = $this->generate_short_code();
        $url = "http://api.mytxtbox.com/v3/messages/send?".
                "From=freshword"
                ."&To=$phone"
                ."&Content=".urlencode("$pin")
                ."&ClientId=ihounyms"
                ."&ClientSecret=icbvukgq"
                ."&RegisteredDelivery=true";
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
                //echo "cURL Error #:" . $err;
            } else {
                //echo $response;
            }
    }

    public function activate_account($auth_code){
      $q  = $this->db->select('user_id,user_status')->from('ts_user')->where('user_activation_code',$auth_code)->get()->row();
      if($q == ""){
       return array('status' => 204,'message' => 'Invalid activation pin.');
     }
     else if($q->user_status == 1){
       return array('status' => 200,'message' => 'Account already active..');
     }
      else{
        $data = array(
          'user_status'=>1
        );
        $this->db->where('user_id',$q->user_id)->update('ts_user',$data);
        return array('status' => 200,'message' => 'Account activated successfully.');
      }
    }

    public function reset_password_code($auth_code){
      $q  = $this->db->select()->from('ts_user')->where('user_reset_code',$auth_code)->get()->row();
      if($q == ""){
       return array('status' => 204,'message' => 'Invalid reset pin.');
      }
      else if($q->user_reset_code == $auth_code){
        return array('status' => 200,'message' => 'Passed','email'=>$q->user_email);
      }
    }

    public function update_password($email,$data){
      $q = $this->db->where('user_email',$email)->update('ts_user',$data);
      return array('status' => 200,'message' => 'Password updated successfully', 'email'=>$email, 'pass'=>$data, 'query_res'=>$q);
    }

    public function feed_data($data){
      $q = $this->db->insert('ts_feed',$data);
      if($q === true){
        return array('status' => 201,'message' => 'Data has been created.', 'query'=>$q);
      }else{
        return array('status' => 204,'message' => 'Error inserting data.', 'query'=>$q);
      }
    }

    private function generate_auth_key($email){
      $q  = $this->db->select()->from('ts_user')->where('user_email',$email)->get()->row();
      if($q != ""){
        $last_login = date('Y-m-d H:i:s');
        $token_set = substr( md5(rand()), 0, 7);
        $token = hash('sha256', $token_set);
        $expired_at = date("Y-m-d H:i:s", strtotime('+12 hours'));
        $this->db->trans_start();
        $this->db->where('user_id',$q->user_id)->update('ts_user',array('last_login' => $q->last_login));
        $this->db->insert('users_authentication',array('users_id' => $q->user_id,'token' => $token,'expired_at' => $expired_at));
        if ($this->db->trans_status() === FALSE){
           $this->db->trans_rollback();
           return array('status' => 500,'message' => 'Internal server error.');
        } else {
           $this->db->trans_commit();
           return array('status' => 200,'message' => 'Successfully login.','id' => $q->user_id, 'token' => $token);
        }
      }
    }

    public function facebook_data($data=array()){
        $q  = $this->db->select()->from('ts_user')->where('fb_id',$data['fb_id'])->get()->row();
        if($q == ""){//user is new member
          $query = $this->db->insert('ts_user', $data);
          $access = $this->generate_auth_key($data['fb_email']);
          return array('query'=>$query, 'API_ACCESS'=>$access);
        }
        else{//user already exist //
          $query = $this->generate_auth_key($q->user_email);
          return $query;
        }
    }


    public function google_data($data = array()){
      $q  = $this->db->select()->from('ts_user')->where('g_user_id',$data['g_user_id'])->get()->row();
      if($q == ""){//user is new member
        $query = $this->db->insert('ts_user', $data);
        $access = $this->generate_auth_key($data['g_email']);
        return array('query'=>$query, 'API_ACCESS'=>$access);
      }
      else{//user already exist //
        $query = $this->generate_auth_key($q->user_email);
        return $query;
      }
    }

    public function user_profile_data($id){
      $q  = $this->db->select()->from('ts_user')->where('user_id',$id)->get()->row();
      if($q == ""){
        return array('status' => 404,'message' => 'Unauthorized access','query'=> $q);
      }
      else{
        return array('status'=> 200, 'message'=>'Profile data fetched', 'query'=>$q);
      }
    }

    public function product_id($id){
      return $this->db->select()->from('ts_products')->where('prod_id',$id)->get()->row();
    }

    public function phone_momo($mobile){
      // set API Access Key
    $access_key = 'a41000a45a900d1ba598be3e977fc387';

    // set phone number
    $phone_number = $mobile;

    // Initialize CURL:
    $ch = curl_init('http://apilayer.net/api/validate?access_key='.$access_key.'&number='.$phone_number.'');
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




    public function new_momo($mobile){
      // set API Access Key
    $access_key = 'a41000a45a900d1ba598be3e977fc387';

    // set phone number
    $phone_number = $mobile;

    // Initialize CURL:
    $ch = curl_init('http://apilayer.net/api/validate?access_key='.$access_key.'&number='.$phone_number.'');
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

    public function bin_checker($bin){
      // These code snippets use an open-source library. http://unirest.io/php
      $response = Unirest\Request::post("https://neutrinoapi-bin-lookup.p.mashape.com/bin-lookup",
        array(
          "X-Mashape-Key" => "wrHFEnNcydmsh5iQylcOaqKzWlEXp1zthIzjsn2Zbh1HPddeNR",
          "Content-Type" => "application/x-www-form-urlencoded",
          "Accept" => "application/json"
        ),
        array(
          "bin-number" => $bin,
          "customer-ip" => "60.234.81.148"
        )
      );

      return $response;
    }
}
