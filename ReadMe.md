ByteMonsoon Tracker v2.1.1 (a 2.2.0 Beta)
---------------------------------------------------
Check our sourceforge page at http://www.sourceforge.net/projects/bytemonsoon/
  for the latest version and our community forums at http://www.jmstacey.net

---------------------------------------------------
* Contributing
---------------------------------------------------
	There is a lot to be done. See the TODO section below or head on over
	  to the community forums and take a look at the bugs forum and see
	  what you can do. Decent Documentation (preferably html) is also
	  needed.
	If you write up a fix or a new feature, email all the affected files to me

- Jmstacey (bytemonsoon@saevian.com)

--------------------------------------------------- 
* TODO
---------------------------------------------------
	+ Google: making configurable php scripts without slowing (if/thens)
	+ Add sort by buttons and configuration for various files
	+ Optimize template system
		- Remove unecassary functions (if any)
	+ Add web browser safety check and redirection for announce.php so browsers can not initiate peers or get peer data.
	+ Find out how to implement share ratio enforcment settings and do it
	+ Overall Information/statistics page
		- Total number of torrent
		- Total number of peers
		- Configuration option: count unique peers like bnbt
	+ Revise BEncode functions related to uploading a file and cut all unecassaries.
	+ Should ByteMonsoon use only the compact protocol or should it have 3 settings (on(force), auto(on by default but will fall back if client does not support it), off)
		- Bring up comments that it will slow processing speed down a little with checks
		- White Docs of bittorrent might help decide if compact is the standard now
	+ Optimize Switch: Rejects connections to the database when cpu usage is at or above specified size (off by default :: include error message to tracker)
	+ Fast cache (Create a static index page(s) every so often for the interface to help reduce load)
	+ Install script
		- Check file and directory permissions before continuing with installation
		- Collect configuration options for database
		- Collect and write configuration file.
	+ Move configuration options to a mysql table
	+ User Accounts
		- 3 Levels of access
			- Admin
				- Configuration page
					- Add, delete, and edit Categories
					- Modify Configuration options
				- Privileges
					- ALL
			- Moderator
				- Privileges
					- Edit Posts
					- Delete / Edit torrents
			- User
				- Account Managment page
					- Change email address
					- Manage torrents
						- Add, delete, and edit capabilities
				- Privileges
					- Configuration options
						- Upload torrents
						- Rate torrents
						- Post comments

PLEASE NOTE:
If you decide to send me changes, please send the entire file. I use
  ExamDiff to find the differences in code and having all the files
  affected in their full entirety makes the job much easier for me.
If possible, please let me know why you made the changes. Either as
  comments above the modified code, or as part of the email.

---------------------------------------------------
* Support
---------------------------------------------------
	If you need any help, have any suggestions, found a bug, or just want
	  to talk about bytemonsoon. Visit our forums at
	  http://www.jmstacey.net
