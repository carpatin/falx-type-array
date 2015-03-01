<?php

// Define a constant to hold the base path of the project
define('BASE_PATH', dirname(__DIR__));
define('DS', DIRECTORY_SEPARATOR);
// Update include paths list to add paths for this project's libs
$includePaths = explode(PATH_SEPARATOR, get_include_path());
$includePaths = array_merge($includePaths, array(
    BASE_PATH . DS . 'library'
        ));
set_include_path(implode(PATH_SEPARATOR, $includePaths));
// Include the file that contains the autoloader class and the register the autoloader
require BASE_PATH . DS . 'library' . DS . 'Autoloader.php';
Autoloader::register();
