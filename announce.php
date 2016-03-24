<?php
/*
ByteMonsoon 2.1.1
http://www.sourceforge.net/projects/bytemonsoon
bytemonsoon@saevian.com
*/
require_once("includes/functions_client.php");

$req = "info_hash:peer_id:!ip:port:uploaded:downloaded:left:compact:!event";
foreach (explode(":", $req) as $x) {
	if ($x[0] == "!") {
		$x = substr($x, 1);
		$opt = 1;
	}
	else
		$opt = 0;
	if (!isset($_GET[$x])) {
		if (!$opt)
			benc_err("Missing Key. (This is an internal tracker problem regarding the announce.php file, please notify the administrator.");
		continue;
	}
	$GLOBALS[$x] = unesc($_GET[$x]);
}

/*
if($compact != 1)
{
	benc_err("This tracker uses the compact protocol which your client does not support. Please check for updates, or Try the Latest Generic or BitTornado clients. ");
}
*/

foreach (array("info_hash","peer_id") as $x) {
	if (strlen($GLOBALS[$x]) != 20)
		benc_err("invalid $x (" . strlen($GLOBALS[$x]) . " - " . urlencode($GLOBALS[$x]) . ")");
}

if (empty($ip) || !preg_match('/^(\d{1,3}\.){3}\d{1,3}$/s', $ip))
	$ip = $_SERVER["REMOTE_ADDR"];

$port = 0 + $port;
$downloaded = 0 + $downloaded;
$uploaded = 0 + $uploaded;
$left = 0 + $left;

foreach(array("num want", "numwant", "num_want") as $k) {
	if (isset($_GET[$k])) {
		$rsize = 1 + $_GET[$k];
		break;
	}
	else
		$rsize = MAX_PEERS;
}

if (!$port || $port > 0xffff)
	benc_err("Invalid Port");

if (!isset($event))
	$event = "";

$seeder = ($left == 0) ? "yes" : "no";

dbconn();

$res = mysql_query("SELECT id, banned, seeders + leechers AS numpeers FROM torrents WHERE (info_hash = " . sqlesc($info_hash) . ")");

$torrent = mysql_fetch_assoc($res);
if (!$torrent) {
	if (REQUIRE_UPLOAD)
		benc_err("Torrent is not Registered or allowed on this tracker.");
	else {
		mysql_query("INSERT INTO torrents (info_hash, added) VALUES (" . sqlesc($info_hash) . ", NOW())");
		$torrentid = mysql_insert_id();
	}
}
else
$torrentid = $torrent["id"];

$limit = "";
if ($torrent["numpeers"] > $rsize)
	$res = mysql_query("SELECT seeder, peer_id, ip, port FROM peers WHERE torrent = $torrentid AND ORDER BY RAND() LIMIT $rsize");
else
$res = mysql_query("SELECT seeder, peer_id, ip, port FROM peers WHERE torrent = $torrentid");

$resp = "d8:intervali" . ANNOUNCE_INTERVAL . "e5:peersl";
unset($self);
while ($row = mysql_fetch_assoc($res)) {
	$row["peer_id"] = str_pad($row["peer_id"], 20);

	if ($row["peer_id"] === $peer_id) {
		$self = $row;
		continue;
	}

	$resp .= "d" .
		"2:ip" . strlen($row["ip"]) . ":" . $row["ip"] . 
		"7:peer id20:" . $row["peer_id"] .
		"4:porti" . $row["port"] . "ee";
}

$resp .= "ee";

$selfwhere = "torrent = $torrentid AND (peer_id = " . sqlesc($peer_id) . ")";

if (!isset($self)) {
	$res = mysql_query("SELECT seeder, peer_id, ip, port FROM peers WHERE $selfwhere");
	$row = mysql_fetch_assoc($res);
	if ($row)
		$self = $row;
}

$updateset = array();

if ($event == "stopped") {
	if (isset($self)) {
		mysql_query("DELETE FROM peers WHERE $selfwhere");
		if (mysql_affected_rows()) {
			if ($self["seeder"] == "yes")
				$updateset[] = "seeders = seeders - 1";
			else
				$updateset[] = "leechers = leechers - 1";
		}
	}
}
else {
	if ($event == "completed")
		$updateset[] = "times_completed = times_completed + 1";

	if (isset($self)) {
		mysql_query("UPDATE peers SET ip = " . sqlesc($ip) . ", port = $port, uploaded = $uploaded, downloaded = $downloaded, to_go = $left, last_action = NOW(), seeder = '$seeder' WHERE $selfwhere");
		if (mysql_affected_rows() && $self["seeder"] != $seeder) {
			if ($seeder == "yes") {
				$updateset[] = "seeders = seeders + 1";
				$updateset[] = "leechers = leechers - 1";
			}
			else {
				$updateset[] = "seeders = seeders - 1";
				$updateset[] = "leechers = leechers + 1";
			}
		}
	}
	else {
		$ret = mysql_query("INSERT INTO peers (torrent, peer_id, ip, port, uploaded, downloaded, to_go, started, last_action, seeder) VALUES ($torrentid, " . sqlesc($peer_id) . ", " . sqlesc($ip) . ", $port, $uploaded, $downloaded, $left, NOW(), NOW(), '$seeder')");
		if ($ret) {
			if ($seeder == "yes")
				$updateset[] = "seeders = seeders + 1";
			else
				$updateset[] = "leechers = leechers + 1";
		}
	}
}

if ($seeder == "yes") {
	if ($torrent["banned"] != "yes")
		$updateset[] = "visible = 'yes'";
	$updateset[] = "last_action = NOW()";
}

if (count($updateset))
	mysql_query("UPDATE torrents SET " . join(",", $updateset) . " WHERE id = $torrentid");

benc_resp_raw($resp);

?>
