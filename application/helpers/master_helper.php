<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!function_exists('upload_file')) {

    function upload_file($input_name = 'file', $dir = 'uploads/', $options = array()) {
		$ci = &get_instance();
        $response = array(
			'status' => 'warning',
			'content' => NULL,
			'message' => 'Kiểm tra thông tin nhập'
		);
		
		$ci->load->library('upload');
		$config['upload_path'] = FCPATH . $dir;
		if (isset($options['allowed_types']) && $options['allowed_types'] != '') {
			$config['allowed_types'] = $options['allowed_types'];
		} else {
			$config['allowed_types'] = 'gif|jpg|png'; //default
		}
		if (isset($options['max_size'])) {
			$config['max_size'] = $options['max_size'];
		} else {
			$config['max_size']  = 2048;
		}
		$config['file_name'] = strtolower(alias(pathinfo($_FILES[$input_name]["name"], PATHINFO_FILENAME)));
		$ci->upload->initialize($config);
	
		if($ci->upload->do_upload($input_name)){
			$uploadData = $ci->upload->data();
			$uploadedFile = $uploadData['file_name'];
			$response = array(
				'status' => 'success',
				'content' => $uploadedFile,
				'message' => 'Upload ảnh thành công!'
			);
		} else {
			$response = array(
				'status' => 'error',
				'content' => $ci->upload->display_errors(),
				'message' => 'Có lỗi xảy ra! Vui lòng kiểm tra lại!'
			);
		}

        return $response;
    }
}

if (!function_exists('send_sms')) {

    function send_sms($phone = '', $content = '', $options = NULL){
		$message = array();
		$message['status'] = 'warning';
		$message['content'] = null;
		$message['message'] = 'Cấu hình không hợp lệ';
		if(trim($phone) == '' || trim($content) == ''){
			return json_encode($message);
		}
        $ci = &get_instance();
		$sms_api_key = $ci->config->item('api_key', 'sms');
		$sms_secret_key = $ci->config->item('secret_key', 'sms');
        $sms_brandname = isset($options['brandname']) ? $options['brandname'] : $ci->config->item('brandname', 'sms');
        $sms_type = isset($options['type']) ? (int)$options['type'] : 2;
        $ch = curl_init();
		$SampleXml = "<RQST>"
						. "<APIKEY>". $sms_api_key ."</APIKEY>"
						. "<SECRETKEY>". $sms_secret_key ."</SECRETKEY>"
						. "<ISFLASH>0</ISFLASH>"
						. "<SMSTYPE>" . $sms_type . "</SMSTYPE>"
						. "<CONTENT>". $content ."</CONTENT>"
						//. "<BRANDNAME>QCAO_ONLINE</BRANDNAME>"
                        . "<BRANDNAME>" . $sms_brandname . "</BRANDNAME>"
						. "<CONTACTS>"
						. "<CUSTOMER>"
						. "<PHONE>". $phone ."</PHONE>"
						. "</CUSTOMER>"
						. "</CONTACTS>"
					. "</RQST>";
		curl_setopt($ch, CURLOPT_URL,            "http://api.esms.vn/MainService.svc/xml/SendMultipleMessage_V4/");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST,           1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,     $SampleXml);
		curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: text/plain'));

		$result = curl_exec($ch);
		$response = simplexml_load_string($result);
		if ($response === FALSE) {
	    	$message['status'] = 'danger';
			$message['content'] = $data;
			$message['message'] = 'Lỗi: ' . curl_error($ch);
		} elseif($response->CodeResult == 100) {
			$message['status'] = 'success';
			$message['content'] = $response;
			$message['message'] = 'Gửi tin nhắn thành công';
		} else {
			$message['status'] = 'danger';
			$message['content'] = $response;
			$message['message'] = 'Gửi tin nhắn không thành công';
		}
	    curl_close($ch);
	    return json_encode($message);
	}

}

if (!function_exists('format_date')) {

    function format_date($time = 0) {
        if($time == 0){
            $time = time();
        }

        return date('d-m-Y', $time);
    }

}

if (!function_exists('get_start_date')) {

    function get_start_date($str_date) {
        $date = parse_date($str_date) . " 00:00:00";

        return strtotime($date);
    }

}

if (!function_exists('get_end_date')) {

    function get_end_date($str_date) {
        $date = parse_date($str_date) . " 23:59:59";

        return strtotime($date);
    }

}

if (!function_exists('get_current_date')) {

    function get_current_date($str_date) {
        $date = parse_date($str_date) . " " . date('H:i:s');
        return strtotime($date);
    }

}

if (!function_exists('convert_date')) {

    function convert_date($str_date) {
        $dates = explode(" ", $str_date);
        if (isset($dates[0]) && isset($dates[1])) {
            $date = parse_date($dates[1], '/') . " " . $dates[0];
            return strtotime($date);
        }
        return 0;
    }

}

if (!function_exists('parse_date')) {

    function parse_date($str_date, $glue = '-') {
        $dates = explode($glue, $str_date);
        $str_date = $dates[2] . "-" . $dates[1] . "-" . $dates[0];

        return $str_date;
    }

}

if (!function_exists('get_first_last_date')) {

    function get_first_last_date($type = 'month', $time = 0) {
        if($time == 0){
            $time = time();
        }
        switch ($type) {
            case 'week':
                /*
                $first_day = strtotime('monday this week');
                $last_day = strtotime('sunday this week');
                */
                $day = date('w', $time);
                $first_day = strtotime('-' . ($day - 1) . ' days');
                $last_day = strtotime('+' . (7 - $day) . ' days');
                break;
            case 'month':
                $first_day = strtotime(date('Y-m-01', $time));
                $last_day = strtotime(date('Y-m-t', $time));
                break;
            case 'year':
                $first_day = strtotime(date('Y-01-01', $time));
                $last_day = strtotime(date('Y-12-t', $time));
                break;
            default:
                $first_day = 0;
                $last_day = 0;
                break;
        }
        return array(
            'first_day' => $first_day,
            'last_day' => $last_day
        );
    }

}

if (!function_exists('get_package_history_image')) {
    function get_package_history_image($id = 0) {
        $ci = &get_instance();
        $row = $ci->M_users_package_history->get(array('id' => $id));
        return isset($row['image']) ? $row['image'] : '';
    }
}

if (!function_exists('get_config_value')) {
    function get_config_value($config_name = '') {
        $ci = &get_instance();
        $row = $ci->M_configs->get($config_name);
        return isset($row['config_value']) ? $row['config_value'] : '';
    }
}

if (!function_exists('dividend_yield_users')) {
    function dividend_yield_users($has_verify_by = 0) {
        $bool = FALSE;
        $ci = &get_instance();
        //lay users đã trả lai hom nay (1)
        //lay nhung users khong thuoc (1) va có mua gói
        $time = time();
        $start_date = get_start_date(date('Y-m-d', $time));
        $end_date = get_end_date(date('Y-m-d', $time));
        $user_ids = $ci->M_users_commission->gets_user_id(array(
            'status' => 1,
            'in_action' => array('DIVIDEND_YIELD'),
            'start_date_start' => $start_date,
            'start_date_end' => $end_date,
        ));
        // echo "<pre>";
        // print_r($user_ids);
        // echo "</pre>";

        $args = array(
            'has_package_timer' => 0
        );
        if(is_array($user_ids) && !empty($user_ids)){
            $args['not_in_id'] = $user_ids;
        }
        $users = $ci->M_users->gets($args);
        // echo "<pre>";
        // print_r($users);
        // echo "</pre>";
        // die();
        if(is_array($users) && !empty($users)){
            foreach ($users as $user) {
                set_dividend_yield_user($user['userid'], $has_verify_by);
            }
            $bool = TRUE;
        }
        return $bool;
    }
}

