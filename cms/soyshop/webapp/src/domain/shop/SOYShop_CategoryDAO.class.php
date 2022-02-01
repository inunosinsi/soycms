<?php
/**
 * @entity shop.SOYShop_Category
 */
abstract class SOYShop_CategoryDAO extends SOY2DAO{

	/**
     * @return id
     * @trigger onInsert
     */
    abstract function insertImpl(SOYShop_Category $bean);

	/**
	 * @index id
	 * @order category_order,id
	 */
    abstract function get();

    /**
     * @return list
     * @index id
     * @order category_order,id
     */
    abstract function getByIsOpen($isOpen);

    /**
     * @return object
     */
    abstract function getById($id);

    /**
     * @return object
     */
    abstract function getByAlias($alias);

	/**
	 * @return list
	 */
	abstract function getByName($name);

   /**
	 * @trigger onUpdate
	 */
	abstract function updateImpl(SOYShop_Category $bean);

	/**
	 * @final
	 */
	function onInsert($query, $binds){

		if(strlen((string)$binds[":name"]) < 1){
			$mapping = $this->getMapping();
			$binds[":name"] = "new_category_" . count($mapping);
		}

		if(strlen((string)$binds[":alias"]) < 1){
			$binds[":alias"] = rawurldecode($binds[":name"]);
		}

		if(!isset($binds[":order"]) || !is_numeric($binds[":order"])){
			$binds[":order"] = 0;
		}

		return array($query, $binds);
	}

	/**
	 * @final
	 */
	function onUpdate($query, $binds){
		if(!isset($binds[":order"]) || !is_numeric($binds[":order"])){
			$binds[":order"] = 0;
		}

		return array($query, $binds);
	}

	/**
	 * @final
	 * @return id
	 */
	function insert($bean){
		$id = $this->insertImpl($bean);

		//build mapping
		$this->buildMapping();

		return $id;
	}

	/**
	 * @final
	 */
	function update($bean){
		$this->updateImpl($bean);

		//build mapping
		$this->buildMapping();
	}

	abstract function deleteImpl($id);

	/**
	 * final
	 */
	function deleteById($id){

		try{
			SOYShopPlugin::load("soyshop.category.customfield");
			SOYShopPlugin::invoke("soyshop.category.customfield", array(
				"deleteCategoryId" => $id
			));

			$obj = $this->getById($id);

			$newParent = $obj->getParent();

			$this->updateParent($id,$newParent);

			$this->deleteImpl($id);

			$this->buildMapping();

		}catch(Exception $e){
			throw $e;
		}
	}

	/**
	 * @columns #parent#
	 * @query #parent# = :id
	 */
	abstract function updateParent($id,$parent);

	/**
	 * @final
	 */
	function buildMapping($id = null,$array = null){

		$mapping = array();

		$array = $this->get();

		$tree = array();
		$root = array();

		foreach($array as $obj){
			if($obj->getParent()){
				$parent = $obj->getParent();
				if(!isset($tree[$parent]))$tree[$parent] = array();
				$tree[$parent][] = $obj;
			}else{
				$root[] = $obj;
			}
		}

		foreach($array as $obj){
			$mapping[$obj->getId()] = array_unique($this->_buildMapping($obj->getId(),$tree));
		}

		SOYShop_DataSets::put("category.mapping",$mapping);
	}

	/**
	 * @final
	 */
	function _buildMapping($id,$array){
		$res = array();
		if(isset($array[$id]) && !empty($array)){

			//子ツリー
			foreach($array[$id] as $obj){

				$res = array_merge($res,$this->_buildMapping($obj->getId(),$array));

				//子を追加
				$res[] = $obj->getId();
			}

		}

		//自分自身
		$res[] = $id;

		return $res;
	}

	/**
	 * @final
	 */
	function getMapping(){
		return SOYShop_DataSets::get("category.mapping", array());
	}

	function isAlias($alias){

		try{
			$this->getByAlias($alias);
		}catch(Exception $e){
			return false;
		}
		return true;
	}

	/**
	 * ルートカテゴリから取得
	 * @param current category, include curent, ksort
	 */
	function getAncestry($current, $myself=true, $ksort=true){
		$category = array();

		if(is_null($current))return $category;
		if($myself)$category[] = $current;
		try{
			$node = $current;
			$hasParent = true;
			while($hasParent){
				$parent = $node->getParent();

				if(empty($parent)){
					$hasParent = false;
				}else{
					$node = $this->getById($node->getParent());
					$category[] = $node;
				}
			}
		}catch(Exception $e){
			return array();
		}

		if($ksort)krsort($category);

		return $category;
	}

	/**
	 *
	 */
	function getRootCategories(){

		$all = $this->get();
		$root = array();

		foreach($all as $obj){
			$parent = $obj->getParent();
			if(empty($parent)) $root[] = $obj;
		}

		return $root;
	}
}
