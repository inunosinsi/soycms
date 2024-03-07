<form method="post" id="soy_inquiry_form">

<table class="soy_inquiry_message" id="soy_inquiry_message_confirm">
<tr>
	<td>
	<?php $message = $config->getMessage(); echo $message["confirm"]; ?>
	</td>
</tr>
</table>

<table id="inquiry_form" class="inquiry_form">
<?php 
$dummyFormObj = new SOYInquiry_Form();
foreach($columns as $column){
	//連番カラムは表示しない
	if($column->getType() == "SerialNumber") continue;

	$id = $column->getId();
	$obj = $column->getColumn($dummyFormObj);
	$label = $obj->getLabel();
	$view = $obj->getView();

	if(strlen((string)$view) < 1) continue;

	//個人情報保護方針は表示しない
	if(get_class($obj) == "PrivacyPolicyColumn" && (int)$view === 1) continue;

	$class = array();
	if($column->getType() == "PlainText") $class[] = "title";

	$tr_prop = (string)$obj->getTrProperty();
	if(count($class)){
		if(strlen($tr_prop)){
			if(is_numeric(strpos($tr_prop, "class="))){
				preg_match('/class="(.*?)"/', $tr_prop, $tmp);
				if(isset($tmp[1])){
					$tr_prop = preg_replace('/class="(.*?)"/', "class=\"" . trim($tmp[1]) . " " . implode(" ", $class) . "\"", $tr_prop);
				}
			}
		}else{
			$tr_prop = "class=\"" . implode(" ", $class) . "\"";
		}
	}

	if(strlen($tr_prop)){
		echo "<tr " . $tr_prop . ">\n";
	}else{
		echo "<tr>\n";
	}

	if(strlen((string)$label)){
		echo "<th>".$label."</th>\n";
		echo "<td>".$view."</td>\n";
	}else{
		echo "<td colspan=\"2\">".$view."</td>\n";
	}

	echo "</tr>\n";
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
