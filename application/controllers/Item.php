<?php defined('BASEPATH') OR exit('No direct script access allowed');

class item extends CI_Controller {

	function __construct() {
        parent::__construct();
        check_not_login();
        $this->load->model(['item_m', 'category_m', 'unit_m']);
    }

	public function index()
	{
		$data['row'] = $this->item_m->get();
		$this->load->view('product/item/item_data', $data);
	}

	public function add() {
		$item = new stdClass();
		$item->item_id = null;
		$item->barcode = null;
		$item->name = null;
		$item->price = null;

		$query_category = $this->category_m->get();
		$category[null] = ' Choose ';
		foreach($query_category->result() as $cat) {
			$category[$cat->category_id] = $cat->name;
		}
			
		$query_unit = $this->unit_m->get();
		$unit[null] = ' Choose ';
		foreach($query_unit->result() as $unt) {
			$unit[$unt->unit_id] = $unt->name;
		}

		$data = array(
			'page' => 'add',
			'row' => $item,
			'category' => $category, 'selectedcategory' => null,
			'unit' => $unit, 'selectedunit' => null,
		);
		$this->load->view('product/item/item_form', $data);
	}

	public function edit($id) {
		 $query = $this->item_m->get($id);
		 if($query->num_rows() > 0) {
			$item = $query->row();
			$query_category = $this->category_m->get();
			$category[null] = ' Choose ';
			foreach($query_category->result() as $cat) {
				$category[$cat->category_id] = $cat->name;
			}

			$query_unit = $this->unit_m->get();
			$unit[null] = ' Choose ';
			foreach($query_unit->result() as $unt) {
				$unit[$unt->unit_id] = $unt->name;
			}
	
			$data = array(
				'page' => 'edit',
				'row' => $item,
				'category' => $category, 'selectedcategory' => $item->category_id,
				'unit' => $unit, 'selectedunit' => $item->unit_id,
			);
			$this->load->view('product/item/item_form', $data);
		 } else {
			echo "<script>alert('Data tidak ditemukan!');";
            echo "window.location='".site_url('item')."';</script>";
		 }
	}

	public function process() {
		$post = $this->input->post(null, TRUE);
		if(isset($_POST['add'])) {
			if($this->item_m->check_barcode($post['barcode'])->num_rows() > 0) {
				$this->session->set_flashdata('error', "Barcode $post[barcode] sudah dipakai barang lain");
				redirect('item/add');
			} else {
				$config['upload_path']    = './uploads/product/';
                $config['allowed_types']  = 'gif|jpg|png|jpeg';
				$config['max_size']       = 2048;
				$config['file_name']       = 'item-'.date('ymd').'-'.substr(md5(rand()),0,10);
				$this->load->library('upload', $config);

				if(@$_FILES['image']['name'] != null) {
					if($this->upload->do_upload('image')) {
						$post['image'] = $this->upload->data('file_name');
						$this->item_m->add($post);						
						if($this->db->affected_rows() > 0) {
							$this->session->set_flashdata('success', 'Data berhasil disimpan');	
						}
						redirect('item');
					} else {
						$error = $this->upload->display_errors();
						$this->session->set_flashdata('error', $error);
						redirect('item/add');
					}
				} else {
					$post['image'] = null;
					$this->item_m->add($post);						
					if($this->db->affected_rows() > 0) {
						$this->session->set_flashdata('success', 'Data berhasil disimpan');	
					}
					redirect('item');
				}
			}	
		} else if(isset($_POST['edit'])) {
			if($this->item_m->check_barcode($post['barcode'], $post['id'])->num_rows() > 0) {
				$this->session->set_flashdata('error', "Barcode $post[barcode] sudah dipakai barang lain");
				redirect('item/edit/'.$post['id']);
			} else {
				$this->item_m->edit($post);
			}
		} 
	}

	public function del($id) {
		$this->item_m->del($id);
		if($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Data berhasil dihapus');
        }
        redirect('item');
	}
}
