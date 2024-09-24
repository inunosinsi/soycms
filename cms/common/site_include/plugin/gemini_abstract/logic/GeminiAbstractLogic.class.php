<?php

class GeminiAbstractLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::import("site_include.plugin.gemini_abstract.util.GeminiAbstractUtil");
	}

	/**
	 * @param int, int
	 * @return string
	 */
	function generate(int $blogPageId, int $entryId){
		$count = GeminiAbstractUtil::getCount();
		if($count <= 0) return "";
		
		return SOY2Logic::createInstance("logic.ai.GeminiApiLogic")->executePrompt(
			GeminiAbstractUtil::buildPrompt($blogPageId, $entryId, $count)
		);
	}

	/**
	 * 概要が未生成、もしくは生成した概要が古い記事の概要を更新する
	 */
	function update(){
		$blogLabelIds = GeminiAbstractUtil::getBlogPageIds();
		if(!count($blogLabelIds)) return;

		$dao = new SOY2DAO();

		try{
			$res = $dao->executeQuery(
				"SELECT entry_id, label_id FROM EntryLabel ".
				"WHERE label_id IN (".implode(",", $blogLabelIds).") ".
				"AND entry_id NOT IN (".
					"SELECT entry_id FROM EntryAttribute ".
					"WHERE entry_field_id = '".GeminiAbstractUtil::FIELD_ID."'".
				") ".
				"ORDER BY entry_id DESC ".
				"LIMIT 1"
			);
		}catch(Exception $e){
			$res = array();
		}
		
		if(!count($res) && GeminiAbstractUtil::isAbstractUpdate()){
			try{
				$res = $dao->executeQuery(
					"SELECT entry_id, label_id FROM EntryLabel ".
					"WHERE label_id IN (".implode(",", $blogLabelIds).") ".
					"AND entry_id IN (".
						"SELECT entry_id, entry_extra_values FROM EntryAttribute ".
						"WHERE entry_field_id = '".GeminiAbstractUtil::FIELD_ID."'".
						"ORDER BY entry_extra_values ASC ".
					") ".
					"LIMIT 1"
				);
			}catch(Exception $e){
				$res = array();
			}
		}

		if(isset($res[0]["entry_id"]) && is_numeric($res[0]["entry_id"])){
			$entryId = (int)$res[0]["entry_id"];
			$blogLabelId = array_search($res[0]["label_id"], $blogLabelIds);
			$result = self::generate($blogLabelId, $entryId);
			if(strlen($result)){
				GeminiAbstractUtil::saveAbstract($entryId, $result);
			}
			return;
		}

		
	}
}