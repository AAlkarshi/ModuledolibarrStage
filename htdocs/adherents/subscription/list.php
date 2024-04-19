<?php
/* Copyright (C) 2001-2002 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2003      Jean-Louis Bergamo   <jlb@j1b.org>
 * Copyright (C) 2004-2023 Laurent Destailleur  <eldy@users.sourceforge.net>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *      \file       htdocs/adherents/subscription/list.php
 *      \ingroup    member
 *      \brief      list of subscription
 */

// Load Dolibarr environment
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent.class.php';
require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent_type.class.php';
require_once DOL_DOCUMENT_ROOT.'/adherents/class/subscription.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';

$langs->loadLangs(array("members", "companies", "banks"));

$action     = GETPOST('action', 'aZ09') ? GETPOST('action', 'aZ09') : 'view'; // The action 'create'/'add', 'edit'/'update', 'view', ...
$massaction = GETPOST('massaction', 'alpha'); // The bulk action (combo box choice into lists)
$show_files = GETPOST('show_files', 'int'); // Show files area generated by bulk actions ?
$confirm    = GETPOST('confirm', 'alpha'); // Result of a confirmation
$cancel     = GETPOST('cancel', 'alpha'); // We click on a Cancel button
$toselect   = GETPOST('toselect', 'array'); // Array of ids of elements selected into a list
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : str_replace('_', '', basename(dirname(__FILE__)).basename(__FILE__, '.php')); // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha'); // Go back to a dedicated page
$optioncss  = GETPOST('optioncss', 'aZ'); // Option for the css output (always '' except when 'print')
$mode       = GETPOST('mode', 'aZ'); // The output mode ('list', 'kanban', 'hierarchy', 'calendar', ...)

$statut = (GETPOSTISSET("statut") ? GETPOST("statut", "alpha") : 1);
$search_ref = GETPOST('search_ref', 'alpha');
$search_type = GETPOST('search_type', 'alpha');
$search_lastname = GETPOST('search_lastname', 'alpha');
$search_firstname = GETPOST('search_firstname', 'alpha');
$search_login = GETPOST('search_login', 'alpha');
$search_note = GETPOST('search_note', 'alpha');
$search_account = GETPOST('search_account', 'int');
$search_amount = GETPOST('search_amount', 'alpha');
$search_all = '';

$date_select = GETPOST("date_select", 'alpha');

// Load variable for pagination
$limit = GETPOST('limit', 'int') ? GETPOST('limit', 'int') : $conf->liste_limit;
$sortfield = GETPOST('sortfield', 'aZ09comma');
$sortorder = GETPOST('sortorder', 'aZ09comma');
$page = GETPOSTISSET('pageplusone') ? (GETPOST('pageplusone') - 1) : GETPOST("page", 'int');
if (empty($page) || $page < 0 || GETPOST('button_search', 'alpha') || GETPOST('button_removefilter', 'alpha')) {
	// If $page is not defined, or '' or -1 or if we click on clear filters
	$page = 0;
}
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (!$sortorder) {
	$sortorder = "DESC";
}
if (!$sortfield) {
	$sortfield = "c.dateadh";
}

// Initialize technical objects
$object = new Subscription($db);
$extrafields = new ExtraFields($db);
$hookmanager->initHooks(array('subscriptionlist'));

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// List of fields to search into when doing a "search in all"
$fieldstosearchall = array(
);
$arrayfields = array(
	'd.ref'=>array('label'=>"Ref", 'checked'=>1),
	'd.fk_type'=>array('label'=>"Type", 'checked'=>1),
	'd.lastname'=>array('label'=>"Lastname", 'checked'=>1),
	'd.firstname'=>array('label'=>"Firstname", 'checked'=>1),
	'd.login'=>array('label'=>"Login", 'checked'=>1),
	't.libelle'=>array('label'=>"Label", 'checked'=>1),
	'd.bank'=>array('label'=>"BankAccount", 'checked'=>1, 'enabled'=>(isModEnabled('banque'))),
	/*'d.note_public'=>array('label'=>"NotePublic", 'checked'=>0),
	 'd.note_private'=>array('label'=>"NotePrivate", 'checked'=>0),*/
	'c.dateadh'=>array('label'=>"DateSubscription", 'checked'=>1, 'position'=>100),
	'c.datef'=>array('label'=>"EndSubscription", 'checked'=>1, 'position'=>101),
	'd.amount'=>array('label'=>"Amount", 'checked'=>1, 'position'=>102),
	'c.datec'=>array('label'=>"DateCreation", 'checked'=>0, 'position'=>500),
	'c.tms'=>array('label'=>"DateModificationShort", 'checked'=>0, 'position'=>500),
//	'd.statut'=>array('label'=>"Status", 'checked'=>1, 'position'=>1000)
);

