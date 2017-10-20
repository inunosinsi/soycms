<?php
/**
 * @table Block
 */
class Block {

	//表示順も兼ねます。
	const BLOCK_LIST = "EntryBlockComponent,LabeledBlockComponent,SiteLabeledBlockComponent,MultiLabelBlockComponent,PluginBlockComponent";

	/**
	 * @id
	 */
    private $id;

    /**
     * @column soy_id
     */
    private $soyId;

    /**
     * @column page_id
     */
    private $pageId;

    private $class;
	private $object;

	/**
	 * @no_persistent
	 */
	private $_object;

	/**
	 * @no_persistent
	 */
	private $isUse = false;

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getSoyId() {
		return $this->soyId;
	}
	function setSoyId($soyId) {
		$this->soyId = $soyId;
	}
	function getClass() {
		return $this->class;
	}
	function setClass($class) {
		$this->class = $class;
	}
	function getObject() {
		return $this->object;
	}

	function setObject($object) {
		if(is_object($object)){
			$this->object = serialize($object);
			$this->_object = $object;
		}else{
			$this->object = $object;
			$this->_object = unserialize($object);
		}
	}

	/**
	 * Block#objectのインスタンスが欲しい時はこちらを呼びましょう。
	 */
	function getObjectInstance(){
		return $this->_object;
	}

	function getPageId() {
		return $this->pageId;
	}
	function setPageId($pageId) {
		$this->pageId = $pageId;
	}

	/**
	 * @return BlockComponent
	 *
	 * BlockComponentの設置場所は「/common/site_include/block」以下
	 *
	 */
	function getBlockComponent(){

		try{

			if(strlen($this->getClass())<1)throw new Exception();

			if(!class_exists($this->getClass())){
				include_once(CMS_BLOCK_DIRECTORY . $this->getClass() . "/block.php");
			}

			if($this->getObject()){
				$component = unserialize($this->object);
			}else{
				$className = $this->getClass();
				$component = new $className;
			}

			return $component;

		}catch(Exception $e){

		}

		return new BrokenBlockComponent();
	}

	/**
	 * @return Array ブロックプラグインリスト
	 */
	public static function getBlockComponentList(){

		$dir = CMS_BLOCK_DIRECTORY;

		$files = (defined("SOYCMS_BLOCK_LIST")) ? explode(",",SOYCMS_BLOCK_LIST) : explode(",",self::BLOCK_LIST);

		$array = array();
		foreach($files as $key => $file){

			if(!is_dir(CMS_BLOCK_DIRECTORY . $file)){
				continue;
			}

			if(strstr($file,"."))continue;

			include_once(CMS_BLOCK_DIRECTORY . $file . "/block.php");

			$array[$file] = new $file;
		}

		return $array;
	}

	/**
	 * テンプレートに書かれているかどうか
	 * @return boolean
	 */
	function isUse(){
		return $this->isUse;
	}

	function setIsUse($value){
		$this->isUse = (boolean)$value;
	}

}


/**
 * Block
 */
interface BlockComponent{

	const ORDER_ASC = 1;//昇順
	const ORDER_DESC = 2;//降順

	/**
	 * @return SOY2HTML
	 * 設定画面用のHTMLPageComponent
	 */
	function getFormPage();

	/**
	 * @return SOY2HTML
	 * 表示用コンポーネント
	 */
	function getViewPage($page);

	/**
	 * @return SOY2HTML
	 * 一覧表示用コンポーネント
	 */
	function getInfoPage();

	/**
	 * @return string コンポーネント名
	 */
	function getComponentName();

	/**
	 * @return string コンポーネント説明
	 */
	function getComponentDescription();

}

/**
 * 万が一データが壊れた場合にBroken情報を伝えるためのコンポーネント
 */
class BrokenBlockComponent implements BlockComponent{
	/**
	 * @return SOY2HTML
	 * 設定画面用のHTMLPageComponent
	 */
	function getFormPage(){return "broken"; }

	/**
	 * @return SOY2HTML
	 * 表示用コンポーネント
	 */
	function getViewPage($page){}

	/**
	 * @return SOY2HTML
	 * 一覧表示用コンポーネント
	 */
	function getInfoPage(){ return "this block is broken"; }

	/**
	 * @return string コンポーネント名
	 */
	function getComponentName(){ return "this block is broken"; }

	/**
	 * @return string コンポーネント説明
	 */
	function getComponentDescription(){return ""; }


}
?>
