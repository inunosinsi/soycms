<?php

class SearchAction extends SOY2Action{

	private $limit;
	private $offset;
	private $totalCount;

    function execute(SOY2ActionRequest $request, SearchActionForm $form, SOY2ActionResponse $response) {
		$this->limit = (is_numeric($form->getLimit()) ? $form->getLimit() : 10);
    	$this->offset =(is_numeric($form->getOffset()) ? $form->getOffset() : 0);

    	$entries = self::searchEntries((string)$form->getFreeword_text(),array(
    		"op" => $form->getLabelOperator(),
    		"labels" => $form->getLabel()
       	), $form->getCdate(), $form->getUdate(), $form->getSort(), $form->getCustomFields(), $form->getSearchFields(), $form->getTagCloudTags());

    	$count = $this->totalCount;

    	$this->setAttribute("from", $this->offset);

    	if(count($entries) < $this->limit){
    		$this->setAttribute("to",$this->offset+count($entries));
    	}else{
    		$this->setAttribute("to",$this->offset+$this->limit);
    	}

		$this->setAttribute("Entities",$entries);
		$this->setAttribute("total",$this->totalCount);
		$this->setAttribute("limit",$this->limit);

		$this->setAttribute("form",$form);
    }

    private function searchEntries(string $freewordText, array $label, array $cdate, array $udate, array $sort, array $customfields=array(), array $searchfields=array(), array $tagCloudTags=array(), $others = null){
    	$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
    	$dao = SOY2DAOFactory::create("LabeledEntryDAO");
    	$dao->setLimit($this->limit);
    	$dao->setOffset($this->offset);
		
    	$query = new SOY2DAO_Query();
    	$query->prefix = "select";
		$query->distinct = true;
		$query->sql = " id,alias,title,content,more,cdate,udate,openPeriodStart,openPeriodEnd,isPublished ";
		$query->table = " Entry left outer join EntryLabel on (Entry.id = EntryLabel.entry_id) ";
		$query->order = "Entry." . $sort["type"] . " " . $sort["sort"];
		$binds = array();
		$where = array();


		//フリーワード検索を作成
		if(strlen($freewordText) != 0){
			$keywords = preg_split('/[\s,]+/',$freewordText);
			$freeword = array();
			$keywordCounts = 0;
			$freewordQuery = array();

			foreach(array("title","content","more") as $column){
				$freeword = array();
				foreach($keywords as $keyword){

					$bind_key = ':freeword'.$keywordCounts;

					if($keyword[0] == "-"){
						$keyword = substr($keyword,1);
						$freeword[] = 'Entry.'.$column." not like ".$bind_key."";
					}else{
						$freeword[] = 'Entry.'.$column." like ".$bind_key."";
					}

					$binds[$bind_key] = '%'.$keyword.'%';
					$keywordCounts ++;
				}

				$freewordQuery[] = "(" . implode(' AND ',$freeword) . ")";
			}

			$where[]= " ( ".implode(' OR ',$freewordQuery)." ) ";
		}

		//記事管理者に見えないラベルの付いた記事は除外する
		$prohibitedLabelIds = array();
		if(!UserInfoUtil::hasSiteAdminRole()){
			$labelLogic = SOY2LogicContainer::get("logic.site.Label.LabelLogic");
			$prohibitedLabelIds = $labelLogic->getProhibitedLabelIds();

			$labelQuery = new SOY2DAO_Query();
			$labelQuery->prefix = "select";
			$labelQuery->sql = "EntryLabel.entry_id";
			$labelQuery->table = "EntryLabel";
			$labelQuery->distinct = true;
			if(count($prohibitedLabelIds)) $labelQuery->where = 'EntryLabel.label_id IN (' . implode(",", $prohibitedLabelIds) . ')';
			$where[] = 'Entry.id NOT IN ('.$labelQuery.')';
		}

		//ラベル絞込みを作成
		if(count($label["labels"])){
			//int化
			$label["labels"] = array_map(function($v) {return (int)$v;} ,$label["labels"]);

			$labelQuery = new SOY2DAO_Query();
			$labelQuery->prefix = "select";
			$labelQuery->sql = "EntryLabel.entry_id";
			$labelQuery->table = "EntryLabel";
			$labelQuery->distinct = true;
			$labelQuery->where = 'EntryLabel.label_id IN (' . implode(",", $label["labels"]) . ')';

			if($label["op"] == "AND" && count($label["labels"])){
				$labelQuery->having = "count(EntryLabel.entry_id) = ".count($label["labels"]);
				$labelQuery->group = "EntryLabel.entry_id";
			}

			$where[] = 'Entry.id IN ('.$labelQuery.')';
		}

		//作成日時、更新日時で絞り込み タイムスタンプに変換
		foreach(array("start", "end") as $lab){
			$cdate[$lab] = (isset($cdate[$lab]) && is_string($cdate[$lab])) ? self::_str2timestamp($cdate[$lab], $lab) : 0;
			$udate[$lab] = (isset($udate[$lab]) && is_string($udate[$lab])) ? self::_str2timestamp($udate[$lab], $lab) : 0;
		}

		//作成日時、更新日時で絞り込み
		if($cdate["start"] > 0 || $cdate["end"] > 0){
			$dWhere = array();
			if($cdate["start"] > 0){
				$dWhere[] = "Entry.cdate >= " . $cdate["start"];
			}
			if($cdate["end"] > 0){
				$dWhere[] = "Entry.cdate <= " . $cdate["end"];
			}
			$where[] = "(" . implode(" AND ", $dWhere) . ")";
		}

		if($udate["start"] > 0 || $udate["end"] > 0){
			$dWhere = array();
			if($udate["start"] > 0){
				$dWhere[] = "Entry.udate >= " . $udate["start"];
			}
			if($udate["end"] > 0){
				$dWhere[] = "Entry.udate <= " . $udate["end"];
			}
			$where[] = "(" . implode(" AND ", $dWhere) . ")";
		}

		if(count($customfields)){
			$queries = array();
			foreach($customfields as $fieldId => $fieldValue){
				$fieldValue = trim($fieldValue);
				if(!strlen($fieldValue)) continue;
				$queries[] = "(entry_field_id = '" . trim($fieldId) . "' AND entry_value LIKE :cfa" . $fieldId . ")";
				$binds[":cfa".$fieldId] = "%" . htmlspecialchars($fieldValue, ENT_QUOTES, "UTF-8") . "%";
			}
			
			if(count($queries)){
				$customQuery = new SOY2DAO_Query();
				$customQuery->prefix = "select";
				$customQuery->sql = "entry_id";
				$customQuery->table = "EntryAttribute";
				$customQuery->distinct = true;
				$customQuery->where = implode(" AND ", $queries);
				$where[] = 'Entry.id IN ('.$customQuery.')';
			}
		}

		if(count($searchfields)){
			$queries = array();
			foreach($searchfields as $fieldId => $fieldValue){
				if(is_string($fieldValue)){
					$fieldValue = trim($fieldValue);
					if(!strlen($fieldValue)) continue;
					$queries[] = $fieldId . " LIKE :csf" . $fieldId;
					$binds[":csf".$fieldId] = "%" . htmlspecialchars($fieldValue, ENT_QUOTES, "UTF-8") . "%";
				}else if(is_array($fieldValue)){
					$keys = array_keys($fieldValue);
					if($keys[0] == "start"){	//range
						if(is_numeric($fieldValue["start"])) $queries[] = $fieldId . " >= " . (int)$fieldValue["start"];
						if(is_numeric($fieldValue["end"])) $queries[] = $fieldId . " <= " . (int)$fieldValue["end"];
					}else{	//checkbox
						if(count($fieldValue)){
							foreach($fieldValue as $idx => $fieldV){
								$queries[] = $fieldId . " LIKE :csf".$fieldId.$idx;
								$binds[":csf".$fieldId.$idx] = "%" . htmlspecialchars($fieldV, ENT_QUOTES, "UTF-8") . "%";
							}
						}
					}
				}
			}
			
			if(count($queries)){
				$customQuery = new SOY2DAO_Query();
				$customQuery->prefix = "select";
				$customQuery->sql = "entry_id";
				$customQuery->table = "EntryCustomSearch";
				$customQuery->distinct = true;
				$customQuery->where = implode(" AND ", $queries);
				$where[] = 'Entry.id IN ('.$customQuery.')';
			}
		}

		//タグクラウド
		if(count($tagCloudTags)){
			$queries = array();
			foreach($tagCloudTags as $tagId){
				$queries[] = "word_id = " . (int)$tagId;
			}

			$tagQuery = new SOY2DAO_Query();
			$tagQuery->prefix = "select";
			$tagQuery->sql = "entry_id";
			$tagQuery->table = "TagCloudLinking";
			$tagQuery->distinct = true;
			$tagQuery->where = implode(" AND ", $queries);
			$where[] = 'Entry.id IN ('.$tagQuery.')';
		}
		

		$query->where = implode(" AND ",$where);

		try{
			$results = $dao->executeQuery($query,$binds);
		}catch(Exception $e){
			$results = array();
		}

		$this->totalCount = $dao->getRowCount();

		$ret_val = array();
		if(count($results)){
			foreach($results as $row){
				$obj = $dao->getObject($row);
				$obj->setLabels($logic->getLabelIdsByEntryId($obj->getId()));
				$ret_val[] = $obj;
			}
		}
		return $ret_val;
    }