// Security check
$result = restrictedArea($user, 'adherent', '', '', 'cotisation');

$permissiontodelete = $user->hasRight('adherent', 'cotisation', 'creer');


/*
 * Actions
 */

if (GETPOST('cancel', 'alpha')) {
	$action = 'list';
	$massaction = '';
}
if (!GETPOST('confirmmassaction', 'alpha') && $massaction != 'presend' && $massaction != 'confirm_presend') {
	$massaction = '';
}

$parameters = array('socid'=>isset($socid) ? $socid : null);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {
	// Selection of new fields
	include DOL_DOCUMENT_ROOT.'/core/actions_changeselectedfields.inc.php';

	// Purge search criteria
	if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')) { // All tests are required to be compatible with all browsers
		$search_type = "";
		$search_ref = "";
		$search_lastname = "";
		$search_firstname = "";
		$search_login = "";
		$search_note = "";
		$search_amount = "";
		$search_account = "";
		$toselect = array();
		$search_array_options = array();
	}
	if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')
		|| GETPOST('button_search_x', 'alpha') || GETPOST('button_search.x', 'alpha') || GETPOST('button_search', 'alpha')) {
		$massaction = ''; // Protection to avoid mass action if we force a new search during a mass action confirmation
	}

	// Mass actions
	$objectclass = 'Subscription';
	$objectlabel = 'Subscription';
	$uploaddir = $conf->adherent->dir_output;
	include DOL_DOCUMENT_ROOT.'/core/actions_massactions.inc.php';
}


/*
 * View
 */

$form = new Form($db);
$subscription = new Subscription($db);
$adherent = new Adherent($db);
$accountstatic = new Account($db);

$now = dol_now();

// List of subscriptions
$sql = "SELECT d.rowid, d.login, d.firstname, d.lastname, d.societe, d.photo, d.statut as status,";
$sql .= " d.gender, d.email, d.morphy,";
$sql .= " c.rowid as crowid, c.fk_type, c.subscription,";
$sql .= " c.dateadh, c.datef, c.datec as date_creation, c.tms as date_update,";
$sql .= " c.fk_bank as bank, c.note as note_private,";
$sql .= " b.fk_account";
// Add fields from extrafields
if (!empty($extrafields->attributes[$object->table_element]['label'])) {
	foreach ($extrafields->attributes[$object->table_element]['label'] as $key => $val) {
		$sql .= ($extrafields->attributes[$object->table_element]['type'][$key] != 'separate' ? ", ef.".$key." as options_".$key : '');
	}
}
// Add fields from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListSelect', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
$sql .= $hookmanager->resPrint;
$sql = preg_replace('/,\s*$/', '', $sql);

$sqlfields = $sql; // $sql fields to remove for count total

$sql .= " FROM ".MAIN_DB_PREFIX."adherent as d";
$sql .= " JOIN ".MAIN_DB_PREFIX."subscription as c on d.rowid = c.fk_adherent";
$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."adherent_extrafields as ef on (d.rowid = ef.fk_object)";
$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."bank as b ON c.fk_bank = b.rowid";
$sql .= " WHERE d.entity IN (".getEntity('adherent').")";
if (isset($date_select) && $date_select != '') {
	$sql .= " AND c.dateadh >= '".((int) $date_select)."-01-01 00:00:00'";
	$sql .= " AND c.dateadh < '".((int) $date_select + 1)."-01-01 00:00:00'";
}
if ($search_ref) {
	if (is_numeric($search_ref)) {
		$sql .= " AND c.rowid = ".((int) $search_ref);
	} else {
		$sql .= " AND 1 = 2"; // Always wrong
	}
}
if ($search_type) {
	$sql .= natural_search(array('c.fk_type'), $search_type);
}
if ($search_lastname) {
	$sql .= natural_search(array('d.lastname', 'd.societe'), $search_lastname);
}
if ($search_firstname) {
	$sql .= natural_search(array('d.firstname'), $search_firstname);
}
if ($search_login) {
	$sql .= natural_search('d.login', $search_login);
}
if ($search_note) {
	$sql .= natural_search('c.note', $search_note);
}
if ($search_account > 0) {
	$sql .= " AND b.fk_account = ".((int) $search_account);
}
if ($search_amount) {
	$sql .= natural_search('c.subscription', $search_amount, 1);
}
if ($search_all) {
	$sql .= natural_search(array_keys($fieldstosearchall), $search_all);
}
// Add where from extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_sql.tpl.php';
// Add where from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListWhere', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
$sql .= $hookmanager->resPrint;

