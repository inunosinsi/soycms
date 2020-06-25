<?php

class TagCloudWordListComponent extends HTMLList {

	private $url;
	private $ranks; //タグの使用頻度が何位でクラスのrank01を次の数字にするか？

	protected function populateItem($entity, $key, $int){
		$rank = (isset($entity["word_id"]) && isset($this->ranks[$entity["word_id"]])) ? $this->ranks[$entity["word_id"]] : 1;
		$this->addLink("tag_link", array(
			"soy2prefix" => "cms",
			"link" => (isset($entity["word_id"]) && is_numeric($entity["word_id"])) ? $this->url . "?tagcloud=" . $entity["word_id"] : "",
			"attr:class" => self::_buildClass($rank)
		));

		$this->addLink("tag_hash_link", array(
			"soy2prefix" => "cms",
			"link" => (isset($entity["hash"]) && is_string($entity["hash"])) ? $this->url . "?tagcloud=" . $entity["hash"] : "",
			"attr:class" => self::_buildClass($rank)
		));

		$this->addLabel("tag", array(
			"soy2prefix" => "cms",
			"text" => (isset($entity["word"])) ? $entity["word"] : ""
		));
	}

	private function _buildClass($rank){
		if(strlen($rank) === 1) $rank = "0" . $rank;
		return "tagcloud rank" . $rank;
	}

	function setUrl($url){
		$this->url = $url;
	}
	function setRanks($ranks){
		$this->ranks = $ranks;
	}
}
