<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2017-09-19 14:12:46 --> Severity: Warning --> mysqli::query(): MySQL server has gone away F:\xampp\htdocs\inclusify\system\database\drivers\mysqli\mysqli_driver.php 305
ERROR - 2017-09-19 14:12:46 --> Severity: Warning --> mysqli::query(): Error reading result set's header F:\xampp\htdocs\inclusify\system\database\drivers\mysqli\mysqli_driver.php 305
ERROR - 2017-09-19 14:12:46 --> Query error: MySQL server has gone away - Invalid query: SELECT GROUP_CONCAT(TypeEntityID) as TypeEntityID
FROM `Follow`
WHERE `Type` = 'user'
AND `StatusID` = '2'
AND `UserID` = '1189'
ORDER BY `TypeEntityID` ASC
ERROR - 2017-09-19 14:13:07 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at F:\xampp\htdocs\inclusify\system\libraries\Session\drivers\Session_files_driver.php:178) F:\xampp\htdocs\inclusify\system\core\Common.php 570
ERROR - 2017-09-19 14:13:07 --> Severity: Error --> Maximum execution time of 30 seconds exceeded F:\xampp\htdocs\inclusify\system\libraries\Session\drivers\Session_files_driver.php 178
