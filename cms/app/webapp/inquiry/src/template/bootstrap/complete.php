<div class="alert alert-info" data-role="alert">
	<?php $message = $config->getMessage(); echo $message["complete"]; ?>
</div>

<div class="form-group">
	<label>お問い合わせ番号：</label><br>
	<?php echo $inquiry->getTrackingNumber(); ?>
</div>

<div class="form-group">
	<label>お問い合わせ内容：</label><br>
	<pre><?php echo $inquiry->getContent(); ?></pre>
</div>

<div class="form-group">
	<label>お問い合わせ日時：</label><br>
	<?php echo date("Y-m-d H:i:s",$inquiry->getCreateDate()); ?>
</div>

<div class="text-center">
	<a href="<?php echo $page_link; ?>" class="btn btn-primary btn-lg">戻る</a>
</div>
