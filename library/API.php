<?php
	function antiXss($text){
		return strip_tags($text);	
	}

	function checkRememberMe(){
		if(isset($_COOKIE['userId']) and isset($_COOKIE['secretHash'])){
			$userId = intval($_COOKIE['userId']);
			if($userId > 0){
				$secretHash = $_COOKIE['secretHash'];
				Zend_Loader::loadClass('User');
				$user = new User();
				$user = $user->fetchRow($user->select()->where('id = ?', $userId));
				if($secretHash == md5("ThisisSalt".$user->id."wichlesssalt".$user->join_date."wichlesssalt".$user->email."doeqkjdsjkah231".$user->join_date."dsadsa")){
					$loginEmail = $user->email;
					$loginPassword = $user->password;
					Zend_Auth::getInstance()->clearIdentity();
					Zend_Loader::loadClass('Zend_Filter_StripTags');
					$f = new Zend_Filter_StripTags();
					$email = $f->filter($loginEmail);
					$password = $f->filter($loginPassword);

					Zend_Loader::loadClass('Zend_Auth_Adapter_DbTable');
					$db = Zend_Registry::get('db');
					$authAdapter = new Zend_Auth_Adapter_DbTable($db);
					$authAdapter->setTableName('user');
					$authAdapter->setIdentityColumn('email');
					$authAdapter->setCredentialColumn('password');

					$authAdapter->setIdentity($email);
					$authAdapter->setCredential($password);

					$auth = Zend_Auth::getInstance();
					$result = $auth->authenticate($authAdapter);
					if ($result->isValid()) {
						$data = $authAdapter->getResultRowObject(null,'password');
						$auth->getStorage()->write($data);
						$maxSessionTime=60*60*24*30*6; // six months
						Zend_Session::rememberMe($maxSessionTime);
					}
				}
			}
		}
	}

	function topTags(){
		Zend_Loader::loadClass('Tag');
		Zend_Loader::loadClass('Message');
		Zend_Loader::loadClass('TagMessageRel');		
		$db = Zend_Registry::get('db');	
		$select = "SELECT t.tag, COUNT(r.id) as num
					FROM tag as t, tag_message_rel as r, message as m
					WHERE  
						m.id = r.message_id 
						AND t.id = r.tag_id 
						AND m.created_at > '".date("Y-m-d H:i:s",time()-60*60*24)."'
					GROUP BY t.id ORDER BY COUNT(r.id) DESC LIMIT 0,10";
		return $db->fetchAll($select);
	}
	
	function secondsToString($seconds){
		$minutes = $seconds/60;
		if($minutes < 0){
			return "Less than a minute";
		}
		if($minutes < 60){
			return number_format($minutes,2)." minutes ago";
		}
		$hours = $minutes/60;
		if($hours < 24){
			return number_format($hours,2)." hours ago";
		}
		$days = $hours/24;
		return number_format($days,2)." days ago";
	}
	

	function processMessage($tempMessage){
		if($tempMessage){
			#--------------------------------------------------------------
			#	 check for url's
			#--------------------------------------------------------------
			// check to see if the string contains the letters 'www'
			if(stristr($tempMessage, "www")){
				// create empty string to append to
				$newMessage = "";	
				// if so, explode the string into an array at each space
				$messageArray = explode(" ", $tempMessage);
				// step through array
				foreach($messageArray as $word){
					//check to see if word contains www	
					if (stristr($word, "www") === false){
						// if not, append it to the new string	
						$newMessage .= " " . $word;	
					} else {
						// if so, strip the 'http://' off of it if included	
						$word = stristr($word, "www");
						// format into a hyperlink (modify target frame)	
						$word = "<a href=http://$word target=_blank>$word</a>";
						// append to new string	
						$newMessage .= " " . $word;	
					}
				}
				// set equal to another var	
				$emailMessage = $newMessage;
			}
			// if 'www' is not in the string...
			if(!stristr($tempMessage, "www")){
				// set to another var and move on	
				$emailMessage = $tempMessage;
			}
			#--------------------------------------------------------------
			#	 Check for email's
			#--------------------------------------------------------------
			// check string for '@' and '.' to see if it includes an email address
			if(stristr($emailMessage, "@") && stristr($emailMessage, ".")){
				// explode it if it does contain one	
				$messageArray = explode(" ", $emailMessage);
				// create empty string to append to
				$tempMessage = " ";
				// step through array	
				foreach($messageArray as $word)	{
					// if the word is not an email address, append and move on
					if (stristr($word, "@") === false){
						$tempMessage .= " " . $word;	
					} else {
						$word = "<a href=mailto:$word?subject=Waddup>$word</a>";
						$tempMessage .= " " . $word;	
					}
				}
			} else {
				$tempMessage = $emailMessage;
			}
			// return the resultant string with URL's and emails formatted to hyperlinks	
			return $tempMessage;
		}
	}	
	
	function clickable_link($text){
		# this functions deserves credit to the fine folks at phpbb.com

		$text = preg_replace('#(script|about|applet|activex|chrome):#is', "\\1:", $text);

		// pad it with a space so we can match things at the start of the 1st line.
		$ret = ' ' . $text;

		// matches an "xxxx://yyyy" URL at the start of a line, or after a space.
		// xxxx can only be alpha characters.
		// yyyy is anything up to the first space, newline, comma, double quote or <
		$ret = preg_replace("#(^|[\n ])([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $ret);

		// matches a "www|ftp.xxxx.yyyy[/zzzz]" kinda lazy URL thing
		// Must contain at least 2 dots. xxxx contains either alphanum, or "-"
		// zzzz is optional.. will contain everything up to the first space, newline,
		// comma, double quote or <.
		$ret = preg_replace("#(^|[\n ])((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $ret);

		// matches an email@domain type address at the start of a line, or after a space.
		// Note: Only the followed chars are valid; alphanums, "-", "_" and or ".".
		$ret = preg_replace("#(^|[\n ])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $ret);

		$ret = preg_replace("(\B@([A-Za-z0-9\_]+)(?![-a-z0-9\_]))", "@<a href=\"http://www.twirex.com/user-post/user/\\1\">\\2\\1</a>", $ret);
		$ret = preg_replace("(\B#([/A-Za-z0-9\_]+)(?![-a-z0-9\_]))", "#<a href=\"http://www.twirex.com/\\1\">\\2\\1</a>", $ret);

		// loop through the matches with foreach
				
		// Remove our padding..
		$ret = substr($ret, 1);
		return $ret;
	}	