// Count total nb of records
$nbtotalofrecords = '';
if (!getDolGlobalInt('MAIN_DISABLE_FULL_SCANLIST')) {
	/* The fast and low memory method to get and count full list converts the sql into a sql count */
	$sqlforcount = preg_replace('/^'.preg_quote($sqlfields, '/').'/', 'SELECT COUNT(*) as nbtotalofrecords', $sql);
	$sqlforcount = preg_replace('/GROUP BY .*$/', '', $sqlforcount);
	$resql = $db->query($sqlforcount);
	if ($resql) {
		$objforcount = $db->fetch_object($resql);
		$nbtotalofrecords = $objforcount->nbtotalofrecords;
	} else {
		dol_print_error($db);
	}

	if (($page * $limit) > $nbtotalofrecords) {	// if total resultset is smaller than the paging size (filtering), goto and load page 0
		$page = 0;
		$offset = 0;
	}
	$db->free($resql);
}

// Complete request and execute it with limit
$sql .= $db->order($sortfield, $sortorder);
if ($limit) {
	$sql .= $db->plimit($limit + 1, $offset);
}

$resql = $db->query($sql);
if (!$resql) {
	dol_print_error($db);
	exit;
}

$num = $db->num_rows($resql);


// Direct jump if only one record found
if ($num == 1 && getDolGlobalString('MAIN_SEARCH_DIRECT_OPEN_IF_ONLY_ONE') && $search_all && !$page) {
	$obj = $db->fetch_object($resql);
	$id = $obj->rowid;
	header("Location: ".DOL_URL_ROOT.'/adherents/subscription/card.php?id='.$id);
	exit;
}

// Output page
// --------------------------------------------------------------------

$title = $langs->trans("Subscriptions");
if (!empty($date_select)) {
	$title .= ' ('.$langs->trans("Year").' '.$date_select.')';
}
$help_url = 'EN:Module_Foundations|FR:Module_Adh&eacute;rents|ES:M&oacute;dulo_Miembros|DE:Modul_Mitglieder';

llxHeader('', $title, $help_url);

$arrayofselected = is_array($toselect) ? $toselect : array();

$param = '';
if (!empty($mode)) {
	$param .='&mode='.urlencode($mode);
}
if (!empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) {
	$param .= '&contextpage='.urlencode($contextpage);
}
if ($limit > 0 && $limit != $conf->liste_limit) {
	$param .= '&limit='.((int) $limit);
}
if ($statut != '') {
	$param .= "&statut=".urlencode($statut);
}
if ($search_type) {
	$param .= "&search_type=".urlencode($search_type);
}
if ($date_select) {
	$param .= "&date_select=".urlencode($date_select);
}
if ($search_lastname) {
	$param .= "&search_lastname=".urlencode($search_lastname);
}
if ($search_login) {
	$param .= "&search_login=".urlencode($search_login);
}
if ($search_account) {
	$param .= "&search_account=".urlencode($search_account);
}
if ($search_amount) {
	$param .= "&search_amount=".urlencode($search_amount);
}
if ($optioncss != '') {
	$param .= '&optioncss='.urlencode($optioncss);
}
// Add $param from extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_param.tpl.php';
// Add $param from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListSearchParam', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
$param .= $hookmanager->resPrint;

// List of mass actions available
$arrayofmassactions = array(
	//'presend'=>img_picto('', 'email', 'class="pictofixedwidth"').$langs->trans("SendByMail"),
	//'builddoc'=>img_picto('', 'pdf', 'class="pictofixedwidth"').$langs->trans("PDFMerge"),
);
if (!empty($permissiontodelete)) {
	$arrayofmassactions['predelete'] = img_picto('', 'delete', 'class="pictofixedwidth"').$langs->trans("Delete");
}
if (GETPOST('nomassaction', 'int') || in_array($massaction, array('presend', 'predelete'))) {
	$arrayofmassactions = array();
}
$massactionbutton = $form->selectMassAction('', $arrayofmassactions);

