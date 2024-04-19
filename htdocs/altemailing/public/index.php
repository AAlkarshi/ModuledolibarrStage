<?php
/* Copyright (C) 2023 Julien Canipel  <jcanipel@alternative-erp.com>
 */

/**
 *   	\file       htdocs/altemailing/public/index.php
 *		\ingroup    altemailing
 *		\brief      Page to manage link in e-mail
 */
if (!defined('NOREQUIREMENU')) {
	define('NOREQUIREMENU', '1');
}
// If there is no need to load and show top and left menu
if (!defined("NOLOGIN")) {
	define("NOLOGIN", '1');
}
if (!defined('NOIPCHECK')) {
	define('NOIPCHECK', '1'); // Do not check IP defined into conf $dolibarr_main_restrict_ip
}
if (!defined('NOBROWSERNOTIF')) {
	define('NOBROWSERNOTIF', '1');
}
// If this page is public (can be called outside logged session)

// For MultiCompany module.
// Do not use GETPOST here, function is not defined and define must be done before including main.inc.php
$entity = (!empty($_GET['entity']) ? (int) $_GET['entity'] : (!empty($_POST['entity']) ? (int) $_POST['entity'] : 1));
if (is_numeric($entity)) {
	define("DOLENTITY", $entity);
}

require '../../main.inc.php';
dol_include_once('/altemailing/class/campagne.class.php');

$action = GETPOST('action', 'aZ09');
$code = GETPOST('code', 'aZ09');
$id = GETPOST('id', 'aZ09');

if($action == 'unsubscribe'){
	$object = new Campagne($db);
	print $object->unsubscribe($id);
}

if($action == 'link'){
	$object = new Campagne($db);
	print $object->clickLink($code, $id);

}

if($action == 'pixel'){
	$object = new Campagne($db);
	print $object->pixel($id);

}

?>