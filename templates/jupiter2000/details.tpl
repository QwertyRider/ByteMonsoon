<div align="left">
<h3>Torrent Information</h3>
<ul>
	<li><strong>Hash:</strong> {torrent.hash}
	<li><strong>Added:</strong> {torrent.added}
</ul>

<block="seeders">
<div align='left'>
<p>Seeders ({torrent.seeders})</p>
<table summary="seeders">
	<tr>
    	<th class='ip'>Peer IP</th>
		<th class='bytes'>Uploaded</th>
		<th class='bytes'>Downloaded</th>
		<th class='connected'>Connected</th>
		<opt name="show.share_ratio"><th class="number">Share Ratio</th></opt name="show.share_ratio">
	</tr>
<loop="seeders">
	<tr>
		<td class='ip'>{seeders.ip}</td>
		<td class='bytes'>{seeders.uploaded}</td>
		<td class='bytes'>{seeders.downloaded}</td>
		<td class='connected'>{seeders.connected}</td>
		<opt name="show.share_ratio"><td class="number_{seeders.share_ratio_color}">{seeders.share_ratio}</td></opt name="show.share_ratio">
	</tr>
</loop="seeders">
</table>
</div>
</block="seeders">

<block="leechers">
<div align="left">
<p>Leechers ({torrent.leechers})</p>
<table summary='leechers'>
	<tr>
    	<th class='ip'>Peer IP</th>
		<th class='bytes'>Uploaded</th>
		<th class='bytes'>Downloaded</th>
		<opt name="show.progress"><th class="bytes">Progress</th></opt name="show.progress">
		<th class='connected'>Connected</th>
		<opt name="show.share_ratio"><th class="number">Share Ratio</th></opt name="show.share_ratio">
	</tr>
<loop="leechers">
	<tr>
		<td class='ip'>{leechers.ip}</td>
		<td class='bytes'>{leechers.uploaded}</td>
		<td class='bytes'>{leechers.downloaded}</td>
		<opt name="show.progress"><td class="percent">{leechers.progress}</td></opt name="show.progress">
		<td class='connected'>{leechers.connected}</td>
		<opt name="show.share_ratio"><td class="number_{leechers.share_ratio_color}">{leechers.share_ratio}</td></opt name="show.share_ratio">
	</tr>
</loop="leechers">
</table>
</div>
</block="leechers">
</div>