if (!function_exists('set_dividend_yield_user')) {
    function set_dividend_yield_user($user_id = 0, $has_verify_by = 0) {
        $ci = &get_instance();
        $user = $ci->M_users->get($user_id);
        if(!(is_array($user) && !empty($user))){
            return;
        }
        $time = time();
        $time_limited = isset($user['package_timer']) ? (int) $user['package_timer'] : 0;

        $status = 1;
        $verified = $time;
        $verify_by = ($has_verify_by != 0) ? $has_verify_by : $user_id;
        $note = NULL;
        $payment = 'CREDIT_CARD';

        $total_stock = get_stock_user($user_id);
        $stock_pay_value = get_config_value('stock_pay_value');
        $dividend_yield = $total_stock * $stock_pay_value;

        if($dividend_yield > 0){
            $action = 'DIVIDEND_YIELD';
            $value_cost = $dividend_yield;
            $percent = 0;
            $value = $dividend_yield;
            $data_commission = array(
                'order_id' => NULL,
                'user_id' => $user_id,
                'extend_by' => NULL,
                'action' => $action,
                'payment' => $payment,
                'value_cost' => $value_cost,
                'percent' => $percent,
                'value' => $value,
                'message' => 'Người dùng được trả lãi cổ tức',
                'note' => $note,
                'status' => $status,
                'created' => $time,
                'verified' => $verified,
                'verify_by' => $verify_by
            );
            $commission_id = $ci->M_users_commission->add($data_commission);

            if($commission_id != 0){
                $timer = floatval(get_config_value('timer'));
                $package_timer = $time_limited + $timer*24*60*60;
                $ci->M_users->update($user_id, array(
                    'package_timer' => $package_timer,
                ));

                //F1
                $parent_id = isset($user['referred_by']) ? (int) $user['referred_by'] : 0;
                if($parent_id != 0){
                    $action = 'SUB_DIVIDEND_YIELD_ROOT';
                    $value_cost = $dividend_yield;
                    $percent = 20;
                    $value = $dividend_yield * $percent / 100;
                    $data_commission = array(
                        'order_id' => NULL,
                        'user_id' => $parent_id,
                        'extend_by' => $commission_id,
                        'action' => $action,
                        'payment' => $payment,
                        'value_cost' => $value_cost,
                        'percent' => $percent,
                        'value' => $value,
                        'message' => 'Người dùng được hưởng hoa hồng từ người dùng cấp dưới trực tiếp được trả lãi cổ tức',
                        'note' => $note,
                        'status' => $status,
                        'created' => $time,
                        'verified' => $verified,
                        'verify_by' => $verify_by
                    );
                    $ci->M_users_commission->add($data_commission);

                    //F2
                    $F2_user = $ci->M_users->get($parent_id);
                    $F2_id = isset($F2_user['referred_by']) ? (int) $F2_user['referred_by'] : 0;
                    if($F2_id != 0){
                        $action = 'SUB_DIVIDEND_YIELD';
                        $value_cost = $dividend_yield;
                        $percent = 10;
                        $value = $dividend_yield * $percent / 100;
                        $data_commission = array(
                            'order_id' => NULL,
                            'user_id' => $F2_id,
                            'extend_by' => $commission_id,
                            'action' => $action,
                            'payment' => $payment,
                            'value_cost' => $value_cost,
                            'percent' => $percent,
                            'value' => $value,
                            'message' => 'Người dùng được hưởng hoa hồng từ người dùng cấp dưới được trả lãi cổ tức',
                            'note' => $note,
                            'status' => $status,
                            'created' => $time,
                            'verified' => $verified,
                            'verify_by' => $verify_by
                        );
                        $ci->M_users_commission->add($data_commission);

                        //F3
                        $F3_user = $ci->M_users->get($F2_id);
                        $F3_id = isset($F3_user['referred_by']) ? (int) $F3_user['referred_by'] : 0;
                        if($F3_id != 0){
                            $action = 'SUB_DIVIDEND_YIELD';
                            $value_cost = $dividend_yield;
                            $percent = 5;
                            $value = $dividend_yield * $percent / 100;
                            $data_commission = array(
                                'order_id' => NULL,
                                'user_id' => $F3_id,
                                'extend_by' => $commission_id,
                                'action' => $action,
                                'payment' => $payment,
                                'value_cost' => $value_cost,
                                'percent' => $percent,
                                'value' => $value,
                                'message' => 'Người dùng được hưởng hoa hồng từ người dùng cấp dưới được trả lãi cổ tức',
                                'note' => $note,
                                'status' => $status,
                                'created' => $time,
                                'verified' => $verified,
                                'verify_by' => $verify_by
                            );
                            $ci->M_users_commission->add($data_commission);
                        }
                    }
                }
            }
        }
    }
}

