<?php
/*
ByteMonsoon 2.1.1
http://www.sourceforge.net/projects/bytemonsoon
bytemonsoon@saevian.com
*/
require_once('includes/functions_interface.php');
$gentime_start = getmicrotime();

if (!ENABLE_INTERFACE)
	die('The interface is disabled.');

dbconn();	

// Template Output: Stage 1 (Start)
include('includes/page_header.php');
$tpl = new phemplate('', 'remove');
$tpl->set_file('output', 'templates/' . THEME . '/details.tpl', TPL_BLOCK);

$id = 0;
$id = $_GET['id'];
if (!isset($id) || !$id)
	die('Invalid torrent ID');
	
$res = mysql_query("
	SELECT info_hash, added, seeders, leechers 
		FROM torrents 
		WHERE (torrents.id = $id)
");
function hex_esc($matches) {
	return sprintf('%02x', ord($matches[0]));
}

if (!$row = mysql_fetch_array($res))
	die('Invalid torrent ID');
$hash = preg_replace_callback('/./s', 'hex_esc', str_pad($row[0], 20));

$torrent_data['id'] = $id;
$torrent_data['hash'] = $hash;
$torrent_data['added'] = $row[1];
$torrent_data['seeders'] = $row[2];
$torrent_data['leechers'] = $row[3];

// Template Output: Stage 2
$tpl->set_var('torrent', $torrent_data);

$now = time();
if ($row[2] != 0) {
	$k = 0;
	$subres = mysql_query("SELECT ip, uploaded, downloaded, UNIX_TIMESTAMP(started) FROM peers WHERE (torrent = $id AND seeder = 'yes')");
	while ($subrow = mysql_fetch_array($subres)) {
		$seeders[$k]['ip'] = preg_replace('/\.\d+$/', '.xxx', $subrow[0]); 
		$seeders[$k]['uploaded'] = make_size($subrow[1]);
		$seeders[$k]['downloaded'] = make_size($subrow[2]);
		
		if (ENABLE_SHARE_RATIO) {
			$show['share_ratio'] = 'true';
			if ($subrow[2] == 0 && $subrow[1] > 0) {
				$seeders[$k]['share_ratio'] = 'Perfect';
				$seeders[$k]['share_ratio_color'] = 'green';
			}
			else {
				$seeders[$k]['share_ratio'] = @round($subrow[1] / $subrow[2], 3);
				$seeders[$k]['share_ratio_color'] = make_share_ratio_color($seeders[$k]['share_ratio']);
			}
		}
		$seeders[$k]['connected'] = make_time($now - $subrow[3]);
		
		$k++;
	}
	// Template Output: Stage 3 
	$tpl->set_loop('seeders', $seeders);
	$tpl->process('output', 'seeders', TPL_APPEND);
}

if ($row[3] != 0) {
	$k = 0;
	$subres = mysql_query("SELECT ip, uploaded, downloaded, to_go, UNIX_TIMESTAMP(started) FROM peers WHERE (torrent = $id AND seeder = 'no')");
	while ($subrow = mysql_fetch_array($subres)) {
		$leechers[$k][ip] = preg_replace("/\.\d+$/", '.xxx', $subrow[0]); 
		$leechers[$k][uploaded] = make_size($subrow[1]);
		$leechers[$k][downloaded] = make_size($subrow[2]);
		
		if (ENABLE_SHARE_RATIO) {
			$show[share_ratio] = 'true';
			if ($subrow[2] == 0 && $subrow[1] > 1) {
				$leechers[$k][share_ratio] = 'Perfect';
				$leechers[$k][share_ratio_color] = 'green';
			}
			else {
				$leechers[$k][share_ratio] = @round($subrow[1] / $subrow[2], 3);
				$leechers[$k][share_ratio_color] = make_share_ratio_color($seeders[$k][share_ratio]);
			}
		}
		if (REQUIRE_UPLOAD) {
			// if (SHOW_PROGRESS_DETAILS)
				// $leechers[$k]['progress'] = sprintf('%.2f%%', 100 * (1 - ($e['to_go'] / $torrent['size'])));
		}
		
		$leechers[$k]['connected'] = make_time($now - $subrow[4]);
		
		$k++;
	}
	// Template Output: Stage 4
	$tpl->set_loop('leechers', $leechers);
	$tpl->process('output', 'leechers', TPL_APPEND);
}

// Template Output: Stage 5 (End)
$tpl->set_var('show', $show);
echo $tpl->process('', 'output', TPL_LOOP | TPL_OPTIONAL);
include('includes/page_footer.php');	

?>