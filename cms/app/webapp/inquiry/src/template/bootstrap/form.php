<form method="post" enctype="multipart/form-data">

<div class="alert alert-info" data-role="alert">
	<?php $message = $config->getMessage(); echo $message["information"]; ?>
</div>

<?php $counter = 0; ?>
<?php $columnCount = count($columns); ?>
<?php foreach($columns as $column){
	//連番カラムは表示しない
	if($column->getType() == "SerialNumber") continue;

	/**
	 * @プライバシーポリシーは必ず一行目で別テーブルで表示する
	 */

	echo "<div class=\"form-group\">\n";

	$id = $column->getId();
	$obj = $column->getColumn();
	$label = $obj->getLabel();
	$annotation = $obj->getAnnotation();

	echo "<label>";
	echo $label;
	if($column->getRequire()){
		echo "<span style=\"color:red;\">(必須)</span>";
	}
	echo "</label>\n";
	 if(isset($errors[$id])){
    	echo "<p class=\"alert alert-danger\" data-role=\"alert\">";
    	echo $errors[$id];
    	echo "</p>";
    }

	$form = $obj->getForm();

	//form-controlを付与する
	if($column->getType() == "AddressJs"){

	}else{
		if(!strpos($form, "class=")){
			$form = preg_replace("/>/", " class=\"form-control\">", $form, 1);
		}else{
			if(!strpos($form, "form-control")){
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

<div class="text-center">
	<input name="data[hash]" type="hidden" value="<?php echo $random_hash; ?>">
	<p class="textcenter"><input name="confirm" type="submit" class="btn btn-primary btn-lg" value="入力内容の確認"></p>
</div>
</form>
