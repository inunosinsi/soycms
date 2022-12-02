<?php
/**
 * @param bool $isReserve=false
 * @return array
 */
function soycms_get_plugin_ids(bool $isReserve=false){
    $list = array();
		
    $dir = CMS_PAGE_PLUGIN;
    $files = scandir($dir);
    foreach($files as $pluginFileName){
        $pluginId = soycms_get_plugin_id_by_plugin_file_name($pluginFileName);
        if(strlen($pluginId)) {
            $list[] = $pluginId;
            if($isReserve && $pluginId != $pluginFileName) $list[] = $pluginFileName;
        }
    }

    return $list;
}

/**
 * @param string
 * @return array
 */
function soycms_get_plugin_id_by_plugin_file_name(string $pluginFileName){
    if($pluginFileName[0] == "." || !is_dir(CMS_PAGE_PLUGIN . $pluginFileName) || !is_readable(CMS_PAGE_PLUGIN . $pluginFileName ."/".$pluginFileName.".php")) return "";

    $lines = explode("\n", file_get_contents(CMS_PAGE_PLUGIN . $pluginFileName ."/".$pluginFileName.".php"));
    $pluginId = "";

    foreach($lines as $line){
        preg_match('/PLUGIN_ID.*=.*\"(.*)\";/', $line, $tmp);
        if(!isset($tmp[1])) continue;
        $pluginId = $tmp[1];
        break;
    }

    return (strlen($pluginId)) ? $pluginId : $pluginFileName;
}