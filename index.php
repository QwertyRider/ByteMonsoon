<?php
/*
ByteMonsoon 2.1.1
http://www.sourceforge.net/projects/bytemonsoon
bytemonsoon@saevian.com
*/
require_once('includes/functions_interface.php');
$gentime_start = getmicrotime();

if (!ENABLE_INTERFACE)
{
	die('The interface is disabled.');
}
dbconn();
$res = mysql_query("SELECT id, info_hash, FROM_UNIXTIME(added, '%m-%d %H:%i'), seeders, leechers FROM torrents");
function hex_esc($matches)
{
	return sprintf('%02x', ord($matches[0]));
}

$k = 0;
$data = "";
while ($row = mysql_fetch_array($res)) 
{
	$hash = preg_replace_callback('/./s', 'hex_esc', str_pad($row[1], 20));
	// Begin Color check
	if ($row[3] == 0)
	{
		$data[$k]['seeders_color'] = 'red';
	}
	elseif ($row[3] > 0 && $row[3] < 7)
	{
		$data[$k]['seeders_color'] = 'yellow';
	}
	else
	{
		$data[$k]['seeders_color'] = 'green';
	}		
	if ($row[4] == 0)
	{
		$data[$k]['leechers_color'] = 'red';
	}
	elseif ($row[4] > 0 && $row[4] < 7)
	{
		$data[$k]['leechers_color'] = 'yellow';
	}
	else
	{	
		$data[$k]['leechers_color'] = 'green';
	}
	// End Color check
	
	// Begin array for output to template
	$data[$k]['id'] = $row[0];
	$data[$k]['hash'] = $hash;
	$data[$k]['added'] = $row[2];
	$data[$k]['seeders'] = $row[3];
	$data[$k]['leechers'] = $row[4];
	$k++;
}

// Output to template
include('includes/page_header.php');
$tpl = new phemplate('', 'remove');
$tpl->set_loop('torrent', $data);
$tpl->set_file('output', 'templates/' . THEME . '/index.tpl');
echo $tpl->process('', 'output', TPL_LOOP);

include('includes/page_footer.php');

?>