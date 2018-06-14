<?php  defined('BASEPATH') OR exit('No direct script access allowed');

 Class ThemeModel extends CI_Model{
   protected $themeTable = "product_themes";

   function __construct(){
     parent:: __construct();
   }

   function get_themes_data(){
     $q = $this->db->select('*')->from($this->themeTable)->get()->result();
     if($q == true){
       return array('status'=>200, 'result'=>$q);
     }
     return array('status'=>>204, 'message'=> 'No Content found');
   }
 }