print '<form method="POST" id="searchFormList" action="'.$_SERVER["PHP_SELF"].'">'."\n";
if ($optioncss != '') {
	print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
}
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
print '<input type="hidden" name="action" value="list">';
print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
print '<input type="hidden" name="page" value="'.$page.'">';
print '<input type="hidden" name="contextpage" value="'.$contextpage.'">';
print '<input type="hidden" name="date_select" value="'.$date_select.'">';
print '<input type="hidden" name="page_y" value="">';
print '<input type="hidden" name="mode" value="'.$mode.'">';

$newcardbutton = '';
$newcardbutton .= dolGetButtonTitle($langs->trans('ViewList'), '', 'fa fa-bars imgforviewmode', $_SERVER["PHP_SELF"].'?mode=common'.preg_replace('/(&|\?)*mode=[^&]+/', '', $param), '', ((empty($mode) || $mode == 'common') ? 2 : 1), array('morecss'=>'reposition'));
$newcardbutton .= dolGetButtonTitle($langs->trans('ViewKanban'), '', 'fa fa-th-list imgforviewmode', $_SERVER["PHP_SELF"].'?mode=kanban'.preg_replace('/(&|\?)*mode=[^&]+/', '', $param), '', ($mode == 'kanban' ? 2 : 1), array('morecss'=>'reposition'));
if ($user->hasRight('adherent', 'cotisation', 'creer')) {
	$newcardbutton .= dolGetButtonTitleSeparator();
	$newcardbutton .= dolGetButtonTitle($langs->trans('NewSubscription'), '', 'fa fa-plus-circle', DOL_URL_ROOT.'/adherents/list.php?status=-1,1');
}

print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, $massactionbutton, $num, $nbtotalofrecords, $subscription->picto, 0, $newcardbutton, '', $limit, 0, 0, 1);

$topicmail = "Information";
$modelmail = "subscription";
$objecttmp = new Subscription($db);
$trackid = 'sub'.$object->id;
include DOL_DOCUMENT_ROOT.'/core/tpl/massactions_pre.tpl.php';

if ($search_all) {
	$setupstring = '';
	foreach ($fieldstosearchall as $key => $val) {
		$fieldstosearchall[$key] = $langs->trans($val);
		$setupstring .= $key."=".$val.";";
	}
	print '<!-- Search done like if MYOBJECT_QUICKSEARCH_ON_FIELDS = '.$setupstring.' -->'."\n";
	print '<div class="divsearchfieldfilter">'.$langs->trans("FilterOnInto", $search_all).join(', ', $fieldstosearchall).'</div>'."\n";
}

$moreforfilter = '';
/*$moreforfilter.='<div class="divsearchfield">';
 $moreforfilter.= $langs->trans('MyFilter') . ': <input type="text" name="search_myfield" value="'.dol_escape_htmltag($search_myfield).'">';
 $moreforfilter.= '</div>';*/

$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldPreListTitle', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
if (empty($reshook)) {
	$moreforfilter .= $hookmanager->resPrint;
} else {
	$moreforfilter = $hookmanager->resPrint;
}

if (!empty($moreforfilter)) {
	print '<div class="liste_titre liste_titre_bydiv centpercent">';
	print $moreforfilter;
	$parameters = array();
	$reshook = $hookmanager->executeHooks('printFieldPreListTitle', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	print $hookmanager->resPrint;
	print '</div>';
}

$varpage = empty($contextpage) ? $_SERVER["PHP_SELF"] : $contextpage;
$selectedfields = ($mode != 'kanban' ? $form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage, getDolGlobalString('MAIN_CHECKBOX_LEFT_COLUMN', '')) : ''); // This also change content of $arrayfields
$selectedfields .= (count($arrayofmassactions) ? $form->showCheckAddButtons('checkforselect', 1) : '');

print '<div class="div-table-responsive">'; // You can use div-table-responsive-no-min if you dont need reserved height for your table
print '<table class="tagtable nobottomiftotal liste'.($moreforfilter ? " listwithfilterbefore" : "").'">'."\n";

