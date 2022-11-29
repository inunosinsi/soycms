<?php
class STRConfigPage extends WebPage{

	private $pluginObj;
	const ENTRY_LIMIT = 50;
	const ENTRY_CONTENT_LENGTH = 30;	//検索時に取得しておく文字数

	function __construct(){}

	function doPost(){

		if(soy2_check_token()){
			if(isset($_POST["replace"]) && strlen($_POST["r"]) && (isset($_POST["ReplaceEntries"]))){
				$entryIds = $_POST["ReplaceEntries"];
				if(is_array($entryIds) && count($entryIds)){
					$r = trim((string)$_POST["r"]);
					$q = trim((string)self::_getParameter("q"));
					$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
					foreach($entryIds as $entryId){
						$entry = soycms_get_entry_object($entryId);
						$title = (string)$entry->getTitle();
						if(strlen($title) && is_numeric(mb_strpos($title, $q))){
							$title = str_replace($q, $r, $title);
							$entry->setTitle($title);
						}
						$content = (string)$entry->getContent();
						if(strlen($content) && is_numeric(mb_strpos($content, $q))){
							$content = str_replace($q, $r, $content);
							$entry->setContent($content);
						}
						$more = (string)$entry->getMore();
						if(strlen($more) && is_numeric(mb_strpos($more, $q))){
							$more = str_replace($q, $r, $more);
							$entry->setMore($more);
						}

						$logic->update($entry);
					}
				}
			}
		}
	}

	function execute(){
		if(isset($_GET["reset"])) self::_reset();

		parent::__construct();

		self::_formArea();
		self::_results();
	}

	private function _reset(){
		self::_setParameter("q");
		self::_setParameter("r");
		SOY2PageController::jump("Plugin.ConfigPage?string_batch_replace");
	}

	private function _formArea(){
		$this->addForm("form");

		$this->addInput("q", array(
			"name" => "q",
			"value" => self::_getParameter("q")
		));

		$this->addLink("reset_link", array(
			"link" => SOY2PageController::createLink("Plugin.ConfigPage?string_batch_replace&reset")
		));

		$this->addInput("r", array(
			"name" => "r",
			"value" => self::_getParameter("r")
		));
	}

	private function _results(){
		$arr = self::_search();
		DisplayPlugin::toggle("result", count($arr));

		SOY2::import("site_include.plugin.string_batch_replace.component.STREntryResultListComponent");

		$this->createAdd("result_list", "STREntryResultListComponent", array(
			"list" => $arr
		));
	}

	private function _search(){
		$q = trim((string)self::_getParameter("q"));
		if(!strlen($q)) return array();

		$sql = "SELECT id, title, content FROM Entry ".
				"WHERE title LIKE :title ".
				"OR content LIKE :content ".
				"OR more LIKE :more";

		// とりあえず50件
		$sql .= " LIMIT " . self::ENTRY_LIMIT;

		
		$dao = new SOY2DAO();
		try{
			$res = $dao->executeQuery($sql, array(":title" => "%".$q."%", ":content" => "%".$q."%", ":more" => "%".$q."%"));
		}catch(Exception $e){
			$res = array();
		}

		if(!count($res)) return array();

		$arr = array();
		foreach($res as $v){
			$arr[(int)$v["id"]] = array("title" => $v["title"], "content" => mb_substr(strip_tags($v["content"]), 0, self::ENTRY_CONTENT_LENGTH));
		}
		return $arr;
	}

	/**
	 * @param string
	 * @return string
	 */
	private function _getParameter(string $key){
		if(array_key_exists($key, $_POST)){
			$v = (string)$_POST[$key];
			self::_setParameter($key, $v);
			return $v;
		}else{
			return (string)SOY2ActionSession::getUserSession()->getAttribute(StringBatchReplacePlugin::PLUGIN_ID.":".$key);
		}
	}

	/**
	 * @param string, string
	 */
	private function _setParameter(string $key, string $v=""){
		SOY2ActionSession::getUserSession()->setAttribute(StringBatchReplacePlugin::PLUGIN_ID.":".$key, $v);
	}

	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}
}