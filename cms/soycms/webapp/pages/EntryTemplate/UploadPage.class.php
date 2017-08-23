<?php

class UploadPage extends CMSWebPageBase{

	function doPost(){
		if(soy2_check_token()){
			$result = $this->run("EntryTemplate.TemplateUploadAction");

			if($result->success()){
				$this->addMessage("ENTRY_TEMPLATE_UPLOAD_SUCCESS");
			}else{
				$this->addErrorMessage("ENTRY_TEMPLATE_UPLOAD_FAILED");
			}
		}

		echo '<html><head><script type="text/javascript">parent.location.reload();</script></head></html>';


	}

	function __construct() {
		parent::__construct();
		$this->createAdd("upload_form","HTMLForm");
	}
}
