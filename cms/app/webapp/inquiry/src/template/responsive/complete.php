<div class="soy_inquiry_message">
	<?php $message = $config->getMessage(); echo $message["complete"]; ?>
</div>

<div class="soy_iqnuiry_responsive">
	<dl>
	<dt>お問い合わせ番号：</dt>
	<dd><?php echo $inquiry->getTrackingNumber(); ?></dd>
	<dt>お問い合わせ内容：</dt>
	<dd><pre><?php echo $inquiry->getContent(); ?></pre></dd>
	<dt>お問い合わせ日時：</dt>
	<dd><?php echo date("Y-m-d H:i:s",$inquiry->getCreateDate()); ?></dd>
	</dl>
</div>
<p class="btn">
<a href="<?php echo $page_link; ?>">戻る</a>
</p>