if (!function_exists('get_dividend_yield_user')) {
    function get_dividend_yield_user($user_id = 0) {
        $ci = &get_instance();
        $user = $ci->M_users->get($user_id);
        if(!(is_array($user) && !empty($user))){
            return;
        }
        $time = time();
        $time_limited = isset($user['package_timer']) ? (int) $user['package_timer'] : 0;
        if(!($time_limited != 0 && $time >= $time_limited)){
            return;
        }

        $status = 1;
        $verified = $time;
        $verify_by = $user_id;
        $note = NULL;
        $payment = 'CREDIT_CARD';

        $total_stock = get_stock_user($user_id);
        $stock_pay_value = get_config_value('stock_pay_value');
        $dividend_yield = $total_stock * $stock_pay_value;
        // echo "<br>" . $total_stock;
        // echo "<br>" . $stock_pay_value;
        // echo "<br>" . $dividend_yield;
        //die;
        if($dividend_yield > 0){
	        $action = 'DIVIDEND_YIELD';
	        $value_cost = $dividend_yield;
	        $percent = 0;
	        $value = $dividend_yield;
	        $data_commission = array(
	            'order_id' => NULL,
	            'user_id' => $user_id,
	            'extend_by' => NULL,
	            'action' => $action,
	            'payment' => $payment,
	            'value_cost' => $value_cost,
	            'percent' => $percent,
	            'value' => $value,
	            'message' => 'Người dùng được trả lãi cổ tức',
	            'note' => $note,
	            'status' => $status,
	            'created' => $time,
	            'verified' => $verified,
	            'verify_by' => $verify_by
	        );
	        $commission_id = $ci->M_users_commission->add($data_commission);

	        if($commission_id != 0){
	            $timer = floatval(get_config_value('timer'));
	            //$package_timer = $time + $timer*24*60*60;
                $package_timer = $time_limited + $timer*24*60*60;
	            $ci->M_users->update($user_id, array(
	                'package_timer' => $package_timer,
	            ));

	            //F1
                $parent_id = isset($user['referred_by']) ? (int) $user['referred_by'] : 0;
	            if($parent_id != 0){
	                $action = 'SUB_DIVIDEND_YIELD_ROOT';
	                $value_cost = $dividend_yield;
	                $percent = 20;
	                $value = $dividend_yield * $percent / 100;
	                $data_commission = array(
	                    'order_id' => NULL,
	                    'user_id' => $parent_id,
	                    'extend_by' => $commission_id,
	                    'action' => $action,
	                    'payment' => $payment,
	                    'value_cost' => $value_cost,
	                    'percent' => $percent,
	                    'value' => $value,
	                    'message' => 'Người dùng được hưởng hoa hồng từ người dùng cấp dưới trực tiếp được trả lãi cổ tức',
	                    'note' => $note,
	                    'status' => $status,
	                    'created' => $time,
	                    'verified' => $verified,
	                    'verify_by' => $verify_by
	                );
	                $ci->M_users_commission->add($data_commission);

                    //F2
                    $F2_user = $ci->M_users->get($parent_id);
                    $F2_id = isset($F2_user['referred_by']) ? (int) $F2_user['referred_by'] : 0;
                    if($F2_id != 0){
                        $action = 'SUB_DIVIDEND_YIELD';
                        $value_cost = $dividend_yield;
                        $percent = 10;
                        $value = $dividend_yield * $percent / 100;
                        $data_commission = array(
                            'order_id' => NULL,
                            'user_id' => $F2_id,
                            'extend_by' => $commission_id,
                            'action' => $action,
                            'payment' => $payment,
                            'value_cost' => $value_cost,
                            'percent' => $percent,
                            'value' => $value,
                            'message' => 'Người dùng được hưởng hoa hồng từ người dùng cấp dưới được trả lãi cổ tức',
                            'note' => $note,
                            'status' => $status,
                            'created' => $time,
                            'verified' => $verified,
                            'verify_by' => $verify_by
                        );
                        $ci->M_users_commission->add($data_commission);

                        //F3
                        $F3_user = $ci->M_users->get($F2_id);
                        $F3_id = isset($F3_user['referred_by']) ? (int) $F3_user['referred_by'] : 0;
                        if($F3_id != 0){
                            $action = 'SUB_DIVIDEND_YIELD';
                            $value_cost = $dividend_yield;
                            $percent = 5;
                            $value = $dividend_yield * $percent / 100;
                            $data_commission = array(
                                'order_id' => NULL,
                                'user_id' => $F3_id,
                                'extend_by' => $commission_id,
                                'action' => $action,
                                'payment' => $payment,
                                'value_cost' => $value_cost,
                                'percent' => $percent,
                                'value' => $value,
                                'message' => 'Người dùng được hưởng hoa hồng từ người dùng cấp dưới được trả lãi cổ tức',
                                'note' => $note,
                                'status' => $status,
                                'created' => $time,
                                'verified' => $verified,
                                'verify_by' => $verify_by
                            );
                            $ci->M_users_commission->add($data_commission);
                        }
                    }
	            }
	        }
	    }
    }
}

if (!function_exists('check_dividend_yield_user')) {
    function check_dividend_yield_user($user_id = 0) {
        $ci = &get_instance();
        echo $user_id;
        $user = $ci->M_users->get($user_id);
        if(!(is_array($user) && !empty($user))){
            return;
        }
        //F1
        $parent_id = isset($user['referred_by']) ? (int) $user['referred_by'] : 0;
        echo "<br>" . $parent_id;
        if($parent_id != 0){
            //F2
            $F2_user = $ci->M_users->get($parent_id);
            $F2_id = isset($F2_user['referred_by']) ? (int) $F2_user['referred_by'] : 0;
            echo "<br>" . $F2_id;
            if($F2_id != 0){
                //F3
                $F3_user = $ci->M_users->get($F2_id);
                $F3_id = isset($F3_user['referred_by']) ? (int) $F3_user['referred_by'] : 0;
                echo "<br>" . $F3_id;
            }
        }
    }
}

if (!function_exists('get_bank_info')) {
    function get_bank_info($user_id = 0) {
        $ci = &get_instance();
        $row = $ci->M_users->get($user_id);
        $arr_address = array();
        if (isset($row['account_holder']) && trim($row['account_holder']) != '') {
            $arr_address[] = 'Tên chủ sở hữu: ' . $row['account_holder'];
        }
        if (isset($row['account_number']) && trim($row['account_number']) != '') {
            $arr_address[] = 'Số tài khoản: ' . $row['account_number'];
        }
        if (isset($row['banker_name']) && trim($row['banker_name']) != '') {
            $arr_address[] = 'Ngân hàng: ' . $row['banker_name'];
        }
        if (isset($row['branch_bank']) && trim($row['branch_bank']) != '') {
            $arr_address[] = 'Chi nhánh: ' . $row['branch_bank'];
        }

        $str = '';
        if (is_array($arr_address) && !empty($arr_address)) {
            $str = implode('<br/>', $arr_address);
        }

        return $str;
    }
}

if (!function_exists('get_bonus_user')) {
    function get_bonus_user($user_id = 0) {
        $ci = &get_instance();

        $total_bonus = $ci->M_users_commission->get_total(array(
            'user_id' => $user_id,
            'status' => 1,
            'in_action' => array('SUB_BUY_PACKAGE', 'SUB_BUY_PACKAGE_ROOT', 'SUB_BUY_PACKAGE_BONUS_LEVEL', 'SUB_BUY', 'SUB_BUY_ROOT', 'SUB_BUY_BONUS_LEVEL')
        ));

        $balance = abs($total_bonus);

        return $balance;
    }
}

if (!function_exists('get_buy_package_user')) {
    function get_buy_package_user($user_id = 0) {
        $ci = &get_instance();

        $total_buy_package = $ci->M_users_commission->get_total(array(
            'user_id' => $user_id,
            'status' => 1,
            'in_action' => array('BUY_PACKAGE')
        ));

        $balance = abs($total_buy_package);

        return $balance;
    }
}

if (!function_exists('get_buy_user')) {
    function get_buy_user($user_id = 0) {
        $ci = &get_instance();

        $total_buy_package = $ci->M_users_commission->get_total(array(
            'user_id' => $user_id,
            'status' => 1,
            'in_action' => array('BUY')
            //'in_action' => array('BUY_PACKAGE', 'BUY')
        ));

        $balance = abs($total_buy_package);

        return $balance;
    }
}

if (!function_exists('get_balance_package_user')) {
    function get_balance_package_user($user_id = 0) {
        $ci = &get_instance();

        $in_user_id = get_childs_user($user_id);
        $in_user_id[] = $user_id;

        $total_buy_package = $ci->M_users_commission->get_total(array(
            'in_user_id' => $in_user_id,
            'status' => 1,
            'in_action' => array('BUY_PACKAGE'),
            'payment' => 'MHG'
        ));

        $balance = abs($total_buy_package);

        return $balance;
    }
}

