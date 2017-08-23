<?php

class DeletePage extends CMSWebPageBase{

    function DeletePage($arg) {
    	if(soy2_check_token()){
	    	$pageId = @$arg[0];
	    	$treeId = @$arg[1];
	
			$result = $this->run("Page.Mobile.VirtualTreeDeleteAction",array(
				"pageId"=>$pageId,
				"treeId"=>$treeId
			));
    	}
		
		echo '<script type="text/JavaScript">';
		echo 'window.parent.document.getElementById("virtual_tree").contentWindow.location.reload();';
		echo 'window.parent.document.main_form.soy2_token.value = \''.soy2_get_token().'\';';
		echo 'window.parent.common_close_layer(window.parent);';
		echo '</script>';    	

    }
}
?>