	/**
	 * @param string, string
	 * @return 0
	 */
	private function _str2timestamp(string $str, string $mode="start"){
		$str = trim($str);
		if(!strlen($str)) return 0;

		preg_match('/[\d]{4}\/[\d]{2}\/[\d]{2}/', $str, $tmp);
		if(!isset($tmp[0])) return 0;

		$arr = explode("/", $tmp[0]);

		$t = mktime(0, 0, 0, (int)$arr[1], (int)$arr[2], (int)$arr[0]);
		if($mode == "start") return $t;

		return $t + (24 * 60 * 60) - 1;
	}
}

class SearchActionForm extends SOY2ActionForm{

	private $freeword_text;
	private $label;
	private $cdate=array();
	private $udate=array();
	private $sort=array();
	private $customfields;
	private $searchfields;
	private $tagCloudTags;
	private $limit;
	private $offset;

	private $labelOperator;

	function getLabel(){
		if(!isset($_GET["label"]) && isset($_GET["labelOperator"])){
			$this->label = array();
		} else if(!count($this->label) && isset($_COOKIE["ENTRY_SEARCH_LABELS"])){
			$this->label = soy2_unserialize((string)$_COOKIE["ENTRY_SEARCH_LABELS"]);
			if(!is_array($this->label)) $this->label = array();
		}
		return $this->label;
	}
	function setLabel($label){
		if(isset($_GET["label"]) && is_array($_GET["label"]) && count($_GET["label"])){
			soy2_setcookie("ENTRY_SEARCH_LABELS", soy2_serialize($_GET["label"]));
		}else if(isset($_GET["labelOperator"])){	//ラベルオペレータは検索ボタンを押したら必ずあるので、この値を検索の有無の判定として利用する
			soy2_setcookie("ENTRY_SEARCH_LABELS");	//一度ラベル付き検索した後にラベルを外す処理
		}

		if(!is_array($label)) $label = array();
		$this->label = $label;
	}

