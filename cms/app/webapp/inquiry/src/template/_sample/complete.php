<table class="soy_inquiry_message" id="soy_inquiry_message_complete">
<tr>
	<td colspan="2">
	<?php $message = $config->getMessage(); echo $message["complete"]; ?>
	</td>
</tr>
</table>

<table id="inquiry_form" class="inquiry_form">
<tr>
	<td>お問い合わせ番号：</td>
	<td><?php echo $inquiry->getTrackingNumber(); ?></td>
</tr>
<tr>
	<td>お問い合わせ内容：</td>
	<td><pre><?php echo $inquiry->getContent(); ?></pre></td>
</tr>
<tr>
	<td>お問い合わせ日時：</td>
	<td><?php echo date("Y-m-d H:i:s",$inquiry->getCreateDate()); ?></td>
</tr>
</table>


<a href="<?php echo $page_link; ?>">戻る</a>