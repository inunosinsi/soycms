<?php

class RecentEntryListComponent extends HTMLList{

	private $labels = array();

	public function setLabels($array){
		if(is_array($array)) $this->labels = $array;
	}

	protected function populateItem($entity){

		$this->addLink("title", array(
			"link" => SOY2PageController::createLink("Entry.Detail")."/".$entity->getId(),
			"text" => (strlen($entity->getTitle())==0) ? CMSMessageManager::get("SOYCMS_NO_TITLE") : $entity->getTitle(),
		));

		//ラベルは３つまで表示
		$selectedList = (is_array($entity->getLabels())) ? $entity->getLabels() : array();
		$labelText = "";
		$strlen = 0;
		$counter = 0;
		foreach($this->labels as $label){
			if(!in_array($label->getId(),$selectedList)) continue;

			if($counter>3){
				$labelText .= "...";
				break;
			}

			$attr = array();
			$attr[] = 'href="'.htmlspecialchars(SOY2PageController::createLink("Entry.List")."/".$label->getId(),ENT_QUOTES,'UTF-8').'"';
			$attr[] = 'class="label label-default label-soy"';
			$attr[] = 'style="color:#' . sprintf("%06X",$label->getColor()).'; background-color:#' . sprintf("%06X",$label->getBackgroundColor()).'; margin-left:4px;"';

			//ある文字数越えたら追加しない
			if(($strlen+strlen($label->getCaption())) > 300){
				continue;
			}

			$strlen .= strlen($label->getCaption()) + 2;
			$labelText .= '<a '.implode(" ",$attr).'>'.$label->getDisplayCaption().'</a>';

			$counter++;
		}

		$this->addLabel("content", array(
			"html"=> $labelText
		));

		$this->addLabel("udate", array(
			"text" => (is_numeric($entity->getUdate())) ? CMSUtil::getRecentDateTimeText($entity->getUdate()) : "",
			"title" => (is_numeric($entity->getUdate())) ? date("Y-m-d H:i:s", $entity->getUdate()) : ""
		));
	}

	function foldingDescription($description,$width = 20){
		//折り返しありの場合
		$tmp = "";
		$strlen = 0;

		$counter = mb_strlen($description) / $width + 1;

		for($i=0;$i<$counter;$i++){
			$str = mb_strimwidth($description,$strlen,$width);

			if(strlen($str)<1)continue;

			if($i != 0)$tmp .= "<br />";
			$tmp .= htmlspecialchars($str);
			$strlen += mb_strlen($str);
		}

		return $tmp;
	}
}
