<?php

class CategoryLogic extends SOY2LogicBase{

	private $categoryMap = array();
	private $uniqueNames = array();
	private $categories = array();
	private $dao;

	function __construct(){
    	$this->dao = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
	}

	/**
	 * 自身の先祖を > でつなげたものの配列
	 */
	function getCategoryMap(){
		if(!is_array($this->categories) || !count($this->categories)) $this->categories = $this->getCategories();
		$ids = array_keys($this->categories);

		foreach($ids as $id){
			$this->categoryMap[$id] = $this->getCategoryChain($id);
		}

		return $this->categoryMap;
	}

	/**
	 * カテゴリIDに対応したカテゴリ一覧を作成
	 * @param boolean $intOnly only category_id's list
	 * @return array(id => name)
	 */
	function getCategoryList($intOnly=false){
		if(!is_array($this->categories) || !count($this->categories)) $this->categories = $this->getCategories();
		if(!count($this->categories)) return array();

		$list = array();
		foreach($this->categories as $cat){
			if($intOnly && !is_numeric($cat->getName())) continue;	//intOnlyがtrueの場合は数字以外のカテゴリ名を除く
			$list[$cat->getId()] = $cat->getName();
		}
		return $list;
	}


	/**
	 * カテゴリー名を > でつなげたもの
	 * ルート>...>親>自分
	 */
	public function getCategoryChain($id){
		static $chains = array();
		$chain = array();

		if(isset($chains[$id])){
			return $chains[$id];
		}

		for(
			$category = $this->getById($id);//自分
			$category && !array_key_exists($category->getId(), $chain);//親が取れていてループになっていない
			$category = $this->getById($category->getParent())//親
		){
			//$chain[] = $category;
			$chain[$category->getId()] = $this->escapeCategoryName($category->getName());
		}

		$chain = array_reverse($chain, true);
		$tmp_chain = "";
		foreach($chain as $tmp_id => $tmp_name){
			$chains[$tmp_id] = $tmp_chain.$tmp_name;
			$tmp_chain = $chains[$tmp_id] . ">";
		}

		return $chains[$id];
	}

	/**
	 * カテゴリー名をIDで取得
	 */
	public function getById($id){
		static $categories = array();

		if(count($this->categories)){
			$categories = $this->categories;
		}

		if(!array_key_exists($id, $categories)){
			try{
				$categories[$id] = $this->dao->getById($id);
			}catch(Exception $e){
				$categories[$id] = null;
			}
		}

		return $categories[$id];
	}

	/**
	 * 名前で特定できるカテゴリー
	 */
	function getUniqueNames(){
    	$this->categories = $this->getCategories();

    	$unique = $notUnique = array();
    	foreach($this->categories as $category){
			$name = $category->getName();

			if(array_search($name, $notUnique) !== false){
				//do nothing
				continue;
			}

			$dupulicate = array_search($name, $unique);
			if($dupulicate === false){
				$unique[$category->getId()] = $name;
			}else{
				unset($unique[$dupulicate]);
				$notUnique[] = $name;
			}

    	}
		$this->uniqueNames = $unique;
		return $this->uniqueNames;
	}

    /**
     * カテゴリー全部
     */
    function getCategories(){
    	if(!$this->categories) $this->categories = $this->dao->get();
    	return $this->categories;
    }

    /**
     * カテゴリー名を > でつなげるためにエスケープする
     */
    function escapeCategoryName($name){
    	return str_replace(">", "&gt;", $name);
    }

    function unescapeCategoryName($str){
    	return str_replace("&gt;", ">", $str);
    }

    /**
     * > でつながったカテゴリー名から末尾の自身のカテゴリー名を取得する
     */
    function getNameFromChain($chain){
    	$names = explode(">", $chain);
    	$name = array_pop($names);

    	return $this->unescapeCategoryName($name);
    }

    //カテゴリーカスタムフィールド
    function setAttribute($id, $key, $value){
    	$dao = self::_attrDao();
    	$dao->delete($id,$key);

    	$obj = new SOYShop_CategoryAttribute();
		$obj->setCategoryId($id);
		$obj->setFieldId($key);
		$obj->setValue($value);

		$dao->insert($obj);
    }

    /**
     * 削除
     */
	function delete($id){
		$this->getCategories();
		if($id instanceof SOYShop_Category) $id = $id->getId();
		try{
			$this->dao->deleteById($id);
			unset($this->categories[$id]);
		}catch(Exception $e){

		}
	}

	/**
	 * 商品データの更新を実行する
	 * @param
	 */
	function update($cat){
		$this->getCategories();
		try{
			if(is_array($cat)){
				$old = $this->categories[$cat["id"]];
				$cat = SOY2::cast($old, (object)$cat);
			}
			$this->dao->update($cat);
			$this->categories[$cat->getId()] = $cat;
		}catch(Exception $e){
			return false;
		}
	}

	/**
	 * 商品データの挿入を実行する
	 * @param SOYShop_Item
	 */
	function insert(SOYShop_Category $cat){
		$this->getCategories();
		try{
			$id = $this->dao->insert($cat);
			$cat->setId($id);
			$this->categories[$id] = $cat;
			return $id;
		}catch(Exception $e){
			//
			return null;
		}
	}

	/**
	 * カテゴリーの更新または挿入を実行する
	 * 同じIDのカテゴリーがすでに登録されている場合に更新を行う
	 * @param SOYShop_Category
	 * @return id
	 */
	function insertOrUpdate(SOYShop_Category $cat){
		if(strlen($cat->getId())){
			$this->update($cat);
			return $cat->getId();
		}else{
			return $this->insert($cat);
		}
	}

	/**
	 * インポートで新カテゴリーを作成
	 */
	function import($arr){
		$parents = array_reverse(explode(">", $arr["name"]));
		$parentsChains = array();
		foreach($parents as $cat){
			foreach($parentsChains as $key => $chain){
				$parentsChains[$key][] = $cat;
			}
			$parentsChains[] = array($cat);
		}

		$categoryMap = $this->getCategoryMap();
		$newParents = array();
		foreach($parentsChains as $chain){
			$chain = array_reverse($chain);
			$parentsChain = implode(">", $chain);
			$id = array_search($parentsChain, $categoryMap);
			if($id === false){
				$newParents[] = $parentsChain;
			}else{
				break;
			}
		}
		$newParents = array_reverse($newParents);

		$count = count($this->getCategories());
		$parent = ($id!==false) ? $id : null ;
		foreach($newParents as $chain){
			$category = new SOYShop_Category();

			$name = $this->getNameFromChain($chain);
			$category->setName($name);

			if($chain == $arr["name"]){
				if(isset($arr["alias"])) $category->setAlias($arr["alias"]);
				if(isset($arr["order"])) $category->setOrder($arr["order"]);
			}
			if(strlen($category->getAlias()) == 0){
				$category->setAlias("category-" . $count . ".html");
			}

			$category->setParent($parent);

			try{
				$id = $this->insert($category);
			}catch(Exception $e){
				$category->setAlias(md5(time()));
				$id = $this->insert($category);
			}
			$parent = $id;
			$count++;
		}

		return $id;
	}

	private function _attrDao(){
    	static $dao;
    	if(!$dao) $dao = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");
    	return $dao;
    }
}
