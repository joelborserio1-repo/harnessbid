<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- DEFINITIONS
define(PAGE_file,'login');
define(PAGE_name,'Homepage');
$TPL_body_ovr = 'body-login.php';
$zulu->template->body_class[] = 'bg-fill';

//-- LOGIN FORM
if(PAGE_action==NULL) {
	$zulu->nav->title = "Sign in to ".MAIN_name;
	$form_login = new form;
	
	// $_GET Access
	if($_GET['Do']=='signin') {
		$key = $db->escape_string($_GET['Access']);
		$username = $db->escape_string($_GET['Username']);
		
		$usr_lookup = $class_user->user_data(['username'=>$username,'first'=>true]);

		if($usr_lookup['id']>0) {
			$access_check = md5($usr_lookup['username'].$usr_lookup['password']);
			if($access_check==$key) {
				$login_ovr = true;
				$_POST['password'] = $usr_lookup['password'];
				$_POST['username'] = $usr_lookup['username'];
			}
		}
	}
	
	//-- Lock out
	if($_SESSION['LOGIN_FAIL_TIMEOUT'] > time()) {
		$zulu->notification_set("You have exceeded your login attempts and have been temporarily locked out.",2);	
	}
	
	//-- Post Form
	if($_POST) {
		if(isset($_POST['forgot'])) {
			$data = array("username"=>$db->escape_string($_POST['username']));
			$login_post = $class_user->user_forgot($data);
			
			if($login_post['success']) {
				$zulu->notification_set($login_post['reason'],1);
			} else {
				$zulu->notification_set($login_post['reason'],2);	
			}
		} elseif($_SESSION['LOGIN_FAIL_TIMEOUT'] > time()) {
			$_SESSION['LOGIN_FAIL'] += 1;
			$result = $class_user->login_fail_check();
			$zulu->notification_set($result['reason'],2);
		} else {
			$data = array("username"=>$db->escape_string($_POST['username']),"password"=>$db->escape_string($_POST['password']));
			if($_POST['method']=='pin') {
				if($form_login->validate(array('pin'))) {
					$zulu->notification_set("Please specify a PIN.",2);
					$stop = true;
				}
				$sp = explode("%",$_COOKIE[user::ZL_REM]);
				$data['pin'] = $db->escape_string($_POST['pin']);
				$data['username'] = $db->escape_string($sp[0]);
				$data['password_token'] = $db->escape_string($sp[1]);
			} else {
				if($_POST['remember']>0&&$form_login->validate(array('pin','pin_c'))) {
					$zulu->notification_set("When using 'remember me', you must specify a quick access PIN. This is 4 characters long and is required to prevent unauthorised access.",2);
					$stop = true;
				}
				if($_POST['remember']>0&&strlen($_POST['pin'])<4) {
					$zulu->notification_set("Please ensure your PIN is 4 characters long.",2);
					$stop = true;
				}
				if($_POST['remember']>0&&$_POST['pin']!=$_POST['pin_c']) {
					$zulu->notification_set("Please ensure your PIN numbers match.",2);
					$stop = true;
				}
				if($login_ovr) {
					$data['password_bypass'] = true;
				}
				if($form_login->validate(array('username','password'))) {
					$zulu->notification_set("Please specify a username and password.",2);
					$stop = true;
				}
			}
			
			if(!$stop) {
				$login_post = $class_user->user_login($data);
				
				if($login_post['success']) {
					
					//Remember me?
					if($_POST['remember']>0) {
						$class_user->user_login_remember($login_post['user']['username'],$login_post['user']['password']);
						$class_user->pin_set($db->escape_string($_POST['pin']),$login_post['user']['id']);
					}
					
					//Show First text?
					if($_GET['First']>0) {
						$login_post['reason'] = "Welcome! Your <b>username and password have been emailed to you</b>. You've been automatically logged in, feel free to change your password under the 'Users' tab on the bottom left.";
					}
					
					//Dismiss Welcome pages?
					$user_meta = $class_user->user_meta($login_post['user']['id']);
					
					if(!isset($user_meta['welcome_dismiss'])&&$user_meta['plan_master_mode']['value']=='main') {
						$_GET['location'] = $zulu->link_page('account',array('query'=>array('Action'=>'welcome')));
					} elseif(!isset($user_meta['welcome_dismiss'])&&$user_meta['plan_master_mode']['value']=='web') {
						$_GET['location'] = $zulu->link_page('website',array('query'=>array('Action'=>'get_started')));
					}
					
					$zulu->notification_set($login_post['reason'],1);
					if($_GET['location']!=NULL) {
						header("Location: ".urldecode($_GET['location']));
					} else {
						header("Location: ".$zulu->link_page("index"));
					}
					exit;	
				} else {
					$_SESSION['LOGIN_FAIL'] += 1;
					if($_SESSION['LOGIN_FAIL'] >= 5) {
						$result = $class_user->login_fail_check();
						$login_post['reason'] = $result['reason'];
					}
					$zulu->notification_set($login_post['reason'],2);	
				}
			}
		}
	}
	
	if($class_user->authorised->remember) {
		define(TOGGLE_PIN,true);	
	} else {
		define(TOGGLE_PIN,false);
	}
    
    $zulu->template->body_class[] = 'page-login';
	
	//JS
	$zulu->template->jquery[] = "
	$(\"input[name='pin']\").keyup(function() {
		var value = $(this).val();
		if(value.length>=4) {
			$(\"input[name='pin_c']\").focus();
		}
	});
	$(\"input[name='pin_c']\").keyup(function() {
		var value = $(this).val();
		if(value.length>=4) {
			$(\"button[name='login']\").focus();
		}
	});
	$(\"input[name='remember']\").change(function() {
		remember_fields();
	});
	function remember_fields() {
		if($(\"input[name='remember']\").is(\":checked\")) {
			$(\".field-pin\").show();
		} else {
			$(\".field-pin\").hide();
		}
		return false;
	}
	remember_fields();
	
	";
}
if(PAGE_action=='logout') {
	$class_user->user_logout();
	$zulu->notification_set($login_post['reason'],2);
	header("Location: ".$zulu->link_page("login"));
	exit;
}
if(PAGE_action=='user_forget') {
	$class_user->user_login_remember_clear();
	header("Location: ".$zulu->link_page("login"));
	exit;
}