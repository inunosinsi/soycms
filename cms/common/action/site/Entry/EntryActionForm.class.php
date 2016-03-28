<?php 
class EntryActionForm extends SOY2ActionForm{
	
	var $id;
	var $title;
	var $content;
	var $more;
	var $cdate;
	var $openPeriodStart;
	var $openPeriodEnd;
	var $isPublished;
	var $style;
	var $description;
	
	//2009-02-12追加
	var $alias;

	function setId($value){
		$this->id = $value;
	}
	function setTitle($value){
		$this->title = $value;
	}
	function setContent($value){
		$this->content = $value;
	}
	function setMore($value){
		$this->more = $value;
	}
	function setCdate($cdate) {
		$this->cdate = $cdate;
	}
	function setOpenPeriodStart($start) {
		$utime = (strlen($start)) ? strtotime($start) : false;
		if(!($utime === false)){
			$this->openPeriodStart = $utime;	
		}
		
	}
	function setOpenPeriodEnd($end) {
		$utime = (strlen($end)) ? strtotime($end) : false;
		if(!($utime === false)){
			$this->openPeriodEnd = $utime;	
		}
	}
	function setIsPublished($isPublished) {
		$this->isPublished = $isPublished;
	}
	
	function setStyle($style){
		$this->style= $style;
	}
	
	function setDescription($description){
		$this->description = $description;
	}

	function setAlias($alias){
		$this->alias = $alias;
	}
}
?>
