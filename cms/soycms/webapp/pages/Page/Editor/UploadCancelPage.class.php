<?php
class UploadCancelPage extends CMSWebPageBase{
	function doPost(){
		echo json_encode(
			$this->run("Entry.CancelUploadFileAction")->getAttribute("result")
		);
		exit;
	}
}
