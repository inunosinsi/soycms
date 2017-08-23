<?php

class SiteConfigLogic extends SOY2LogicBase{

    function get(){
    	$dao = SOY2DAOFactory::create("cms.SiteConfigDAO");
    	try{
    		return $dao->get();
    	}catch(Exception $e){
    		//SiteConfigがないのでデフォルトの値をINSERT
    		$siteConfig = new SiteConfig();
    		$siteConfig->setCharset(1);
    		$dao->insert($siteConfig);
    		return $dao->get();
    	}
    }
    
    function update($entity){
    	$dao = SOY2DAOFactory::create("cms.SiteConfigDAO");
    	$upDir = $entity->getDefaultUploadDirectory();
    	$upDir = str_replace("..","",$upDir);
    	
    	if(!file_exists(UserInfoUtil::getSiteDirectory().$upDir)){
    		throw new Exception("cannot found upload directory");
    	}
    	return $dao->update($entity);
    	
    }
}
?>