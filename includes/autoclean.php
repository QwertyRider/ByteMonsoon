<?
/*
ByteMonsoon 2.1.1
http://www.sourceforge.net/projects/bytemonsoon
bytemonsoon@saevian.com
*/

function docleanup() {
	@set_time_limit(0);
	ignore_user_abort(1);
	
	// Clean out peers table
	$deadtime = time() - ANNOUNCE_INTERVAL;
	mysql_query('DELETE FROM peers WHERE last_action < FROM_UNIXTIME($deadtime)');
	mysql_query('OPTIMIZE TABLE peers');
	
	// Clean out dead torrents
	$deadtime = time() - HARD_TORRENT_TIMEOUT;
	mysql_query('DELETE FROM torrents WHERE last_action < FROM_UNIXTIME($deadtime)');
	mysql_query('OPTIMIZE TABLE torrents');
	
	// Update seeds and leechers count in torrents table (Not Optimized yet)
	$torrents = array();
	$res = mysql_query('SELECT torrent, seeder, COUNT(*) AS c FROM peers GROUP BY torrent, seeder');
	while ($row = mysql_fetch_assoc($res)) {
		if ($row['seeder'] == 'yes')
			$key = 'seeders';
		else
			$key = 'leechers';
		$torrents[$row['torrent']][$key] = $row['c'];
	}

	$fields = explode(':', 'leechers:seeders');
	$res = mysql_query('SELECT id, seeders, leechers FROM torrents');
	if (mysql_num_rows($res)) while ($row = mysql_fetch_assoc($res)) {
		$id = $row['id'];
		$torr = @$torrents[$id];
		foreach ($fields as $field) {
			if (!isset($torr[$field]))
				$torr[$field] = 0;
		}
		$update = array();
		foreach ($fields as $field) {
			if ($torr[$field] != $row[$field])
				$update[] = '$field = ' . $torr[$field];
		}
		if (count($update))
			mysql_query('UPDATE torrents SET ' . implode(',', $update) . ' WHERE id = $id');
	}
	
}

?>