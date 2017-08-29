<?php
/**
 * ページのリストを取得する
 */
class PageListAction extends SOY2Action{

	var $buildTree = false;

	private $offset;
	private $count;
	private $order;

	function setBuildTree($value){
		$this->buildTree = $value;
	}

	/**
	 * TODO ツリー作成をロジックに移動
	 */
    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){

 		$dao = SOY2DAOFactory::create("cms.PageDAO");

 		if(is_null($this->offset)){
 			$pages = $dao->get();
 		}else{
 			$pages = $dao->getInRange($this->offset,$this->count,$this->order);
 		}


 		if($this->buildTree){

	 		//ツリー化を行う
	 		$tmpArray = array();
	 		$trashArray = array();

	 		foreach($pages as $key => $tmp){

	 			if($tmp->getIsTrash() == 1){
	 				if(!is_null($tmp->getParentPageId()) && isset($pages[$tmp->getParentPageId()]) && $pages[$tmp->getParentPageId()]->getIsTrash() == 0){
	 					//TrashROOTの時の処理
	 					$trashArray[] = $tmp;
	 				}else if(!is_null($tmp->getParentPageId()) && isset($pages[$tmp->getParentPageId()])){
		 				//Treeの中間に位置するページの処理
		 				$pages[$tmp->getParentPageId()]->addChildPage($tmp);
		 			}else{
		 				//ROOTのときの処理
		 				$trashArray[] = $tmp; //rootが入っていく
		 			}
	 			}else{
		 			if(!is_null($tmp->getParentPageId()) && isset($pages[$tmp->getParentPageId()])){
		 				$pages[$tmp->getParentPageId()]->addChildPage($tmp);
		 			}else{
		 				$tmpArray[] = $tmp; //rootが入っていく
		 			}
	 			}
	 		}

	 		$pageArray = $pages;
	 		$pages = $tmpArray;
	 		$pageTreeArray = $this->getPageTree(null,$pages);
		 	$this->setAttribute("PageTree",$pageTreeArray);
		 	$this->setAttribute("PageList",$pages);
		 	$this->setAttribute("PageArray",$pageArray);
		 	$this->setAttribute("RemovedPageList",$trashArray);
		 	return SOY2Action::SUCCESS;

 		}

		$trashArray = array();
		$pageArray = array();
		foreach($pages as $key => $tmp){
			if($tmp->getIsTrash() == 1){
				$trashArray[$tmp->getId()] = $tmp;
			}else{
				$pageArray[$tmp->getId()] = $tmp;
			}
		}

		$this->setAttribute("PageList",$pageArray);
 		$this->setAttribute("RemovedPageList",$trashArray);

 		return SOY2Action::SUCCESS;
    }


    /**
     * TODO　複雑なツリーの時おかしくなる
     */
    function getPageTree($parentPage,$pages,$prefix = ""){

    	$pageTree = array();
    	$_prefix = $prefix;
    	$pages = array_values($pages);

    	$counter = 0;
    	foreach($pages as $key => $page){
    	 	$counter++;

    	 	$text = $prefix;
    	 	if($key == (count($pages)-1)){
    	 		$text .= "└";

				//echo $page->getNodePathCount();

    	 		if(!is_null($parentPage) && $parentPage->getNodePathCount($counter+1) > 1){
    	 			$_prefix = $prefix ."｜";
    	 		}else{
    	 			$_prefix = $prefix . "　";
    	 		}

    	 	}else{
    	 		$text .= "├";
    	 		$_prefix = $prefix ."｜";
    	 	}

    	 	$text .= $page->getTitle();
    	 	$pageTree[$page->getId()] = $text ;

    	 	$pageTree = $pageTree + $this->getPageTree($page,$page->getChildPages(),$_prefix);
    	}

    	return $pageTree;
    }


    function getOffset() {
    	return $this->offset;
    }
    function setOffset($offset) {
    	$this->offset = $offset;
    }
    function getCount() {
    	return $this->count;
    }
    function setCount($count) {
    	$this->count = $count;
    }

    function getOrder() {
    	return $this->order;
    }
    function setOrder($order) {
    	$this->order = $order;
    }
}
?>