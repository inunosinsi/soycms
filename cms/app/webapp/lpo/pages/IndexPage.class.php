<?php

class IndexPage extends WebPage{

    function __construct() {
    	$limit = 10;
    	
    	WebPage::__construct();
    	
    	$results = $this->getCount($limit);
    	
    	$listDao = SOY2DAOFactory::create("SOYLpo_ListDAO");
    	
    	$this->createAdd("no_analysis","HTMLModel",array(
    		"visible" => (count($results)==0)
    	));
    	
    	$this->createAdd("analysis_list","AnalysisList",array(
    		"list" => $results,
    		"dao" => $listDao
    	));
    }
    
    function getCount($limit){
    	$dao = new SOY2DAO();
    	
    	$sql = "SELECT lpo_id, ".
    			"COUNT(lpo_id) count ".
    			"FROM soylpo_log ".
    			"GROUP BY lpo_id ;".
    			"ORDER By count DESC ".
    			"LIMIT ".$limit;
    			
    	try{
    		$results = $dao->executeQuery($sql);
    	}catch(Exception $e){
    		$results = array();
    	}
    	
    	return $results;
    }
}

class AnalysisList extends HTMLList{
	
	private $dao;
	
	protected function populateItem($entity){
		$entry = $this->getEntry($entity["lpo_id"]);
		
		$this->createAdd("id","HTMLLabel",array(
			"text" => $entry->getId()
		));
		
		$this->createAdd("title","HTMLLink",array(
			"text" => $entry->getTitle(),
			"link" => SOY2PageController::createLink(APPLICATION_ID.".List.Detail.".$entry->getId()),
		));
		
		$this->createAdd("count","HTMLLabel",array(
			"text" => $entity["count"]
		));
		
		$this->createAdd("mode","HTMLLabel",array(
			"text" => $entry->getModeText()
		));
		
		$this->createAdd("keyword","HTMLLabel",array(
			"text" => $entry->getKeyword()
		));
		
		$this->createAdd("edit","HTMLLink",array(
			"link" => SOY2PageController::createLink(APPLICATION_ID.".List.Detail.".$entry->getId())
		));
		
	}
	
	function getEntry($id){
		try{
			$entry = $this->dao->getById($id);
		}catch(Exception $e){
			$entry = new SOYLpo_List();
		}
		
		return $entry;
	}
	
	function setDao($dao){
		$this->dao = $dao;
	}
}
?>