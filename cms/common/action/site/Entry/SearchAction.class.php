<?php

class SearchAction extends SOY2Action{

	var $limit;
	var $offset;
	var $totalCount;

    function execute($request,$form,$response) {

    	$this->limit = (is_numeric($form->limit) ? $form->limit : 10);
    	$this->offset =(is_numeric($form->offset) ? $form->offset : 0);

    	$entries = $this->searchEntries($form->freeword_text,array(
    		"op"=>$form->labelOperator,
    		"labels"=>$form->label
       	));

    	$count = $this->totalCount;

    	$this->setAttribute("from",$this->offset);

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

    function searchEntries($freewordText,$label,$others = null){
    	$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
    	$dao = SOY2DAOFactory::create("LabeledEntryDAO");
    	$dao->setLimit($this->limit);
    	$dao->setOffset($this->offset);

    	$query = new SOY2DAO_Query();
    	$query->prefix = "select";
		$query->distinct = true;
		$query->sql = " id,alias,title,content,more,cdate,openPeriodStart,openPeriodEnd,isPublished ";
		$query->table = " Entry left outer join EntryLabel on(Entry.id = EntryLabel.entry_id) ";
		$query->order = "udate desc";
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
			$labelQuery->where = 'EntryLabel.label_id IN (' . implode(",", $prohibitedLabelIds) . ')';
			$where[] = 'Entry.id NOT IN ('.$labelQuery.')';
		}

		//ラベル絞込みを作成
		if(count($label["labels"])){
			//int化
			$label["labels"] = array_map(create_function('$v','return (int)$v;'),$label["labels"]);

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

		$query->where = implode(" AND ",$where);

		$result = $dao->executeQuery($query,$binds);

		$this->totalCount = $dao->getRowCount();

		$ret_val = array();
		foreach($result as $row){
			$obj = $dao->getObject($row);
			$obj->setLabels($logic->getLabelIdsByEntryId($obj->getId()));
			$ret_val[] = $obj;

		}
		return $ret_val;

    }
}

class SearchActionForm extends SOY2ActionForm{

	var $freeword_text;
	var $label;
	var $limit;
	var $offset;

	var $labelOperator;

	function setLabel($label){
		$this->label = $label;
		if(!is_array($this->label)){
			$this->label = array();
		}
	}

	function setFreeword_text($text){
		$this->freeword_text = $text;
	}

	function setLabelOperator($op){
		$this->labelOperator = $op;
	}

	function setLimit($limit){
		$this->limit = $limit;
	}

	function setOffset($offset){
		$this->offset = $offset;
	}


}
?>