	function getCdate(){
		return $this->cdate;
	}
	function setCdate($cdate){
		$this->cdate = (is_array($cdate) && isset($cdate["start"]) && isset($cdate["end"])) ? $cdate : array("start" => "", "end" => "");
	}
	function getUdate(){
		return $this->udate;
	}
	function setUdate($udate){
		$this->udate = (is_array($udate) && isset($udate["start"]) && isset($udate["end"])) ? $udate : array("start" => "", "end" => "");
	}
	function getSort(){
		return $this->sort;
	}
	function setSort($sort){
		$this->sort = (is_array($sort) && isset($sort["type"]) && isset($sort["sort"])) ? $sort : array("type" => "udate", "sort" => "desc");
	}

	function getCustomFields(){
		if(!is_array($this->customfields) && isset($_COOKIE["ENTRY_SEARCH_CUSTOMFIELDS"])){
			$this->customfields = soy2_unserialize($_COOKIE["ENTRY_SEARCH_CUSTOMFIELDS"]);
		}
		if(!is_array($this->customfields)) $this->customfields = array();
		return $this->customfields;
	}

	function setCustomfields($customfields){
		if(isset($_GET["customfield"]) && is_array($_GET["customfield"])){
			soy2_setcookie("ENTRY_SEARCH_CUSTOMFIELDS", soy2_serialize($_GET["customfield"]));
			$customfields = $_GET["customfield"];
		}

		if(is_null($customfields) && isset($_COOKIE["ENTRY_SEARCH_CUSTOMFIELDS"])){
			$customfields = soy2_unserialize($_COOKIE["ENTRY_SEARCH_CUSTOMFIELDS"]);
		}

		$this->customfields = $customfields;
	}

