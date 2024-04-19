<?php
/* Copyright (C) 2023      Julien Canipel <jcanipel@alternative-erp.com>all
*/

/**
 * \file    htdocs/altemailing/class/actions_altemailing.class.php
 * \ingroup altemailing
 * \brief   hook overload.
 *
 */

/**
 * Class ActionsAltemailing
 */
class ActionsAltemailing
{
	/**
	 * @var DoliDB Database handler.
	 */
	public $db;

	/**
	 * @var string Error code (or message)
	 */
	public $error = '';

	/**
	 * @var array Errors
	 */
	public $errors = array();


	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	public $pass = 0;

	/**
	 * Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}
	
	public function selectForFormsListWhere(&$parameters, &$object, &$action, $hookmanager){
		global $object, $id;
		if(!is_object($object)){
			$object = new stdClass();
		}
		dol_syslog(get_class($this)."::selectForFormsListWhere CompleteTabsHead for".get_class($object));
		
		if(get_class($object) == "CampagneClics" && $this->pass == 0){
			$this->resprints = " WHERE EXISTS (select 1 from llx_v_campagne_thirdparties v where t.rowid = v.fk_soc and v.fk_campagne = ".$id.")";
			$this->pass++;
		}else if(get_class($object) == "CampagneClics" && $this->pass == 1){
			$this->resprints = " WHERE t.fk_campagne = ".$id;
			$this->pass++;
		}
		return 0;
	}
}
