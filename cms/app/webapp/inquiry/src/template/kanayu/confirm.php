<?php $message = $config->getMessage(); echo $message["confirm"]; ?>

<form method="post" id="soy_inquiry_form">

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


	echo "<div class=\"py-4 grid grid-cols-1 sm:grid-cols-3 gap-2\">\n";
	if(strlen((string)$label)){
		echo "<dt class=\"text-sm font-bold text-gray-500\">".$label."</dt>\n";
	}
	echo "<dd class=\"text-base text-gray-900 sm:col-span-2 font-medium\">".$view."</dd>\n";
	echo "</div>\n";
}
?>

<?php
echo $hidden_forms;
?>

<div class="flex flex-col sm:flex-row gap-4 justify-center pt-6">
    <input type="submit" name="form" class="px-10 py-4 bg-gray-200 text-gray-700 font-bold rounded-full hover:bg-gray-300 transition" value="入力画面に戻る">
    <input type="submit" name="send" class="px-12 py-4 bg-orange-500 text-white font-extrabold rounded-full text-lg shadow-xl hover:bg-orange-600 transition transform hover:scale-105 active:scale-95" value="この内容で送信する">
</div>

</form>
