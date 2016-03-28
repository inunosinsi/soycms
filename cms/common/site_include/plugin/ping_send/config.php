<table class="list" style="margin-top:10px;width:90%;">
	<thead>
	<tr>
		<th>ブログ名</th>
		<th>最終送信時刻</th>
		<th></th>
	</tr>
	</thead>

	<tbody>
	<?php foreach($blogs as $key => $blog){ ?>
	<tr>
		<td><?php echo htmlspecialchars($blog->getTitle()); ?></td>
		<td style="text-align:center;" id="last_send_ping_<?php echo $blog->getId(); ?>"><?php if(isset($this->lastSendDate[$blog->getId()])){ echo date("Y-m-d H:i:s",$this->lastSendDate[$blog->getId()]); }else{ echo "-"; } ?></td>
		<td style="text-align:center;">
			<form
				method="post"
				target="sending_iframe_<?php echo $blog->getId(); ?>"
				onsubmit="
					$('#send_ping_button_<?php echo $blog->getId(); ?>').attr('disabled','disabled');
					$('#loading_<?php echo $blog->getId(); ?>').css('visibility','visible');
				"
			>
				<input type="hidden" name="blog_id" value="<?php echo $blog->getId(); ?>" />
				<input type="hidden" name="send_ping" value="" />
				<input type="submit" id="send_ping_button_<?php echo $blog->getId(); ?>" value="更新情報を送信する" />
				<span class="loading" id="loading_<?php echo $blog->getId(); ?>" style="visibility:hidden;">&nbsp;&nbsp;&nbsp;&nbsp;</span>
			</form>
			<iframe
				id="sending_iframe_<?php echo $blog->getId(); ?>"
				name="sending_iframe_<?php echo $blog->getId(); ?>"
				style="width:0px;height:0px;border:0px;"
			></iframe>
		</td>
	</tr>
	<?php } ?>
	</tbody>

</table>

<form method="post">
	<fieldset>
		<legend>Pingサーバ情報</legend>

		<input type="submit" name="update_ping_server" value="Pingサーバ情報を更新する" />
		<textarea name="ping_server" style="width:90%;" rows="8"><?php echo htmlspecialchars(implode("\n",$this->pingServers), ENT_QUOTES, "UTF-8"); ?>
		</textarea>
	</fieldset>
</form>
