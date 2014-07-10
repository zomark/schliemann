
<?php

$realm = 'Schliemann Correspondence';

//user => password
$users = array('admin' => 'admin123', 'guest' => 'schliemann123');


//Check the supplied $_SERVER variables for a vaild authentication.
//If authenticated, return the user name,
// otherwise, set the 401 header fields and return false
function sc_get_user($server) {
	global $realm, $users;
	
	//Authentication headers set?
	if (empty($server['PHP_AUTH_DIGEST'])) {
		header('HTTP/1.1 401 Unauthorized');
		header('WWW-Authenticate: Digest realm="'.$realm.
			   '",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');
		return false;
	}

	//Analyze the PHP_AUTH_DIGEST variable
	if (!($data = http_digest_parse($server['PHP_AUTH_DIGEST'])) ||
	!isset($users[$data['username']])) {
		header('HTTP/1.1 401 Unauthorized');
		header('WWW-Authenticate: Digest realm="'.$realm.
			   '",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');
		error_log("authenticate: Cannot parse PHP_AUTH_DIGEST");
		return false;
	}

	//Generate the valid response
	$A1 = md5($data['username'] . ':' . $realm . ':' . $users[$data['username']]);
	$A2 = md5($server['REQUEST_METHOD'].':'.$data['uri']);
	$valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);

	if ($data['response'] != $valid_response) {
		header('HTTP/1.1 401 Unauthorized');
		header('WWW-Authenticate: Digest realm="'.$realm.
			   '",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');
		error_log("authenticate: Invalid response");
		return false;
	}

	//Ok, valid username & password
	return $data['username'];
}


//Parse the http auth header
function http_digest_parse($txt)
{
    // protect against missing data
    $needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
    $data = array();
    $keys = implode('|', array_keys($needed_parts));
	
	//error_log("Parsing $txt against $keys");

    preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

    foreach ($matches as $m) {
        $data[$m[1]] = $m[3] ? $m[3] : $m[4];
        unset($needed_parts[$m[1]]);
    }

    return $needed_parts ? false : $data;
}

?>
