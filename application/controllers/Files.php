<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Files extends CI_Controller {
	function __construct() {
        parent::__construct();
    }
    
    public function upload(){
		//upload_files();
        $data = array();
        
        //load form validation library
        $this->load->library('form_validation');
        
        //load file helper
        $this->load->helper('file');
        
        if($this->input->post('uploadFile')){
			$allowed_types = array(
				"png",
				"jpg",
				"jpeg"
			);
			$param1 = 456;
			$param2 = 234;
			$param3 = 'i \'234';
			$param4 = 'ii "234';
			//(1)
			/*
			$params = json_encode(array(
				$param1,
				$param2,
				$param3,
				$param4,
			));
			*/
			//(2)
			$params = "{$param1},{$param2},{$param3},{$param4}";
			
            //$this->form_validation->set_rules('file', '', 'callback_file_check[' . $params . ']');//(1)
            //$this->form_validation->set_rules('file', '', "callback_file_check[{$params}]");//(2)
            $this->form_validation->set_rules('file', '', "callback_file_check[$params]");//(3)

            if($this->form_validation->run() == true){
				/*
                //upload configuration
                $config['upload_path']   = 'uploads/files/';
                $config['allowed_types'] = implode('|', $allowed_extension);
                $config['max_size']      = 1024;
                $this->load->library('upload', $config);
                //upload file to directory
                if($this->upload->do_upload('file')){
                    $uploadData = $this->upload->data();
                    $uploadedFile = $uploadData['file_name'];
                    //insert file information into the database
                    
                    $data['success_msg'] = 'File has been uploaded successfully.';
                }else{
                    $data['error_msg'] = $this->upload->display_errors();
                }
				*/
				$response = upload_file('file', 'uploads/files/');
				if($response['status'] == 'success'){
					$data['success_msg'] = 'File has been uploaded successfully.';
				}else{
					$data['error_msg'] = $response['message'];
				}
            }
        }
        
        //load the view
        $this->load->view('files/upload', $data);
    }
    
    /*
     * file value and type check during validation
     */
    public function file_check($str = '', $params = '') {
		$function_name = 'file_check';
		$bool = true;
		$message = '';
		$input_name = 'file';
		//var_dump($str);
		//var_dump(json_decode($params)); die;//(1)
		//var_dump(explode(',', $params)); die;//(2)
		$allowed_types = array(
			"png",
			"jpg",
			"jpeg"
		);
		
		$file_extension = strtolower(pathinfo($_FILES[$input_name]["name"], PATHINFO_EXTENSION));
		if (!file_exists($_FILES[$input_name]["tmp_name"])) {
			$bool = false;
			$message = "Choose image file to upload.";
		}
		else if (! in_array($file_extension, $allowed_types)) {
			$bool = false;
			$message = "Upload valiid images. Only extension " . implode(', ', $allowed_types) . " are allowed.";
		}
		else if (($_FILES[$input_name]["size"] > 2000000)) {
			$bool = false;
			$message = "Image size exceeds 2MB";
		}
		else{
			$MAX_WIDTH = 300;
			$MAX_HEIGHT = 200;
			$fileinfo = @getimagesize($_FILES[$input_name]["tmp_name"]);
			$width = $fileinfo[0];
			$height = $fileinfo[1];
			if ($width > $MAX_WIDTH || $height > $MAX_HEIGHT) {
				$bool = false;
				$message = "Image dimension should be within {$MAX_WIDTH}x{$MAX_HEIGHT}";
			}
		}
		/*
        if(isset($_FILES['file']['name']) && $_FILES['file']['name'] != ""){
			//$allowed_mime_types = array('application/pdf','image/gif','image/jpeg','image/pjpeg','image/png','image/x-png');
			$allowed_mime_types = 'application/pdf';
			$mime = get_mime_by_extension($_FILES['file']['name']);
			if (is_array($allowed_mime_types) && !in_array($mime, $allowed_mime_types)){
				$this->form_validation->set_message('file_check', 'Please select only pdf/gif/jpg/png file.');
                return false;
			} elseif ($mime !== $allowed_mime_types) {
                $this->form_validation->set_message('file_check', 'Please select only pdf/gif/jpg/png file.');
                return false;
            }
        }else{
            $this->form_validation->set_message('file_check', 'Please choose a file to upload.');
            return false;
        }
		*/
		if(!$bool){
			$this->form_validation->set_message($function_name, $message);
		}
		return $bool;
    }
}