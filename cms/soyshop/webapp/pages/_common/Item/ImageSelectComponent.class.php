<?php

class ImageSelectComponent extends HTMLInput{

	private $width = 0;
	private $height = 0;
	private $domId;

	function getStartTag(){

		return $this->getWrapperStart() . parent::getStartTag() . $this->getWrapperEnd();

	}

	function setValue($value){
		$array = parse_url($value);
		if($array)$value = $array["path"];
		parent::setValue($value);
	}

	function getWrapperStart(){
		$html = array();

		$domText = '<?php echo $'.$this->getPageParam().'["'.$this->getId().'_attribute"]["id"]; ?>';

		$id = ($this->domId) ? $domText : $this->getAttribute("id");

		$size = "";

		if($this->width)$size .= "width:" . $this->width . "px;";
		if($this->height)$size .= "height:" . $this->height . "px;";

		$html[] = '<div class="image_select" id="image_select_wrapper_'.$id.'">';

		//選択ボタン
		$html[] = '<a class="btn btn-default" href="javascript:void(0);" onclick="return ImageSelect.popup(\''.$id.'\');">Select</a>';
		//$html[] = '<a class="btn btn-default" href="javascript:void(0);" data-toggle="modal" data-target="#imageSelectModal">Select</a>';

		//クリアボタン
		$html[] = '<a class="btn btn-default" href="javascript:void(0);" onclick="return ImageSelect.clear(\''.$id.'\');">Clear</a>';

		//プレビュー画像
		$html[] = '<a id="image_select_preview_link_'.$id.'" href="<?php echo $'.$this->getPageParam().'["'.$this->getId().'"]; ?>" target="_blank">';
		/**$html[] = '<img class="image_select_preview" id="image_select_preview_'.$id.'" src="/' . SOYSHOP_ID . '/im.php?src=<?php echo $'.$this->getPageParam().'["'.$this->getId().'"]; ?>&width=100"  style="'.$size.'<?php if(!$'.$this->getPageParam().'["'.$this->getId().'"]){ ?>display:none;<?php }?>" />';**/
		$html[] = '<img class="image_select_preview" id="image_select_preview_'.$id.'" src="<?php echo $'.$this->getPageParam().'["'.$this->getId().'"]; ?>"  style="'.$size.'<?php if(!$'.$this->getPageParam().'["'.$this->getId().'"]){ ?>display:none;<?php }?>" />';
		$html[] = '</a>';

		$html[] = '</div>';


		return implode("\n",$html);
	}

	function getWrapperEnd(){
		return '';
	}

	function execute(){

		parent::execute();

		if(!isset($_GET["debug"])){
			$this->setType("hidden");
		}

	}


	function getWidth() {
		return $this->width;
	}
	function setWidth($width) {
		$this->width = $width;
	}
	function getHeight() {
		return $this->height;
	}
	function setHeight($height) {
		$this->height = $height;
	}

	function getDomId() {
		return $this->domId;
	}
	function setDomId($domId) {
		$this->domId = $domId;
		$this->setAttribute("id",$domId);
	}
}
