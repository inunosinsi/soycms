<?php
SOY2::import("module.site.common.output_user",".php");
SOY2::import("domain.user.SOYShop_User");

class SOYShop_UserListComponent extends HTMLList{

	private $obj;
	private $pageClassName;
	private $iteration;

	function execute(){
		$iteration = $this->getAttribute("cms:count");
		if(strlen($iteration)){
			$this->iteration = $iteration;
		}
		parent::execute();
	}

	protected function populateItem($entity, $key, $counter, $length){
		if(false == ($entity instanceof SOYShop_User)){
			$entity = new SOYShop_User();
		}

		//指定の表示回数を超えていたら表示しない
		if($this->iteration > 0 && $counter > $this->iteration){
			return false;
		}

		//実行
		soyshop_output_user($this, $entity, $this->obj);

		//非公開は表示しない。ただし、商品詳細確認モードがtrueの場合は表示する。
		return $this->getIsDisplay($entity);
	}

	/**
	 * 商品詳細ページを表示するか？
	 * @param object SOYShop_Item
	 * @return boolean
	 */
	function getIsDisplay($entity){
		if($entity->getIsDisabled() == SOYShop_User::USER_IS_DISABLED) return false;
		return true;
	}

	function setObj($obj){
		$this->obj = $obj;
		if($obj){
			$this->pageClassName = get_class($obj);
		}
	}

	public function getPageClassName(){
		return $this->pageClassName;
	}
}
?>
