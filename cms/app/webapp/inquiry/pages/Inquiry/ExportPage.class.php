<?php

class ExportPage extends WebPage{

	function doPost(){

		if(isset($_POST["output"])){

			$formId = (isset($_POST["formId"])) ? $_POST["formId"] : null;
			$start = (isset($_POST["start"]) && $_POST["start"] != "投稿日時（始）") ? $_POST["start"] : null;
			$end = (isset($_POST["end"]) && $_POST["end"] != "投稿日時（終）") ? $_POST["end"] : null;
			$flag = (isset($_POST["flag"])) ? $_POST["flag"] : null;
			$charset = (isset($_POST["charset"])) ? $_POST["charset"] : "UTF-8";
			$this->setCharset($charset);

			$formDao = SOY2DAOFactory::create("SOYInquiry_FormDAO");
    		$form = $formDao->getById($formId);
			$columnDao = SOY2DAOFactory::create("SOYInquiry_ColumnDAO");
    		$columns = $columnDao->getOrderedColumnsByFormId($formId);

    		$func = "";

    		$counter = 0;
    		$label = '"ID","投稿時刻","受付番号",';
    		foreach($columns as $column){
    			$counter++;

    			$label .= "\"" . $column->getLabel() . "\"";

    			$func .= 'echo "\\"";';
    			$func .= 'if(!is_array($array['.$column->getId().']))$array['.$column->getId().']=array($array['.$column->getId().']);';
    			$func .= 'echo str_replace(array("\\r","\\n","\\""),array(" "," ", "\\"\\""),implode(" ", $array['.$column->getId().']));';

    			if($counter < count($columns)){
    				$label .= ",";
    				$func .= 'echo "\\",";';
    			}else{
    				$label .= "\r\n";
    				$func .= 'echo "\\"\\r\\n";';
    			}
    		}

			$function = function($array) use ($func) { return eval($func); };

			$dao = SOY2DAOFactory::create("SOYInquiry_InquiryDAO");
    		$inquiries = $dao->search($formId, strtotime($start), strtotime($end), null, $flag);

    		header("Cache-Control: public");
			header("Pragma: public");
	    	header("Content-Disposition: attachment; filename=inquiry_".$form->getFormId() . "_" . date("YmdHis").".csv");
    		header("Content-Type: text/csv; charset=".$this->charset.";");

    		error_reporting(0);
    		set_time_limit(60);

    		ob_start();
    		echo $label;

    		foreach($inquiries as $inquiry){

    			echo "\"" . $inquiry->getId() . "\",";
    			echo "\"" . date("Y-m-d H:i:s",$inquiry->getCreateDate()) . "\",";
    			echo "\"" . $inquiry->getTrackingNumber() . "\",";

    			$data = $inquiry->getDataArray();
				$function($data);
    		}

    		$csv = ob_get_contents();
    		ob_end_clean();
    		echo $this->encodeTo($csv);
		}

		exit;
	}


	private $charset;

    function __construct() {
		SOY2DAOFactory::importEntity("SOYInquiry_Inquiry");
    	parent::__construct();

		$formId = (isset($_GET["formId"]) && strlen($_GET["formId"])>0) ? $_GET["formId"] : null;
		$start = (isset($_GET["start"]) && $_GET["start"] != "投稿日時（始）") ? $_GET["start"] : null;
		$end = (isset($_GET["end"]) && $_GET["end"] != "投稿日時（終）") ? $_GET["end"] : null;
		$flag = (isset($_GET["flag"])) ? $_GET["flag"] : null;

    	$this->addForm("export_form", array(
    		"method" => "POST",
    		"onsubmit" => "if(this.start.value == '投稿日時（始）'){this.start.value = '';}"
    		             ."if(this.end.value == '投稿日時（終）'){this.end.value = '';}"
    	));

    	$formDao = SOY2DAOFactory::create("SOYInquiry_FormDAO");
    	try{
    		$forms = $formDao->get();
    	}catch(Exception $e){
    		$forms = array();
    	}

    	$this->addSelect("forms", array(
    		"name" => "formId",
			"options" => $forms,
    		"property" => "name",
    	));

    	$this->addInput("start", array(
    		"name" => "start",
    		"value" => (strlen($start) >0) ? $start : "投稿日時（始）",
    		"style" => (strlen($start) >0) ? "" : "color: grey;",
    		"onfocus" => "if(this.value == '投稿日時（始）'){ this.value = ''; this.style.color = '';}",
    		"onblur"  => "if(this.value.length == 0){ this.value='投稿日時（始）'; this.style.color = 'grey'}"
    	));

    	$this->addInput("end", array(
    		"name" => "end",
    		"value" => (strlen($end) >0) ? $end : "投稿日時（終）",
    		"style" => (strlen($end) >0) ? "" : "color: grey;",
    		"onfocus" => "if(this.value == '投稿日時（終）'){ this.value = ''; this.style.color = '';}",
    		"onblur"  => "if(this.value.length == 0){ this.value='投稿日時（終）'; this.style.color = 'grey'}"
    	));

    	$flags = array(
    		"" => "全て",
    		SOYInquiry_Inquiry::FLAG_NEW => "未読のみ",
    		SOYInquiry_Inquiry::FLAG_READ => "既読のみ",
    		SOYInquiry_Inquiry::FLAG_DELETED => "削除済"
    	);

    	$this->addSelect("flag", array(
    		"name" => "flag",
    		"options" => $flags,
    		"indexOrder" => true,
    		"selected" => ""
    	));

    	$this->addCheckBox("charset_sjis", array(
    		"name" => "charset",
    		"value" => "Shift_JIS",
    		"label" => "Shift_JIS",
    		"selected" => true
    	));

    	$this->addCheckBox("charset_utf8", array(
    		"name" => "charset",
    		"value" => "UTF-8",
    		"label" => "UTF-8"
    	));
    }

	function setCharset($charset) {

		switch($charset){
			case "Shift-JIS":
			case "Shift_JIS":
				$charset = "Shift_JIS";
				break;
			default:
				$charset = "UTF-8";
				break;
		}

		$this->charset = $charset;
	}

    function encodeTo($str){
		if($this->charset != "UTF-8"){
			return mb_convert_encoding($str, $this->charset, "UTF-8");
		}

		return $str;
    }
}
