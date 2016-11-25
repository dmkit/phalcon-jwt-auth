<?php
$file = '/vendor/autoload.php';
$i=1;
// 3 subdirectories
$directoryDepth = 3;
for($i; $i<=$directoryDepth; $i++) {
	$real_path = __DIR__ . '/..'.$file;
	if( file_exists($real_path) ) {
		require_once $real_path;
		break;
	}
}