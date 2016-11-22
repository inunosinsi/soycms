<?php
/*
 * soyshop.site.beforeoutput.php
 * Created: 2010/03/11
 */

class EntryImportBeforeOutput extends SOYShopSiteBeforeOutputAction{

	function beforeOutput($page){
		
		SOY2::import("util.SOYAppUtil");
		SOY2::import("module.plugins.parts_entry_import.component.EntryListComponent");
		SOY2::import("module.plugins.parts_entry_import.util.EntryImportUtil");
		
		$config = EntryImportUtil::getConfig();
		$old = SOYAppUtil::switchAdminDsn();
		
		SOY2::import("util.CMSPlugin");
		SOY2::import("util.UserInfoUtil");
		
		$site = EntryImportUtil::getSite($config["siteId"]);
		
		SOYAppUtil::resetAdminDsn($old);
		
		
		$old = EntryImportUtil::switchSiteDsn($site->getDataSourceName());
				
		/* サイト → ブログ → 記事一覧 */
		$page->createAdd("entry_list","EntryListComponent", array(
			"soy2prefix" => "block",
			"list" => EntryImportUtil::getBlogEntiryList($config["blogId"], (int)$config["count"]),
			"blogUrl" => EntryImportUtil::getBlogUrl($config["blogId"], $site->getUrl()),
			"customField" => self::getCustomfieldConfig($config["siteId"]),
			"entryAttributeDao" => SOY2DAOFactory::create("cms.EntryAttributeDAO"),
			"thisIsNewDate" => self::getSOYCMSThisIsNewConfig($config["siteId"])
		));
		
		//元に戻す
		EntryImportUtil::resetSiteDsn($old);
	}
	
	private function getCustomfieldConfig($siteId){
		$fname = $_SERVER["DOCUMENT_ROOT"] . $siteId . '/.plugin/CustomFieldAdvanced.config';
		if(file_exists($fname)){
			include_once(dirname(__FILE__) . "/class/CustomFieldPluginAdvanced.class.php");
			include_once(dirname(__FILE__) . "/class/CustomField.class.php");
			$obj = unserialize(file_get_contents($fname));
			return $obj->customFields;
		}else{
			return array();
		}
	}
	
	private function getSOYCMSThisIsNewConfig($siteId){
		$fname = $_SERVER["DOCUMENT_ROOT"] . $siteId . '/.plugin/SOYCMS_ThisIsNew.config';
		if(file_exists($fname)){
			preg_match('/"daysToBeNew";s:[0-9]:"(.*?)";/', file_get_contents($fname), $tmp);
			if(isset($tmp[1]) && is_numeric($tmp[1])) return (int)$tmp[1];
		}
		
		$thisIsNewConfig = SOYShop_DataSets::get("common_this_is_new", array("date" => 7));
		return (int)$thisIsNewConfig["date"];
	}
}

SOYShopPlugin::extension("soyshop.site.beforeoutput","parts_entry_import","EntryImportBeforeOutput");