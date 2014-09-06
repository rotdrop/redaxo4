<?php

	
	function replaceMailto($string="") {
		global $REX;
		$return = '';		
		$parts = preg_split('/<span class="mailto">/',$string);
		foreach($parts as $part) {
			$mailEndPos = strpos($part,"</span>");
			if ($mailEndPos && count($parts) > 1) {
				$email = substr($part,0,$mailEndPos);
				$restl = substr($part,strlen($email));
				$isValid = checkEmailAddress($email);
				if ($isValid) {
					$jsString = "";
					for ($i = 0; $i < strlen($email); $i++) {
						$jsString 	.= chr(ord(substr($email, $i, 1)) + 3);
					}
					$imgLink = '/inc/php/image.inc.php/email.png?email='.$email.'&stamp='.$REX['TIMESTAMP'];
					$jsLink  = '<a href="javascript:THIS.mailTo(\'pdlowr='.$jsString.'\');" class="mailto">';
					$jsLink .= '<img src="'.$imgLink.'" border="0" /></a>';
					$return .= $jsLink . $restl;
				} else {
					$return .= $part;
				}
				
			} else {
				$return .= $part;
			}
		}
		return $return;
	}
	/*
	 * @function 	überprüft E-Mail-Adresse auf Gültigkeit
	 * @return 		[boolean] true/false
	 * @author		Unbekannt -> aus /e38phplib/const.php
	 * @date		20081030
	 */

	function checkEmailAddress($email) {
		// First, we check that there's one @ symbol, and that the lengths are right
          //		if (!ereg("[^@]{1,64}@[^@]{1,255}", $email)) {
		if (!preg_match('/[^@]{1,64}@[^@]{1,255}/', $email)) {
			// Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
			return false;
		}
		// Split it into sections to make life easier
		$email_array = explode("@", $email);
		$local_array = explode(".", $email_array[0]);
		for ($i = 0; $i < sizeof($local_array); $i++) {
                          //if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[0])) {
			if (!preg_match('@^(([A-Za-z0-9!#$%&\'*+/=?^_`{|}~-][A-Za-z0-9!#$%&\'*+/=?^_`{|}~\.-]{0,63})|("[^(\\|")]{0,62}"))$@', $local_array[0])) {
				return false;
			}
		}

                //if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
		if (!preg_match('@^\[?[0-9\.]+\]?$@', $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
			$domain_array = explode(".", $email_array[1]);
			if (sizeof($domain_array) < 2) {
				return false; // Not enough parts to domain
			}
			for ($i = 0; $i < sizeof($domain_array); $i++) {
                          //if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) {
				if (!preg_match('@^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$@', $domain_array[$i])) {
					return false;
				}
			}
		}
		return true;
	}
?>