if (!function_exists('get_commission_user')) {
    function get_commission_user($user_id = 0, $not_in_id = 0) {
        $ci = &get_instance();

        $pay_in = $ci->M_users_commission->get_total(array(
            'user_id' => $user_id,
            'status' => 1,
            'in_action' => array('PAY_IN')
        ));

        $withdrawal = $ci->M_users_commission->get_total(array(
            'user_id' => $user_id,
            'status' => 1,
            'in_action' => array('WITHDRAWAL'),
            'not_in_id' => $not_in_id,
        ));

        $total_commission_buy = $ci->M_users_commission->get_total(array(
            'user_id' => $user_id,
            'status' => 1,
            'in_action' => array('SUB_BUY', 'SUB_BUY_ROOT', 'SUB_BUY_BONUS_LEVEL')
        ));

        $total_stock_exchange = $ci->M_users_commission->get_total(array(
            'user_id' => $user_id,
            'status' => 1,
            'in_action' => array('STOCK_EXCHANGE')
        ));

        $total_stock_buy_more = abs($ci->M_users_commission->get_total(array(
            'user_id' => $user_id,
            'status' => 1,
            'in_action' => array('STOCK_BUY_MORE'),
            'payment' => 'CREDIT_CARD'
        )));

        $total_dividend_yield = abs($ci->M_users_commission->get_total(array(
            'user_id' => $user_id,
            'status' => 1,
            'in_action' => array('DIVIDEND_YIELD', 'SUB_DIVIDEND_YIELD', 'SUB_DIVIDEND_YIELD_ROOT'),
        )));

        $balance = $total_dividend_yield + $pay_in + $total_commission_buy + abs($total_stock_exchange) - $total_stock_buy_more - abs($withdrawal);

        return array(
            'total_pay_in' => abs($pay_in),
            'total_withdrawal' => abs($withdrawal),
            //'total_commission_package' => abs($total_commission_package),
            'total_commission_buy' => abs($total_commission_buy),
            'total_stock_exchange' => abs($total_stock_exchange),
            'total_stock_buy_more' => $total_stock_buy_more,
            'total_dividend_yield' => $total_dividend_yield,
            'balance' => $balance,
        );
    }
}

if (!function_exists('get_balance_user')) {
    function get_balance_user($user_id = 0, $not_in_id = 0) {
        $data = get_commission_user($user_id, $not_in_id);
        return isset($data['balance']) ? $data['balance'] : 0;
    }
}

if (!function_exists('get_stock_user')) {
    function get_stock_user($user_id = 0, $not_in_id = 0) {
        $ci = &get_instance();

        $stock_withdrawal = abs($ci->M_users_commission->get_total(array(
            'user_id' => $user_id,
            'status' => 1,
            'in_action' => array('STOCK_WITHDRAWAL'),
            'not_in_id' => $not_in_id,
        )));
        // echo $stock_withdrawal; die;

        $total_commission_package = $ci->M_users_commission->get_total(array(
            'user_id' => $user_id,
            'status' => 1,
            'in_action' => array('SUB_BUY_PACKAGE', 'SUB_BUY_PACKAGE_ROOT', 'SUB_BUY_PACKAGE_BONUS_LEVEL')
        ));

        $stock = $ci->M_users_commission->get_total(array(
            'user_id' => $user_id,
            'status' => 1,
            'in_action' => array('STOCK_BONUS', 'STOCK_BUY', 'STOCK_BUY_BONUS', 'STOCK_BONUS_ID', 'SUB_STOCK_BONUS_ID_ROOT', 'SUB_STOCK_BONUS_ID')
        ));

        $stock_buy_more = abs($ci->M_users_commission->get_total_cost(array(
            'user_id' => $user_id,
            'status' => 1,
            'in_action' => array('STOCK_BUY_MORE')
        )));

        $stock_pay_in = abs($ci->M_users_commission->get_total_cost(array(
            'user_id' => $user_id,
            'status' => 1,
            'in_action' => array('STOCK_PAY_IN')
        )));

        $stock_exchange = abs($ci->M_users_commission->get_total_cost(array(
            'user_id' => $user_id,
            'status' => 1,
            'in_action' => array('STOCK_EXCHANGE')
        )));

        $total_buy_package = abs($ci->M_users_commission->get_total(array(
            'user_id' => $user_id,
            'status' => 1,
            'in_action' => array('BUY_PACKAGE'),
            'payment' => 'MHG'
        )));

        return $total_commission_package + $stock + $stock_buy_more + $stock_pay_in - $stock_withdrawal - $stock_exchange - $total_buy_package;
    }
}

if (!function_exists('get_stock_user_available')) {
    function get_stock_user_available($user_id = 0, $not_in_id = 0) {
        $ci = &get_instance();

        $stock_withdrawal = abs($ci->M_users_commission->get_total(array(
            'user_id' => $user_id,
            'status' => 1,
            'in_action' => array('STOCK_WITHDRAWAL'),
            'not_in_id' => $not_in_id,
        )));

        $stock = $ci->M_users_commission->get_total_cost(array(
            'user_id' => $user_id,
            'status' => 1,
            'in_action' => array('STOCK_BUY_MORE')
        ));

        $stock_pay_in = abs($ci->M_users_commission->get_total_cost(array(
            'user_id' => $user_id,
            'status' => 1,
            'in_action' => array('STOCK_PAY_IN')
        )));

        $stock_exchange = $ci->M_users_commission->get_total_cost(array(
            'user_id' => $user_id,
            'status' => 1,
            'in_action' => array('STOCK_EXCHANGE')
        ));

        $total_buy_package = abs($ci->M_users_commission->get_total(array(
            'user_id' => $user_id,
            'status' => 1,
            'in_action' => array('BUY_PACKAGE'),
            'payment' => 'MHG'
        )));

        $total_commission_package = abs($ci->M_users_commission->get_total(array(
            'user_id' => $user_id,
            'status' => 1,
            'in_action' => array('SUB_BUY_PACKAGE', 'SUB_BUY_PACKAGE_ROOT')
        )));

        return abs($stock + $stock_pay_in + $total_commission_package - $stock_withdrawal - $stock_exchange - $total_buy_package);
    }
}

if (!function_exists('get_prefix_group_action')) {
    function get_prefix_group_action($action = '') {
        $prefix_group_action = '';

        if(in_array($action, array('BUY_PACKAGE', 'SUB_BUY_PACKAGE_ROOT', 'SUB_BUY_PACKAGE', 'SYSTEM_PACKAGE', 'STOCK_BUY', 'STOCK_BUY_BONUS', 'SUB_BUY_PACKAGE_BONUS_LEVEL'))){
            $prefix_group_action = ' PACKAGE';
        }elseif(in_array($action, array('BUY', 'SUB_BUY', 'SUB_BUY_ROOT', 'SELL', 'BUY_SYSTEM', 'SUB_BUY_BONUS_LEVEL'))){
            $prefix_group_action = ' BUY';
        }elseif(in_array($action, array('TRANSFER', 'TRANSFERED'))){
            $prefix_group_action = ' TRANSFER';
        }

        return $prefix_group_action;
    }
}

