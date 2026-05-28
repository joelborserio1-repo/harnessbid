<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- Class: SUBSCRIBE
class subscribe {
	function __construct($subscribe_price=0) {
		global $db,$class_user;
		$this->db = $db;

		$this->SUBSCRIBE_PRICE = $subscribe_price;
		$this->user = $class_user;
		$this->VALID_override = array("account"=>[],
			"user"=>[],
			"login"=>[],
			"quote"=>['url'=>['Action'=>'print']],
			"sale"=>['url'=>['Action'=>'print']],
			"project"=>['url'=>['Action'=>'print']],
			"form_post"=>['url'=>['Action'=>'submit']]
		);

		$this->OPTION = [
			'opt_sale',
			'opt_staff',
			'opt_procedure',
			'opt_product',
			'opt_task',
			'opt_project',
			'opt_mail',
			'opt_client',
			'opt_quote',
			'opt_sell',
			'opt_book',
			'opt_schedule',
			'opt_website',
			'opt_support',
			'opt_renew',
			'opt_form_post',
			'opt_sale_po',
			'opt_production',
		];

		//-- Check Authorised
	}
	function session_var($uid) {
		global $class_user,$class_cache;

		$user_data = $class_user->user_data(array("id"=>$uid,"first"=>true));
		$user_meta = $class_user->user_meta($user_data['id']);

		if($user_meta['plan_id']['value']>0) {

			$plan_id = $user_meta['plan_id']['value'];
			$plan_data = $this->plan_data($plan_id);

			foreach(explode(",",$plan_data['config_module']) as $item) {
				$_SESSION['zl_user'][$item] = 1;
			}

			//Use overrides
			foreach($this->OPTION as $opt) {
				if($user_meta[$opt]['value']>0) {
					$_SESSION['zl_user'][$opt] = 1;
				}
			}

			//Plan is BUSINESS / ECOM
			if($plan_data['config_braintree_prefix']=='web_business') {
				$_SESSION['zl_user']['_plan']['website_type'] = 'ZULUCMS';
			} else {
				$_SESSION['zl_user']['_plan']['website_type'] = 'ZULUSHP';
			}
		} else {
			//Use Default
			foreach($this->OPTION as $opt) {
				$_SESSION['zl_user'][$opt] = $user_meta[$opt]['value'];
			}
		}

		$class_cache->dump('user_meta');
		return;
	}
	function valid_access() {
		global $class_user;

		$valid = false;
		$meta = $class_user->authorised->_meta;
		$user_valid = (($meta['plan_expiry']+86400)>time()||$meta['plan_free']>0?true:false);

		if(array_key_exists(PAGE_file,$this->VALID_override)||$user_valid) {
			if($user_valid&&!$class_user->authorised->public) {
				$valid = true;
			}
			if(count($this->VALID_override[PAGE_file]['url'])>0) {
				foreach($this->VALID_override[PAGE_file]['url'] as $uri_var=>$uri_val) {
					if($_GET[$uri_var]==$uri_val) {
						$valid = true;
					}
				}
			} else {
				$valid = true;
			}
			return ($valid?true:false);
		} else {
			return false;
		}
	}
	function status($id=NULL) {
		global $class_user,$zulu;

		if($id>0) {
			$user_id = $id;
		} else {
			$user_id = $class_user->authorised->id;
		}
		//$user_data = $this->user->user_data(array('id'=>$user_id));
		$meta = $zulu->meta_array($class_user->user_meta($user_id));

		$return = array();
		if($meta['plan_expiry']>0) {
			if($meta['plan_expiry']<time()) {
				$return['active'] = false;
			} else {
				$return['active'] = true;
			}
			$return['autobilling'] = ($meta['subscribe_braintree_id']!=NULL?true:false);
			$return['expire_time'] = $meta['plan_expiry'];
			$return['setup'] = true;
		} else {
			$return['setup'] = false;
		}
		if($meta['plan_id']>0) {
			$plan_data = $this->plan_data($meta['plan_id'],$meta['plan_period']."mo");
			$return['plan'] = $plan_data;
		}
		$return['trial_used'] = ($meta['subscribe_trial']>0?true:false);

		return $return;
	}
	function plan_cost_field($code) {
		switch($code) {
			case '1mo':
			$field = 'price';
			break;
			case '12mo':
			$field = 'price_year';
			break;
			default:
			$field = 'price';
			break;
		}
		return $field;
	}
	function plan_data($id,$period=NULL) {
		$data = zulu::table_data("user_plan",$id);
		if($period!=NULL) {
			$period_number = str_replace("mo","",$period);
			$price_base = $data[$this->plan_cost_field($period)];
			$data['price'] = array(
				'monthly'	=>	$price_base,
				'period'	=>	sprintf("%.2f",($price_base*$period_number)),
				'yearly'	=>	sprintf("%.2f",($price_base*12))
			);
		}
		return $data;
	}
	function set_expiry($id,$timestamp) {
		global $zulu;
		$zulu->meta_update("user",$id,"plan_expiry",$timestamp);
		return true;
	}
	function delivery_next($config=array()) { //next delivery
		if($config['subscription']>0) {
			$subscription_id = $config['subscription'];
			$subscription_data = $this->subscription_data($subscription_id);
			$sh_subscription_slot = $this->delivery_slot($subscription_data['date_start']);
			if($sh_subscription_slot['time_cutoff']>0) {
				$sql_where[] = "time_cutoff >= ".$sh_subscription_slot['time_cutoff'];
			}
		}
		$sql_where[] = "time_cutoff > ".time(); //add the current time to cutoff
		$data_slot = table_data("sh_subscription_slot",0,array("where"=>$sql_where,"sort"=>"time_cutoff ASC","limit"=>1));
		$data_slot = $data_slot[0];
		$return['id'] = $data_slot['id'];
		$return['process'] = $data_slot['time_process'];
		$return['cutoff'] = $data_slot['time_cutoff'];
		$return['data'] = $data_slot;

		return $return;
	}
	function delivery_current($config=array()) { //next delivery
		$sql_where[] = "time_cutoff < ".time(); //add the current time to cutoff
		$data_slot = table_data("sh_subscription_slot",0,array("where"=>$sql_where,"sort"=>"time_cutoff DESC","limit"=>1));
		$data_slot = $data_slot[0];
		$return['id'] = $data_slot['id'];
		$return['process'] = $data_slot['time_process'];
		$return['cutoff'] = $data_slot['time_cutoff'];
		$return['data'] = $data_slot;

		return $return;
	}
	function delivery_slot($id=0,$config) { //delivery data
		if($config['new']) {
			$td_config['where'][] = "time_cutoff > ".time();
		}
		return table_data("sh_subscription_slot",$id,$td_config);
	}
	function region_data($id=0) { //region data
		return table_data("sh_subscription_region",$id,array('sort'=>'region ASC'));
	}
	function subscription_data($id=0) { //subscription data
		return table_data("sh_subscription",$id);
	}
	function subscription_status($id) {
		$subscription_data = $this->subscription_data($id);
		if($this->subscription_count($id,array(1))>0&&$subscription_data['status']==1) {
			$array['css'] = 'green';
			$array['label'] = 'Active';
		} elseif($subscription_data['status']==0) {
			$array['css'] = 'red';
			$array['label'] = 'Cancelled';
		} elseif($subscription_data['payment_connect']<1) {
			$array['css'] = 'red';
			$array['label'] = 'Setup Payment';
		} else {
			$array['css'] = 'grey';
			$array['label'] = 'Pending';
		}
		return $array;
	}
	function subscription_next_selected($id) {
		$slot_data = $this->delivery_next(array('subscription'=>$id));
		$next_slot_id = $slot_data['id'];
		$slot_reserve_check = $this->db->query("SELECT id FROM subscription_reserve WHERE subscription = '$id' AND slot = '$next_slot_id'");
		return ($slot_reserve_check->num_rows>0?true:false);
	}
	function subscription_next($id) {
		$slot_data = $this->delivery_next(array('subscription'=>$id));
		return $slot_data;
	}
	function subscription_count($id,$status=array()) { //total number of processes
		$query = $this->db->query("SELECT period,id FROM subscription_transaction WHERE ".(count($status)>0?"status IN (".implode(",",$status).") AND ":NULL)." subscription = '$id' ORDER BY period DESC LIMIT 1");
		return $query->num_rows;
	}
	function member_count($id,$status=array()) { //total number of processes
		$query = $this->db->query("SELECT id FROM subscription WHERE ".(count($status)>0?"status IN (".implode(",",$status).") AND ":NULL)." member = '$id'");
		return $query->num_rows;
	}
	function subscription_clear($id) {
		$this->db->query("DELETE FROM subscription_reserve WHERE subscription = '$id'");
		return true;
	}
	function subscription_last($id,$status=array()) { //last processed subscription
		$subscribe_data = table_data("sh_subscription",$id);
		$query = $this->db->query("SELECT period,id FROM subscription_transaction WHERE ".(count($status)>0?"status = (".implode(",",$status).") AND ":NULL)." status = '', subscription = '$id' ORDER BY period DESC LIMIT 1");
		if($query->num_rows>0) {
			$value = $row['period'];
		} else {
			$delivery_slot_data = $this->delivery_slot($subscribe_data['date_start']);
			$value = $delivery_slot_data['time_process'];
		}
		return $value;
	}
	function period_convert($input) { //conver periods
		//$split_input = str_split($input,4);
		//$time = mktime(0,0,0,$split_input[1],1,$split_input[0]);
		return date('D, jS M',$input);
	}
	function slot_token_id($token=NULL,$id=0) {
		if($token!=NULL) {
			$slot_data = table_data("sh_subscription_slot",0,array('where'=>array('token = "'.$token.'"')));
			$slot_data = $slot_data[0];
			$value = $slot_data['id'];
		} else {
			$slot_data = table_data("sh_subscription_slot",$id);
			$value = $slot_data['token'];
		}
		if($value==NULL) {
			$slot_data = $this->subscription_next();
			$value = $slot_data['data']['id'];
		}
		return $value;
	}
	function subscribe_data($config=array()) {
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);
		if($config['member']!=NULL) {
			$sql_config['where'][] = "member = '".$config['member']."'";
		}
		if($config['token']!=NULL) {
			$sql_config['where'][] = "token = '".$config['token']."'";
		}
		if($config['live']) {
			$sql_config['where'][] = "status = 1";
		}
		if($config['first']) {
			$sql_config['first'] = true;
		}
		$data = zulu::table_data("sh_subscription",$id,$sql_config);
		return $data;
	}
	function next_renew($period=NULL) {
		switch($period) {
			case '1mo':
			$stt = "+1 Month";
			break;
			case '3mo':
			$stt = "+3 Month";
			break;
			case '6mo':
			$stt = "+6 Month";
			break;
			case '12mo':
			$stt = "+12 Month";
			break;
			default:
			$stt = "+1 Month";
			break;
		}

		$next_month = strtotime(date("d-m-Y",strtotime($stt)));
		return $next_month;//(date("d-m-Y h:ia",$next_month));
	}

	//MEMBER ACCOUNT UPGRADE
	function apply_plan($member,$plan,$period,$trial=false) {
		global $zulu,$class_user,$class_subscribe;

		if(!$trial||($trial&&($class_user->authorised->_meta['subscribe_trial']<=0||$class_user->authorised->_meta['subscribe_trial']==''))) {
			$row_PLAN = $zulu->table_data('user_plan',$plan);
			$row_MEM = $class_user->user_data(array('id'=>$member));

			if($trial) {
				$period = 1;
			}

			$name = stripslashes($row_MEM['name_first']);
			$email = $row_MEM['email'];

			$referer = $row_MEM['account_referer'];

			$expiry = strtotime("+".$period." months",strtotime(date('d-m-Y')));
			$plan_cost = $row_PLAN[$this->plan_cost_field($period.'mo')];

			$total_cost = $zulu->dollar($plan_cost*$period);

			//apply
			if(!$trial) {
				$class_user->account_adjust($member,$zulu->serial(),$plan,'user_plan',"Account Plan Purchase","Payment for Plan ({$row_PLAN['name']})",($total_cost*-1));
				$subject = "Account Subscription Purchased";
			} else {
				$total_cost = 0;
				$subject = "Account Trial Activated";
				$zulu->meta_update("user",$member,"subscribe_trial",1);
			}

			//update meta
			$zulu->meta_update("user",$member,"plan_id",$plan);
			$zulu->meta_update("user",$member,"plan_period",$period);
			$zulu->meta_update("user",$member,"plan_expiry",$expiry);
			$zulu->meta_update("user",$member,"plan_cancelled",0);
			$zulu->meta_update("user",$member,"plan_fee",$plan_cost);

			//email update
			$to = $email;
			$mess = "<p>Hello {$name},</p><p>Thanks for using ".MAIN_name."!</p>";
			$mess .= "
			<div class=\"info account\">
				<h2>Plan: ".stripslashes($row_PLAN['name'])."</h2>
				<p>Total: <span>$".$zulu->dollar($total_cost,1)."</span> Period: <span>".$period." Months</span> Expiry: <span>".date('d/m/Y',$expiry)."</span></p>
			</div>";
			$mess .= "<hr><p>If you wish to <i>change</i> your plan before expiry, you can do so via the subscriptions page. We do not offer any refunds for plans with under 3 months subscription remaining.</i></p>";

			$zulu->mail_send($to,$subject,$mess);

			//email referer if first referal
			if($row_MEM['account_referer_applied']<1&&$referer>0) {
				$query_REF_MEM = $this->db->query("SELECT * FROM sh_member WHERE mid = '$referer' AND status = 1 AND account_expire > ".time());
				$row_REF_MEM = $query_REF_MEM->fetch_array();
				$newexp = strtotime('+1 Month',$row_REF_MEM['account_expire']);

				member_credit($referer,$zulu->serial(),0,'member_account_referer',"Account Plan Extended","Plan extended from referral '".$row_MEM['fname']." ".$row_MEM['lname']."'.",0);

				$this->db->query("UPDATE sh_member SET account_expire = '".$newexp."' WHERE mid = '$referer'"); //upd referer
				$this->db->query("UPDATE sh_member SET account_referer_applied = 1 WHERE mid = '$member'"); //upd mem

				//email update
				$to = $row_REF_MEM['email'];
				$subject = "Account Subscription Extended";
				$mess = "<p>Howdy ".$row_REF_MEM['fname']." ".$row_REF_MEM['lname'].",</p><p>Cheers for your referral! You have recieved a FREE 1 month extension of your account plan for your contribution to helping ".MAIN_name." grow!</p>";
				$mess .= "
				<div class=\"info account\">
					<h2><a href=\"".$main_URL."members/credit.php\">Plan Extended: 1 Month</a></h2>
					<p>Total: <span>FREE</span> Period: <span>1 Month</span> Expiry: <span>".date('d/m/Y',$newexp)."</span></p>
				</div>";
				$mess .= "<hr><p>If you wish to <i>change</i> your plan before expiry, please contact us. We do not offer any refunds for plans with under 3 months subscription remaining.</i></p>";

				$zulu->mail_send($to,$subject,$mess);
			}
			$this->session_var($member);

			return true;
		} else {
			return false;
		}
	}
	function plan_array() {
		$plan_data = zulu::table_data('user_plan',0,array('sort'=>"price_year ASC"));
		foreach($plan_data as $row_PLAN) {
			$arr[$row_PLAN['id']] = $row_PLAN['name'];
		}
		return $arr;
	}
	function plan_html($form=1,$trial_ovr=false,$trial_period=1,$config=[]) {
		global $class_user;

		$sql_where = [];
		if($config['master_mode']!=NULL) {
			$sql_where[] = "master_mode = '".$config['master_mode']."'";
		}

		//User
		if($class_user->authorised->_meta['subscribe_gst_exempt']>0) {
			$price_suffix = "Prices are in NZD and exempt from GST";
		} else {
			$price_suffix = "Prices are in NZD and exclude GST";
		}

		//Plans
		$plan_data = zulu::table_data('user_plan',0,array('where'=>$sql_where,'sort'=>"price_year ASC"));
		$first = ' first';

		$array_plan = explode('-',$_GET['plan']);

		foreach($plan_data as $row_PLAN) {

		$trial_ovr_this = $trial_ovr;

		$plan_id = $row_PLAN['id'];
		$price_1mo = $row_PLAN['price'];
		$price_12mo = $row_PLAN['price_year'];
		$name = stripslashes($row_PLAN['name']);
		$description = stripslashes($row_PLAN['description']);
		$class = ' '.$row_PLAN['config_css_class'];

		$period = (strstr($array_plan[1],'1')?1:0);
		$period = (strstr($array_plan[1],'12')?12:$period);

		if($plan_id==$array_plan[0]) {
			switch($array_plan[1]) {
				case 'fee_1mo':
				$selected_fee = $price_1mo;
				break;
				case 'fee_12mo':
				$selected_fee = $price_12mo;
				break;
			}
			$total_pay = $selected_fee*$period;
			$plan_select_id = $plan_id;
			$plan_select_period = $period;
		}

    	unset($spec_html);
		$feature_array = explode("\n",$row_PLAN['features']);
		foreach($feature_array as $row) {
			$spec_html .= "<li>".$row."</li>";
		}

		$plan .= "
				<div class=\"col{$first}{$class}\">
					<div class=\"plan-wrap\">
					<h2>{$name}</h2>
					<div class=\"inner\">
						<p class=\"desc\">{$description}</p>

						<h4><span class=\"far fa-clock\"></span> Select renewal period:</h4>
						<ul class=\"options\">
							".(!$trial_ovr_this?NULL:"<li><input type=\"radio\" data-period=\"1\" data-total=\"0\" name=\"plan\" value=\"{$plan_id}-fee_trial\" /> 30 Day Trial <span class=\"price red\"><span class=\"fas fa-star\"></span> FREE</span></li>")."

							<li>".($form>0?"<input type=\"radio\" data-period=\"1\" data-total=\"{$price_1mo}\" name=\"plan\" value=\"{$plan_id}-fee_1mo\" /> ":"<a href=\"{$main_URL_REL}members/register.php?Plan={$plan_id}-fee_1mo\" class=\"signup\">Sign Up</a>")."1 Month
							".($price_1mo>0?"
							<span class=\"price\">\${$price_1mo}p/m</span>":
						   "<span class=\"price red\">FREE</span>")
							."
							</li>
							<li>".($form>0?"<input type=\"radio\" name=\"plan\" data-period=\"12\" data-total=\"{$price_12mo}\" value=\"{$plan_id}-fee_12mo\" /> ":"<a href=\"{$main_URL_REL}members/register.php?Plan={$plan_id}-fee_12mo\" class=\"signup\">Sign Up</a>")."12 Month
							".($price_12mo>0?"
							<span class=\"price\">\${$price_12mo}p/m</span>":
						   "<span class=\"price red\">FREE</span>")
							."</li>
						</ul>

						<h4><span class=\"fas fa-check\"></span> Features:</h4>
						<ul class=\"bullet-list\">
							".$spec_html."
						</ul>

						</div>
					</div>
				</div>";

			unset($first);
		}

		//Payment Data
		if($total_pay>0) {
			$custom = array(
			'member'	=>		$_SESSION['SH_Mid'],
			'plan_id'	=>		$array_plan[0],
			'plan_period'	=>	$period
			);
			$this->data->plan_custom = $custom;
		}
		$this->data->plan_total = $total_pay;
		return "<div class=\"plan-subscription coltable\">".$plan."</div>
				<hr />
				<p class=\"disclaimer\">Please Note: <i class=\"fas fa-check\"></i> Cancel anytime <i class=\"fas fa-check\"></i> No minimum term <i class=\"fas fa-info-circle\"></i> {$price_suffix}</p>";
	}

}
