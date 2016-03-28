<?php

class UpdateAction extends SOY2Action{

	protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
    
    	//Formの値をLogicに渡す値に整形
    	$siteRoleArray = array();
    	$siteRoleForm = $request->getParameter("siteRole");
    	$adminLogic = SOY2Logic::createInstance("logic.admin.Administrator.AdministratorLogic");
    	
    	
    	foreach($siteRoleForm as $userId => $siteRole){
    		//初期ユーザーは更新しない
    		if($adminLogic->checkDefaultUser($userId)){
    			continue;
    		}
    		foreach($siteRole as $siteId => $permission){
	    		array_push($siteRoleArray,array(
	    			"userId"=>$userId,
	    			"siteId"=>$siteId,
	    			"siteRole"=>$permission
	    		));
    		}
    	}
    	
    	$logic = SOY2Logic::createInstance("logic.admin.SiteRole.SiteRoleLogic");
    	if($logic->updateSiteRoles($siteRoleArray)){
    		return SOY2Action::SUCCESS;
    	}else{
    		return SOY2Action::FAILED;
    	}
    	
    
    }
}
?>