<?php

$source = SOY2::RootDir() . "logic/init/theme/bryon/sample/noimage.jpg";
$dist = SOYSHOP_SITE_DIRECTORY . "themes/sample/noimage.jpg";
copy($source, $dist);
