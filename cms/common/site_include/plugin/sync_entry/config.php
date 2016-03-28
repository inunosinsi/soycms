
<div class="export_function_block">
<fieldset>
	<legend>記事&rArr;ファイル</legend>
	<form method="post">
		<input type="radio" name="all_entries" id="label_all" value="1" checked="checked" /><label for="label_all">全ての記事</label><br />
		<input type="radio" name="all_entries" id="by_label" value="" checked="checked" /><label for="by_label">ラベルで指定</label><br />
		<div style="padding-left:1em;">
		<?php
		$labels = SOY2DAOFactory::create("cms.LabelDAO")->get();
		foreach($labels as $label){
			echo '<input type="checkbox" name="label[]" id="label_' . $label->getId() .'" value="'.htmlspecialchars($label->getId()).'" />';
			echo '<label for="label_' . $label->getId() .'">'.htmlspecialchars($label->getCaption()).'</label><br/>';
		}
		?>
		</div>
		<input type="submit" name="export" value="ファイルに書き出す" />
	</form>
</fieldset>
</div>

<?php
$targetDir = UserInfoUtil::getSiteDirectory().$this->getTargetDir();
$template_overwrited = array();
$output_date = max($this->output_date,$this->sync_date);
$target_file = "";

$files = (file_exists($targetDir) && is_dir($targetDir)) ? scandir($targetDir) : array();

foreach($files as $file){
	if($file[0] == ".")continue;

	if($output_date < filemtime($targetDir . "/" . $file)){
		$template_overwrited[] = $file;
		continue;
	}
}

?>
<div class="export_function_block">

<form method="post">
<fieldset>
	<legend>ファイル&rArr;記事</legend>
	<?php
	if(!empty($template_overwrited)){
		echo "以下のファイルが書き出し後に編集されています。<br/>";
		foreach($template_overwrited as $key => $file){
			echo '<input type="checkbox" name="imports[]" id="import_file_' . $key .'" value="'.htmlspecialchars($file,ENT_QUOTES).'" checked="checked"/>';
			echo '<label for="import_file_' . $key .'">'.htmlspecialchars($file).'</label><br/>';
		}
	}else{
		echo "編集されたファイルはありません。";
	}
	?>

	<input type="submit" name="import" value="ファイルから読み込む" <?php if(empty($template_overwrited)) echo 'disabled="disabled"';?> />
</filedset>
</form>
</div>

<br style="clear:both" />

<form method="post">
	<fieldset>
	<legend>設定</legend>
		<dl style="margin-left:1em">
			<dt>書き出し先ディレクトリ</dt>
			<dd><?php echo htmlspecialchars(UserInfoUtil::getSiteDirectory()); ?><input class="text" name="targetDir" value="<?php echo htmlspecialchars($this->getTargetDir(),ENT_QUOTES,"UTF-8"); ?>" /></dd>
			<dt>書き出しファイルのモード（属性）</dt>
			<dd><input class="text" name="config[fileMode]" value="<?php echo htmlspecialchars($this->getFileMode(),ENT_QUOTES,"UTF-8"); ?>" /></dd>
			<dt>自動更新</dt>
			<dd>
				<input type="hidden" name="config[autoImport]" value="0"/>
				<input type="checkbox" name="config[autoImport]" value="1" id="auto_import" <?php if($this->autoImport){ echo 'checked="checked"'; } ?> /><label for="auto_import">更新されたファイルがあれば自動的にテンプレートを更新する</label>
			</dd>
			<dt>ファイル名の先頭のIDを0で埋める（例：3桁→001）</dt>
			<dd>
				<select name="config[zeroPaddingWidth]">
					<option value="0">なし</option>
					<?php for($i=2;$i<6;$i++){ ?>
					<option <?php if($this->zeroPaddingWidth == $i) echo 'selected="selected"'; ?>><?php echo $i; ?></option>
					<?php } ?>
				</select>桁
			</dd>
		</dl>
		<div class="section">
			<input type="submit" name="save" value="保存" />
		</div>
	</fieldset>
</form>

<br style="clear:both" />

<form method="post">
<fieldset>
	<legend>ファイルの削除</legend>
	<input type="submit" name="delete" id="delete_files" value="書き出されたファイルを削除する" <?php if($this->countExportedFiles() ==0){ echo 'disabled="disabled"';} ?> />
	<input type="checkbox" name="delete_dir" id="delete_dir" value="1" onclick="if(this.checked){ $('#delete_files').prop('disabled', false);}" <?php if($this->isDirDeletable() == false){ echo 'disabled="disabled"';} ?> /><label for="delete_dir">ディレクトリも削除する</label>
</fieldset>
</form>

<br style="clear:both" />