if (!function_exists('show_treetable_users')) {

    function show_treetable_users($users, $parent_id = 0, $stt = 0, $total = 0){
        $cate_child = array();
        foreach ($users as $key => $item){
            if ($item['referred_by'] == $parent_id){
                $cate_child[] = $item;
                unset($users[$key]);
            }
        }
        if ($cate_child){
            $stt++;
            if ($stt == 11){
                return;
            }
            $parent = '';
            foreach ($cate_child as $key => $item){
                $root = $item['userid'];
                // $revenue = $root;
                // $revenue = formatRice(get_balance_user($root));
                $revenue = formatRice(get_buy_package_user($root));
                if ($stt > 1){
                    $parent = $item['referred_by'];
                }
                echo '<tr>
                        <td>
                            <div class="tt" data-tt-id="' . $root . '" data-tt-parent="' . $parent . '">' . $item['full_name'] . ' - ' . $item['username'] . ' - ' . $revenue . '</div>
                        </td>
                    </tr>';
                show_treetable_users($users, $item['userid'], $stt, $total);
            }
        }
    }

}

if (!function_exists('get_parent_level')) {

    function get_parent_level($users, $user_id = 0, $stt = 0){
        $subs_parent = array();
        foreach ($users as $key => $item){
            if ($item['userid'] == $user_id){
                $parent_id = $item['referred_by'];
                foreach ($users as $user){
                    if ($user['userid'] == $parent_id){
                        $stt++;
                        if ($stt == 12){
                            return;
                        }
                        $subs_parent = get_parent_level($users, $parent_id, $stt);
                        $subs_parent[] = $parent_id;
                    }
                }
            }
        }
        return $subs_parent;
    }

}

if (!function_exists('get_childs_user')) {

    function get_childs_user($user_id = 0){
        $CI = &get_instance();
        $childs = $CI->M_users->get_childs($user_id);
        // $children = array();
        $children = array();
        if(is_array($childs) && !empty($childs)){
            $children = array_column($childs, 'userid');
            foreach ($childs as $child) {
                $children = array_merge($children, get_childs_user($child['userid']));
                //$children[$child['userid']] = get_childs_user($child['userid']);
            }
        }
        return array_unique($children);
    }

}

if (!function_exists('get_data_parent_level')) {

    function get_data_parent_level($users, $user_id = 0){
        $data_parent = array(
            'root' => 0,
            'subs' => 0
        );
        $data = get_parent_level($users, $user_id);
        if(is_array($data) && !empty($data)){
            $root_id = end($data);
            array_pop($data);
            arsort($data);
            $data = array_values($data);
            $data_parent = array(
                'root' => $root_id,
                'subs' => $data
            );
        }
        return $data_parent;
    }

}

if (!function_exists('get_parent_user')) {
    function get_parent_user($user_id = 0, $level = 1) {
        // echo "---------------------------------------------------------------------<br/>";
        // echo "level: " . $level . "<br/>";
        $CI = &get_instance();
        $parent_id = $CI->M_users->get_parent($user_id);
        // echo "user_id: $user_id, parent_id: $parent_id" . "<br/>";
        if($parent_id != -1){
            $level_values = $CI->config->item('level_bonus');
            /*
            $level_values = array(
                'LEVEL_1' => 1,
                'LEVEL_2' => 2,
                'LEVEL_3' => 3,
                'LEVEL_4' => 4,
            );
            */
            $row_parent = $CI->M_users->get($parent_id);
            $parent_level = isset($row_parent['level']) ? $row_parent['level'] : NULL;
            $parent_level_value = (int) display_value_array($level_values, $parent_level);

            $childs = $CI->M_users->get_childs($parent_id);
            if(is_array($childs) && !empty($childs)){
                // if($parent_id == 59){
                //     echo "<pre>";
                //     print_r($childs);
                //     echo "</pre>";
                //     //die();
                // }
                $count = 0;
                foreach ($childs as $child) {
                    $level_child = isset($child['level']) ? $child['level'] : NULL;
                    $level_child_value = (int) display_value_array($level_values, $level_child);
                    // tổng số >= 2 trở lên thì cho lên cấp
                    if($level_child_value >= $level){
                        $count++;
                    }
                    if($count >= 2){
                        $level++;
                        // echo "count: $count, level: $level <br/>";
                        if($parent_level_value < $level && $level <= 4){
                            $CI->M_users->update($parent_id, array('level' => 'LEVEL_' . $level));
                        }
                        if($level == 4){
                            break;
                        }
                        get_parent_user($parent_id, $level);
                        break;
                    }
                }
            }
        }
    }
}

if (!function_exists('set_level_parent_user')) {
    function set_level_parent_user($user_id = 0, $level = 0) {
        // echo "---------------------------------------------------------------------<br/>";
        // echo "level: " . $level . "<br/>";
        $CI = &get_instance();
        $parent_id = $CI->M_users->get_parent($user_id);
        // echo "user_id: $user_id, parent_id: $parent_id" . "<br/>";
        if($parent_id != -1){
        	$level_values = $CI->config->item('level_value');
        	/*
            $level_values = array(
                'LEVEL_0' => 0,
                'LEVEL_1' => 1,
                'LEVEL_2' => 2,
                'LEVEL_3' => 3,
                'LEVEL_4' => 4,
            );
            */
            $row_parent = $CI->M_users->get($parent_id);
            $parent_package = isset($row_parent['package']) ? (int) $row_parent['package'] : 0;
            $parent_level = isset($row_parent['level']) ? $row_parent['level'] : NULL;
            $parent_level_str = display_value_array($level_values, $parent_level);
            $parent_level_value = (int) $parent_level_str;

            $childs = $CI->M_users->get_childs($parent_id);
            if(is_array($childs) && !empty($childs)){
                //$revenue = 0;
                // $revenue = get_balance_user($parent_id);
                $revenue = get_balance_package_user($parent_id);
                /*
                if(in_array($parent_id, array(59, 509, 510, 514, 517, 518, 524, 525, 526, 527))){
                    $revenue = 500000000;
                    // echo "<pre>";
                    // print_r($childs);
                    // echo "</pre>";
                    //die();
                }
                */
                $count = 0;
                foreach ($childs as $child) {
                    $level_child = isset($child['level']) ? $child['level'] : NULL;
                    $level_child_str = display_value_array($level_values, $level_child);
                    $level_child_value = trim($level_child_str) == '' ? -1 : (int) $level_child_str;
                    // tổng số >= 2 trở lên thì cho lên cấp
                    if($level_child_value >= $level){
                        $count++;
                    }
                    if($count >= 2){
                        $level++;
                        // echo "count: $count, level: $level <br/>";
                        if($parent_level_value < $level && $parent_package != 0){
                            if($level == 1){
                                /*
                                $args_package_history_exist = array(
                                    'user_id' => $parent_id,
                                    'package' => $parent_package,
                                    'status' => 1,
                                );
                                $row_package_history = $CI->M_users_package_history->get($args_package_history_exist);
                                */
                                // if($revenue >= 490000000 && $parent_package != 0 && is_array($row_package_history) && !empty($row_package_history)){
                                // if($revenue >= 490000000){
                                if($revenue >= 4900000){
                                    $CI->M_users->update($parent_id, array('level' => 'LEVEL_' . $level));
                                }
                            }elseif($level <= 4){
                                $CI->M_users->update($parent_id, array('level' => 'LEVEL_' . $level));
                            }
                        }
                        if($level == 4){
                            break;
                        }
                        set_level_parent_user($parent_id, $level);
                        break;
                    }
                }
            }
        }
    }
}

