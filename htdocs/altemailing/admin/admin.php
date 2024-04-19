<?php
/* Copyright (C) 2023 Julien Canipel  <jcanipel@alternative-erp.com>
 */

/**
 *   	\file       htdocs/altemailing/admin/admin.php
 *		\ingroup    altemailing
 *		\brief      Admin page
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--; $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/altemailing/lib/altemailing_campagne.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
$form = new Form($db);
// Load translation files required by the page
$langs->loadLangs(array('admin', 'altemailing@altemailing', 'other'));

if (!$user->admin) {
	accessforbidden();
}
// Test module categorie
if (empty($conf->categorie->enabled)) {
	$langs->load("errors");
	setEventMessages($langs->trans("ErrorModuleCategorieInac"), null, 'errors');
}
$action = GETPOST('action', 'aZ09');

if ($action == 'setvalue' && $user->admin) {
	$db->begin();
	$result = dolibarr_set_const($db, "ALTEMAILING_SEND_ALL_TO", GETPOST('ALTEMAILING_SEND_ALL_TO', 'alpha'), 'chaine', 0, '', $conf->entity);
	if (!$result > 0) {
		$error++;
	}
	$result = dolibarr_set_const($db, "ALTEMAILING_SEND_LOG_TO", GETPOST('ALTEMAILING_SEND_LOG_TO', 'alpha'), 'chaine', 0, '', $conf->entity);
	if (!$result > 0) {
		$error++;
	}
	$var = GETPOST('ALTEMAILING_URL_PUBLIC', 'alpha');
	if(substr(GETPOST('ALTEMAILING_URL_PUBLIC', 'alpha'),-1) != '/'){
		$var = GETPOST('ALTEMAILING_URL_PUBLIC', 'alpha').'/';
	}
	$result = dolibarr_set_const($db, "ALTEMAILING_URL_PUBLIC", $var, 'chaine', 0, '', $conf->entity);
	if (!$result > 0) {
		$error++;
	}
	if (!$error) {
		$db->commit();
		setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
	} else {
		$db->rollback();
		dol_print_error($db);
	}
}


/*
 *	View
 */
 
 
$form = new Form($db);

llxHeader('', $langs->trans("altemailingsetup"));


$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1">'.$langs->trans("BackToModuleList").'</a>';
print load_fiche_titre($langs->trans("AdminModul"), $linkback);

$head = altemailing_prepare_head();


print dol_get_fiche_head($head, 'AdminModul', '', -1);
print '<form method="post" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="setvalue">';
print '<span class="opacitymedium">'.$langs->trans("altemailingsetupDesc")."</span><br>\n";
print '<br>';

print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';
// Header
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("general").'</td>';
print '<td>'.$langs->trans("Value").'</td>';
print "</tr>";

print '<tr class="oddeven"><td class="fieldrequired">';
print $form->textwithpicto($langs->trans("ALTEMAILING_SEND_ALL_TO"), $langs->trans('UseToTestCampagneSansEnvoyerAuCliuent'), 1).' ';
print '</td><td>';
print '<input class="flat" name="ALTEMAILING_SEND_ALL_TO" size="32" value="'.$conf->global->ALTEMAILING_SEND_ALL_TO.'">';
print '</td></tr>';

print '<tr class="oddeven"><td class="fieldrequired">';
print $form->textwithpicto($langs->trans("ALTEMAILING_SEND_LOG_TO"), $langs->trans('UseToSendEndLog'), 1).' ';
print '</td><td>';
print '<input class="flat" name="ALTEMAILING_SEND_LOG_TO" size="32" value="'.$conf->global->ALTEMAILING_SEND_LOG_TO.'">';
print '</td></tr>';

print '<tr class="oddeven"><td class="fieldrequired">';
print $form->textwithpicto($langs->trans("ALTEMAILING_URL_PUBLIC"), $langs->trans('UseToSendEndLog'), 1).' ';
print '</td><td>';
print '<input class="flat" name="ALTEMAILING_URL_PUBLIC" id="ALTEMAILING_URL_PUBLIC" size="120" value="'.($conf->global->ALTEMAILING_URL_PUBLIC ? $conf->global->ALTEMAILING_URL_PUBLIC : $dolibarr_main_url_root.'/altemailing/public/').'">';
print '&nbsp;'.img_picto($langs->trans('Default'), 'refresh', 'id="reset_url_public" class="linkobject"');
print '</td></tr>';
print '<script>document.getElementById("reset_url_public").addEventListener("click", function(){ document.getElementById("ALTEMAILING_URL_PUBLIC").value = "'.$dolibarr_main_url_root.'/altemailing/public/";});</script>';
print '</table>';
print '</div>';
print $form->buttonsSaveCancel("Modify", '');
print '</form>';
print '<br>';


print dol_get_fiche_end();

print '</form>';
// End of page
llxFooter();
$db->close();