// Fields title search
// --------------------------------------------------------------------
print '<tr class="liste_titre_filter">';
// Action column
if (getDolGlobalString('MAIN_CHECKBOX_LEFT_COLUMN')) {
	print '<td class="liste_titre center maxwidthsearch">';
	$searchpicto = $form->showFilterButtons('left');
	print $searchpicto;
	print '</td>';
}
// Line numbering
if (getDolGlobalString('MAIN_SHOW_TECHNICAL_ID')) {
	print '<td class="liste_titre">&nbsp;</td>';
}

// Ref
if (!empty($arrayfields['d.ref']['checked'])) {
	print '<td class="liste_titre left">';
	print '<input class="flat maxwidth50" type="text" name="search_ref" value="'.dol_escape_htmltag($search_ref).'"></td>';
}

// Type
if (!empty($arrayfields['d.fk_type']['checked'])) {
	print '<td class="liste_titre left">';
	print '<input class="flat maxwidth50" type="text" name="search_type" value="'.dol_escape_htmltag($search_type).'">';
	print'</td>';
}

if (!empty($arrayfields['d.lastname']['checked'])) {
	print '<td class="liste_titre left">';
	print '<input class="flat maxwidth75" type="text" name="search_lastname" value="'.dol_escape_htmltag($search_lastname).'"></td>';
}

if (!empty($arrayfields['d.firstname']['checked'])) {
	print '<td class="liste_titre left">';
	print '<input class="flat maxwidth75" type="text" name="search_firstname" value="'.dol_escape_htmltag($search_firstname).'"></td>';
}

if (!empty($arrayfields['d.login']['checked'])) {
	print '<td class="liste_titre left">';
	print '<input class="flat maxwidth75" type="text" name="search_login" value="'.dol_escape_htmltag($search_login).'"></td>';
}

if (!empty($arrayfields['t.libelle']['checked'])) {
	print '<td class="liste_titre">';
	print '';
	print '</td>';
}

if (!empty($arrayfields['d.bank']['checked'])) {
	print '<td class="liste_titre">';
	$form->select_comptes($search_account, 'search_account', 0, '', 1, '', 0, 'maxwidth100');
	print '</td>';
}

if (!empty($arrayfields['c.dateadh']['checked'])) {
	print '<td class="liste_titre">&nbsp;</td>';
}

if (!empty($arrayfields['c.datef']['checked'])) {
	print '<td class="liste_titre">&nbsp;</td>';
}

if (!empty($arrayfields['d.amount']['checked'])) {
	print '<td class="liste_titre right">';
	print '<input class="flat" type="text" name="search_amount" value="'.dol_escape_htmltag($search_amount).'" size="4">';
	print '</td>';
}
// Extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_input.tpl.php';

