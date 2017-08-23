<?php

class AddAfterPage extends CMSWebPageBase{

    function AddAfterPage($arg) {
    	$pageId = @$arg[0];
    	$treeId = @$arg[1];
    	
    	$res = $this->run("Page.Mobile.AddVertualPageAction",array(
    		"pageId"=>$pageId,
    		"treeId"=>$treeId
    	));
    	
    	$this->jump("Page.Mobile.ModifyPopupPage.".$pageId."/".$res->getAttribute("id")."?msg=create");
    	
    	exit;
    	
    	echo '<script type="text/JavaScript">';
		echo 'window.parent.document.getElementById("virtual_tree").contentWindow.location.reload();';
		echo 'window.parent.common_close_layer(window.parent);';
		echo '</script>';    	
    }
}
?>