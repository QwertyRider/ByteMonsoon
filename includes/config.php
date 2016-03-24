<?php
/*
ByteMonsoon 2.1.1
http://www.sourceforge.net/projects/bytemonsoon
bytemonsoon@saevian.com
*/

define('MYSQL_HOST', 'localhost');
define('MYSQL_DB', 'tracker');
define('MYSQL_USER', 'root');
define('MYSQL_PASS', '');
define('MYSQL_PERSISTANT', true);
// define('optimize') = false; //Switch to reject database connection when cpu goes over specified limit or over peer limit. To be decided. Possibly split into 2 configurations since cpu load will not work on all Operating Systems.

define('COMPACT_MODE', 'auto'); // on (force compact) : auto (default to compact but fall back if not supported
define('ANNOUNCE_INTERVAL', 1800);
// define('compression_level') = 0; // 0-9 :: 0 = off  9 == max compression
define('MAX_PEERS', 50);
// define('enable_autoclean') = true;
	define('AUTOCLEAN_INTERVAL', 900);
	define('HARD_TORRENT_TIMEOUT', 86400 * 30);
//	define('soft_torrent_timeout') = 4 * 3600;

define('ENABLE_INTERFACE', 'true');
	define('THEME', 'jupiter2000');
	define('TITLE', 'ByteMonsoon Development Tracker');
	define('VERSION', '2.1.1');
//	define('ALLOW_SORT', true); // Allow columns on various pages to be sorted alphabetically/
//	define('enable_comments') = false;
//		define('comment_length') = 800;
//	define('enable_ratings') = false;
//		define('ratings_pictures') = ''; // No idea how to implement this yet
	define('ENABLE_SHARE_RATIO', true); // Calculate and show ratings on details page (see next configuration)
//		define('min_share_ratio') = 0.050; // Set the minimum allowed share ration (Unsure about how to enfore this yet, please help)
//	define('torrents_per_page') // Set the number of torrents to display per page	
	define('REQUIRE_UPLOAD', false); // Do not enable unless you know what you are doing! Upload feature not finished.
//		define('torrent_dir') = 'C:/Program Files/Abyss Web Server/htdocs/tracker/torrents/';
//		define('torrent_url') = 'http://www.jmstacey.net/tracker/torrents/';
//		define('max_torrent_size') = 10000000; // Precautionary measure incase a file gets through the bencode screen thats not really a .torrent
//		define('enable_external_stats') = false; // Allow and collect stats from external trackers	
		define('SHOW_PROGRESS', false); // Show peer progress on details page
//			define('PROGRESS_IMAGE', ''); // Use image to show progress (no idea how to do this, yet)
//		define('enable_account_signups') = false;
//			define('inactive_timeout') = 86400 * 90;
//			define('email_activation') = false;
//			define('unactivated_timeout') = 86400 * 3;

?>