if (!function_exists('counts_child_user')) {
    function counts_child_user($user_id = 0) {
        $CI = &get_instance();
        $childs = $CI->M_users->get_childs($user_id);
        $count = count($childs);
        if(is_array($childs) && !empty($childs)){
            foreach ($childs as $child) {
                $count += counts_child_user($child['userid']);
            }
        }
        return $count;
    }
}

if (!function_exists('get_balance_system_users')) {
    function get_balance_system_users($user_id = 0) {
        $CI = &get_instance();
        $childs = $CI->M_users->get_childs($user_id);
        $balance = get_buy_package_user($user_id);
        if(is_array($childs) && !empty($childs)){
            foreach ($childs as $child) {
                $balance += get_balance_system_users($child['userid']);
            }
        }
        return $balance;
    }
}

if (!function_exists('current_full_url')) {
    function current_full_url() {
        $CI = &get_instance();
        $url = $CI->config->site_url($CI->uri->uri_string());
        return $_SERVER['QUERY_STRING'] ? $url . '?' . $_SERVER['QUERY_STRING'] : $url;
    }
}

if (!function_exists('get_first_element')) {
    function get_first_element($data = '') {
        $str = $data;
        if (is_array($data) && !empty($data)) {
            $str = reset($data);
        }
        return $str;
    }
}

/*
 * Importion: This is change page url admin
 */
if (!function_exists('get_admin_url')) {

    function get_admin_url($module_slug = '') {
        $html = '';
        $ci = & get_instance();
        $base_url = $ci->config->item('base_url');
        $html .= $base_url . 'admin';
        if (trim($module_slug) != '') {
            $html .= '/' . $module_slug;
        }

        return $html;
    }

}

if (!function_exists('add_css')) {

    function add_css($names = array()) {
        $html = '';
        $data = array();
        if (is_array($names) && !empty($names)) {
            foreach ($names as $value) {
                $data[] = '<link href="' . get_asset('css_path') . $value . '.css" type="text/css" rel="stylesheet" />';
            }
        }

        if (is_array($data) && !empty($data)) {
            $html = implode("\n\t\t", $data);
        }

        return $html;
    }

}

if (!function_exists('add_js')) {

    function add_js($names = array()) {
        $html = '';
        $data = array();
        if (is_array($names) && !empty($names)) {
            foreach ($names as $value) {
                $data[] = '<script type="text/javascript" src="' . get_asset('js_path') . $value . '.js"></script>';
            }
        }

        if (is_array($data) && !empty($data)) {
            $html = implode("\n\t\t", $data);
        }

        return $html;
    }

}

if (!function_exists('create_folder')) {

    function create_folder($path_folder = 'uploads/', $create_index_file = true) {
        if (!is_dir($path_folder)) {
            mkdir('./' . $path_folder, 0777, TRUE);
            if ($create_index_file) {
                $index_file = 'index.html';
                copy_file('uploads/' . $index_file, $path_folder . '/' . $index_file);
            }
        }
    }

}

if (!function_exists('copy_file')) {

    function copy_file($from_file, $to_file, $delete = false) {
        $file = FCPATH . $from_file;
        $newfile = FCPATH . $to_file;
        copy($file, $newfile);
        if ($delete) {
            @unlink($file);
        }
    }

}

if (!function_exists('active_link')) {

    function activate_menu($controller = '') {
        $CI = get_instance();
        $class = $CI->router->fetch_class(); //tra ve lop chua fuction hien tai
        return ($class == $controller) ? 'active' : '';
    }

}

if (!function_exists('is_home')) {

    function is_home() {
        $ci = & get_instance();
        if ($ci->uri->uri_string() == '') {
            return true;
        }

        return false;
    }

}

if (!function_exists('get_asset')) {

    function get_asset($folder = '') {
        $html = '';
        $ci = & get_instance();
        $base_url = $ci->config->item('base_url');
        $html .= $base_url;
        if (trim($folder) != '') {
            $html .= $ci->config->item($folder);
        }

        return $html;
    }

}

if (!function_exists('get_view_page')) {

    function get_view_page($view_page = '') {
        $data = array(
            'page_grid' => 'Lưới',
            'page_list' => 'Danh sách',
        );
        $html = '';
        if (isset($data[$view_page])) {
            $html .= $data[$view_page];
        }

        return $html;
    }

}

if (!function_exists('validate_file_exists')) {

    function validate_file_exists($file = '') {
        $bool = true;

        if (is_dir($file) || !file_exists(FCPATH . $file)) {
            $bool = false;
        }

        return $bool;
    }

}

if (!function_exists('get_image')) {

    function get_image($path_image = '', $path_default_image = 'uploads/no_image.png') {
        $html = $path_image;

        if (is_dir($path_image) || !file_exists(FCPATH . $path_image)) {
            $html = $path_default_image;
        }

        return base_url($html);
    }

}

if (!function_exists('get_media')) {

    function get_media($module_name = '', $image = '', $default_image = 'no_image.png', $str_format = '') {
        $folder = '';
        $ci = & get_instance();
        $modules_path = $ci->config->item('modules_path');
        if (trim($module_name) != '' && isset($modules_path[$module_name])) {
            $folder = $modules_path[$module_name];
        }
        $src = $image;
        $path_image = $folder . $image;
        if (is_dir($path_image) || !file_exists(FCPATH . $path_image)) {
            $src = $default_image;
        }
        if(trim($str_format) != ''){
            $src = $str_format . '-' . $src;
        }

        $temp_folder = rtrim($folder, '/');
        $temp_arr = explode('/', $temp_folder);
        $temp_arr_count = count($temp_arr);
        if($temp_arr_count > 2){
            unset($temp_arr[0]);
            $module_name = implode('/', $temp_arr);
        }

        return base_url('media' . '/' . $module_name . '/' . $src);
    }

}

if (!function_exists('filter_content')) {

    function filter_content($content = '') {
        $pattern = '(ckeditor\/kcfinder\/upload\/images)';
        return preg_replace($pattern, 'image' , $content);
    }

}

if (!function_exists('get_option_per_page')) {

    function get_option_per_page($option_selected = '') {
        $ci = &get_instance();
        $html = '';
        $data = range($ci->config->item('item', 'admin_list'), $ci->config->item('total', 'admin_list'), $ci->config->item('item', 'admin_list'));
        foreach ($data as $value) {
            $selected = '';
            if ($option_selected == $value) {
                $selected = ' selected="selected"';
            }
            $html .= "<option value='$value' $selected>" . $value . "</option>";
        }

        return $html;
    }

}

if (!function_exists('get_option_select')) {

    function get_option_select($data, $option_selected = '') {
        $html = '';
        foreach ($data as $key => $value) {
            $selected = '';
            if ($option_selected == $key) {
                $selected = ' selected="selected"';
            }
            $html .= "<option value='$key' $selected>" . $value . "</option>";
        }

        return $html;
    }

}

if (!function_exists('display_value_array')) {

    function display_value_array($data, $key = '') {
        $html = '';
        if (isset($data[$key])) {
            $html = $data[$key];
        }

        return $html;
    }

}

