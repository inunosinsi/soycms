<?php
//SOY2の設定
SOY2::RootDir(dirname(__FILE__) . "/src/");

//SOY2HTMLの設定
SOY2HTMLConfig::PageDir(dirname(__FILE__) . "/pages/");

//SOY2DAOの設定
SOY2DAOConfig::DaoDir(SOY2::RootDir() . "domain/");
SOY2DAOConfig::EntityDir(SOY2::RootDir() . "domain/");

if(SOYCMS_DB_TYPE == "sqlite"){
	SOY2DAOConfig::Dsn("sqlite:" . CMS_COMMON . "db/gallery.db");
}else{
	include_once(CMS_COMMON . "config/db/".SOYCMS_DB_TYPE.".php");
	SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
	SOY2DAOConfig::user(ADMIN_DB_USER);
	SOY2DAOConfig::pass(ADMIN_DB_PASS);
}

SOY2::import("func.common", ".php");

//PHP
mb_internal_encoding("UTF-8");

define("SOY_GALLERY_IMAGE_UPLOAD_DIR", soy2_realpath($_SERVER["DOCUMENT_ROOT"])."GalleryImage/");
