<?php
// @author: Santiago Zavala
// @date: 6/11/2008
class RegisterController extends Zend_Controller_Action{
	
	function init()	{
		include('./application/init.php');
	}
	
	
	function indexAction(){
		$this->view->title = "Register";

		$email = strip_tags($_POST["email"]);
		$password = strip_tags($_POST["password"]);
		$username = strip_tags($_POST["userName"]);
		if(!empty($_POST)){
			$user = new User();
			$data = array(
							'username'      =>  str_replace(" ", "_", $username),
							'password'      =>  $password,
							'email'      => $email,
							'join_date'      => date("Y-m-d H:i:s"));
			$error .= $user->insert_valid($data);
			$this->view->error = $error;
			if($error == ""){
				$_POST["login_email"]=  $email;
				$_POST["login_password"]=  $password;
				$this->_forward('index','login');
			}				
		}
		$this->view->username = $username;
		$this->view->email = $email;
	}
}