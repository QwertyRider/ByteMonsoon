<?php
/*
ByteMonsoon 2.1.1
http://www.sourceforge.net/projects/bytemonsoon
bytemonsoon@saevian.com
*/
require_once('config.php'); 

function dbconn() {
	if (MYSQL_PERSISTANT)
	mysql_pconnect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS) or die(mysql_error());
	else
	mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS) or die(mysql_error());
	
	mysql_select_db(MYSQL_DB) or die(mysql_error());
	
	autoclean();
	
}

function autoclean() {
$now = time();
$res = mysql_query("SELECT value FROM avps WHERE (arg = 'lastcleantime')");
$row = mysql_fetch_array($res);
if (!$row) {
	mysql_query("INSERT INTO avps (arg, value) VALUES ('lastcleantime',$now)");
	return;
}

if ($row[0] + AUTOCLEAN_INTERVAL < $now) {
	mysql_query("UPDATE avps SET value=$now WHERE (arg='lastcleantime') AND (value = $row[0])");
	require_once('autoclean.php');
	register_shutdown_function('docleanup');
}
}

function unesc($x) {
	if (get_magic_quotes_gpc())
		return stripslashes($x);
	return $x;
}

function sqlesc($x) {
	return "'".mysql_real_escape_string($x)."'";
}

function benc_err($msg) {
	benc_resp_raw(benc(array('type' => 'dictionary', 'value' => array('failure reason' => array('type'=> 'string', 'value' => $msg)))));
	exit();
}

function benc($obj) {
	if (!is_array($obj) || !isset($obj['type']) || !isset($obj['value']))
		return;
	$c = $obj['value'];
	switch ($obj['type']) {
		case 'string':
			return strlen($c) . ":$c";
		case 'integer':
			return "i" . $c . 'e';
		case 'list':
			$s = 'l';
			foreach ($c as $e) {
				$s .= benc($e);
			}
			$s .= 'e';
			return $s;
		case 'dictionary':
			$s = 'd';
			$keys = array_keys($c);
			sort($keys);
			foreach ($keys as $k) {
			$v = $c[$k];
			$s .= strlen($k) . ":$k";
			$s .= benc($v);
			}
			$s .= 'e';
			return $s;
		default:
			return;
	}
}

function benc_resp_raw($x) {
	header('Content-Type: text/plain');
	header('Pragma: no-cache');
	echo $x;
}

?>