<?php defined('BASEPATH') OR exit('No direct script access allowed');

Class NotificationModel extends CI_Model{

   protected $true  = 1;
   protected $false = 0;
   protected $notifyTable     = "merchant_notification";
   protected $emailNotify     = "emailNotify";
   protected $appNotify       = "appNotify";
   protected $pushNotify      = "pushNotify";
   protected $purchaseNotify  = "purchaseNotify";
   protected $commentNotify   = "commentNotify";


   function __construct(){
     parent:: __construct();
   }


   //node mailer to send send emails
   function sendEmail(){

   }

   function sendSMS(){

   }

   function pushNotification(){

   }


   //if this is empty set all values to
   function getNotificationStatus($id){
     $q = $this->db->select('emailNotify, appNotify, pushNotify, purchaseNotify, commentNotify')->from($this->notifyTable)->where('merchant_id', $id)->limit(1)->get()->row();
     if($q == ""){
       return false;
     }
     return array('status'=>true, 'res'=>$q);
   }

   function setNotificationEmail($id, $status){
     $a = $this->getNotificationStatus($id);
     if($a == false){
       $data = array($this->emailNotify=>$status);
       $q = $this->db->insert($this->notifyTable, $data);
       if($q == true){
         return array('status'=>201, 'message'=> 'Email notification enabled');
        }
        return array('status'=>404, 'message'=> 'Error enabling email notification');
     }
     $data = array($this->emailNotify=>$status);
     $c = $this->db->where('merchant_id', $id)->update($this->notifyTable,$data);
     if($c == false){
       return array('status'=>404, 'message'=>'Error enabling  email notification');
     }
     return array('status'=>201, 'message'=> 'Email Notificaton successfully notified');
   }


   function setNotificationApp($id, $status){
     $a = $this->getNotificationStatus($id);
     if($a == false){
       $data = array($this->appNotify=>$status);
       $q = $this->db->insert($this->notifyTable, $data);
       if($q == true){
         return array('status'=>201, 'message'=> 'myFreshWord notification enabled');
        }
        return array('status'=>404, 'message'=> 'Error enabling myFreshWord notification');
     }
     $data = array($this->appNotify=>$status);
     $c = $this->db->where('merchant_id', $id)->update($this->notifyTable,$data);
     if($c == false){
       return array('status'=>404, 'message'=>'Error enabling  myFreshWord notification');
     }
     return array('status'=>201, 'message'=> 'myFreshWord Notificaton successfully notified');
   }

   function setPushNotification($id, $status){
     $a = $this->getNotificationStatus($id);
     if($a == false){
       $data = array($this->pushNotify=>$status);
       $q = $this->db->insert($this->notifyTable, $data);
       if($q == true){
         return array('status'=>201, 'message'=> 'Push notification enabled');
        }
        return array('status'=>404, 'message'=> 'Error enabling push notification');
     }
     $data = array($this->pushNotify=>$status);
     $c = $this->db->where('merchant_id', $id)->update($this->notifyTable,$data);
     if($c == false){
       return array('status'=>404, 'message'=>'Error enabling  push notification');
     }
     return array('status'=>201, 'message'=> 'Push Notificaton successfully notified');
   }

   function setPurchaseNotification($id, $status){
     $a = $this->getNotificationStatus($id);
     if($a == false){
       $data = array($this->purchaseNotify=>$status);
       $q = $this->db->insert($this->notifyTable, $data);
       if($q == true){
         return array('status'=>201, 'message'=> 'Purchase notification enabled');
        }
        return array('status'=>404, 'message'=> 'Error enabling purchase notification');
     }
     $data = array($this->purchaseNotify=>$status);
     $c = $this->db->where('merchant_id', $id)->update($this->notifyTable,$data);
     if($c == false){
       return array('status'=>404, 'message'=>'Error enabling  purchase notification');
     }
     return array('status'=>201, 'message'=> 'Purchase Notificaton successfully notified');
   }

   function setCommentNotification($id, $status){
     $a = $this->getNotificationStatus($id);
     if($a == false){
       $data = array($this->commentNotify=>$status);
       $q = $this->db->insert($this->notifyTable, $data);
       if($q == true){
         return array('status'=>201, 'message'=> 'Comment notification enabled');
        }
        return array('status'=>404, 'message'=> 'Error enabling comment notification');
     }
     $data = array($this->commentNotify=>$status);
     $c = $this->db->where('merchant_id', $id)->update($this->notifyTable,$data);
     if($c == false){
       return array('status'=>404, 'message'=>'Error enabling  comment notification');
     }
     return array('status'=>201, 'message'=> 'Comment Notificaton successfully notified');
   }

}
