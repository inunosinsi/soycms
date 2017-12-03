<?php

class MailUserExImportLogic{
	const MUST  = 100;
	const MAY   = 1;
	const NEVER = 0;
	
	const SEPARATOR_TYPE_TAB = "tab";
	const SEPARATOR_TYPE_COMMA = "comma";
	const SEPARATOR_TAB = "\t";
	const SEPARATOR_COMMA = ",";
	
	const DATE_FORMAT = "Y-m-d";
	
	const CHARSET_SJIS = "Shift_JIS";
	const CHARSET_UTF8 = "UTF-8";
	
	private static $charset = self::CHARSET_UTF8;
	private static $separator_type = self::SEPARATOR_TYPE_COMMA;
	private static $separator = self::SEPARATOR_COMMA;
	private static $quotate = true;
	
	private static $config = array(
		"id"                 => array("name" => "メールID",               "isExported" => self::MAY,   "isImported" => self::MAY),
		"mailAddress"        => array("name" => "メールアドレス",         "isExported" => self::MUST,  "isImported" => self::MUST),
        "name"               => array("name" => "名前",                   "isExported" => self::MAY,   "isImported" => self::MAY),
        "reading"            => array("name" => "フリガナ",               "isExported" => self::MAY,   "isImported" => self::MAY),
        "gender"             => array("name" => "性別",                   "isExported" => self::MAY,   "isImported" => self::MAY),
        "birthday"           => array("name" => "生年月日",               "isExported" => self::MAY,   "isImported" => self::MAY),
        "zipCode"            => array("name" => "郵便番号",               "isExported" => self::MAY,   "isImported" => self::MAY),
        "area"               => array("name" => "住所（都道府県）",       "isExported" => self::MAY,   "isImported" => self::MAY),
        "address1"           => array("name" => "住所１",                 "isExported" => self::MAY,   "isImported" => self::MAY),
        "address2"           => array("name" => "住所２",                 "isExported" => self::MAY,   "isImported" => self::MAY),
        "telephoneNumber"    => array("name" => "電話番号",               "isExported" => self::MAY,   "isImported" => self::MAY),
        "faxNumber"          => array("name" => "FAX番号",                "isExported" => self::MAY,   "isImported" => self::MAY),
        "cellphoneNumber"    => array("name" => "携帯電話",               "isExported" => self::MAY,   "isImported" => self::MAY),
        "jobName"            => array("name" => "勤務先名称・職種",       "isExported" => self::MAY,   "isImported" => self::MAY),
        "jobZipCode"         => array("name" => "勤務先郵便番号",         "isExported" => self::MAY,   "isImported" => self::MAY),
        "jobArea"            => array("name" => "勤務先住所（都道府県）", "isExported" => self::MAY,   "isImported" => self::MAY),
        "jobAddress1"        => array("name" => "勤務先住所１",           "isExported" => self::MAY,   "isImported" => self::MAY),
        "jobAddress2"        => array("name" => "勤務先住所２",           "isExported" => self::MAY,   "isImported" => self::MAY),
        "jobTelephoneNumber" => array("name" => "勤務先電話番号",         "isExported" => self::MAY,   "isImported" => self::MAY),
        "jobFaxNumber"       => array("name" => "勤務先FAX番号",          "isExported" => self::MAY,   "isImported" => self::MAY),
        "notSend"         	 => array("name" => "メール配信可否",         "isExported" => self::MAY,   "isImported" => self::MAY),
        "attribute1"         => array("name" => "属性１",                 "isExported" => self::MAY,   "isImported" => self::MAY),
        "attribute2"         => array("name" => "属性２",                 "isExported" => self::MAY,   "isImported" => self::MAY),
        "attribute3"         => array("name" => "属性３",                 "isExported" => self::MAY,   "isImported" => self::MAY),
        "memo"               => array("name" => "備考",                   "isExported" => self::MAY,   "isImported" => self::MAY),
        "mailErrorCount"     => array("name" => "メール配信失敗回数",     "isExported" => self::NEVER, "isImported" => self::NEVER),
        "registerDate"       => array("name" => "登録年月日",             "isExported" => self::NEVER, "isImported" => self::NEVER),
        "updateDate"         => array("name" => "更新年月日",             "isExported" => self::NEVER, "isImported" => self::NEVER),
	);

    /**
     * GET_PROPERTIES
     * ユーザーのプロパティのうち、$itemのキーに含まれる項目を返す
     * @param Array $item array("property" => "checked", ...)
     * @param String $mode "export" or "import"
     * @return Array
     */
    private static function GET_PROPERTIES(Array $item, $mode){
		if($mode !== "isExported" AND $mode !== "isImported") return array();

		$properties = array();
		foreach(MailUserExImportLogic::$config as $property => $array){
			switch(MailUserExImportLogic::$config[$property][$mode]){
				case MailUserExImportLogic::MUST :
					$properties[] = $property;
					break;
				case MailUserExImportLogic::MAY :
					if(array_key_exists($property, $item)){
						$properties[] = $property;
					}else{
						//do nothing
					}
					break;
				case MailUserExImportLogic::NEVER :
				default:
					//do nothing
					break;
			}
		}
		return $properties;	
    }
    
