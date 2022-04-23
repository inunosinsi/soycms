<?php
//SOY CMS共通設定の読み込み
include_once(CMS_COMMON."common.inc.php");

//CMSApplicationの読み込み
if(!defined("CMS_APPLICATION_WEBAPP")) define("CMS_APPLICATION_WEBAPP", CMS_APPLICATION_ROOT_DIR . "webapp/");
if(!defined("APPLICATION_BASE_PATH")) define("APPLICATION_BASE_PATH", CMS_APPLICATION_ROOT_DIR . "base/");
include_once(APPLICATION_BASE_PATH . "CMSApplication.class.php");

CMSApplication::import("util.CMSUtil");
CMSApplication::import("util.UserInfoUtil");
CMSApplication::import("util.SOYShopUtil");

//メッセージ
CMSApplication::import("util.CMSMessageManager");
CMSMessageManager::addMessageDirectoryPath(CMS_SOYBOY_MESSAGE_DIR);
CMSMessageManager::addMessageDirectoryPath(CMS_HELP_MESSAGE_DIR);
CMSMessageManager::addMessageDirectoryPath(CMS_CONTROLPANEL_MESSAGE_DIR);