	function getSearchFields(){
		if(!is_array($this->searchfields) && isset($_COOKIE["ENTRY_SEARCH_SEARCHFIELDS"])){
			$this->customfields = soy2_unserialize($_COOKIE["ENTRY_SEARCH_SEARCHFIELDS"]);
		}
		if(!is_array($this->searchfields)) $this->searchfields = array();
		return $this->searchfields;
	}

	function setSearchfields($searchfields){
		if(isset($_GET["searchfield"]) && is_array($_GET["searchfield"])){
			soy2_setcookie("ENTRY_SEARCH_SEARCHFIELDS", soy2_serialize($_GET["searchfield"]));
			$searchfields = $_GET["searchfield"];
		}

		if(is_null($searchfields) && isset($_COOKIE["ENTRY_SEARCH_SEARCHFIELDS"])){
			$searchfields = soy2_unserialize($_COOKIE["ENTRY_SEARCH_SEARCHFIELDS"]);
		}

		$this->searchfields = $searchfields;
	}

	function getTagCloudTags(){
		if(!is_array($this->tagCloudTags) && isset($_COOKIE["ENTRY_SEARCH_TAG_CLOUD"]) && is_string($_COOKIE["ENTRY_SEARCH_TAG_CLOUD"])){
			$this->tagCloudTags = explode(",", $_COOKIE["ENTRY_SEARCH_TAG_CLOUD"]);
		}
		if(!is_array($this->tagCloudTags)) $this->tagCloudTags = array();
		return $this->tagCloudTags;
	}
	function setTagCloudTags($tagCloudTags){
		if(isset($_GET["tag_cloud"]) && is_string($_GET["tag_cloud"])){
			soy2_setcookie("ENTRY_SEARCH_TAG_CLOUD", $_GET["tag_cloud"]);
			$tagCloudTags = explode(",", $_GET["tag_cloud"]);
		}

		if(is_null($tagCloudTags) && isset($_COOKIE["ENTRY_SEARCH_TAG_CLOUD"])){
			$tagCloudTags = soy2_unserialize($_COOKIE["ENTRY_SEARCH_TAG_CLOUD"]);
		}
		$this->tagCloudTags = $tagCloudTags;
	}

	function getFreeword_text(){
		if(is_null($this->freeword_text) && isset($_COOKIE["FREEWORD_TEXT"])){
			$this->freeword_text = $_COOKIE["FREEWORD_TEXT"];
		}
		return $this->freeword_text;
	}
	function setFreeword_text($text){
		if(isset($_GET["freeword_text"])){
			soy2_setcookie("FREEWORD_TEXT", $_GET["freeword_text"]);
		}

		if(is_null($text) && isset($_COOKIE["FREEWORD_TEXT"])){
			$text = $_COOKIE["FREEWORD_TEXT"];
		}

		$this->freeword_text = $text;
	}

	function getLabelOperator(){
		if(is_null($this->labelOperator) && isset($_COOKIE["LABEL_OPERATOR"])){
			$this->labelOperator = $_COOKIE["LABEL_OPERATOR"];
		}
		return $this->labelOperator;
	}
	function setLabelOperator($op){
		if(isset($_GET["labelOperator"])){
			soy2_setcookie("LABEL_OPERATOR", $_GET["labelOperator"]);
		}
		$this->labelOperator = $op;
	}

	function getLimit(){
		return $this->limit;
	}
	function setLimit($limit){
		$this->limit = $limit;
	}

	function getOffset(){
		return $this->offset;
	}
	function setOffset($offset){
		$this->offset = $offset;
	}
}