if (!function_exists('get_file_name_uploads_path')) {

    function get_file_name_uploads_path($path) {
        $ext = end(explode("/", $path));
        return $ext;
    }

}

if (!function_exists('get_module_path')) {

    function get_module_path($module_name = '') {
        $html = '';
        $ci = & get_instance();
        $modules_path = $ci->config->item('modules_path');
        if (trim($module_name) != '' && isset($modules_path[$module_name])) {
            $html = $modules_path[$module_name];
        }

        return $html;
    }

}

if (!function_exists('get_shops_thumbnais_default_size')) {

    function get_shops_thumbnais_default_size() {
        $html = '';
        $ci = & get_instance();
        $shops_thumbnais_sizes = $ci->config->item('shops_thumbnais_sizes');

        if (is_array($shops_thumbnais_sizes)) {
            $keys = array_keys($shops_thumbnais_sizes);
            if (isset($keys[0])) {
                $html = $keys[0];
            }
        }

        return $html;
    }

}

if (!function_exists('get_shops_thumbnais_sizes')) {

    function get_shops_thumbnais_sizes($key = '185x181') {
        $array = NULL;
        $ci = & get_instance();
        $shops_thumbnais_sizes = $ci->config->item('shops_thumbnais_sizes');
        if (trim($key) != '' && isset($shops_thumbnais_sizes[$key])) {
            $array = $shops_thumbnais_sizes[$key];
        }

        return $array;
    }

}

if (!function_exists('get_posts_thumbnais_sizes')) {

    function get_posts_thumbnais_sizes($key = '185x181') {
        $array = NULL;
        $ci = & get_instance();
        $posts_thumbnais_sizes = $ci->config->item('posts_thumbnais_sizes');
        if (trim($key) != '' && isset($posts_thumbnais_sizes[$key])) {
            $array = $posts_thumbnais_sizes[$key];
        }

        return $array;
    }

}

if (!function_exists('display_label')) {

    function display_label($content = '', $lable_type = 'success') {
        $html = '';
        $html .= "<span class='label label-$lable_type'>$content</span>";

        return $html;
    }

}

if (!function_exists('get_option_gender')) {

    function get_option_gender($option_selected = '') {
        $html = '';
        $ci = & get_instance();
        $ci->config->load('params');
        $genders = $ci->config->item('gender');
        foreach ($genders['data'] as $key => $value) {
            $selected = '';
            if ($option_selected == $key) {
                $selected = ' selected="selected"';
            }
            $html .= "<option value='$key' $selected>" . $value . "</option>";
        }

        return $html;
    }

}

if (!function_exists('get_gender')) {

    function get_gender($gender_key = '') {
        $html = '';
        $ci = & get_instance();
        $ci->config->load('params');
        $genders = $ci->config->item('gender');
        if (isset($genders['data'][$gender_key])) {
            $html .= $genders['data'][$gender_key];
        }

        return $html;
    }

}

if (!function_exists('number_format_en')) {
    /*
     * format number n000 to n,000.00
     */

    function number_format_en($number, $decimals = 2) {
        return number_format($number, $decimals, '.', ',');
    }

}

if (!function_exists('number_format_vi')) {
    /*
     * format number n000 to n.000,00
     */

    function number_format_vi($number, $decimals = 2) {
        return number_format($number, $decimals, ',', '.');
    }

}

if (!function_exists('number_format_normal')) {
    /*
     * format number n000 to n.000.00
     */

    function number_format_normal($number, $decimals = 2) {
        return number_format($number, $decimals, '.', '.');
    }

}

if (!function_exists('format_m_d_Y_strtotime')) {
    /*
     * convert date format m-d-Y or m/d/Y to Y-m-d
     */

    function format_m_d_Y_strtotime($str, $separator = '-') {
        $dates = explode($separator, $str);
        return $dates[2] . '-' . $dates[0] . '-' . $dates[1];
    }

}

if (!function_exists('format_d_m_Y_strtotime')) {
    /*
     * convert date format d-m-Y or d/m/Y to Y-m-d
     */

    function format_d_m_Y_strtotime($str, $separator = '-') {
        $dates = explode($separator, $str);
        return $dates[2] . '-' . $dates[1] . '-' . $dates[0];
    }

}

if (!function_exists('get_tag')) {

    function get_tag($tag, $xml) {
        preg_match_all('/<' . $tag . '>(.*)<\/' . $tag . '>$/imU', $xml, $match);
        return $match[1];
    }

}

if (!function_exists('get_rand_string')) {

    function get_rand_string($length = 11) {
        $str = '';
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $size = strlen($chars);
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[rand(0, $size - 1)];
        }
        return $str;
    }

}

if (!function_exists('is_bot')) {

    function is_bot() {
        /* This function will check whether the visitor is a search engine robot */

        $botlist = array("Teoma", "alexa", "froogle", "Gigabot", "inktomi",
            "looksmart", "URL_Spider_SQL", "Firefly", "NationalDirectory",
            "Ask Jeeves", "TECNOSEEK", "InfoSeek", "WebFindBot", "girafabot",
            "crawler", "www.galaxy.com", "Googlebot", "Scooter", "Slurp",
            "msnbot", "appie", "FAST", "WebBug", "Spade", "ZyBorg", "rabaz",
            "Baiduspider", "Feedfetcher-Google", "TechnoratiSnoop", "Rankivabot",
            "Mediapartners-Google", "Sogou web spider", "WebAlta Crawler", "TweetmemeBot",
            "Butterfly", "Twitturls", "Me.dium", "Twiceler");

        foreach ($botlist as $bot) {
            if (strpos($_SERVER['HTTP_USER_AGENT'], $bot) !== false)
                return true; // Is a bot
        }

        return false; // Not a bot
    }

}

