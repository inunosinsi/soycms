<?php

class TreeComponent extends HTMLLabel{

    private $list;
    private $root = "";
    private $expand = true;

    function execute(){
		$depth = ($this->getAttribute("depth")) ? $this->getAttribute("depth") : 0;

		$html = TreeComponent::buildTree($this->getList(),$this,$depth);

		if(strlen($this->root) > 0){
			$html = "<li>" . $this->root . "<ul>" . $html . "</ul></li>";
		}

		$html .= $this->getScript();

		$this->setHtml($html);

		parent::execute();
	}

	public static function buildTree($array,$component = null,$depth = 0){
		if(!$component)$component = new TreeComponent();

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

		$html = $component->_buildTree($root,$tree,$depth);
		return $html;
	}

	function getList() {
		return $this->list;
	}
	function setList($list) {
		$this->list = $list;
	}

	function _buildTree($array,$tree,$depth){
		$html = array();

		foreach($array as $obj){

			if(is_null($this->getHref($obj->getId()))){
				$onclick = $this->getOnclick($obj->getId());
				$html[] = '<li><a class="'.$this->getClass($obj->getId()).'" href="javascript:void(0);" onclick="'.$onclick.'" object:id="'.$obj->getId().'" title="' . $obj->getNameWithStatus() . '">' . $obj->getNameWithStatus() . '</a>';
			}else{
				$href = $this->getHref($obj->getId());
				$html[] = '<li><a class="'.$this->getClass($obj->getId()).'" href="'. $href .'" object:id="'.$obj->getId().'">'. $obj->getNameWithStatus() .'</a>';
			}

			if(isset($tree[$obj->getId()])){
				$html[] = '<ul>';
				$html[] = $this->_buildTree($tree[$obj->getId()],$tree,($depth+1));
				$html[] = '</ul>';
			}
			$html[] = "</li>";
		}

		return implode("\n" . str_repeat("\t",$depth),$html);
	}

	function getOnclick($id){
		return 'return common_category_tree_click($(this));';
	}

	function getClass($id){
		return "";
	}

	function getHref($id){
		return null;
	}

	function getRoot() {
		return $this->root;
	}
	function setRoot($root) {
		$this->root = $root;
	}

	function getExpand() {
		return $this->expand;
	}
	function setExpand($expand) {
		$this->expand = $expand;
	}

	function getScript(){
		$collapse = ($this->expand) ? "false" : "true";

		$script = '
		<script type="text/javascript">
		$(document).ready(function(){

			// first example
			$("#category_tree").treeview({
				persist: "location",
				collapsed: '.$collapse.',
				unique: false
			});

			$(".category_tree").each(function(){
				$(this).treeview({
					persist: "location",
					collapsed: '.$collapse.',
					unique: false
				});
			});

		});
		</script>';

		return $script;
	}
}
?>
