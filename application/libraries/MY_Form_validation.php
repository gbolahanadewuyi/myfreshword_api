<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Form_validation extends CI_Form_validation {
     protected $CI;
     function __construct() {
        parent::__construct();
                // reference to the CodeIgniter super object
         $this->CI =& get_instance();
    }
       function count_array_check($str = array()) {
         $this->CI->form_validation->set_message('count_array_check', 'Minimum of 3 %s  selections required');

         if(sizeof($str) >= 3) { // do your validations
                return TRUE;
          } else {
              return FALSE;
          }
       }

     function is_unique2($str, $field){
        list($table, $field, $exclude_field, $exclude_value)=explode('.', $field);

        return (bool) $this->CI->db
            ->where($field, $str)
            ->where($exclude_field.' !=', $exclude_value)
            ->limit(1)
            ->get($table)->num_rows();
    }
}
