<table summary='files'>
	<tr>
    	<th class='name'>Hash</th>
		<th class='date'>Added</th>
    	<th class='number'>SDs</th>
		<th class='number'>DLs</th>
	</tr>
<loop="torrent">
	<tr>
		<td class='name'><a class='stats' href='details.php?id={torrent.id}'>{torrent.hash}</a></td>
		<td class='date'>{torrent.added}</td>
		<td class='number_{torrent.seeders_color}'>{torrent.seeders}</td>
		<td class='number_{torrent.leechers_color}'>{torrent.leechers}</td>
	</tr>
</loop="torrent">
</table>
<br>