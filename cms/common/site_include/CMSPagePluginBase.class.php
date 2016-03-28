<?php

class CMSPagePluginBase extends PluginBase{

	protected $_soy2_prefix = "cms";

	private $page;

	private $arguments;

	function execute(){
    	$soyValue = $this->soyValue;

    	if(is_array($soyValue)){
    		$soyValue[0]->_page_arguments = $this->getArguments();

			//prepareというメソッドがあればそれを呼ぶ
    		if(method_exists($soyValue[0],"prepare")){
    			$soyValue[0]->prepare();
    		}

    		$attributes = $this->getAttributes();
    		foreach($attributes as $key => $value){
	    		$key = str_replace(":","_",$key);

	    		$funcName = "set".ucwords($key);

	    		if(method_exists($soyValue[0],$funcName)){
	    			$soyValue[0]->$funcName($value);
	    		}
	    	}

	    	$this->clearAttribute($key);
    	}

    	//オブジェクトにprivateまたはprotectedな変数があるとヌル文字が入るためaddslashesする
    	$this->setInnerHTML('<?php echo call_user_func(unserialize(stripslashes(\''.addslashes(serialize($soyValue)).'\')),\''.
    		str_replace("'","\\'",$this->getInnerHTML()).
		'\','.$this->page->getId().'); ?>');
    }

    function getPage() {
    	return $this->page;
    }
    function setPage($page) {
    	$this->page = $page;
    }

    function getArguments() {
    	return $this->arguments;
    }
    function setArguments($arguments) {
    	$this->arguments = $arguments;
    }
}
?>