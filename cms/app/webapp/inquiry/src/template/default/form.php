<form method="post" enctype="multipart/form-data">

<table class="soy_inquiry_message" id="soy_inquiry_message_information">
<tr>
	<td>
	<?php $message = $config->getMessage(); echo $message["information"]; ?>
	</td>
</tr>
</table>

<?php
//各項目の出力行を取得する
$columnCount = count($columns);
$raws = array();
foreach($columns as $key => $column){
	//連番カラムは表示しない
	if($column->getType() == "SerialNumber") continue;

	$output = "";

	$id = $column->getId();
	$obj = $column->getColumn();
	$label = ($column->getType() != "Enquete" && $column->getType() != "EnqueteFree") ? $obj->getLabel() : null;
	$annotation = $obj->getAnnotation();

	$class = array();
	if($column->getRequire()) $class[] = "require";
	if(isset($errors[$id]))   $class[] = "error";
	if($column->getType() == "PlainText") $class[] = "title";

	$tr_prop = $obj->getTrProperty();
	if(count($class)){
		if(strlen($tr_prop)){
			if(strpos($tr_prop, "class=") !== false){
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
		$output .= "<tr " . $tr_prop . ">\n";
	}else{
		$output .= "<tr>\n";
	}

	if(strlen($label)>0){
		$output .= "<th>";
		if($column->getRequire()){
			$output .= "*";
		}
		$output .= $label;
		$output .= "</th>\n";
		$output .= "<td>\n";
	    $output .= "\t".$obj->getForm();
	    if(isset($annotation) && strlen($annotation)){
	    	$output .= "&nbsp;".$annotation;
	    }
	    if(isset($errors[$id])){
	    	$output .= "<br/>";
	    	$output .= "<span class=\"error_message\">";
	    	$output .= $errors[$id];
	    	$output .= "</span>";
	    }
	    $output .= "\n</td>\n";
	}else{
		if(($column->getType() == "Enquete" || $column->getType() == "EnqueteFree")){	//アンケートカラムの場合は文言を大きく表示
			$cnf = $column->getConfig();
			$config = (strlen($cnf)) ? soy2_unserialize($cnf) : array();
			$label = (isset($config["question"]) && strlen($config["question"])) ? htmlspecialchars($config["question"], ENT_QUOTES, "UTF-8") : $obj->getLabel();
			$output .= "<th colspan=\"2\">\n";
			$output .= $label;
			$output .= "\n</th>\n";
			$output .= "</tr><tr>";
		}

		$output .= "<td colspan=\"2\">\n";
	    $output .= "\t".$obj->getForm();
	    if(isset($errors[$id])){
	    	$output .= "<br/>";
	    	$output .= "<span class=\"error_message\">";
	    	$output .= $errors[$id];
	    	$output .= "</span>";
		}
	    $output .= "\n</td>\n";
	}

	$output .= "</tr>\n";

	//プライバシーポリシーが最初か最後の場合は別テーブルで表示する
	if($column->getType()==="PrivacyPolicy" && $key === 0){
		$beforeForm = $output;
	}elseif($column->getType()==="PrivacyPolicy" && $key === $columnCount-1){
		$afterForm = $output;
	}else{
		$raws[$key] = $output;
	}

}
?>

<div id="inquiry_form_wrapper">
<?php /* 出力 */

//最初のプライバシーポリシー
if(isset($beforeForm)) echo '<table class="inquiry_form" id="inquiry_privacy_policy">'.$beforeForm.'</table>';
//フォーム本体
echo '<table id="inquiry_form" class="inquiry_form">';
foreach($raws as $output){
	echo $output;
}
echo '</table>';
//最後のプライバシーポリシー
if(isset($afterForm)) echo '<table class="inquiry_form" id="inquiry_privacy_policy">'.$afterForm.'</table>';

?>
</div>

<table>
	<tr>
		<td style="text-align:center;border-style:none;">
			<input name="data[hash]" type="hidden" value="<?php echo $random_hash; ?>" />
			<input name="confirm" type="submit" value="入力内容の確認" />
		</td>
	</tr>
</table>

</form>
