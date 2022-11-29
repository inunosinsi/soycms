<?php

class STREntryResultListComponent extends HTMLList {

	protected function populateItem($entity, $key){
		$entryId = (is_numeric($key)) ? (int)$key : 0;
		$this->addCheckBox("check", array(
			"name" => "ReplaceEntries[]",
			"value" => $entryId
		));

		$this->addLabel("title", array(
			"text" => (isset($entity["title"]) && is_string($entity["title"])) ? $entity["title"] : ""
		));	

		$this->addLabel("content", array(
			"text" => (isset($entity["content"]) && is_string($entity["content"])) ? $entity["content"] : ""
		));


		/**
		 * JSONのURLは/siteId/意味のないハッシュ値_記事ID.json?replace_plugin_mode=1or2になる
		 */
		$url = self::_buildUrl();
		$this->addLink("content_confirm_link", array(
			"link" => "javascript:void(0);",
			"onclick" => "open_confirm_modal(\"".self::_buildUrl().self::_buildNonExistentURL()."_".$entryId.".json?replace_plugin_mode=1\");"
		));

		$this->addLink("more_confirm_link", array(
			"link" => "javascript:void(0);",
			"onclick" => "open_confirm_modal(\"".self::_buildUrl().self::_buildNonExistentURL()."_".$entryId.".json?replace_plugin_mode=2\");"
		));

		$this->addLink("detail_link", array(
			"link" => SOY2PageController::createLink("Entry.Detail.".$entryId)
		));
	}

	private function _buildUrl(){
		static $url;
		if(is_null($url)){
			$u = UserInfoUtil::getSitePublishURL();
			$u = substr($u, strpos($u, "://")+3);
			$url = substr($u, strpos($u, "/"));
		}
		return $url;
	}

	/**
	 * @存在しないURLを発行
	 */
	private function _buildNonExistentURL(){
		static $uri;
		if(is_null($uri)){
			for(;;){
				$uri = md5(time());
				try{
					$_dust = soycms_get_hash_table_dao("page")->getActivePageByUri($uri);
				}catch(Exception $e){
					break;
				}
			}
		}
		return $uri;
	}
}