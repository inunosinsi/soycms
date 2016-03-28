<?php
if(!defined("ADMIN_DB_DSN"))define("ADMIN_DB_DSN","sqlite:".SOY2::RootDir()."db/cms.db");
if(!defined("ADMIN_DB_PASS"))define("ADMIN_DB_PASS","");
if(!defined("ADMIN_DB_USER"))define("ADMIN_DB_USER","");
if(!defined("ADMIN_DB_EXISTS"))define("ADMIN_DB_EXISTS",file_exists(SOY2::RootDir()."db/cms.db") && filesize(SOY2::RootDir()."db/cms.db")>0);

if(!defined("CMS_FILE_DB"))define("CMS_FILE_DB","sqlite:".SOY2::RootDir()."db/file.db");
if(!defined("CMS_FILE_DB_EXISTS"))define("CMS_FILE_DB_EXISTS",file_exists(SOY2::RootDir()."db/file.db"));
?>