    /**
     * GET_PROPERTIES_TO_EXPORT
     * 出力する項目を返す
     * @param Array $item array("exported_property" => "checked", ...)
     * @return Array
     */
    static function GET_PROPERTIES_TO_EXPORT(Array $item){
		return self::GET_PROPERTIES($item, "isExported");
    }
    
    /**
     * GET_PROPERTIES_TO_IMPORT
     * 入力する項目を返す
     * @param Array $item array("imported_property" => "checked", ...)
     * @return Array
     */
    static function GET_PROPERTIES_TO_IMPORT(Array $item){
		return self::GET_PROPERTIES($item, "isImported");
    }
    
	/**
	 * 文字コードをセットする
     * @param String
	 */
	static function SET_CHARSET($value){
		self::$charset = ($value === self::CHARSET_SJIS) ? self::CHARSET_SJIS : self::CHARSET_UTF8 ;
	}

    /**
     * 区切り文字のタイプをセットする：コンマ、タブ
     * @param String
     */
    static function SET_SEPARATOR($value){
    	if($value === self::SEPARATOR_TYPE_TAB){
	    	self::$separator_type = self::SEPARATOR_TYPE_TAB;
	    	self::$separator      = self::SEPARATOR_TAB;
    	}else{
	    	self::$separator_type = self::SEPARATOR_TYPE_COMMA;
	    	self::$separator      = self::SEPARATOR_COMMA;
    	}
    }
    
	/**
	 * ダブルクオーテーションで括るかどうかをセットする
     * @param String
     * @return Boolean
	 */
	static function SET_QUOTATE($value){
		self::$quotate = (strlen($value)) ? true : false ;
	}

    /**
     * 指定された書式に従って値をダブルクオーテーションでくくる
     * @param String
     * @return String
     */
    static function QUOTATE($value){
    	if(
    	  self::$quotate
    	  OR ( strpos($value, "\n") !== false ) 
    	  OR ( strpos($value, "\r") !== false ) 
    	  OR ( self::$separator_type === self::SEPARATOR_TYPE_TAB   AND strpos($value, self::SEPARATOR_TAB) !== false )
    	  OR ( self::$separator_type === self::SEPARATOR_TYPE_COMMA AND strpos($value, self::SEPARATOR_COMMA) !== false )
    	){
    		$value = '"' . str_replace('"', '""', $value) . '"';
    	}
    	
    	return $value;
    }
    
    /**
     * 指定された書式に従って値からダブルクオーテーションを外す
     * @param String
     * @return String
     */
    static function DEQUOTATE($value){
    	if(
    	  self::$quotate 
    	  OR ( strpos($value, "\n") !== false ) 
    	  OR ( strpos($value, "\r") !== false ) 
    	  OR ( self::$separator_type === self::SEPARATOR_TYPE_TAB   AND strpos($value, self::SEPARATOR_TAB) !== false )
    	  OR ( self::$separator_type === self::SEPARATOR_TYPE_COMMA AND strpos($value, self::SEPARATOR_COMMA) !== false )
		){
    		$value = substr($value, 1, strlen($value)-2);
			$value = str_replace('""', '"', $value);
    	}

    	return $value;
    }
    
    /**
     * 指定された書式に従ってTSVまたはCSVをばらして、ダブルクオーテーションを外す
     * @param String
     * @return Array
     */
    static function EXPLODE($line){
		if( self::$separator_type === self::SEPARATOR_TYPE_TAB ){
			preg_match_all('/([^"]*?(?:"[^"]*?"[^"]*?)*)(?:\t|\r\n|\r|\n|$)/', $line, $values);
		}else{
			preg_match_all('/([^"]*?(?:"[^"]*?"[^"]*?)*)(?:,|\r\n|\n|\r|$)/', $line, $values);
		}
		
		$values = $values[1];
		
		foreach($values as $key => $value){
			$values[$key] = self::DEQUOTATE($value);
		}

    	return $values;
    }
    
    /**
     * 指定された書式に従ってダブルクオーテーションを付けてTSVまたはCSVにする
     * @param Array
     * @return String
     */
    static function IMPLODE($array){
    	foreach($array as $key => $value){
    		$array[$key] = MailUserExImportLogic::QUOTATE($value);
    	}
 		return implode(MailUserExImportLogic::$separator, $array);
    }
    
    /**
     * Shift_JISが指定されていればSHIFT_JISに変換する
     * @param String
     * @returm String
     */
    static function ENCODE($value){
    	if( self::$charset === self::CHARSET_SJIS ){
    		return mb_convert_encoding($value, self::CHARSET_SJIS, self::CHARSET_UTF8);
    	}else{
    		return $value;
    	}
    }
    /**
     * Shift_JISが指定されていればSHIFT_JISからUTF-8に変換する
     * @param String
     * @returm String
     */
    static function DECODE($value){
    	if( self::$charset === self::CHARSET_SJIS ){
    		return mb_convert_encoding($value, self::CHARSET_UTF8, self::CHARSET_SJIS);
    	}else{
    		return $value;
    	}
    }
    
    /**
     * 指定された区切り文字が含まれるかどうか
     * @param String
     * @return Boolean
     */
    static function HAS_SEPARATOR($line){
    	return (strpos($line, self::$separator) !== false ) ? true : false ;
    }
}
?>