<?php
SOY2::import("domain.cms.Page");

/**
 * @table MobilePage
 */
class MobilePage extends Page{
	
	private $virtual_tree = array();
	
	/**
	 * 保存用のstdObjectを返します。
	 */
	function getConfigObj(){
		$obj = parent::getPageConfigObject();
		
		$obj->virtual_tree = $this->getVirtual_tree();
		return $obj;
	}
	
	public static function cast(Page $page){
		if($page->getPageType() != Page::PAGE_TYPE_MOBILE){
			throw new Exception("This Page is not Mobile Page");
		}
		
		$mobilePage = SOY2::cast("MobilePage",$page);
		$config = $mobilePage->getPageConfigObject();
    	
    	if($config){
    		$config = unserialize($mobilePage->getPageConfig());
    		SOY2::cast($mobilePage,$config);
    	}
    	
    	//Rootが無い場合
    	if(count($mobilePage->getVirtual_tree())==0){
    		$rootTree = $this->getRootVirtualTreePage();
			$mobilePage->insertVirtual_tree($rootTree);	
    	}
    	
    	return $mobilePage;			
	}

	function getVirtual_tree() {
		return $this->virtual_tree;
	}
	function setVirtual_tree($virtual_tree) {
		$this->virtual_tree = $virtual_tree;
	}
	
	/**
	 * @return ID エラー時は-1
	 */
	function insertVirtual_tree(VirtualTreePage $tree,$parent = 0){
		$keys = array_keys($this->virtual_tree);
		if(count($keys) == 0){
			$newId = 0;
		}else{
			$newId = max($keys) + 1;
		}
		$tree->setId($newId);
		$tree->setParent($parent);
		$this->virtual_tree[$newId] = $tree;
		
		//ROOTは子を追加しない
		if($newId == 0){
			return -1;
		}
		
		$this->virtual_tree[$parent]->addChild($newId);
		
		return $newId;
	}
	
	function updateVirtual_tree(VirtualTreePage $tree){
		$this->virtual_tree[$tree->getId()] = $tree;
	}
	
	function deleteVirtual_tree($treeId,$recuresive = true){
		if(isset($this->virtual_tree[$treeId])){
			$delete_tree_childs = $this->virtual_tree[$treeId]->getChild();
			if($recuresive){
				foreach($delete_tree_childs as $childId){
					$this->deleteVirtual_tree($childId,true);
				}
			}else{
				foreach($delete_tree_childs as $childId){
					$this->virtual_tree[0]->addChild($childId);
				}
			}
			unset($this->virtual_tree[$treeId]);
			return true;
		}else{
			return false;
		}
	}
	
	function getVirtualTreeById($id){
		if(isset($this->virtual_tree[$id])){
			return $this->virtual_tree[$id];
		}else{
			return null;
		}
	}
	
	function getVirtualTreeByAlias($alias){
		foreach($this->virtual_tree as $tree){
			if(strcmp($tree->getAlias(),$alias) === 0){
				return $tree;
			}
		}
		return null;
	}
	
}

class VirtualTreePage{
	private $id;
	private $type = 0;
	private $size = "1";
	private $entries = array();
	private $label = null;
	private $title;
	private $parent = 0;
	private $child = array();
	private $alias;
	
	const TYPE_ENTRY = 0;
	const TYPE_LABEL = 1;
	const TYPE_ROOT  = -1;

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getType() {
		return $this->type;
	}
	function setType($type) {
		$this->type = $type;
	}
	function getSize() {
		return $this->size;
	}
	function setSize($size) {
		$this->size = $size;
	}
	function getEntries() {
		return $this->entries;
	}
	function setEntries($entries) {
		if(is_null($entries)){
			$entries = array();
		}
		$this->entries = $entries;
	}
	function addEntry($entryId){
		$this->entries[] = $entryId;
	}
	function getLabel() {
		return $this->label;
	}
	function setLabel($label) {
		$this->label = $label;
	}
	function getTitle() {
		return $this->title;
	}
	function setTitle($title) {
		$this->title = $title;
	}
	function getChild() {
		return $this->child;
	}
	function setChild($child) {
		$this->child = $child;
	}
	function addChild($child){
		if(in_array($child,$this->child)){
			return false;
		}else{
			$this->child[] = $child;
		}
	}
	function removeChild($id){
		if(($key = array_search($id,$this->child)) !== false){
			unset($this->child[$key]);
		}else{
			return false;
		}
	}
	function getParent() {
		return $this->parent;
	}
	function setParent($parent) {
		$this->parent = $parent;
	}

	function getAlias() {
		if(strlen($this->alias)<1){
			$this->alias = $this->id;
		}
		return $this->alias;
	}
	function setAlias($alias) {
		$this->alias = $alias;
	}
}

?>