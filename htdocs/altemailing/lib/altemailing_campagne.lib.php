<?php
/* Copyright (C) 2023 Julien Canipel  <jcanipel@alternative-erp.com>
 */
/**
 * \file    htdocs/altemailing/lib/altemailing_campagne.lib.php
 * \ingroup altemailing
 * \brief   Library files with common functions for campagne
 */

/**
 *  Define head array for tabs of infotelecom tools setup pages
 *
 *  @return			Array of head
 */
function altemailing_prepare_head()
{
	global $langs, $conf;

	$h = 0;
	$head = array();

	$head[$h][0] = DOL_URL_ROOT."/altemailing/admin/admin.php";
	$head[$h][1] = $langs->trans("Admin");
	$head[$h][2] = 'admin';
	$h++;
	
	$object = new stdClass();

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'infotelecomadmin');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'infotelecomadmin', 'remove');

	return $head;
}


/**
 * Prepare array of tabs for Campagne
 *
 * @param	Campagne	$object		Campagne
 * @return 	array					Array of tabs
 */
function campagnePrepareHead($object)
{
	global $db, $langs, $conf;

	$langs->load("altemailing@altemailing");

	$showtabofpagecontact = 1;
	$showtabofpagenote = 1;
	$showtabofpagedocument = 1;
	$showtabofpageagenda = 1;

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/altemailing/card.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Campagne");
	$head[$h][2] = 'card';
	$h++;

	$head[$h][0] = dol_buildpath("/altemailing/tielist.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Diffusion");
	$head[$h][2] = 'thirdparties';
	$h++;

	$head[$h][0] = dol_buildpath("/altemailing/cliclist.php", 1).'?id='.$object->id.'&mode=open';
	$head[$h][1] = $langs->trans("Reads");
	$head[$h][2] = 'read';
	$h++;

	$head[$h][0] = dol_buildpath("/altemailing/cliclist.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Clics");
	$head[$h][2] = 'clic';
	$h++;
	
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'altemailing@altemailing');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'altemailing@altemailing', 'remove');

	return $head;
}
