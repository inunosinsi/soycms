<?php
SOY2::import("logic.csv.ExImportLogicBase");
class ExImportLogic extends ExImportLogicBase{
	
	private $pluginObj;
	
	function ExImportLogic(){
		
	}
	
	function importFile(){
		$file = $_FILES["import_file"];
		
		//ファイルを選択していない場合は処理を停止する
		if(!isset($file["tmp_name"]) || strlen($file["tmp_name"]) === 0) return false;
		
		set_time_limit(0);
		
		//まず削除
		$this->pluginObj->deleteAllFields();
		
		$displayLabel = 1;				
		$charset = (isset($_POST["format"]["charset"]))? $_POST["format"]["charset"] : "Shift-JIS";
		$quote = (isset($_POST["format"]["quote"])) ? $_POST["format"]["quote"] : null;
		$separator = (isset($_POST["format"]["separator"])) ? $_POST["format"]["separator"] : "comma";
			
		$this->setSeparator($separator);
		$this->setQuote($quote);
		$this->setCharset($charset);
		
		if(!$this->checkUploadedFile($file) || !$this->checkFileContent($file)){
			return false;
		}
		
		//ファイル読み込み・削除
		$fileContent = file_get_contents($file["tmp_name"]);
		unlink($file["tmp_name"]);
		
		//文字コードの変更
		$fileContent = $this->encodeFrom($fileContent);
		
		//データを行単位にばらす
		$lines = $this->GET_CSV_LINES($fileContent);	//fix multiple lines
		if(isset($_POST["format"]["label"]) && $_POST["format"]["label"] == 1){
			array_shift($lines);
		}
		
		foreach($lines as $line){
				if(strlen($line) === 0) continue;
				$item = $this->explodeLine($line);
				$this->pluginObj->insertField(new CustomField(array(
					"id"    => @$item[0],
					"label" => @$item[1],
					"type"  => @$item[2],
					"labelId"  => @$item[6],
					"option" => @$item[4],
					"output"  => @$item[7],
					"defaultValue"  => @$item[9],
					"emptyValue"  => @$item[11],
					"hideIfEmpty"  => (boolean)@$item[10],
					"description" => @$item[8]
			)));
		}
		
		CMSPlugin::savePluginConfig($this->pluginObj->getId(), $this->pluginObj);
		CMSUtil::notifyUpdate();
		CMSPlugin::redirectConfigPage();
	}
	
	function exportFile($fields){
		set_time_limit(0);
		
		$charset = (isset($_POST["format"]["charset"])) ? $_POST["format"]["charset"] : "Shift-JIS";
		$quote = (isset($_POST["format"]["quote"])) ? $_POST["format"]["quote"] : null;
		$separator = (isset($_POST["format"]["separator"])) ? $_POST["format"]["separator"] : "comma";
		$this->setCharset($charset);
		$this->setQuote($quote);
		$this->setSeparator($separator);
		$this->setLabels($this->getLabelArray());

		header("Cache-Control: public");
		header("Pragma: public");
		header("Content-Disposition: attachment; filename=" . $this->pluginObj->getId() . "_config_" .date("YmdHis", time()) . ".csv");
		header("Content-Type: text/csv; charset=" . $this->getCharset() . ";");
		
		ob_start();
		if(isset($_POST["format"]["label"]) && $_POST["format"]["label"] == 1){
			echo $this->getHeader();
			echo "\n";	
		}		
		echo implode("\n", $this->getLines($fields));
		$csv = ob_get_contents();
		ob_end_clean();
			
		echo $csv;
	}
	
	function getLines($fields){
		
		$lines = array();
		
		foreach($fields as $field){
			$line = array();
			
			$line[] = $field->getId();
			$line[] = $field->getLabel();
			$line[] = $field->getType();
			$line[] = $field->getValue();
			$line[] = $field->getOption();
			$line[] = $field->getShowInput();
			$line[] = $field->getLabelId();
			$line[] = $field->getOutput();
			$line[] = $field->getDescription();
			$line[] = $field->getDefaultValue();
			$line[] = $field->getHideIfEmpty();
			$line[] = $field->getEmptyValue();
			
			$lines[] = $this->encodeTo($this->implodeToLine($line));
		}
		
		return $lines;
	}
	
	function getLabelArray(){
		$labels = array();
		
		$labels[] = "ID";
		$labels[] = "ラベル名";
		$labels[] = "タイプ";
		$labels[] = "値";	//おそらく使用しないけど念の為
		$labels[] = "オプション";	//おそらく使用しない
		$labels[] = "showInput";
		$labels[] = "ラベルID";
		$labels[] = "出力";
		$labels[] = "フォームの説明";
		$labels[] = "初期値";
		$labels[] = "空の場合表示しない";
		$labels[] = "空の場合の表示";
		
		return $labels;
	}
	
	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
?>