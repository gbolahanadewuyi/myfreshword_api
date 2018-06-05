<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

Class HubtelApi {

  
  protected $CI;
  protected $client_id = "dgsfkiil";
  protected $client_secret = "czywtkzd";
  function __construct(){

      $this->CI =& get_instance();
      $this->CI->load->helper('url');
      $this->CI->config->item('base_url');
      $this->CI->load->database();

  }


  /**
 * Generate 4 digit shortcode
 * this will be used on multiple occasionss
 */
  function generate_short_code($x){
    return $randomNum = substr(str_shuffle("0123456789"), 0, $x);
  }

  function confirm_merchant_momo(){

  }

 function save_momo_code($data_){
    $data = array(
      'merchant_id' => $data_['merchant_id'],
      'network'     => $data_['network'],
      'mobile'      => $data_['mobile'],
      'code'        => $this->generate_short_code(4)
    );
    $query = $this->CI->db->insert('merchant_momo', $data);
    if($query == true){
      $this->send_message($data['mobile'], $this->merchant_momo_message_content($data['code']));
      return array('status'=>201, 'message'=> 'Merchant momo account created');
    }
    return array('status'=>204, 'message'=> 'Error adding merchant momo number');
  }



  function avoid_momo_duplicates($id){
    $query = $this->CI->db->select()->from('merchant_momo')->where('merchant_id',$id)->limit(1)->get()->row();
    if($query == ""){
      return false;
    }
    return true;
  }


  function merchant_momo_message_content($pin){
    return "Mobile money confirmation code: " .$pin;
  }


  function send_message($phone, $message){

    $url = "http://api.mytxtbox.com/v3/messages/send?".
            "From=myFreshWord"//dynamic
            ."&To=$phone"//dynamic
            ."&Content=".urlencode("$message")//dynamic
            ."&ClientId=dgsfkiil"//dynamic
            ."&ClientSecret=czywtkzd"//dynamic
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
            return "cURL Error #:" . $err;
        } else {
           return $response;
      }
  }

}//end of class
