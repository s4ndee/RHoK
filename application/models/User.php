<?php
class User extends Zend_Db_Table{
protected $_name = 'user';

	function insert_valid($data){
	 	$error = $this->valid($data);
		if ($error == ""){
			$data["password"] = md5($data["password"]);
	 		$userId = $this->insert($data);
	 		return "";
	 	}
	 	return $error;
	}

	function valid($data){
		require_once 'Zend/Validate/StringLength.php';
		$error = "";
		$validator = new Zend_Validate_StringLength(1, 100);
		if (!$validator->isValid($data["username"])) { 
			$error .= "<br>".$data["username"]." ".INVALID_USERNAME;
		}	
		$validator = new Zend_Validate_StringLength(1,30);
		if (!$validator->isValid($data["password"])) { 
			$error .= "<br>". INVALID_PASSWORD;
		}	
		require_once 'Zend/Validate/EmailAddress.php';
		
		$validator = new Zend_Validate_EmailAddress();
		$email = $data["email"];
		if (!$validator->isValid($email)) {		    //
			$error .= "<br>".$data["email"]." ". INVALID_EMAIL;
		}
		$where  = $this->getAdapter()->quoteInto('email = ?', $data["email"]);			
		$order  = 'id';
		$row = $this->fetchRow($where, $order);
		if(is_object($row)){
			$error .= "<br>". $row->email." ".USED_EMAIL;			
		}
		$where  = $this->getAdapter()->quoteInto('username = ?', $data["username"]);			
		$row = $this->fetchRow($where);
		if(is_object($row)){
			$error .= "<br>". $row->username." ".USED_USERNAME;			
		}
		return $error;
	}

	function validLogin($username, $password){
		$where[] = $this->getAdapter()->quoteInto('username = ?', $username);	
		$where[] = $this->getAdapter()->quoteInto('password = ?', md5($password)); 		
		$row = $this->fetchRow($where, 'id');
		if(is_object($row)){
			return ""; 			
		}
		return INCORRECT_LOGIN; 				
	}
	
	public function count() {
        $select = $this->select();
        $select->from($this->_name,'COUNT(*) AS num');
        return $this->fetchRow($select)->num;
    }
}