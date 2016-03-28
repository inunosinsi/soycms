
	<div class="export_function_block">
	<form method="post">
	<fieldset>
		<legend>テンプレート&rArr;ファイル</legend>
		<input type="submit" name="export" value="ファイルに書き出す" />
	</fieldset>
	</form>
	</div>

	<div class="export_function_block">
	<form method="post">
	<fieldset>
		<legend>ファイル&rArr;テンプレート</legend>
<?php
	$modifiedFiles = $this->getModifiedFiles();
	if(!empty($modifiedFiles)){
		if($this->ignoreTimestamp){
			echo "以下の".count($modifiedFiles)."ファイルから読み込むファイルを選んでください。";
			echo '<input type="submit" name="NotIgnoreTimestamp" value="編集されたファイルのみ表示する" />';
		}else{
			echo "以下の".count($modifiedFiles)."ファイルが書き出し後に編集されています。";
			echo '<input type="submit" name="ignoreTimestamp" value="編集されていないファイルも表示する" />';
		}

		echo '<input type="checkbox" name="__check_all__" id="toggle_checked" onclick="toggle_file_checks(this.checked);" '.($this->ignoreTimestamp?'':'checked="checked"').' />';
		echo '<label for="toggle_checked">チェック切替え</label><br/>';

		foreach($modifiedFiles as $key => $file){
			if(strpos($file,".txt")!==false || strpos($file,".xls")!==false)continue;
			echo '<input type="checkbox" name="imports[]" class="file_check" id="import_file_' . $key .'" value="'.htmlspecialchars($file).'" '.($this->ignoreTimestamp?'':'checked="checked"').' />';
			echo '<label for="import_file_' . $key .'">'.htmlspecialchars($file).'</label><br/>';
		}
	}else{
		echo "編集されたファイルはありません。";
		if($this->ignoreTimestamp){
		}else{
			echo '<input type="submit" name="ignoreTimestamp" value="編集されていないファイルも表示する" /><br/>';
		}
	}
?>

		<input type="submit" name="import" value="ファイルから読み込む" <?php if(empty($modifiedFiles)) echo 'disabled="disabled"';?> />

	</fieldset>
	</form>
	</div>

<br style="clear:both" />

<div>
<form method="post">
<fieldset>
	<legend>設定</legend>
	<input type="hidden" name="config[convert_url]" value="0"/>
	<input type="checkbox" name="config[convert_url]" value="1" id="convert_url" <?php if($this->convertURL){ echo 'checked="checked"'; } ?> /><label for="convert_url">URLの書き換えを行う</label>
	<br/>
	<input type="hidden" name="config[auto_import]" value="0"/>
	<input type="checkbox" name="config[auto_import]" value="1" id="auto_import" <?php if($this->autoImport){ echo 'checked="checked"'; } ?> /><label for="auto_import">更新されたファイルがあれば自動的にテンプレートを更新する</label>
	<br/>
	<input type="hidden" name="config[useExtPhpIfPhpAllowed]" value="0"/>
	<input type="checkbox" name="config[useExtPhpIfPhpAllowed]" value="1" id="useExtPhpIfPhpAllowed" <?php if($this->useExtPhpIfPhpAllowed){ echo 'checked="checked"'; } ?> /><label for="useExtPhpIfPhpAllowed">出力時の拡張子を.htmlではなく.phpにする（インポートはどちらでも可）</label>
	<br/>
	ファイル名の先頭のIDを0で埋める（例：001）<select name="config[zeroPaddingWidth]">
		<option value="0">なし</option>
		<?php for($i=1;$i<5;$i++){ ?>
		<option <?php if($this->zeroPaddingWidth == $i) echo 'selected="selected"'; ?>><?php echo $i; ?></option>
		<?php } ?>
	</select>桁
	<br/>
	<input type="submit" name="config[]" value="設定"/>
</fieldset>
</form>
</div>

<br style="clear:both" />

<div>
<form method="post">
<fieldset>
	<legend>ファイルの削除</legend>
	<input type="submit" name="delete" id="delete_files" value="書き出されたファイルを削除する" <?php if($this->countExportedFiles() ==0){ echo 'disabled="disabled"';} ?> />
	<input type="checkbox" name="delete_dir" id="delete_dir" value="1" onclick="if(this.checked){ $('#delete_files').prop('disabled', false);}" <?php if($this->isDirDeletable() == false){ echo 'disabled="disabled"';} ?> /><label for="delete_dir">ディレクトリも削除する</label>
</fieldset>
</form>
</div>

<br style="clear:both" />

<script type="text/javascript">
	function toggle_file_checks(value){
		$("input.file_check").each(function(){
			$(this).prop("checked", value);
		});
	}
</script>