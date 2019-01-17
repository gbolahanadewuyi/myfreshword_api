<?php defined('BASEPATH') or exit('No direct script access allowed');

class MerchantGroupModel extends CI_Model
{

	var $table = 'pastors_listing';
	var $column_order = array(
		'pastors_title', 'name', 'photo', null
	); //set column field database for datatable orderable

	var $column_search = array('pastors_title', 'name','photo'); //set column field database for datatable searchable just firstname , lastname , address are searchable
	var $order = array('id' => 'desc'); // default order
  



	public function __construct()
	{
		parent::__construct();
	}

	// private function _get_datatables_query()
	// {

	// 	$this->db->from($this->table);

	// 	$i = 0;

	// 	foreach ($this->column_search as $item) // loop column
	// 	{
	// 		if ($_POST['search']['value']) // if datatable send POST for search
	// 		{

	// 			if ($i === 0) // first loop
	// 			{
	// 				$this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
	// 				$this->db->like($item, $_POST['search']['value']);
	// 			} else {
	// 				$this->db->or_like($item, $_POST['search']['value']);
	// 			}

	// 			if (count($this->column_search) - 1 == $i) //last loop
	// 			$this->db->group_end(); //close bracket
	// 		}
	// 		$i++;
	// 	}

	// 	// if (isset($_POST['order'])) // here order processing
	// 	// {
	// 	// 	$this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
	// 	// } else if (isset($this->order)) {
	// 	// 	$order = $this->order;
	// 	// 	$this->db->order_by(key($order), $order[key($order)]);
	// 	// }
	// }

	function get_datatables($user_id)
	{
		// $this->_get_datatables_query();
		// if ($_POST['length'] != -1)
        //     $this->db->limit($_POST['length'], $_POST['start']);
          $query  =  $this->db->select("merchant_group.group_name , count(mfw_church_membership.member_group) as 'number'",false)->from('merchant_group')->join('mfw_church_membership' ,'merchant_group.group_name = mfw_church_membership.member_group','left')->where('merchant_group.merchant_id',$user_id)->get()->result();
		// $this->db->where('merchant_id', $user_id);
		// $query = $this->db->get();
		return $query ;
	}

	// function count_filtered($user_id)
	// {
	// 	$this->_get_datatables_query();
	// 	$this->db->where('merchant_id', $user_id);
	// 	$query = $this->db->get();
	// 	return $query->num_rows();
	// }

	// function count_all($user_id)
	// {
	// 	$this->db->where('merchant_id', $user_id);
	// 	$this->db->from($this->table);
	// 	return $this->db->count_all_results();
	// }
}
