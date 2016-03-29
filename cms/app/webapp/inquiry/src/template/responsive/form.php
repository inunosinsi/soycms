<form method="post" enctype="multipart/form-data">

<div class="soy_inquiry_message">
	<?php $message = $config->getMessage(); echo $message["information"]; ?>
</div>

<div class="soy_iqnuiry_responsive">
	<dl>
<?php $counter = 0; ?>
<?php $columnCount = count($columns); ?>
<?php foreach($columns as $column){
	
	/**
	 * @プライバシーポリシーは必ず一行目で別テーブルで表示する
	 */
	
	$id = $column->getId();
	$obj = $column->getColumn();
	$label = $obj->getLabel();
	$annotation = $obj->getAnnotation();
	
	echo "<dt>";
	echo $label;
	if($column->getRequire()){
		echo "<span style=\"color:red;\">(必須)</span>";
	}
	echo "</dt>\n";
	echo "<dd>\n";
	 if(isset($errors[$id])){
    	echo "<p class=\"error_message\">";
    	echo $errors[$id];
    	echo "</p>";
    } 
    echo "\t".$obj->getForm();
    if(isset($annotation) && strlen($annotation)){
    	echo "&nbsp;".$annotation;
    }
    echo "\n</dd>\n";
  	
	$counter++;
}
?>
	</dl>
</div>

<div style="margin-top:10px;text-align:center;">
	<input name="data[hash]" type="hidden" value="<?php echo $random_hash; ?>">
	<p class="textcenter"><input name="confirm" type="submit" value="入力内容の確認"></p>
</div>
</form>