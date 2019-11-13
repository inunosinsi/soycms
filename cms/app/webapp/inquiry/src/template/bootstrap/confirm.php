<form method="post" id="soy_inquiry_form">

<div class="alert alert-info" data-role="alert">
	<?php $message = $config->getMessage(); echo $message["confirm"]; ?>
</div>

<?php foreach($columns as $column){
	//連番カラムは表示しない
	if($column->getType() == "SerialNumber") continue;

	$id = $column->getId();
	$obj = $column->getColumn();
	$label = $obj->getLabel();
	$view = $obj->getView();

	if(strlen($view) < 1) continue;

	//個人情報保護方針は表示しない
	if(get_class($obj) == "PrivacyPolicyColumn" && (int)$view === 1) continue;

	if(strlen($label) > 0 && strlen($view) > 0){
		echo "<div class=\"form-group\">";
		echo "<label>" . $label . "</label><br>";
		echo $view;
		echo "</div>";
	}
}
?>

<?php
echo $hidden_forms;
?>

<?php if($config->getIsUseCaptcha()){ ?>
<br>
<div style="margin-bottom:15px;">
	<img src="<?php echo $captcha_url; ?>" />

	<div>
		<input type="text" name="captcha_value" value="" /><br>
		表示されてる画像の文字(半角英数字大文字)を入力してください。
	</div>
</div>
<?php } ?>

<div class="text-center">
	<input name="form" type="submit" class="btn btn-default btn-lg" value="戻る" >
	<input name="send" type="submit" class="btn btn-primary btn-lg" value="送信" >
</div>

</form>
