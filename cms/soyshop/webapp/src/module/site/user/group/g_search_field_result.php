<?php

function soyshop_g_search_field_result(string $html, HTMLPage $htmlObj){
    $obj = $htmlObj->create("soyshop_g_search_field_result", "HTMLTemplatePage", array(
        "arguments" => array("soyshop_g_search_field_result", $html)
    ));
}
