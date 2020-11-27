<?php
class DateLabel extends HTMLLabel{

	private $defaultFormat = null;
	private $year;
	private $month;
	private $day;
	private $time = null;

	function execute(){
		//日時データ：textまたはyear,month,dayで指定
		if(is_null($this->time) || strlen($this->time) === 0){
			if(strlen($this->text)){
				$this->time = $this->text;
				$this->year = date("Y",$this->time);
				$this->month = date("n",$this->time);
				$this->day = date("j",$this->time);
			}else{
				$this->time = mktime(0,0,0,max(1,$this->month),max(1,$this->day),$this->year);
			}
		}

		//フォーマット
		$format = $this->getAttribute("cms:format");
		if(strlen($format)==0){
			if(is_null($this->defaultFormat) || strlen($this->defaultFormat) == 0){
				$format = "Y-m-d H:i:s";
			}else{
				$format = $this->defaultFormat;
			}
		}

		//条件付きフォーマット
		$format = self::ParseConditionalDateFormat($format, $this->time, $this->year, $this->month, $this->day);

		$this->setText(date($format,$this->time));

		parent::execute();
	}

	function getDefaultFormat() {
		return $this->defaultFormat;
	}
	function setDefaultFormat($defaultFormat) {
		$this->defaultFormat = $defaultFormat;
	}

	function setYear($v){
		$this->year = $v;
	}
	function setMonth($v){
		$this->month = $v;
	}
	function setDay($v){
		$this->day = $v;
	}

	/**
	 * 条件付きフォーマットを解釈・置換する
	 */
	public static function ParseConditionalDateFormat($format, $time, $year, $month, $day){
		//preg_replaceのe修飾子は5.5.0で非推奨になり、7以降は非対応
		//preg_replace_callbackは4.0.5から使えるが無名関数は5.3.0以降
		//と言うわけで、5.2系でも7以降でも問題なく動作するためには、preg_replace＋e修飾子もpreg_replace_callback＋無名関数も使えない
		$matches = array();

		if(preg_match("/%DATE:([^%]*)%/u",$format,$matches) && strlen($matches[0])){
			$format = strtr($format, array($matches[0] => date($matches[1], $time)));
		}

		if(preg_match("/%Y:([^%]*)%/u",$format,$matches) && strlen($matches[0])){
			$format = strtr($format, array($matches[0] => strlen( $year)  ? date($matches[1], $time) : ""));
		}

		if(preg_match("/%M:([^%]*)%/u",$format,$matches) && strlen($matches[0])){
			$format = strtr($format, array($matches[0] => strlen( $month) ? date($matches[1], $time) : ""));
		}

		if(preg_match("/%D:([^%]*)%/u",$format,$matches) && strlen($matches[0])){
			$format = strtr($format, array($matches[0] => strlen( $day)   ? date($matches[1], $time) : ""));
		}

		return $format;
	}
}