// Fields from hook
$parameters = array('arrayfields'=>$arrayfields);
$reshook = $hookmanager->executeHooks('printFieldListOption', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;
// Date creation
if (!empty($arrayfields['c.datec']['checked'])) {
	print '<td class="liste_titre">';
	print '</td>';
}
// Date modification
if (!empty($arrayfields['c.tms']['checked'])) {
	print '<td class="liste_titre">';
	print '</td>';
}

// Action column
if (!getDolGlobalString('MAIN_CHECKBOX_LEFT_COLUMN')) {
	print '<td class="liste_titre center maxwidthsearch">';
	$searchpicto = $form->showFilterButtons();
	print $searchpicto;
	print '</td>';
}
print '</tr>'."\n";

$totalarray = array();
$totalarray['nbfield'] = 0;

// Fields title label
// --------------------------------------------------------------------
print '<tr class="liste_titre">';
// Action column
if (getDolGlobalString('MAIN_CHECKBOX_LEFT_COLUMN')) {
	print_liste_field_titre($selectedfields, $_SERVER["PHP_SELF"], '', '', '', 'align="center"', $sortfield, $sortorder, 'maxwidthsearch ');
	$totalarray['nbfield']++;
}
if (!empty($arrayfields['d.ref']['checked'])) {
	print_liste_field_titre($arrayfields['d.ref']['label'], $_SERVER["PHP_SELF"], "c.rowid", $param, "", "", $sortfield, $sortorder);
	$totalarray['nbfield']++;
}
if (!empty($arrayfields['d.fk_type']['checked'])) {
	print_liste_field_titre($arrayfields['d.fk_type']['label'], $_SERVER["PHP_SELF"], "c.fk_type", $param, "", "", $sortfield, $sortorder);
	$totalarray['nbfield']++;
}
if (!empty($arrayfields['d.lastname']['checked'])) {
	print_liste_field_titre($arrayfields['d.lastname']['label'], $_SERVER["PHP_SELF"], "d.lastname", $param, "", "", $sortfield, $sortorder);
	$totalarray['nbfield']++;
}
if (!empty($arrayfields['d.firstname']['checked'])) {
	print_liste_field_titre($arrayfields['d.firstname']['label'], $_SERVER["PHP_SELF"], "d.firstname", $param, "", "", $sortfield, $sortorder);
	$totalarray['nbfield']++;
}
if (!empty($arrayfields['d.login']['checked'])) {
	print_liste_field_titre($arrayfields['d.login']['label'], $_SERVER["PHP_SELF"], "d.login", $param, "", "", $sortfield, $sortorder);
	$totalarray['nbfield']++;
}
if (!empty($arrayfields['t.libelle']['checked'])) {
	print_liste_field_titre($arrayfields['t.libelle']['label'], $_SERVER["PHP_SELF"], "c.note", $param, "", '', $sortfield, $sortorder);
	$totalarray['nbfield']++;
}
if (!empty($arrayfields['d.bank']['checked'])) {
	print_liste_field_titre($arrayfields['d.bank']['label'], $_SERVER["PHP_SELF"], "b.fk_account", $param, "", "", $sortfield, $sortorder);
	$totalarray['nbfield']++;
}
if (!empty($arrayfields['c.dateadh']['checked'])) {
	print_liste_field_titre($arrayfields['c.dateadh']['label'], $_SERVER["PHP_SELF"], "c.dateadh", $param, "", '', $sortfield, $sortorder, 'center nowraponall ');
	$totalarray['nbfield']++;
}
if (!empty($arrayfields['c.datef']['checked'])) {
	print_liste_field_titre($arrayfields['c.datef']['label'], $_SERVER["PHP_SELF"], "c.datef", $param, "", '', $sortfield, $sortorder, 'center nowraponall ');
	$totalarray['nbfield']++;
}
if (!empty($arrayfields['d.amount']['checked'])) {
	print_liste_field_titre($arrayfields['d.amount']['label'], $_SERVER["PHP_SELF"], "c.subscription", $param, "", '', $sortfield, $sortorder, 'right ');
	$totalarray['nbfield']++;
}

// Extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_title.tpl.php';

// Hook fields
$parameters = array('arrayfields'=>$arrayfields, 'param'=>$param, 'sortfield'=>$sortfield, 'sortorder'=>$sortorder, 'totalarray'=>&$totalarray);
$reshook = $hookmanager->executeHooks('printFieldListTitle', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;
if (!empty($arrayfields['c.datec']['checked'])) {
	print_liste_field_titre($arrayfields['c.datec']['label'], $_SERVER["PHP_SELF"], "c.datec", "", $param, 'align="center" class="nowrap"', $sortfield, $sortorder);
}
if (!empty($arrayfields['c.tms']['checked'])) {
	print_liste_field_titre($arrayfields['c.tms']['label'], $_SERVER["PHP_SELF"], "c.tms", "", $param, 'align="center" class="nowrap"', $sortfield, $sortorder);
}
// Action column
if (!getDolGlobalString('MAIN_CHECKBOX_LEFT_COLUMN')) {
	print getTitleFieldOfList($selectedfields, 0, $_SERVER["PHP_SELF"], '', '', '', '', $sortfield, $sortorder, 'center maxwidthsearch ')."\n";
	$totalarray['nbfield']++;
}
print '</tr>'."\n";

// Loop on record
// --------------------------------------------------------------------
$i = 0;
$savnbfield = $totalarray['nbfield'];
$totalarray = array();
$totalarray['nbfield'] = 0;
$imaxinloop = ($limit ? min($num, $limit) : $num);
while ($i < $imaxinloop) {
	$obj = $db->fetch_object($resql);
	if (empty($obj)) {
		break; // Should not happen
	}

	$subscription->ref = $obj->crowid;
	$subscription->id = $obj->crowid;
	$subscription->dateh = $db->jdate($obj->dateadh);
	$subscription->datef = $db->jdate($obj->datef);
	$subscription->amount = $obj->subscription;
	$subscription->fk_adherent = $obj->rowid;

	$adherent->lastname = $obj->lastname;
	$adherent->firstname = $obj->firstname;
	$adherent->ref = $obj->rowid;
	$adherent->id = $obj->rowid;
	$adherent->statut = $obj->status;
	$adherent->status = $obj->status;
	$adherent->login = $obj->login;
	$adherent->photo = $obj->photo;
	$adherent->gender = $obj->gender;
	$adherent->morphy = $obj->morphy;
	$adherent->email = $obj->email;
	$adherent->typeid = $obj->fk_type;
	$adherent->datefin = $db->jdate($obj->datef);

	$typeid = ($obj->fk_type > 0 ? $obj->fk_type : $adherent->typeid);
	$adht = new AdherentType($db);
	$adht->fetch($typeid);

	$adherent->need_subscription = $adht->subscription;

	if ($mode == 'kanban') {
		if ($i == 0) {
			print '<tr class="trkanban"><td colspan="'.$savnbfield.'">';
			print '<div class="box-flex-container kanban">';
		}
		// Output Kanban
		if ($massactionbutton || $massaction) { // If we are in select mode (massactionbutton defined) or if we have already selected and sent an action ($massaction) defined
			$selected = 0;
			if (in_array($object->id, $arrayofselected)) {
				$selected = 1;
			}
		}

		//fetch informations needs on this mode

		if ($obj->fk_account > 0) {
			$accountstatic->id = $obj->fk_account;
			$accountstatic->fetch($obj->fk_account);
		}
		// Output Kanban
		print $subscription->getKanbanView('', array('selected' => in_array($object->id, $arrayofselected), 'adherent_type' => $adht, 'member' => $adherent, 'bank'=>($obj->fk_account > 0 ? $accountstatic : null)));
		if ($i == ($imaxinloop - 1)) {
			print '</div>';
			print '</td></tr>';
		}
	} else {
		// Show here line of result
		$j = 0;
		print '<tr data-rowid="'.$object->id.'" class="oddeven">';
		// Action column
		if (getDolGlobalString('MAIN_CHECKBOX_LEFT_COLUMN')) {
			print '<td class="nowrap center">';
			if ($massactionbutton || $massaction) {   // If we are in select mode (massactionbutton defined) or if we have already selected and sent an action ($massaction) defined
				$selected = 0;
				if (in_array($obj->crowid, $arrayofselected)) {
					$selected = 1;
				}
				print '<input id="cb'.$obj->crowid.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$obj->crowid.'"'.($selected ? ' checked="checked"' : '').'>';
			}
			print '</td>';
			if (!$i) {
				$totalarray['nbfield']++;
			}
		}
		// Ref
		if (!empty($arrayfields['d.ref']['checked'])) {
			print '<td class="nowraponall">'.$subscription->getNomUrl(1).'</td>';
			if (!$i) {
				$totalarray['nbfield']++;
			}
		}
		// Type
		if (!empty($arrayfields['d.fk_type']['checked'])) {
			print '<td class="tdoverflowmax100">';
			if ($typeid > 0) {
				print $adht->getNomUrl(1);
			}
			print '</td>';
			if (!$i) {
				$totalarray['nbfield']++;
			}
		}

		// Lastname
		if (!empty($arrayfields['d.lastname']['checked'])) {
			print '<td class="tdoverflowmax125">'.$adherent->getNomUrl(-1, 0, 'card', 'lastname').'</td>';
			if (!$i) {
				$totalarray['nbfield']++;
			}
		}
		// Firstname
		if (!empty($arrayfields['d.firstname']['checked'])) {
			print '<td class="tdoverflowmax125" title="'.dol_escape_htmltag($adherent->firstname).'">'.dol_escape_htmltag($adherent->firstname).'</td>';
			if (!$i) {
				$totalarray['nbfield']++;
			}
		}

		// Login
		if (!empty($arrayfields['d.login']['checked'])) {
			print '<td class="tdoverflowmax150" title="'.dol_escape_htmltag($adherent->login).'">'.dol_escape_htmltag($adherent->login).'</td>';
			if (!$i) {
				$totalarray['nbfield']++;
			}
		}

		// Label
		if (!empty($arrayfields['t.libelle']['checked'])) {
			print '<td class="tdoverflowmax400" title="'.dol_escape_htmltag($obj->note_private).'">';
			print dol_escape_htmltag(dolGetFirstLineOfText($obj->note_private));
			print '</td>';
			if (!$i) {
				$totalarray['nbfield']++;
			}
		}

		// Banque
		if (!empty($arrayfields['d.bank']['checked'])) {
			print '<td class="tdmaxoverflow100">';
			if ($obj->fk_account > 0) {
				$accountstatic->id = $obj->fk_account;
				$accountstatic->fetch($obj->fk_account);
				//$accountstatic->label=$obj->label;
				print $accountstatic->getNomUrl(1);
			}
			print "</td>\n";
			if (!$i) {
				$totalarray['nbfield']++;
			}
		}

		// Date start
		if (!empty($arrayfields['c.dateadh']['checked'])) {
			print '<td class="center nowraponall">'.dol_print_date($db->jdate($obj->dateadh), 'day')."</td>\n";
			if (!$i) {
				$totalarray['nbfield']++;
			}
		}
		// Date end
		if (!empty($arrayfields['c.datef']['checked'])) {
			print '<td class="center nowraponall">'.dol_print_date($db->jdate($obj->datef), 'day')."</td>\n";
			if (!$i) {
				$totalarray['nbfield']++;
			}
		}
		// Price
		if (!empty($arrayfields['d.amount']['checked'])) {
			print '<td class="right amount">'.price($obj->subscription).'</td>';
			if (!$i) {
				$totalarray['nbfield']++;
			}
			if (!$i) {
				$totalarray['pos'][$totalarray['nbfield']] = 'd.amount';
			}
			if (empty($totalarray['val']['d.amount'])) {
				$totalarray['val']['d.amount'] = $obj->subscription;
			} else {
				$totalarray['val']['d.amount'] += $obj->subscription;
			}
		}
		// Extra fields
		include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_print_fields.tpl.php';
		// Fields from hook
		$parameters = array('arrayfields'=>$arrayfields, 'obj'=>$obj, 'i'=>$i, 'totalarray'=>&$totalarray);
		$reshook = $hookmanager->executeHooks('printFieldListValue', $parameters); // Note that $action and $object may have been modified by hook
		print $hookmanager->resPrint;
		// Date creation
		if (!empty($arrayfields['c.datec']['checked'])) {
			print '<td class="nowrap center">';
			print dol_print_date($db->jdate($obj->date_creation), 'dayhour', 'tzuser');
			print '</td>';
			if (!$i) {
				$totalarray['nbfield']++;
			}
		}
		// Date modification
		if (!empty($arrayfields['c.tms']['checked'])) {
			print '<td class="nowrap center">';
			print dol_print_date($db->jdate($obj->date_update), 'dayhour', 'tzuser');
			print '</td>';
			if (!$i) {
				$totalarray['nbfield']++;
			}
		}
		// Action column
		if (!getDolGlobalString('MAIN_CHECKBOX_LEFT_COLUMN')) {
			print '<td class="nowrap center">';
			if ($massactionbutton || $massaction) {   // If we are in select mode (massactionbutton defined) or if we have already selected and sent an action ($massaction) defined
				$selected = 0;
				if (in_array($obj->crowid, $arrayofselected)) {
					$selected = 1;
				}
				print '<input id="cb'.$obj->crowid.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$obj->crowid.'"'.($selected ? ' checked="checked"' : '').'>';
			}
			print '</td>';
			if (!$i) {
				$totalarray['nbfield']++;
			}
		}

		print '</tr>'."\n";
	}
	$i++;
}

// Show total line
include DOL_DOCUMENT_ROOT.'/core/tpl/list_print_total.tpl.php';


// If no record found
if ($num == 0) {
	$colspan = 1;
	foreach ($arrayfields as $key => $val) {
		if (!empty($val['checked'])) {
			$colspan++;
		}
	}
	print '<tr><td colspan="'.$colspan.'"><span class="opacitymedium">'.$langs->trans("NoRecordFound").'</span></td></tr>';
}

$db->free($resql);

$parameters = array('arrayfields'=>$arrayfields, 'sql' => $sql);
$reshook = $hookmanager->executeHooks('printFieldListFooter', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;

print '</table>'."\n";
print '</div>'."\n";

print '</form>'."\n";

// End of page
llxFooter();
$db->close();
