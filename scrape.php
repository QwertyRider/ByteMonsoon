<?
/*
ByteMonsoon 2.1.1
http://www.sourceforge.net/projects/bytemonsoon
bytemonsoon@saevian.com
*/
require_once("includes/functions_client.php");

dbconn();

$r = "d5:filesd";

if (!isset($_GET["info_hash"])) {
$query = "SELECT info_hash, times_completed, seeders, leechers FROM torrents ORDER BY info_hash";
} else {
$sql_info_hash = "";
$i = 0;
$info_hashs = split("&info_hash=", substr(urldecode($_SERVER['QUERY_STRING']),10));
foreach ($info_hashs as $info_hash) {
 if (strlen($info_hash) == 20) {
  if ($i > 0) {
   $sql_info_hash .= " OR ";
  }
  $sql_info_hash .= "(info_hash = " . sqlesc($info_hash) . ")";
  $i++;
 }
}

if ($i > 0) {
 $query = "SELECT info_hash, times_completed, seeders, leechers FROM torrents WHERE $sql_info_hash ORDER BY info_hash";
} else {
 $query = "SELECT info_hash, times_completed, seeders, leechers FROM torrents ORDER BY info_hash";
}
}

$res = mysql_query($query);
while ($row = mysql_fetch_assoc($res)) {
	$r .= "20:" . str_pad($row["info_hash"], 20) . "d" .
		"8:completei" . $row["seeders"] . "e" .
		"10:downloadedi" . $row["times_completed"] . "e" .
		"10:incompletei" . $row["leechers"] . "e" .
		"e";
}

$r .= "ee";

echo $r;

?>