if (!function_exists('str_remove_unicode_space')) {

    function str_remove_unicode_space($str = '', $removeSpace = false) {
        $result = "";

//Loại bỏ dấu tiếng việt
        $unicode = array(
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
            'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'D' => 'Đ',
            'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'I' => 'Í|Ì|Ỉ|Ĩ|Ị',
            'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        );
        foreach ($unicode as $nonUnicode => $uni) {
            $str = preg_replace("/($uni)/i", $nonUnicode, $str);
        }

//Xóa khoảng trắng
        if ($removeSpace == true) {
            $arr = explode(" ", $str);
            foreach ($arr as $k => $v) {
                $result .= $v;
            }
        } else {
            $result = $str;
        }

        return $result;
    }

}


if (!function_exists('str_standardize')) {

    function str_standardize($str = '') {
        $str = trim($str); // xóa tất cả các khoảng trắng còn thừa ở đầu và cuối chuỗi
        $str = preg_replace('/\s(?=\s)/', '', $str); // Thay thế nhiều khoảng trắng liên tiếp nhau trong chuỗi = 1 khoảng trắng duy nhất
        $str = preg_replace('/[\n\r\t]/', ' ', $str); // Thay thế những kí tự đặc biệt: xuống dòng, tab = khoảng trắng

        return $str;
    }

}

if (!function_exists('replace_special_url')) {

    function replace_special_url($str = '') {
        $pattern = '/[^\w\d\s]/';
//$str = str_remove_unicode_space($str);
        $str = preg_replace($pattern, "-", $str);
        $str = str_replace(" ", "-", $str);
        $str = preg_replace('/\-(?=\-)/', '', $str);

        return $str;
    }

}

if (!function_exists('show_alert_success')) {

    function show_alert_success($str = '') {
        $html = '';
        if (trim($str) != '') {
            $html .= '
              <div class="alert alert-dismissable alert-success">
                <button data-dismiss="alert" class="close" type="button">×</button>
                <strong>' . $str . '</strong>
              </div>';
        }
        return $html;
    }

}

if (!function_exists('show_alert_danger')) {

    function show_alert_danger($str = '') {
        $html = '';
        if (trim($str) != '') {
            $html .= '
              <div class="alert alert-dismissable alert-danger">
                <button data-dismiss="alert" class="close" type="button">×</button>
                <strong>' . $str . '</strong>
              </div>';
        }
        return $html;
    }

}

if (!function_exists('show_alert_warning')) {

    function show_alert_warning($str = '') {
        $html = '';
        if (trim($str) != '') {
            $html .= '
              <div class="alert alert-dismissable alert-warning">
                <button data-dismiss="alert" class="close" type="button">×</button>
                <h4>' . $str . '</h4>
              </div>';
        }
        return $html;
    }

}

if (!function_exists('display_date')) {

    function display_date($timestamp = 0, $full = FALSE) {
        $html = '';
        if($full){
            $html .= date('H:i:s d/m/Y', $timestamp);
        }else{
            $html .= date('H:i d/m/Y', $timestamp);
        }

        return $html;
    }

}

if (!function_exists('get_day_of_week_vi')) {

    function get_day_of_week_vi($strtotime = 0) {
        $day = date('w', $strtotime);
        switch ($day) {
            case 0:
                $thu = "Chủ nhật";
                break;
            case 1:
                $thu = "Thứ hai";
                break;
            case 2:
                $thu = "Thứ ba";
                break;
            case 3:
                $thu = "Thứ tư";
                break;
            case 4:
                $thu = "Thứ năm";
                break;
            case 5:
                $thu = "Thứ sáu";
                break;
            case 6:
                $thu = "Thứ bảy";
                break;
            default: $thu = "";
                break;
        }
        return $thu;
    }

}

if (!function_exists('php_truncate')) {

    function php_truncate($text, $length) {
        $length = abs((int) $length);
        if (mb_strlen($text, 'UTF-8') > $length) {
            $text = preg_replace("/^(.{1,$length})(\s.*|$)/s", '\\1...', $text);
        }
        return($text);
    }

}

if (!function_exists('formatRiceVND')) {

    function formatRiceVND($price = 0) {
        $symbol = ' VND';
        $symbol_thousand = '.';
        $decimal_place = 0;
        $number = number_format($price, $decimal_place, '', $symbol_thousand);
        return $number . $symbol;
    }

}

if (!function_exists('formatRiceEn')) {

    function formatRiceEn($price = 0) {
        $symbol_thousand = ',';
        $decimal_place = 2;
        $number = number_format($price, $decimal_place, '.', $symbol_thousand);
        return $number;
    }

}

if (!function_exists('formatRice')) {

    function formatRice($price = 0) {
        /*
        $symbol_thousand = '.';
        $decimal_place = 0;
        $number = number_format($price, $decimal_place, '', $symbol_thousand);
        */

        $symbol_thousand = ',';
        $decimal_place = 2;
        $number = number_format($price, $decimal_place, '.', $symbol_thousand);
        return $number;
    }

}

if (!function_exists('alias')) {

    function alias($str = '', $removeSpace = false) {
        $result = "";

//Loại bỏ dấu tiếng việt
        $unicode = array(
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
            'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'D' => 'Đ',
            'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'I' => 'Í|Ì|Ỉ|Ĩ|Ị',
            'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        );
        foreach ($unicode as $nonUnicode => $uni) {
            $str = preg_replace("/($uni)/i", $nonUnicode, $str);
        }

//Xóa khoảng trắng
        if ($removeSpace == true) {
            $arr = explode(" ", $str);
            foreach ($arr as $k => $v)
                $result .= $v;
        } else {
            $result = $str;
        }

        $pattern = '/[^\w\d\s]/';
        $str = $result;
        $str = preg_replace($pattern, "-", $str);
        $result = str_replace(" ", "-", $str);

//$str = str_only_character($str);
//return $str;


        $character = '-';
        $str = $result;
        $result = '';

        $arr_Temps = explode($character, $str);
        foreach ($arr_Temps as $key => $value) {
            if ($value == '') {
                unset($arr_Temps[$key]);
            }
        }

        $end = count($arr_Temps) - 1;
        $i = 0;

        foreach ($arr_Temps as $key => $value) {
            if ($value != '') {
                $result .= $value;
                if ($i != $end) {
                    $result .= $character;
                }
            }
            $i++;
        }

        return $result;
    }

}

if (!function_exists('get_product_discounts')) {

    function get_product_discounts($product_price = 0, $product_sales_price = 0) {
        return ($product_sales_price > 0 ? $product_sales_price : $product_price);
    }

}

if (!function_exists('get_promotion_price')) {

	function get_promotion_price($product_price = 0, $product_promotion_price = 0) {
		if($product_promotion_price > 0 && $product_promotion_price < $product_price){
			$price = $product_promotion_price;
		}else{
			$price = $product_price;
		}
		return $price;
	}

}

if (!function_exists('get_promotion_price_F0')) {

    function get_promotion_price_F0($product_price = 0, $F0 = 0) {
        return $product_price - $F0;
    }

}

if (!function_exists('convert_to_lowercase')) {
	function convert_to_lowercase($word = '') {
		return mb_strtolower($word, 'UTF-8');
	}
}

if (!function_exists('convert_to_uppercase')) {
	function convert_to_uppercase($word = '') {
		return mb_strtoupper($word, 'UTF-8');
	}
}

if (!function_exists('display_option_select')) {

    function display_option_select($data, $option_value = 'id', $option_name = 'name', $option_selected = 0) {
        $html = '';

        if (is_array($data) && !empty($data)) {
            foreach ($data as $value) {
                $selected = '';
                if (is_array($option_selected) && in_array($value[$option_value], $option_selected)) {
                    $selected = ' selected="selected"';
                } elseif ($value[$option_value] == $option_selected) {
                    $selected = ' selected="selected"';
                }
                $html .= "\n";
                $html .= '<option' . $selected . ' value="' . $value[$option_value] . '"' . '>' . $value[$option_name] . '</option>';
            }
        }

        return $html;
    }

}

function parse_id_cart($str_id = '', $all = false) {
    $str = explode('_', $str_id);
    if ($all) {
        $data = array();
        $data['product_id'] = isset($str[0]) ? (int) $str[0] : 0;
        $data['unit_id'] = isset($str[1]) ? (int) $str[1] : 0;
    } else {
        $data = 0;
        if (isset($str[0])) {
            $data = (int) $str[0];
        }
    }

    return $data;
}

//Lê Văn Nhàn
/* End of file master_helper.php */
/* Location: ./application/helpers/master_helper.php */