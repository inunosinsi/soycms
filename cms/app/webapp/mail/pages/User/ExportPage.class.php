<?php
SOY2HTMLFactory::importWebPage("_common.CommonPartsPage");

class ExportPage extends CommonPartsPage{

	private $logic;

    function __construct() {
    	parent::__construct();
    	$this->redirectCheck();
    	$this->createTag();
    	$this->buildForm();
    }
    
    function buildForm(){
    	$this->addForm("export_form");
    }
    
    function doPost(){
    	
    	if(soy2_check_token()){
    		$logic = SOY2Logic::createInstance("logic.user.ExImportLogic");
    		$this->logic = $logic;
	    	
			$format = $_POST["format"];
			$item = $_POST["item"];
			$displayLabel = @$format["label"];
	
			$charset = (isset($format["charset"])) ? $format["charset"] : "Shift_JIS";
	
			$logic->setSeparator(@$format["separator"]);
			$logic->setQuote(@$format["quote"]);
			$logic->setCharset($charset);
	
			//出力する項目にセット
			$logic->setItems($item);
			$logic->setLabels($this->getLabels());
	   	
	   		//DAO: 2000件ずつデータを取得
			$limit = 2000;
			$step = 0;
			$dao = SOY2DAOFactory::create("SOYMailUserDAO");
			$dao->setLimit($limit);
	   	
	   		do{
				if(connection_aborted())exit;
	
				$dao->setOffset($step * $limit);
				$step++;
	
				try{
					$users = $dao->get();
				}catch(Exception $e){
					$users = array();
				}
				
				//CSV(TSV)に変換
				$lines = array();
				foreach($users as $user){
					$user->setBirthday($this->convertBirthday($user->getBirthday()));
					$lines[] = $logic->export($user);
				}
				
				//ファイル出力
				$this->outputFile($lines, $charset, $displayLabel);
	
			}while(count($users) >= $limit);
	   		
	    	exit;
    	}
	    	
    }
    
    function convertBirthday($timestamp){
    	$birthday = null;
    	
    	if(isset($timestamp) && $timestamp > 0){
    		$birthday = date("Y", $timestamp) . "-" . date("m", $timestamp) . "-" . date("d", $timestamp);
    	}
    	
    	return $birthday;
    }
    
    function getLabels(){
		return array(
			"id" => "ID",

			"mailAddress" => "メールアドレス",
			"name" => "名前",
			"reading" => "フリガナ",
			"nickname" => "ニックネーム",
			"genderText" => "性別",
			"birthdayText" => "生年月日",

			"zipCode" => "郵便番号",
			"areaText" => "住所（都道府県）",
			"address1" => "住所１",
			"address2" => "住所２",
			"telephoneNumber" => "電話番号",
			"faxNumber" => "FAX番号",

			"cellphoneNumber" => "携帯電話",
			"url" => "URL",
			"jobName" => "勤務先名称・職種",
			"jobZipCode" => "勤務先郵便番号",
			"jobAreaText" => "勤務先住所（都道府県）",
			"jobAddress1" => "勤務先住所１",
			"jobAddress2" => "勤務先住所２",

			"jobTelephoneNumber" => "勤務先電話番号",
			"jobFaxNumber" => "勤務先FAX番号",
			"attribute1" => "属性１",
			"attribute2" => "属性２",
			"attribute3" => "属性３",
			"memo" => "備考",
		);
	}
    
    function outputFile($lines, $charset, $displayLabel){
    	static $headerSent = false;

		if(!$headerSent){
			$headerSent = true;
			header("Cache-Control: no-cache");
			header("Pragma: no-cache");
			header("Content-Disposition: attachment; filename=mailusers-" . date("YmdHis") . ".csv");
			header("Content-Type: text/csv; charset=" . htmlspecialchars($charset) . ";");

			if($displayLabel){
				echo $this->logic->getHeader();
				echo "\r\n";
			}
		}
		
		if(count($lines) > 0){
			echo implode("\r\n", $lines);
			echo "\r\n";
		}
	}

}
?>