<form method="post">
<?php
/*
 * Created on 2009/07/08
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
$labels = SOY2DAOFactory::create("cms.LabelDAO")->get();
$config = $this->getLabelConfig();

echo '<table class="list" style="width:80%;">';
echo '<th>ラベル名';
echo '<th>設定';
echo '</tr>';

foreach($labels as $label){
	$labelId = $label->getId();
	
	echo '<tr>';
	echo '<td>';
	echo $label->getIcon();
	echo $label->getCaption();
	echo '</td>';
	echo '<td>';
	
	$selected = (isset($config[$labelId]) && $config[$labelId] == 1);
	
	echo '<select name="labelConfig['.$labelId.']">';
	echo '<option value="0">有効</option>';
	if($selected){
		echo '<option value="1" selected>無効にする</option>';
	}else{
		echo '<option value="1">無効にする</option>';
	}
	echo '</select>';
	
	echo '</td>';
	echo '</tr>';
}

echo '</table>';
?>

<div style="text-align:center">
<input type="submit" class="submit_btn" value="保存" />
</div>

</form>
