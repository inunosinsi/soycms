<?php
class LINELoginInstall extends SOYShopPluginInstallerBase{

	function onInstall(){
		//LINE Login用の画像を公開側ディレクトリにコピーする
		$fileDir = SOYSHOP_WEBAPP . "src/module/plugins/line_login/img/";
		$distDir = self::getDistDirectory();

		if(is_dir($fileDir) && is_readable($fileDir)){
			$files = scandir($fileDir);
			foreach($files as $file){
				//.と..を除く
				if(strlen($file) < 3) continue;
				copy($fileDir . $file, $distDir . $file);
			}
		}
	}

	private function getDistDirectory(){
		$dir = SOYSHOP_SITE_DIRECTORY . "themes/social/";
		if(!file_exists($dir)) mkdir($dir);
		$dir .= "line/";
		if(!file_exists($dir)) mkdir($dir);
		return $dir;
	}

	function onUnInstall(){
		//何もしない
	}
}
SOYShopPlugin::extension("soyshop.plugin.install", "line_login", "LINELoginInstall");
