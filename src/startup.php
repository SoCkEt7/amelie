<?php
require($_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php');
use Sunra\PhpSimple\HtmlDomParser;

session_start();

$loader = new Nette\Loaders\RobotLoader;

// Directories for RobotLoader to index (including subdirectories)
$loader->addDirectory($_SERVER['DOCUMENT_ROOT'].'/src/class');

// Set caching to the 'temp' directory
$loader->setTempDirectory($_SERVER['DOCUMENT_ROOT'].'/temp');
$loader->register(); // Activate RobotLoader

//Globals
$password = "nirvana"; // Password for authentication
$h = sha1($password.'migo'); // Hash for URL authentication (legacy)
