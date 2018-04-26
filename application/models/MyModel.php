<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MyModel extends CI_Model {

  // var $client_service = "frontend-client";
  // var $auth_key       = "myfreshword";

  var $client_id = 'ihounyms';
  var $client_secret='icbvukgq';

  public function __construct(){
    parent:: __construct();

  }


  public function check_auth_client(){
        //this is where i am returning the header  user id and authentication id as well
        $client_service = $this->input->get_request_header('User-ID', TRUE);
        $auth_key  = $this->input->get_request_header('Authorization', TRUE);
        return array('user-id'=>$client_service, 'auth'=>$auth_key);
  }


  public function login($username,$password){
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


    public function header_auth(){
      $users_id  = $this->input->get_request_header('User-ID', TRUE);
      $token     = $this->input->get_request_header('Authorization', TRUE);

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
                ."&ClientId=dgsfkiil"
                ."&ClientSecret=czywtkzd"
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
    $access_key = 'a88d6294fe6ae7ded65c1f7fc7911c8e';

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

    public function insert_momo($data){
      $query = $this->db->insert('momo',$data);
      if($query === true){
        return array('status'=> 200, 'message'=>'Momo saved', 'query'=>$query);
      }
      else{
        return array('status'=> 400, 'message'=>'Error saving momo', 'query'=>$query);
      }
    }

    public function check_momo_exist($data = array()){
        $mobile = $data['number'];
        $q  = $this->db->select()->from('momo')->where('number',$mobile)->get()->row();
        if($q->number != $mobile){//if there are not duplicates
          return true;
        }else {// if there are duplicates
          return array('status'=>404 , 'message'=>'Mobile Money Number already used on another account');
        }
    }


    public function momo_by_id($email){
      $q  = $this->db->select()->from('momo')->where('unique_acc',$email)->get()->row();
      if($q->unique_acc == " "){//if email doesnt exist
        return false;
      }else{
        return $q;
      }
    }

    public function bin_checker($bin){
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.freebinchecker.com/bin/".$bin,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache"
            //"postman-token: 8f957757-094d-6903-f2e2-f0dbb9d1ee06"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
          echo "cURL Error #:" . $err;
        } else {
          return  $responseResult = json_decode($response, true);
        }
    }

    //when a user is adding new data that hasnt been bought
    public function addToCart($data = array()){

        $checkLib = $this->check_if_item_is_purchased($data);
        if($checkLib['success'] ==  true){
          $query = $this->db->select()->from('ts_cart')->where('prod_uniqid',$data['prod_uniqid'])->where('prod_purchase_by',$data['prod_purchase_by'])->where('paid',0)->get()->row();
            if($query == ""){//if query didnt bring back anything
              $insertDB = $this->db->insert('ts_cart',$data);
                        if($insertDB == true){
                          return array('success'=>true, 'message'=> 'Product added successfully', 'db_query'=>$insertDB);
                        }else{
                          return array('success'=>false, 'message'=> 'Error adding product to cart', 'db_query'=>$insertDB);
                        }
            }else{
              return array(
                'success'=>false,
                'message'=> 'Product already added'
              );
            }
        }else{
          return $checkLib;
        }


    }

    public function check_if_item_is_purchased($data = array()){
      $query = $this->db->select()->from('ts_paid_prod')->where('prod_uniqid',$data['prod_uniqid'])->where('user_acc',$data['prod_purchase_by'])->get()->row();
      if($query == ""){
        //move to next function
        return array('success'=>true, 'message'=> 'Product not purchased to library yet');
      }else{
        return array(
          'success'=>false,
          'message'=> 'Product already purchased to library'
        );
      }

    }

    public function cartRowCount($data = array()){//counting for cart items that havent been paid
      $this->db->select('*')->from('ts_cart')->where('prod_purchase_by',$data['prod_purchase_by'])->where('paid',0);
      $q = $this->db->get();
      return $q->num_rows();
    }

    public function TotalCartSales($data){//total price of items in the cart not paid for yet
      $this->db->select_sum('prod_price');
      $this->db->from('ts_cart');
      $this->db->where('prod_purchase_by',$data['prod_purchase_by']);//by email
      $this->db->where('paid',0);//where product hasnt been paid yet
      $query=$this->db->get();
      if($query->row()->prod_price === NULL){
        return $data = '0';
      }
      else
      {
        return $query->row()->prod_price;

      }

    }

    public function fetch_cart_data($data){
      $query =  $this->db->select()->from('ts_cart')->where('prod_purchase_by',$data['prod_purchase_by'])->where('paid',0)->order_by('id','desc')->get()->result();
      return $query;
    }

    public function delete_cart_data($data){
      $query = $this->db->where('prod_purchase_by',$data['prod_purchase_by'])->where('paid',0)->where('id',$data['id'])->delete('ts_cart');
      if($query == true){
        return array('status' => 200,'message' => 'Data has been deleted.', 'Query'=>$query);
      }else {
        return array('status' => 400,'message' => 'Data delete error.', 'Query'=>$query);
      }
    }

    //trying to merge arrays here
    public  function library_data($email){
      $query =  $this->db->select('*')->from('ts_paid_prod')->where('user_acc',$email)->order_by('id','desc')->get()->result();
      return array_merge($query,$this->free_library_data());
    }


    public function free_library_data(){
      return $this->db->select('*')->from('ts_paid_prod')->where('user_acc','admin@techloftgh.com')->where('free',1)->order_by('id','desc')->get()->result();
    }

    public function checkout_data($data){
      return  $query = $this->db->insert('ts_paid_prod',$data );
    }

    public function payIN_endpoint($phoneNumber,$payAmount, $churchAccount){

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

    public function email_enable($data, $param){
      //enable email alerts  ===  Id from user profile details
      $this->db->where('id',$param['id'])->update('ts_user',$data);
      return array('status' => 200,'message' => 'Email Notification Enabled.');
    }

    public function sms_enable($data, $param){
      //this is saying send alerts if == Id from user profile details
      //so here we need to check first if user phone number is set
      $query = $this->mobile($data, $param);
      if($query['status'] == 400){
        return $query ;
      }else{
        $this->db->where('id',$param['id'])->update('ts_user',$data);
        return array('status' => 200,'message' => 'SMS Notification Enabled.');
      }
    }

    private function mobile_exist($data, $param){
      $query = $this->db->select('user_mobile')->from('ts_user')->where('user_mobile',$param['mobile'])->where('user_email',$param['email'])->get()->row();
      if($query->user_mobile == ""){
        return array('status'=> 400, 'message'=> 'Update profile info with mobile');
      }
      else{
        return array('status'=> 200, 'message'=> 'mobile number set');
      }
    }

    public function update_user_profile($id,$data){
      $query  = $this->db->where('id',$id)->update('ts_user',$data);
      if($query == true){
        return array('status' => 200,'message' => 'Profile data has been updated.');
      }
      else{
        return array('status' => 400,'message' => 'Error updating profile data.');
      }
    }

    public function check_db_with_rest_client($data){
      $query = $this->db->select('*')->from('ts_user')->where('user_id',$data['id'])->get()->row();
      //check values returned here with the post data being passed from the rest client
      if($query->user_uname == $data['username'] && $query->user_mobile == $data['mobile'] && $query->user_pwd == $data['password']){
        return array(
          'status'=> 401, 'message'=> 'No Profile Changes made'
        );
      }
      else{
        return array('status'=>200, 'message'=> 'Data ready to update');
      }
    }

    public function callback_response($data){
      return $q = $this->check_if_freshword_transaction_id_exist($data);
      //if exist complete payment processing with response
    }

    private function check_if_freshword_transaction_id_exist($data){
      $query = $this->db->select('*')->from('payment_response')->where('freshword_transaction_id',$data['freshword_transaction_id'])->get()->row();
      if($query->freshword_transaction_id ==  $data['freshword_transaction_id']){
          return $q = $this->complete_payment($data);
      }else {
        return array('status'=>400, 'message'=> 'Freshword transaction id is invalid');
      }
    }

    private function complete_payment($data){//this will be an update statement
      $query = $this->db->where('freshword_transaction_id',$data['freshword_transaction_id'])->update('payment_response',$data);
      if($query == true){
        return array('status'=> 200, 'message'=> 'payment process completed');
      }
      else {
        return array('status'=> 400 , 'message'=> 'There was an issue processing your payment');
      }
    }


    public function user_momo_numbers($data){//here we check the unique account using the email address of the user
      $query =  $this->db->select('*')->from('momo')->where('unique_acc',$data['email'])->order_by('id','desc')->get()->result();
      if($query == ""){
        return array('status'=>204, 'message'=> 'Add a mobile money number');
      }
      else{
        //return array('status'=>200, 'message'=> 'User has set mobile money number', 'query'=>$query);//query will return all array with email address
        return $query;
      }
    }

    public function delete_momo_number($data){
      $query = $this->db->where('id',$data['id'])->where('unique_acc',$data['email'])->delete('momo');
      if($query == true){
        return array('status'=>200,'message'=>'Mobile money number removed successfully');
      }
      else{
        return array('status'=>400, 'message'=>'Error removing mobile money number');
      }
    }

    public function set_momo_default($data){//turn all off and set one as set_momo_default
      $q = $this->user_momo_active($data);
      if($q['status'] == 200){
         //set default = 1 to set as active
         $query = $this->db->where('payin_number',$data['payin_number'])->update('momo',$data);
         if($query == true){
           return array('status'=>200, 'message'=>'Number set as default');
         }
      }
      else{
        return $q;
      }
    }

    private function user_momo_active($data){
       $query =  $this->db->select('*')->from('momo')->where('unique_acc',$data['email'])->where('set_default',1)->get()->row();
       if($query == ""){//if no number is active
        $q =   $this->db->where('payin_number',$data['payin_number'])->update('momo',$data);//here the only data to set is set_default to 1
          if($q == true){
            return array('status'=>201, 'message'=> 'Mobile money number set as default');
          }
       }
       else{
         // turn off the already set Number
         $offdata = array(
           'payin_number'=> $query->payin_number,
           'set_default'=>  0
         );
         $res = $this->db->where('payin_number',$offdata['payin_number'])->update('momo',$offdata);
         if($res == true){
           return array('status'=>200, 'message'=> 'Default number changed to zero');
         }
         else{
           return array('status'=>400, 'message'=> 'Default number could not be changed');
         }
       }
    }

      // this should generate the transaction id stored inside the database
      public  function RandomString($length = 39) {
          $randstr ="";
          srand((double) microtime(TRUE) * 1000000);
          //our array add all letters and numbers if you wish
          $chars = array(
              'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'p',
              'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '1', '2', '3', '4', '5',
              '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K',
              'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

          for ($rand = 0; $rand <= $length; $rand++) {
              $random = rand(0, count($chars) - 1);
              $randstr .= $chars[$random];
          }
          //echo $randstr;
          //echo number_format($randstr,0,"","-");
        return wordwrap($randstr, 10, '-', true);
      }

      public function trans_rotate($email){
        $q  = $this->db->select('transactionid')->from('ts_cart')->where('prod_purchase_by',$email)->get()->row();
        if($q == ""){
          return $this->RandomString();
        }
        else{
          return $q->transactionid;//if there is one it will always return this
        }

      }


      //this model is to get just the product comment title of the product
      public function get_comment_title_data($data){//now this should be associated with the comment title
        if($data['prod_id'] == "" && $data['comment_title'] == ""){
          return array('status'=>204, 'message'=> 'There is an error with the comment title');
        }
        else {
          $q =  $this->db->select('*')->from('comments')->where('prod_id',$data['prod_id'])->where('comment_title',$data['comment_title'])->get()->row();
          if($q == true ){
            return array('status'=>200, 'query'=>$q);
          }
        }
      }


      //process payment
      //dont allow users to enter when
      public function payment_to_db($data){
        $q =  $this->db->select('*')->from('payment_response')->where('freshword_transaction_id',$data['freshword_transaction_id'])->get()->row();
        if($q == ""){//here if it shows nothing
          $this->db->insert('payment_response', $data);
          return array('status'=>201, 'message'=>'New payment data inserted');
        }
        else{
          return array('status'=>202, 'message'=> 'payment data already exist');
        }
      }

      public function delete_library_data($email){
        return $this->db->where('prod_purchase_by',$email)->delete('ts_cart');
      }

      public function search_product($search_term){
        //$search_term=$this->input->post('textboxName');
        $search_term="%".$search_term."%";
        $sql="SELECT * FROM ts_products WHERE prod_name LIKE ? ";
        $query=$this->db->query($sql,array($search_term));
        $res=$query->result();//so basically we are going to return an array of the results
         if(count($res) > 0){
           return $res;
         }
         else {
           return array('status'=>400 , 'message'=> 'Sorry No Data found');
         }
      }

}
