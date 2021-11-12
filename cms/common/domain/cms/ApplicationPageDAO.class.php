<?php
/**
 * @entity cms.ApplicationPage
 */
class ApplicationPageDAO {

    function get(){
		return self::_dao()->getByPageType(Page::PAGE_TYPE_APPLICATION);
	}

	/**
	 * IDを指定して取得
	 */
	function getById(int $id){

		$obj = self::_dao()->getById($id);

		if($obj->getPageType() != Page::PAGE_TYPE_APPLICATION){
			throw new Exception("This Page is not Application Page.");
		}


		$page = SOY2::cast("ApplicationPage",$obj);

		$config = $page->getPageConfigObject();

    	if($config){
    		$config = unserialize($page->getPageConfig());
    		SOY2::cast($page,$config);
    	}

    	return $page;
	}

	function updatePageConfig(ApplicationPage $page){

		$dao = self::_dao();
		$_page = $dao->getById($page->getId());

		//テンプレートは更新しない
		$page->setTemplate($_page->getTemplate());

		$configObj = $page->getConfigObj();
		$page->setPageConfig($configObj);
		$dao->update($page);
		$dao->updatePageConfig($page);
	}

	private function _dao(){
		return SOY2DAOFactory::create("cms.PageDAO");
	}
}
