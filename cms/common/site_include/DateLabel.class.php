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
			if(is_string($this->text) && strlen($this->text)){
				$this->time = $this->text;
				$this->year = date("Y",$this->time);
				$this->month = date("n",$this->time);
				$this->day = date("j",$this->time);
			}else{
				$this->time = mktime(0,0,0,max(1,$this->month),max(1,$this->day),$this->year);
			}
		}

		//フォーマット
		$fmt = $this->getAttribute("cms:format");
		if(!is_string($fmt)) $fmt = "";
		if(strlen($fmt)==0){
			if(is_null($this->defaultFormat) || strlen($this->defaultFormat) == 0){
				$fmt = "Y-m-d H:i:s";
			}else{
				$fmt = $this->defaultFormat;
			}
		}

		//条件付きフォーマット
		$fmt = self::ParseConditionalDateFormat($fmt, (int)$this->time, (int)$this->year, (int)$this->month, (int)$this->day);

		$this->setText(date($fmt,$this->time));

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
	public static function ParseConditionalDateFormat(string $fmt, int $time, int $year, int $month, int $day){
		//preg_replaceのe修飾子は5.5.0で非推奨になり、7以降は非対応
		//preg_replace_callbackは4.0.5から使えるが無名関数は5.3.0以降
		//と言うわけで、5.2系でも7以降でも問題なく動作するためには、preg_replace＋e修飾子もpreg_replace_callback＋無名関数も使えない
		$matches = array();

		if(preg_match("/%DATE:([^%]*)%/u",$fmt,$matches) && strlen($matches[0])){
			$fmt = strtr($fmt, array($matches[0] => date($matches[1], $time)));
		}

		if(preg_match("/%Y:([^%]*)%/u",$fmt,$matches) && strlen($matches[0])){
			$fmt = strtr($fmt, array($matches[0] => (is_numeric($year) && $year > 0)  ? date($matches[1], $time) : ""));
		}

		if(preg_match("/%M:([^%]*)%/u",$fmt,$matches) && strlen($matches[0])){
			$fmt = strtr($fmt, array($matches[0] => (is_numeric($month) && $month > 0) ? date($matches[1], $time) : ""));
		}

		if(preg_match("/%D:([^%]*)%/u",$fmt,$matches) && strlen($matches[0])){
			$fmt = strtr($fmt, array($matches[0] => (is_numeric($day) && $day > 0)   ? date($matches[1], $time) : ""));
		}

		return $fmt;
	}
}
