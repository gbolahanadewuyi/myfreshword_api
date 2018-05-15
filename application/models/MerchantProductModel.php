<?php defined('BASEPATH') OR exit('No direct script access allowed');

Class MerchantProductModel extends CI_Model {

  var $table = 'ts_products';
  var $column_order = array('prod_name','prod_urlname','prod_preacher', 'prod_church', 'prod_image', 'img_link', 'prod_tags', 'prod_description',
                            'prod_essay', 'prod_demourl','prod_demoshow','prod_cateid', 'prod_subcateid', 'prod_filename', 'prod_price', 'prod_plan',
                            'prod_free', 'prod_featured','prod_status','prod_uniqid','prod_date','prod_update', 'prod_download_count', 'prod_gallery',
                            'prod_uid','prod_type', 'type_list','file_link','merchant_email', 'currency',null); //set column field database for datatable orderable

  var $column_search = array('prod_name','prod_preacher','prod_church', 'prod_tags', 'prod_price', 'prod_uniqid', 'prod_date', 'prod_download_count'); //set column field database for datatable searchable just firstname , lastname , address are searchable
  var $order = array('prod_id' => 'desc'); // default order



  public function __construct(){
    parent:: __construct();
  }

  private function _get_datatables_query()
    {

        $this->db->from($this->table);

        $i = 0;

        foreach ($this->column_search as $item) // loop column
        {
            if($_POST['search']['value']) // if datatable send POST for search
            {

                if($i===0) // first loop
                {
                    $this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
                    $this->db->like($item, $_POST['search']['value']);
                }
                else
                {
                    $this->db->or_like($item, $_POST['search']['value']);
                }

                if(count($this->column_search) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
            }
            $i++;
        }

        if(isset($_POST['order'])) // here order processing
        {
            $this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        }
        else if(isset($this->order))
        {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function get_datatables($user_email){
      $this->_get_datatables_query();
      if($_POST['length'] != -1)
      $this->db->limit($_POST['length'], $_POST['start']);
    	$this->db->where('merchant_email', $user_email  );
      $query = $this->db->get();
      return $query->result();
    }

    function count_filtered($user_email){
        $this->_get_datatables_query();
        $this->db->where('merchant_email', $user_email );
        $query = $this->db->get();
        return $query->num_rows();
    }

    function count_all($user_email){
    	$this->db->where('merchant_email', $user_email  );
      $this->db->from($this->table);
      return $this->db->count_all_results();
    }
}
