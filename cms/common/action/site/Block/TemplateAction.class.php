<?php

class TemplateAction extends SOY2Action{

	private $pageId;
	function setPageId($pageId){
		$this->pageId = $pageId;
	}

	/**
	 * ページテンプレートからsoy:idに対応するブロックリストと、ブロックの存在しないsoy:idを取得する
	 */
    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		$logic = SOY2Logic::createInstance("logic.site.Block.BlockLogic");
		try{
			$ids = $logic->getSoyIds($this->pageId);
			$blocks = $logic->getByPageId($this->pageId);
		}catch(Exception $e){
			return SOY2Action::FAILED;
		}
		$unset = array();
		$set = array();
		$removed = array();
		
		foreach($blocks as $key => $block){
			$soyId = $block->getSoyId();
			if(in_array($soyId,$ids)){
				$ids[$soyId] = null;
				unset($ids[$soyId]);
				$block->setIsUse(true);
				$set[] = $block;
			}else{
				$removed[] = $block;
			}
		}
		
		foreach($ids as $id){
			$unset[] = $id;
		}
		/*
		foreach($ids as $id){
			$flag = false;
			foreach($blocks as $key => $block){
				if($block->getSoyId() == $id){
					$set[] = $block;
					$flag = true;
					break;
				}
			}
			if(!$flag){
				$unset[] = $id;
			}
		}
		*/
		$this->setAttribute("setupedBlocks",$set);
		$this->setAttribute("unsetSoyIds",$unset);
		$this->setAttribute("removedBlocks",$removed);
		
		return SOY2Action::SUCCESS;
	}
}
?>