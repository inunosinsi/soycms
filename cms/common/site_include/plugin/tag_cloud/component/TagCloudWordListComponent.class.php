<?php

class TagCloudWordListComponent extends HTMLList {

	private $url;
	private $ranks; //タグの使用頻度が何位でクラスのrank01を次の数字にするか？

	protected function populateItem($entity){
		$wordId = (isset($entity["word_id"]) && is_numeric($entity["word_id"])) ? (int)$entity["word_id"] : 0;

		$this->addLabel("tag_word_id", array(
			"soy2prefix" => "cms",
			"text" => $wordId
		));

		$rank = (isset($this->ranks[$wordId]) && is_numeric($this->ranks[$wordId])) ? $this->ranks[$wordId] : 1;
		$this->addLink("tag_link", array(
			"soy2prefix" => "cms",
			"link" => (is_numeric($wordId)) ? $this->url . "?tagcloud=" . $wordId : "",
			"attr:class" => self::_buildClass($rank)
		));

		$hash = (isset($entity["hash"]) && is_string($entity["hash"])) ? $entity["hash"] : "";

		$this->addLabel("tag_word_hash", array(
			"soy2prefix" => "cms",
			"text" => $wordId
		));

		$this->addLink("tag_hash_link", array(
			"soy2prefix" => "cms",
			"link" => (strlen($hash)) ? $this->url . "?tagcloud=" . $hash : "",
			"attr:class" => self::_buildClass($rank)
		));

		$this->addLabel("tag", array(
			"soy2prefix" => "cms",
			"text" => (isset($entity["word"])) ? $entity["word"] : ""
		));
	}

	private function _buildClass(int $rank){
		$rank = (string)$rank;
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
