<?php

function soyshop_g_search_field($html, $htmlObj){
    $obj = $htmlObj->create("soyshop_g_search_field", "HTMLTemplatePage", array(
            "arguments" => array("soyshop_g_search_field", $html)
    ));

}
