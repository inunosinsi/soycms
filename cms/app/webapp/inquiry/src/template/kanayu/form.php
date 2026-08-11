<?php $message = $config->getMessage(); echo $message["information"]; ?>
<form method="post" enctype="multipart/form-data">

<?php 
$counter = 0;
$columnCount = count($columns);
$dummyFormObj = new SOYInquiry_Form();
foreach($columns as $column){
	//連番カラムは表示しない
	if($column->getType() == "SerialNumber") continue;

	/**
	 * @プライバシーポリシーは必ず一行目で別テーブルで表示する
	 */

	echo "<div>\n";

	$id = $column->getId();
	$obj = $column->getColumn($dummyFormObj);
	$label = $obj->getLabel();
	$annotation = $obj->getAnnotation();

	echo "<label class=\"block text-sm font-bold text-gray-700 mb-1\" style=\"margin-top:10px;\">";
	echo $label;
	if($column->getRequire()){
		echo "<span class=\"text-red-500\">*</span>";
	}
	echo "</label>\n";
	// if(isset($errors[$id])){
    	// echo "<p class=\"alert alert-danger\" data-role=\"alert\">";
    	// echo $errors[$id];
    	// echo "</p>";
    // }

	$form = $obj->getForm();

	//form-controlを付与する
	if($column->getType() == "AddressJs"){

	}else{
		if(is_bool(strpos($form, "class="))){
			$form = preg_replace("/>/", " class=\"form-control\">", $form, 1);
		}else{
			if(is_bool(strpos($form, "form-control"))){
				preg_match('/class=\".*?\"/', $form, $tmp);
				if(isset($tmp[0])){
					$prop = trim($tmp[0], "\"") . " form-control\"";
					$form = preg_replace('/class=\".*?\"/', $prop, $form);
				}
			}
		}
	}

    echo "\t". $form;
    if(isset($annotation) && strlen($annotation)){
    	echo "&nbsp;".$annotation;
    }

	echo "\n</div>\n";

	$counter++;
}
?>

	<!-- プライバシーポリシー同意 -->
	<div class="flex items-center justify-center p-4 bg-gray-50 rounded-lg">
	    <label class="flex items-center cursor-pointer">
	        <input type="checkbox" id="policy_agree" required class="w-5 h-5 text-orange-600 rounded border-gray-300 focus:ring-orange-500">
	        <span class="ml-3 text-sm text-gray-600">
	            <button type="button" onclick="toggleModal()" class="text-orange-600 underline font-bold hover:text-orange-700">個人情報の取り扱い</button>に同意する
	        </span>
	    </label>
	</div>

	<div class="text-center">
		<input name="data[hash]" type="hidden" value="<?php echo $random_hash; ?>" />
	    <input type="submit" name="confirm" class="w-full sm:w-auto px-12 py-4 bg-orange-500 text-white font-extrabold rounded-full text-lg shadow-xl hover:bg-orange-600 transition transform hover:scale-105 active:scale-95" value="内容を確認する">
	</div>
</form>
