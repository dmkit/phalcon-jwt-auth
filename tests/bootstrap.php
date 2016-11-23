<?php

$file = __DIR__ . '/../vendor/autoload.php';

if( file_exists($file) ) {
	require_once $file;
} else {
	require_once __DIR__ . '/../../vendor/autoload.php';
}