<?php
	@session_start();
	define('LOGON_USER','camerata');			// User name
	define('LOGON_PASS','academica');			// User passwort
	$REX['PROTECTED_IDS'] = array(11); 			// diese category und deren unterseiten ist geschützt
	$REX['LOGGED_IN'] 	  = FALSE;				// default nicht eingeloggt
	$REX['PROTECTED'] 	  = FALSE;				// default seite nicht geschützt
	$REX['TIMESTAMP']	  = time();				// timestamp für die image function (email generieren)
	$_SESSION['TIMESTAMP'] = $REX['TIMESTAMP'];
 	
	// wir schauen nach wie oft es der User schon probiert hat und werden lästig falls mehr als 5 mal
	// -> noch nicht umgesetzt
	if (!isset($_SESSION['LOG_IN_TRYS'])) {
		$_SESSION['LOG_IN_TRYS'] = 0;
	}
	
	// wir schauen ob der User eingeloggt ist
	if (!isset($_SESSION['LOGGED_IN'])) {
		$_SESSION['LOGGED_IN'] = $REX['LOGGED_IN'];
	} else { // genau andersrum
		$REX['LOGGED_IN'] = $_SESSION['LOGGED_IN'];
	}
	
	// diese Category
	$thisId  = $this->getValue('category_id');
	$thisCat = OOCategory::getCategoryById($thisId,false);
	
	// sind wir genau auf einer Mitgliedscategory
	if (in_array($thisId,$REX['PROTECTED_IDS'])) { // User have to be logged in
		$REX['PROTECTED'] = TRUE;
	}
	// zusätzlich nach den Parents schauen
	if (!$REX['PROTECTED']) {
		foreach($REX['PROTECTED_IDS'] as $key=>$value) {
			$parentCat = OOCategory::getCategoryById($value);
			if ($parentCat && $parentCat->isParent($thisCat)) {;
				$REX['PROTECTED'] = TRUE;
				break;
			}
		}
	}
	// wir schauen ob der User sich versucht einzuloggen
	if (isset($_POST['cmd']) && $_POST['cmd']=='login') {
		$_SESSION['LOG_IN_TRYS']++;
		$loginName = stripslashes(trim($_POST['username']));
		$loginPass = stripslashes(trim($_POST['password']));
		if ($loginName == LOGON_USER && $loginPass == LOGON_PASS) {
			$_SESSION['LOG_IN_TRYS'] = 0;
			$REX['LOGGED_IN'] = TRUE;
			$_SESSION['LOGGED_IN'] = TRUE;
		}
	}
	
	// wir schauen ob der User sich versucht auszuloggen
	if (isset($_GET['cmd']) && $_GET['cmd']=='logout') {
		$REX['LOGGED_IN'] = FALSE;
		$_SESSION['LOGGED_IN'] = FALSE;
		header("location:index.php");
		exit();
	}
	
	// Jetzt sollten wir wissen ob wir auf einer geschützen Seite sind.
	// Das Login-Formular zeigen..
	if ($REX['PROTECTED'] && !$REX['LOGGED_IN']) {
		$REX['SHOW_LOGIN'] = $_SERVER['DOCUMENT_ROOT'].'/cafevwww-new/inc/php/login.inc.php';
	} else {
		$REX['SHOW_LOGIN'] = FALSE;
	}
?>
