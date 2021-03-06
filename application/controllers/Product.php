<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product extends CI_Controller{
  public function __construct(){
    parent::__construct();
    date_default_timezone_set('Asia/Kolkata');
    $this->load->model('User_Model');
    // $this->load->model('Transaction_Model');
  }

  /***********************     category Information      ******************************/
    public function category_list(){
      $eco_user_id = $this->session->userdata('eco_user_id');
      $eco_company_id = $this->session->userdata('eco_company_id');
      $eco_roll_id = $this->session->userdata('eco_roll_id');
      if($eco_user_id == '' && $eco_company_id == ''){ header('location:'.base_url().'User'); }
      $data['category_list'] = $this->User_Model->get_list($eco_company_id,'category_name','ASC','category');
      $this->load->view('Include/head', $data);
      $this->load->view('Include/navbar', $data);
      $this->load->view('User/category_list', $data);
      $this->load->view('Include/footer', $data);
    }

    public function category(){
      $eco_user_id = $this->session->userdata('eco_user_id');
      $eco_company_id = $this->session->userdata('eco_company_id');
      $eco_roll_id = $this->session->userdata('eco_roll_id');
      if($eco_user_id == '' && $eco_company_id == ''){ header('location:'.base_url().'User'); }

      $this->form_validation->set_rules('category_name', 'Name', 'trim|required');
      if ($this->form_validation->run() != FALSE) {
        $category_status = $this->input->post('category_status');
        if(!isset($category_status)){ $category_status = 0; }
        $main_category_id = $this->input->post('main_category_id');
        if($main_category_id == '-1'){
          $is_main = '1';
          $main_category_id = 0;
        } else{
          $is_main = 0;
          $main_category_id = $this->input->post('main_category_id');
        }
        $save_data = array(
          'company_id' => $eco_company_id,
          'category_name' => $this->input->post('category_name'),
          'category_status' => $category_status,
          'category_date' => date('d-m-Y h:m:s A'),
          'category_addedby' => $eco_user_id,
          'is_main' => $is_main,
          'main_category_id' => $main_category_id,

        );
        $category_id = $this->User_Model->save_data('category', $save_data);

        if(isset($_FILES['category_img']['name'])){
           $time = time();
           $image_name = 'category_'.$category_id.'_'.$time;
           $config['upload_path'] = 'assets/images/category/';
           $config['allowed_types'] = 'png|jpg';
           $config['file_name'] = $image_name;
           $filename = $_FILES['category_img']['name'];
           $ext = pathinfo($filename, PATHINFO_EXTENSION);
           $this->upload->initialize($config);
           if ($this->upload->do_upload('category_img')){
             $up_image = array(
               'category_img' => $image_name.'.'.$ext,
             );
             $this->User_Model->update_info('category_id', $category_id, 'category', $up_image);
             $this->session->set_flashdata('upload_status','success');
           }
           else{
             $error = $this->upload->display_errors();
             $this->session->set_flashdata('upload_status',$this->upload->display_errors());
           }
         }
        $this->session->set_flashdata('save_success','success');
        header('location:'.base_url().'Product/category_list');
      }
      $data['main_category_list'] = $this->User_Model->get_list_by_id('company_id',$eco_company_id,'is_main',1,'category_name','ASC','category');
      $this->load->view('Include/head', $data);
      $this->load->view('Include/navbar', $data);
      $this->load->view('User/category', $data);
      $this->load->view('Include/footer', $data);
    }

    // Edit Category...
    public function edit_category($category_id){
      $eco_user_id = $this->session->userdata('eco_user_id');
      $eco_company_id = $this->session->userdata('eco_company_id');
      $eco_roll_id = $this->session->userdata('eco_roll_id');
      if($eco_user_id == '' && $eco_company_id == ''){ header('location:'.base_url().'User'); }
      $this->form_validation->set_rules('category_name', 'Name', 'trim|required');
      if ($this->form_validation->run() != FALSE) {
        $category_status = $this->input->post('category_status');
        if(!isset($category_status)){ $category_status = 0; }
        $update_data = array(
        'category_name' => $this->input->post('category_name'),
        'main_category_id' => $this->input->post('main_category_id'),
        'category_status' => $category_status,
        'category_date' => date('d-m-Y h:m:s A'),
        'category_addedby' => $eco_user_id,
        );
        $this->User_Model->update_info('category_id', $category_id, 'category', $update_data);
        if(isset($_FILES['category_img']['name'])){
           $time = time();
           $image_name = 'category_'.$category_id.'_'.$time;
           $config['upload_path'] = 'assets/images/category/';
           $config['allowed_types'] = 'png|jpg';
           $config['file_name'] = $image_name;
           $filename = $_FILES['category_img']['name'];
           $ext = pathinfo($filename, PATHINFO_EXTENSION);
           $this->upload->initialize($config);
           if ($this->upload->do_upload('category_img')){
             $up_image = array(
               'category_img' => $image_name.'.'.$ext,
             );
             $this->User_Model->update_info('category_id', $category_id, 'category', $up_image);
             $old_img = $this->input->post('old_img');
             if($old_img){ unlink("assets/images/category/".$old_img); }
             $this->session->set_flashdata('upload_status','success');
           }
           else{
             $error = $this->upload->display_errors();
             $this->session->set_flashdata('upload_status',$this->upload->display_errors());
           }
         }

        $this->session->set_flashdata('update_success','success');
        header('location:'.base_url().'Product/category_list');
      }
      $category_info = $this->User_Model->get_info_arr('category_id',$category_id,'category');
      if(!$category_info){ header('location:'.base_url().'Product/category_list'); }
      $data['update'] = 'update';
      $data['category_name'] = $category_info[0]['category_name'];
      $data['main_category_id'] = $category_info[0]['main_category_id'];
      $data['category_status'] = $category_info[0]['category_status'];
      $data['category_img'] = $category_info[0]['category_img'];
      $data['main_category_list'] = $this->User_Model->get_list_by_id('company_id',$eco_company_id,'is_main',1,'category_name','ASC','category');

      $this->load->view('Include/head', $data);
      $this->load->view('Include/navbar', $data);
      $this->load->view('User/category', $data);
      $this->load->view('Include/footer', $data);
    }

    // Delete Category....
    public function delete_category($category_id){
      $eco_user_id = $this->session->userdata('eco_user_id');
      $eco_company_id = $this->session->userdata('eco_company_id');
      $eco_roll_id = $this->session->userdata('eco_roll_id');
      if($eco_user_id == '' && $eco_company_id == ''){ header('location:'.base_url().'User'); }
      $category_info = $this->User_Model->get_info_arr_fields('category_img','category_id', $category_id, 'category');
      $this->User_Model->delete_info('category_id', $category_id, 'category');
      if($category_info){ unlink("assets/images/category/".$category_info[0]['category_img']); }
      $this->session->set_flashdata('delete_success','success');
      header('location:'.base_url().'Product/category_list');
    }

  /***********************     Product Information      ******************************/
    // Product List...
    public function product_list(){
      $eco_user_id = $this->session->userdata('eco_user_id');
      $eco_company_id = $this->session->userdata('eco_company_id');
      $eco_roll_id = $this->session->userdata('eco_roll_id');
      if($eco_user_id == '' && $eco_company_id == ''){ header('location:'.base_url().'User'); }
      $data['product_list'] = $this->User_Model->get_list($eco_company_id,'product_name','ASC','product');
      $this->load->view('Include/head', $data);
      $this->load->view('Include/navbar', $data);
      $this->load->view('User/product_list', $data);
      $this->load->view('Include/footer', $data);
    }

    // Add Product...
    public function product(){
      $eco_user_id = $this->session->userdata('eco_user_id');
      $eco_company_id = $this->session->userdata('eco_company_id');
      $eco_roll_id = $this->session->userdata('eco_roll_id');
      if($eco_user_id == '' && $eco_company_id == ''){ header('location:'.base_url().'User'); }

      $this->form_validation->set_rules('product_name', 'Name', 'trim|required');
      if ($this->form_validation->run() != FALSE) {
        $save_data = $_POST;
        unset($save_data['files']);
        $save_data['company_id'] = $eco_company_id;
        $save_data['product_date'] = date('d-m-Y h:i:s A');
        $save_data['product_addedby'] = $eco_user_id;
        $product_id = $this->User_Model->save_data('product', $save_data);

        if(isset($_FILES['product_img']['name'])){
          $time = time();
          $image_name = 'product_main_'.$product_id.'_'.$time;
          $config['upload_path'] = 'assets/images/product/';
          $config['allowed_types'] = 'png|jpg';
          $config['file_name'] = $image_name;
          $filename = $_FILES['product_img']['name'];
          $ext = pathinfo($filename, PATHINFO_EXTENSION);
          $this->upload->initialize($config);
          if ($this->upload->do_upload('product_img')){
           $up_image = array(
             'product_img' => $image_name.'.'.$ext,
           );
           $this->User_Model->update_info('product_id', $product_id, 'product', $up_image);
          }
          else{
            $error = $this->upload->display_errors();
            $this->session->set_flashdata('upload_status',$this->upload->display_errors());
          }
        }
      }

      $data['manufacturer_list'] = $this->User_Model->get_list_by_id_com($eco_company_id,'manufacturer_status',1,'','','manufacturer_name','ASC','manufacturer');
      $data['main_category_list'] = $this->User_Model->get_list_by_id_com($eco_company_id,'category_status',1,'is_main',1,'category_name','ASC','category');
      $data['category_list'] = $this->User_Model->get_list_by_id_com($eco_company_id,'category_status',1,'is_main',0,'category_name','ASC','category');
      $data['tax_list'] = $this->User_Model->get_list_by_id_com($eco_company_id,'tax_status',1,'','','tax_title','ASC','tax');
      $this->load->view('Include/head', $data);
      $this->load->view('Include/navbar', $data);
      $this->load->view('User/product', $data);
      $this->load->view('Include/footer', $data);
    }

    // Add Product...
    public function edit_product($product_id){
      $eco_user_id = $this->session->userdata('eco_user_id');
      $eco_company_id = $this->session->userdata('eco_company_id');
      $eco_roll_id = $this->session->userdata('eco_roll_id');
      if($eco_user_id == '' && $eco_company_id == ''){ header('location:'.base_url().'User'); }

      $this->form_validation->set_rules('product_name', 'Name', 'trim|required');
      if ($this->form_validation->run() != FALSE) {
        $update_data = $_POST;
        unset($update_data['files']);
        unset($update_data['old_img']);
        $update_data['product_date'] = date('d-m-Y h:i:s A');
        $update_data['product_addedby'] = $eco_user_id;
        $this->User_Model->update_info('product_id', $product_id, 'product', $update_data);

        if(isset($_FILES['product_img']['name'])){
          $time = time();
          $image_name = 'product_main_'.$product_id.'_'.$time;
          $config['upload_path'] = 'assets/images/product/';
          $config['allowed_types'] = 'gif|jpg|png';
          $config['file_name'] = $image_name;
          $filename = $_FILES['product_img']['name'];
          $ext = pathinfo($filename, PATHINFO_EXTENSION);
          $this->upload->initialize($config);
          if ($this->upload->do_upload('product_img')){
           $up_image = array(
             'product_img' => $image_name.'.'.$ext,
           );
           $old_img = $this->input->post('old_img');
           if(isset($old_img)){ unlink("assets/images/product/".$old_img); }
           $this->User_Model->update_info('product_id', $product_id, 'product', $up_image);
          }
          else{
            $error = $this->upload->display_errors();
            $this->session->set_flashdata('upload_status',$this->upload->display_errors());
          }
        }
        header('location:'.base_url().'Product/product_list');
      }
      $product_info = $this->User_Model->get_info_arr('product_id',$product_id,'product');
      if(!$product_info){ header('location:'.base_url().'Product/product_list'); }
      $data['update'] = 'update';
      $data['product_info'] = $product_info[0];

      $data['manufacturer_list'] = $this->User_Model->get_list_by_id_com($eco_company_id,'manufacturer_status',1,'','','manufacturer_name','ASC','manufacturer');
      $data['main_category_list'] = $this->User_Model->get_list_by_id_com($eco_company_id,'category_status',1,'is_main',1,'category_name','ASC','category');
      $data['category_list'] = $this->User_Model->get_list_by_id_com($eco_company_id,'category_status',1,'is_main',0,'category_name','ASC','category');
      $data['tax_list'] = $this->User_Model->get_list_by_id_com($eco_company_id,'tax_status',1,'','','tax_title','ASC','tax');
      $this->load->view('Include/head', $data);
      $this->load->view('Include/navbar', $data);
      $this->load->view('User/product', $data);
      $this->load->view('Include/footer', $data);
    }

    // Product Images...
    public function product_images($product_id){
      $eco_user_id = $this->session->userdata('eco_user_id');
      $eco_company_id = $this->session->userdata('eco_company_id');
      $eco_roll_id = $this->session->userdata('eco_roll_id');
      if($eco_user_id == '' && $eco_company_id == ''){ header('location:'.base_url().'User'); }

      $this->form_validation->set_rules('product_name', 'Name', 'trim|required');
      if ($this->form_validation->run() != FALSE) {
        if(isset($_FILES['product_images_name']['name'])){
          $this->load->library('upload');
          $files = $_FILES;
          $cpt = count($_FILES['product_images_name']['name']);
          for($i=0; $i<$cpt; $i++){
            $doc_type = $_POST['doc_type'][$i];
            $j = $i+1;
            $time = time();
            $image_name = 'product_sub_'.$product_id.'_'.$j.'_'.$time;
              $_FILES['product_images_name']['name']= $files['product_images_name']['name'][$i];
              $_FILES['product_images_name']['type']= $files['product_images_name']['type'][$i];
              $_FILES['product_images_name']['tmp_name']= $files['product_images_name']['tmp_name'][$i];
              $_FILES['product_images_name']['error']= $files['product_images_name']['error'][$i];
              $_FILES['product_images_name']['size']= $files['product_images_name']['size'][$i];
              $config['upload_path'] = 'assets/images/product/';
              $config['allowed_types'] = 'gif|jpg|png';
              $config['file_name'] = $image_name;
              $config['overwrite']     = FALSE;
              $filename = $files['product_images_name']['name'][$i];
              $ext = pathinfo($filename, PATHINFO_EXTENSION);
              $this->upload->initialize($config);
              if($this->upload->do_upload('product_images_name')){
                $file_data['product_images_name'] = $image_name.'.'.$ext;
                $file_data['product_id'] = $product_id;
                $file_data['product_images_addedby'] = $eco_user_id;
                $file_data['product_images_date'] = date('d-m-Y h:i:s A');
                $this->User_Model->save_data('product_images', $file_data);
              }
              else{
                $error = $this->upload->display_errors();
                $this->session->set_flashdata('status',$this->upload->display_errors());
              }
            }
          }
          header('location:'.base_url().'Product/product_list');
        }

      $product_info = $this->User_Model->get_info_arr_fields('*','product_id', $product_id, 'product');
      if(!$product_info){ header('location:'.base_url().'Product/product_list'); }
      $data['product_info'] = $product_info[0];
      $data['product_images_list'] = $this->User_Model->get_list_by_id('product_id',$product_id,'','','product_id','ASC','product_images');

      // print_r($data['product_images_list']);
      $this->load->view('Include/head', $data);
      $this->load->view('Include/navbar', $data);
      $this->load->view('User/product_images', $data);
      $this->load->view('Include/footer', $data);
    }

    public function delete_product_images(){
      $product_images_id = $this->input->post('product_images_id');
      $product_images_name = $this->input->post('product_images_name');

      unlink("assets/images/product/".$product_images_name);
      $this->User_Model->delete_info('product_images_id', $product_images_id, 'product_images');
    }

    // Add Attributes to Product...
    public function product_attribute($product_id){
      $eco_user_id = $this->session->userdata('eco_user_id');
      $eco_company_id = $this->session->userdata('eco_company_id');
      $eco_roll_id = $this->session->userdata('eco_roll_id');
      if($eco_user_id == '' && $eco_company_id == ''){ header('location:'.base_url().'User'); }
      $this->form_validation->set_rules('attribute_id', 'Name', 'trim|required');
      if ($this->form_validation->run() != FALSE) {
        $save_data = array(
          'company_id' => $eco_company_id,
          'product_id' => $product_id,
          'attribute_id' => $this->input->post('attribute_id'),
          'attribute_val_id' => $this->input->post('attribute_val_id'),
          'attribute_price_type' => $this->input->post('attribute_price_type'),
          'attribute_price' => $this->input->post('attribute_price'),
          'product_attribute_status' => $this->input->post('product_attribute_status'),
          'product_attribute_addedby' => $eco_user_id,
          'product_attribute_date' => date('d-m-Y h:i:sA'),
        );
        $this->User_Model->save_data('product_attribute', $save_data);
        $this->session->set_flashdata('save_success','success');
        header('location:'.base_url().'Product/product_attribute/'.$product_id);
      }

      $product_info = $this->User_Model->get_info_arr_fields('*','product_id', $product_id, 'product');
      if(!$product_info){ header('location:'.base_url().'Product/product_list'); }
      $data['product_info'] = $product_info[0];
      $data['attribute_list'] = $this->User_Model->get_list_by_id('attribute_status',1,'','','attribute_name','ASC','attribute');
      $data['product_attribute_list'] = $this->User_Model->get_list_by_id('product_id',$product_id,'','','attribute_id','ASC','product_attribute');
      $this->load->view('Include/head', $data);
      $this->load->view('Include/navbar', $data);
      $this->load->view('User/product_attribute', $data);
      $this->load->view('Include/footer', $data);
    }

    // Add Attributes to Product...
    public function edit_product_attribute(){
      $eco_user_id = $this->session->userdata('eco_user_id');
      $eco_company_id = $this->session->userdata('eco_company_id');
      $eco_roll_id = $this->session->userdata('eco_roll_id');
      if($eco_user_id == '' && $eco_company_id == ''){ header('location:'.base_url().'User'); }
      $product_attribute_id = $this->uri->segment(4);
      $product_id = $this->uri->segment(3);
      $this->form_validation->set_rules('attribute_id', 'Name', 'trim|required');
      if ($this->form_validation->run() != FALSE) {
        $update_data = array(
          'attribute_id' => $this->input->post('attribute_id'),
          'attribute_val_id' => $this->input->post('attribute_val_id'),
          'attribute_price_type' => $this->input->post('attribute_price_type'),
          'attribute_price' => $this->input->post('attribute_price'),
          'product_attribute_status' => $this->input->post('product_attribute_status'),
          'product_attribute_addedby' => $eco_user_id,
          'product_attribute_date' => date('d-m-Y h:i:sA'),
        );
        $this->User_Model->update_info('product_attribute_id', $product_attribute_id, 'product_attribute', $update_data);
        $this->session->set_flashdata('update_success','success');
        header('location:'.base_url().'Product/product_attribute/'.$product_id);
      }

      $product_info = $this->User_Model->get_info_arr_fields('*','product_id', $product_id, 'product');
      if(!$product_info){ header('location:'.base_url().'Product/product_list'); }
      $data['product_info'] = $product_info[0];
      $data['attribute_list'] = $this->User_Model->get_list_by_id('attribute_status',1,'','','attribute_name','ASC','attribute');
      $data['product_attribute_list'] = $this->User_Model->get_list_by_id('product_id',$product_id,'','','attribute_id','ASC','product_attribute');

      $product_attribute_info = $this->User_Model->get_info_arr('product_attribute_id',$product_attribute_id,'product_attribute');
      if(!$product_attribute_info){ header('location:'.base_url().'Product/product_list'); }
      $data['update'] = 'update';
      $data['product_attribute_info'] = $product_attribute_info[0];
      $attribute_id = $product_attribute_info[0]['attribute_id'];
      $data['attribute_val_list'] = $this->User_Model->get_list_by_id('attribute_id',$attribute_id,'','','attribute_val_name','ASC','attribute_val');
      $this->load->view('Include/head', $data);
      $this->load->view('Include/navbar', $data);
      $this->load->view('User/product_attribute', $data);
      $this->load->view('Include/footer', $data);
    }

    public function delete_product_attribute(){
      $eco_user_id = $this->session->userdata('eco_user_id');
      $eco_company_id = $this->session->userdata('eco_company_id');
      $eco_roll_id = $this->session->userdata('eco_roll_id');
      if($eco_user_id == '' && $eco_company_id == ''){ header('location:'.base_url().'User'); }
      $product_attribute_id = $this->uri->segment(4);
      $product_id = $this->uri->segment(3);
      $this->User_Model->delete_info('product_attribute_id', $product_attribute_id, 'product_attribute');
      $this->session->set_flashdata('delete_success','success');
      header('location:'.base_url().'Product/product_attribute/'.$product_id);
    }

    public function get_attribute_val_by_id(){
      $attribute_id = $this->input->post('attribute_id');
      $attribute_val_list = $this->User_Model->get_list_by_id('attribute_id',$attribute_id,'','','attribute_val_name','ASC','attribute_val');
      echo "<option value='' selected >Select Purchase No.</option>";
      foreach ($attribute_val_list as $list) {
        echo "<option value='".$list->attribute_val_id."'> ".$list->attribute_val_name." </option>";
      }
    }



/***********************     Coupon Information      ******************************/

  public function coupon_list(){
    $this->load->view('Include/head');
    $this->load->view('Include/navbar');
    $this->load->view('User/coupon_list');
    $this->load->view('Include/footer');
  }

  // Add Coupon...
  public function coupon(){
    $eco_user_id = $this->session->userdata('eco_user_id');
    $eco_company_id = $this->session->userdata('eco_company_id');
    $eco_roll_id = $this->session->userdata('eco_roll_id');
    if($eco_user_id == '' && $eco_company_id == ''){ header('location:'.base_url().'User'); }

    $this->form_validation->set_rules('coupon_code', 'Name', 'trim|required');
    if ($this->form_validation->run() != FALSE) {
      $manufacturer_status = $this->input->post('manufacturer_status');
      if(!isset($manufacturer_status)){ $manufacturer_status = 0; }
    }



    $data['product_list'] = $this->User_Model->get_list_by_id('product_status',1,'','','product_name','ASC','product');
    $this->load->view('Include/head', $data);
    $this->load->view('Include/navbar', $data);
    $this->load->view('User/coupon', $data);
    $this->load->view('Include/footer', $data);
  }

      /***********************     Sale Information      ******************************/

        public function sale_list(){
          $this->load->view('Include/head');
          $this->load->view('Include/navbar');
          $this->load->view('User/sale_list');
          $this->load->view('Include/footer');
        }
        public function sale(){
          $this->load->view('Include/head');
          $this->load->view('Include/navbar');
          $this->load->view('User/sale');
          $this->load->view('Include/footer');
        }


        /***********************     purchase Information      ******************************/

          public function purchase_list(){
            $this->load->view('Include/head');
            $this->load->view('Include/navbar');
            $this->load->view('User/purchase_list');
            $this->load->view('Include/footer');
          }
          public function purchase(){
            $this->load->view('Include/head');
            $this->load->view('Include/navbar');
            $this->load->view('User/purchase');
            $this->load->view('Include/footer');
          }


/***********************     order_status Information      ******************************/

      public function order_status_list(){
        $this->load->view('Include/head');
        $this->load->view('Include/navbar');
        $this->load->view('User/order_status_list');
        $this->load->view('Include/footer');
      }
      public function order_status(){
        $this->load->view('Include/head');
        $this->load->view('Include/navbar');
        $this->load->view('User/order_status');
        $this->load->view('Include/footer');
      }



  /***********************     Product Attr Information      ******************************/

    // Attribute List
    public function product_attri_list(){
      $eco_user_id = $this->session->userdata('eco_user_id');
      $eco_company_id = $this->session->userdata('eco_company_id');
      $eco_roll_id = $this->session->userdata('eco_roll_id');
      if($eco_user_id == '' && $eco_company_id == ''){ header('location:'.base_url().'User'); }
      $data['attribute_list'] = $this->User_Model->get_list($eco_company_id,'attribute_name','ASC','attribute');
      $this->load->view('Include/head', $data);
      $this->load->view('Include/navbar', $data);
      $this->load->view('User/product_attri_list', $data);
      $this->load->view('Include/footer', $data);
    }

    // Add Attribute...
    public function product_attri(){
      $eco_user_id = $this->session->userdata('eco_user_id');
      $eco_company_id = $this->session->userdata('eco_company_id');
      $eco_roll_id = $this->session->userdata('eco_roll_id');
      if($eco_user_id == '' && $eco_company_id == ''){ header('location:'.base_url().'User'); }

      $this->form_validation->set_rules('attribute_name', 'Name', 'trim|required');
      if ($this->form_validation->run() != FALSE) {
        $save_data = array(
          'company_id' => $eco_company_id,
          'attribute_name' => $this->input->post('attribute_name'),
          'attribute_addedby' => $eco_user_id,
          'attribute_date' => date('d-m-Y h:m:s A'),
        );
        $attribute_id = $this->User_Model->save_data('attribute', $save_data);

        foreach($_POST['input'] as $multi_data){
          $multi_data['attribute_id'] = $attribute_id;
          $this->db->insert('attribute_val', $multi_data);
        }

        $this->session->set_flashdata('save_success','success');
        header('location:'.base_url().'Product/product_attri_list');
      }
      $this->load->view('Include/head');
      $this->load->view('Include/navbar');
      $this->load->view('User/product_attri');
      $this->load->view('Include/footer');
    }

    public function edit_product_attri($attribute_id){
      $eco_user_id = $this->session->userdata('eco_user_id');
      $eco_company_id = $this->session->userdata('eco_company_id');
      $eco_roll_id = $this->session->userdata('eco_roll_id');
      if($eco_user_id == '' && $eco_company_id == ''){ header('location:'.base_url().'User'); }

      $this->form_validation->set_rules('attribute_name', 'Name', 'trim|required');
      if ($this->form_validation->run() != FALSE) {
        $update_data = array(
          'attribute_name' => $this->input->post('attribute_name'),
          'attribute_addedby' => $eco_user_id,
          'attribute_date' => date('d-m-Y h:m:s A'),
        );
        $this->User_Model->update_info('attribute_id', $attribute_id, 'attribute', $update_data);

        foreach($_POST['input'] as $multi_data){
          if(isset($multi_data['attribute_val_id'])){
            $attribute_val_id = $multi_data['attribute_val_id'];
            if(!isset($multi_data['attribute_val_name'])){
              $this->User_Model->delete_info('attribute_val_id', $attribute_val_id, 'attribute_val');
            }else{
              $this->User_Model->update_info('attribute_val_id', $attribute_val_id, 'attribute_val', $multi_data);
            }
          }
          else{
            $multi_data['attribute_id'] = $attribute_id;
            $this->db->insert('attribute_val', $multi_data);
          }
        }
        $this->session->set_flashdata('update_success','success');
        header('location:'.base_url().'Product/product_attri_list');
      }
      $attribute_info = $this->User_Model->get_info('attribute_id', $attribute_id, 'attribute');
      if($attribute_info == ''){ header('location:'.base_url().'Product/attribute_list'); }
      foreach($attribute_info as $info){
        $data['update'] = 'update';
        $data['attribute_name'] = $info->attribute_name;
        $data['attribute_status'] = $info->attribute_status;
      }
      $data['attribute_val_list'] = $this->User_Model->get_list_by_id('attribute_id',$attribute_id,'','','attribute_val_id','ASC','attribute_val');
      $this->load->view('Include/head', $data);
      $this->load->view('Include/navbar', $data);
      $this->load->view('User/product_attri', $data);
      $this->load->view('Include/footer', $data);
    }

    // Delete Attributes.....
    public function delete_product_attri($attribute_id){
      $eco_user_id = $this->session->userdata('eco_user_id');
      $eco_company_id = $this->session->userdata('eco_company_id');
      $eco_roll_id = $this->session->userdata('eco_roll_id');
      if($eco_user_id == '' && $eco_company_id == ''){ header('location:'.base_url().'User'); }
      $this->User_Model->delete_info('attribute_id', $attribute_id, 'attribute');
      $this->User_Model->delete_info('attribute_id', $attribute_id, 'attribute_val');
      $this->session->set_flashdata('delete_success','success');
      header('location:'.base_url().'Product/product_attri_list');
    }
  /***********************     Inventory Information      ******************************/

    public function inventory_list(){
      $this->load->view('Include/head');
      $this->load->view('Include/navbar');
      $this->load->view('User/inventory_list');
      $this->load->view('Include/footer');
    }
    public function inventory(){
      $this->load->view('Include/head');
      $this->load->view('Include/navbar');
      $this->load->view('User/inventory');
      $this->load->view('Include/footer');
    }
}
