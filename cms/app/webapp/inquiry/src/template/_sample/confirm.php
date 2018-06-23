<form method="post">

<table class="soy_inquiry_message" id="soy_inquiry_message_confirm">
<tr>
	<td>
	<?php $message = $config->getMessage(); echo $message["confirm"]; ?>
	</td>
</tr>
</table>

<table id="inquiry_form" class="inquiry_form">
<?php foreach($columns as $column){

	$id = $column->getId();
	$obj = $column->getColumn();
	$label = $obj->getLabel();
	$view = $obj->getView();

	if(strlen($view) < 1) continue;

	//個人情報保護方針は表示しない
	if(get_class($obj) == "PrivacyPolicyColumn" && (int)$view === 1) continue;

	$class = array();
	if($column->getType() == "PlainText") $class[] = "title";
	if(count($class)){
		echo "<tr class=\"".implode(" ",$class)."\">";
	}else{
		echo "<tr>";
	}

	if(strlen($view)>0){
		if(strlen($label)){
			echo "<th>";
			echo $label;
			echo "</th>";
			echo "<td>";
			echo $view;
			echo "</td>";
		}else{
			echo '<td colspan="2">';
			echo $view;
			echo "</td>";
		}
	}

	echo "</tr>";
}
?>
</table>

<?php
echo $hidden_forms;
?>

<?php if($config->getIsUseCaptcha()){ ?>
<div id="inquiry_form_captcha">
<img src="<?php echo $captcha_url; ?>" />

<div>
	<input type="text" name="captcha_value" value="" />
	表示されてる画像の文字(半角英数字大文字)を入力してください。
</div>
</div>
<?php } ?>

<table>
	<tr>
		<td>
			<input name="form" type="submit" value="戻る" />
		</td>

		<td>
			<input name="send" type="submit" value="送信" />
		</td>
	</tr>
</table>

</form>
