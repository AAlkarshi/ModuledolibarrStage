<?php
/* Copyright (C) 2023  Julien Canipel <jcanipel@alternative-erp.com>
 */

/**
 * \file        htdocs/altemailing/class/campagne.class.php
 * \ingroup     altemailing
 * \brief       This file is a CRUD class file for altemailing (Create/Read/Update/Delete)
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

/**
 * Class for Campagne
 */
class Campagne extends CommonObject
{
	/**
	 * @var string ID of module.
	 */
	public $module = 'altemailing';

	/**
	 * @var string ID to identify managed object.
	 */
	public $element = 'campagne';

	/**
	 * @var string Name of table without prefix where object is stored. This is also the key used for extrafields management.
	 */
	public $table_element = 'campagne';
	
	public $table_element_line = 'campagne_liens';

	/**
	 * @var int  Does this object support multicompany module ?
	 * 0=No test on entity, 1=Test with field entity, 'field@table'=Test with link by field@table
	 */
	public $ismultientitymanaged = 1;

	/**
	 * @var int  Does object support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;

	/**
	 * @var string String with name of icon for myobject. Must be the part after the 'object_' into object_myobject.png
	 */
	//public $picto = 'myobject@mymodule';


	const STATUS_DRAFT = 0;
	const STATUS_VALIDATED = 1;
	const STATUS_CLOSED = 2;

	/**
	 * @var int ID
	 */
	public $rowid;

	/**
	 * @var string Ref
	 */
	public $title;

	/**
	 * @var int Entity
	 */
	public $entity;

	/**
	 * @var string label
	 */
	public $label;

	/**
	 * @var int Status
	 */
	public $status;

	/**
	 * @var integer|string date_creation
	 */
	public $datec;

	/**
	 * @var integer tms
	 */
	public $tms;

	/**
	 * @var int ID
	 */
	public $fk_user_author;

	/**
	 * @var int ID
	 */
	public $fk_user_modif;
	public $fk_email_template;

	/**
	 * @var integer|string public $date_debut
	 */
	public $datdeb;

	/**
	 * @var integer|string public $date_fin
	 */
	public $datfin;
	
	public $picto = 'fa-at';
	
	
	public $fields = array();
	
	public $thirdparties = array();


	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $langs;
		$langs->loadLangs(array("altemailing@altemailing"));
		$this->fields = array(
			'rowid'         => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>1, 'visible'=>-2, 'noteditable'=>1, 'notnull'=> 1, 'index'=>1, 'position'=>1, 'comment'=>'Id', 'css'=>'left'),
			'entity'        => array('type'=>'integer', 'label'=>'Entity', 'enabled'=>1, 'visible'=>0, 'notnull'=> 1, 'noteditable'=>1, 'default'=>1, 'index'=>1, 'position'=>2),
			'fk_email_template'        => array('type'=>'integer', 'label'=>'EmailTemplate', 'enabled'=>1, 'visible'=>0, 'notnull'=> 1, 'noteditable'=>0, 'default'=>0, 'index'=>1, 'position'=>999, 'validate'=>1),
			'title'           => array('type'=>'varchar(100)', 'label'=>'Title', 'enabled'=>1, 'visible'=>1, 'noteditable'=>0, 'default'=>'', 'notnull'=> 1, 'showoncombobox'=>1, 'index'=>1, 'position'=>20, 'searchall'=>1, 'comment'=>'Reference of object', 'validate'=>1),
			'label'         => array('type'=>'varchar(255)', 'label'=>'Label', 'enabled'=>1, 'visible'=>1, 'position'=>30, 'searchall'=>1, 'css'=>'minwidth300', 'cssview'=>'wordbreak', 'showoncombobox'=>2, 'validate'=>1),
			'datdeb' => array('type'=>'datetime', 'label'=>'DateDebut', 'enabled'=>1, 'visible'=>1, 'notnull'=> 1, 'position'=>500),
			'datfin' => array('type'=>'datetime', 'label'=>'DateFin', 'enabled'=>1, 'visible'=>1, 'notnull'=> 1, 'position'=>500),
			'datec' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>1, 'visible'=>-2, 'notnull'=> 1, 'position'=>500),
			'datval' => array('type'=>'datetime', 'label'=>'DateValidation', 'enabled'=>1, 'visible'=>-2, 'notnull'=> 1, 'position'=>500),
			'tms'           => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>1, 'visible'=>-2, 'notnull'=> 0, 'position'=>501),
			'fk_user_author' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserAuthor', 'picto'=>'user', 'enabled'=>1, 'visible'=>-2, 'notnull'=> 1, 'position'=>510, 'foreignkey'=>'user.rowid'),
			'fk_user_modif' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserModif', 'picto'=>'user', 'enabled'=>1, 'visible'=>-2, 'notnull'=>-1, 'position'=>511),
			'fk_user_valid' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserModif', 'picto'=>'user', 'enabled'=>1, 'visible'=>-2, 'notnull'=>-1, 'position'=>511),
			'status'        => array('type'=>'integer', 'label'=>'Status', 'enabled'=>1, 'visible'=>0, 'notnull'=> 1, 'default'=>0, 'index'=>1, 'position'=>2000, 'arrayofkeyval'=>array(0=>$langs->trans('Draft'), 1=>$langs->trans('Validated'), 2=>$langs->trans('Closed')), 'validate'=>1),
		);
		$this->db = $db;
	}

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		global $conf;
		dol_syslog(get_class($this)."::create", LOG_DEBUG);
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."campagne(entity, title, label, datdeb, datfin, fk_user_author, fk_email_template) VALUES (";
		$sql.= $conf->entity;
		$sql.= ", '".$this->title."'";
		$sql.= ", '".$this->label."'";
		$sql.= ", ".(dol_strlen($this->datdeb) != 0 ? "'".$this->db->idate($this->datdeb)."'" : 'null');
		$sql.= ", ".(dol_strlen($this->datfin) != 0 ? "'".$this->db->idate($this->datfin)."'" : 'null');
		$sql.= ", ".$user->id;
		$sql.= ", ".$this->fk_email_template.")";
		$this->db->begin();
		$res = $this->db->query($sql);
		if (!$res) {
			$error++;
			if ($this->db->lasterrno() == 'DB_ERROR_RECORD_ALREADY_EXISTS') {
				$this->errors[] = "ErrorRefAlreadyExists";
			} else {
				$this->errors[] = $this->db->lasterror();
			}
		}
		if (!$error) {
			$this->id = $this->db->last_insert_id($this->db->prefix().$this->table_element);
		}
		// Triggers
		if (!$error && !$notrigger) {
			// Call triggers
			$result = $this->call_trigger(strtoupper(get_class($this)).'_CREATE', $user);
			if ($result < 0) {
				$error++;
			}
			// End call triggers
		}
		// Commit or rollback
		if ($error) {
			foreach ($this->errors as $errmsg) {
				dol_syslog(get_class($this)."::create ".$errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
			}
			$this->db->rollback();
			return -1;
		} else {
			$this->db->commit();
			return $this->id;
		}
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param int    $id   Id object
	 * @param string $ref  Ref
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null)
	{
		global $conf;
		/*$result = $this->fetchCommon($id, $ref);
		if ($result > 0 && !empty($this->table_element_line)) {
			$this->fetchLines();
		}*/
		dol_syslog(get_class($this)."::fetch", LOG_DEBUG);
		$sql = "SELECT c.rowid, c.entity, c.status, c.title, c.label, c.datdeb, c.datfin, c.datec, c.tms, c.fk_user_author, c.fk_user_modif, c.fk_email_template";
		$sql.= " ,t.topic, t.content";
		$sql.= " FROM ".MAIN_DB_PREFIX."campagne c";
		$sql.= " LEFT OUTER JOIN ".MAIN_DB_PREFIX."c_email_templates t ON c.fk_email_template = t.rowid";
		$sql.= " WHERE c.entity = ".$conf->entity;
		$sql.= " AND c.rowid = ".$id;
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql) > 0) {
				$res = $this->db->fetch_array($resql);

				$this->id = $res['rowid'];
				$this->entity = $res['entity'];
				$this->status	= (int) $res['status'];
				$this->title		= $res['title'];
				$this->label		= $res['label'];
				$this->fk_user_author = (int)$res['fk_user_author'];
				$this->fk_user_modif = (int)$res['fk_user_modif'];
				$this->fk_email_template = (int)$res['fk_email_template'];
				$this->datdeb = $this->db->jdate($res['datdeb']);
				$this->datfin = $this->db->jdate($res['datfin']);
				$this->datec = $this->db->jdate($res['datec']);
				$this->tms = $this->db->jdate($res['tms']);
				$this->topic = $res['topic'];
				$this->content = $res['content'];
				// Retrieve all extrafield
				// fetch optionals attributes and labels
				//$this->fetch_optionals();

				$this->db->free($resql);

				// multilangs
				if (!empty($conf->global->MAIN_MULTILANGS)) {
					$this->getMultiLangs();
				}
				if($this->fetchThirdparties() >= 0){
					return 1;
				}else{
					return -2;
				}
			} else {
				$this->error = "No campagn found";
				return 0;
			}
		} else {
			dol_print_error($this->db);
			$this->error = $this->db->lasterror;
			$this->errors[] = $this->db->lasterror;
			return -1;
		}
		$this->error = "No campagn found";
		return 0;
	}

	/**
	 * Load thirdparties collection
	 *
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchThirdparties($mode=null)
	{
		dol_syslog(get_class($this)."::fetchThirdparty", LOG_DEBUG);
		$sql = "select distinct v.fk_soc";
		$sql.=" from ".MAIN_DB_PREFIX."v_campagne_thirdparties v";
		$sql.=" where v.fk_campagne = ".$this->id;
		if($mode == "4send"){
			$sql .= " AND not exists (SELECT 1 from ".MAIN_DB_PREFIX."campagne_envois cv";
			$sql .= " where v.fk_campagne = cv.fk_campagne";
			$sql .= " and v.fk_soc = cv.fk_soc";
			$sql .= " and v.email = cv.email";
			$sql .= " and cv.message = '200')";
			$sql .= " and not exists(";
			$sql .= "SELECT 1 from ".MAIN_DB_PREFIX."campagne_blacklist b where v.email=b.email)";
		}
		$this->thirdparties = array();
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql) > 0) {
				$num = $this->db->num_rows($resql);
				$i = 0;
				while($i < $num){
					$res = $this->db->fetch_array($resql);
					$soc = new Societe($this->db);
					$soc->fetch($res["fk_soc"]);
					$this->thirdparties[] = $soc;
					$i++;
				}
				return $i;
				
			}else {
				$this->error = "No Thirdarty found";
				return 0;
			}
		} else {
			dol_print_error($this->db);
			$this->error = $this->db->lasterror;
			$this->errors[] = $this->db->lasterror;
			return -1;
		}
		$this->error = "No Thirdarty found";
		return 0;
	}
	
	/**
	 * Load object lines in memory from the database
	 *
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchLines()
	{
		$this->lines = array();

		$result = $this->fetchLinesCommon();
		return $result;
	}

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = false)
	{
		dol_syslog(get_class($this)."::update", LOG_DEBUG);
		$sql = "UPDATE ".MAIN_DB_PREFIX."campagne SET";
		$sql.= " status = ".$this->status;
		$sql.= ", title = '".$this->title."'";
		$sql.= ", label = '".$this->label."'";
		$sql.= ", datdeb = ".(dol_strlen($this->datdeb) != 0 ? "'".$this->db->idate($this->datdeb)."'" : 'null');
		$sql.= ", datfin = ".(dol_strlen($this->datfin) != 0 ? "'".$this->db->idate($this->datfin)."'" : 'null');
		$sql.= ", fk_user_modif = ".$user->id;
		$sql.= ", fk_email_template = ".$this->fk_email_template;
		$sql.= " WHERE rowid = ".$this->id;
		$this->db->begin();
		$resql = $this->db->query($sql);

		if (!$resql) {
			$error++;

			if ($this->db->errno() == 'DB_ERROR_RECORD_ALREADY_EXISTS') {
				$this->errors[] = $langs->trans('ErrorRefAlreadyExists');
			} else {
				$this->errors[] = "Error ".$this->db->lasterror();
			}
		}

		if (!$error) {
			$result = $this->insertExtraFields();
			if ($result < 0) {
				$error++;
			}
		}

		if (!$error) {
			if (!$notrigger) {
				// Call trigger
				$result = $this->call_trigger(strtoupper(get_class($this)).'_UPDATE', $user);
				if ($result < 0) {
					$error++;
				}
				// End call triggers
			}
		}

		// Commit or rollback
		if ($error) {
			foreach ($this->errors as $errmsg) {
				dol_syslog(get_class($this)."::update ".$errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
			}
			$this->db->rollback();
			return -1 * $error;
		} else {
			$this->db->commit();
			return 1;
		}
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user       User that deletes
	 * @param bool $notrigger  false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false)
	{
		global $langs, $conf;
		dol_syslog(get_class($this)."::delete rowid=".$this->id, LOG_DEBUG);

		
		$error = 0;
		$this->db->begin();

		if (!$error && !$notrigger) {
			$result = $this->call_trigger(strtoupper(get_class($this)).'_DELETE', $user);
			if ($result < 0) {
				$this->db->rollback();
				return -1;
			}
		}

		if (!$error) {
			$sql = 'DELETE FROM '.MAIN_DB_PREFIX.'categorie_campagne';
			$sql .= ' WHERE fk_campagne = '.$this->id;
			$resql = $this->db->query($sql);
			$sql = 'DELETE FROM '.MAIN_DB_PREFIX.'campagne_envois';
			$sql .= ' WHERE fk_campagne = '.$this->id;
			$resql = $this->db->query($sql);
			$sql = 'DELETE FROM '.MAIN_DB_PREFIX.'campagne';
			$sql .= ' WHERE rowid = '.$this->id;
			$resql = $this->db->query($sql);
		}

		if (!$error) {
			dol_syslog(get_class($this)."::delete $this->id by $user->id", LOG_DEBUG);
			$this->db->commit();
			return 1;
		} else {
			$this->error = $this->db->lasterror();
			dol_syslog(get_class($this)."::delete ".$this->error, LOG_ERR);
			$this->db->rollback();
			return -$error;
		}
	}
	
	
	/**
	 * Historise envoi dans database
	 *
	 */
	public function histoEnvoi($email, $hash, $sendid, $message, $socid, $notrigger = false)
	{
		global $conf;
		dol_syslog(get_class($this)."::histoEnvoi", LOG_DEBUG);
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."campagne_envois(email, hash, sendid, message, fk_campagne, fk_soc) VALUES (";
		$sql.= "'".$email."'";
		$sql.= ", '".$hash."'";
		$sql.= ", '".$sendid."'";
		$sql.= ", '".$message."'";
		$sql.= ", ".$this->id;
		$sql.= ", ".$socid.")";
		$this->db->begin();
		$res = $this->db->query($sql);
		if (!$res) {
			$error++;
			if ($this->db->lasterrno() == 'DB_ERROR_RECORD_ALREADY_EXISTS') {
				$this->errors[] = "ErrorRefAlreadyExists";
			} else {
				$this->errors[] = $this->db->lasterror();
			}
		}
		// Triggers
		if (!$error && !$notrigger) {
			// Call triggers
			$result = $this->call_trigger('HISTO_CREATE', $user);
			if ($result < 0) {
				$error++;
			}
			// End call triggers
		}
		// Commit or rollback
		if ($error) {
			foreach ($this->errors as $errmsg) {
				dol_syslog(get_class($this)."::histoEnvoi ".$errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
			}
			$this->db->rollback();
			return -1;
		} else {
			$this->db->commit();
			return $this->id;
		}
	}
	
	
	/**
	* action unsubscribe ajoutée dans mails
	*/
	public function unsubscribe($id){
	
		global $conf, $langs;
		$langs->loadLangs(array("altemailing@altemailing", "other"));
		dol_syslog(get_class($this)."::unsubscribe id=".$id, LOG_DEBUG);
		$sql = "select c.entity, cv.email from ".MAIN_DB_PREFIX."campagne c, ".MAIN_DB_PREFIX."campagne_envois cv";
		$sql.=" where c.rowid = cv.fk_campagne and cv.hash = '".$id."';";
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql) > 0) {
				$res = $this->db->fetch_array($resql);
				$sql = "SELECT count(1) as tot from ".MAIN_DB_PREFIX."campagne_blacklist where email = '".$res['email']."'";
				$resql2 = $this->db->query($sql);
				$res2 = $this->db->fetch_array($resql2);
				if($res2['tot'] > 0){
					return $langs->trans("UserDejaBlacklist")." : ".$res['email'];
				}
				$sql = "INSERT INTO ".MAIN_DB_PREFIX."campagne_blacklist(email, entity, hash) VALUES (";
				$sql.= "'".$res['email']."', ".$res['entity'].", '".$id."')";
				$this->db->begin();
				$ins = $this->db->query($sql);
				if (!$ins) {
					$error++;
					if ($this->db->lasterrno() == 'DB_ERROR_RECORD_ALREADY_EXISTS') {
						$this->errors[] = "ErrorRefAlreadyExists";
					} else {
						$this->errors[] = $this->db->lasterror();
					}
				}
				// Triggers
				if (!$error && !$notrigger) {
					// Call triggers
					$result = $this->call_trigger('BLACKLIST_CREATE', $user);
					if ($result < 0) {
						$error++;
					}
					// End call triggers
				}
				// Commit or rollback
				if ($error) {
					foreach ($this->errors as $errmsg) {
						dol_syslog(get_class($this)."::unsubscribe ".$errmsg, LOG_ERR);
						$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
					}
					$this->db->rollback();
					return -1;
				} else {
					$this->db->commit();
					return $langs->trans("UserInscritBlacklist")." : ".$res['email'];
				}
			}
		} else {
			dol_print_error($this->db);
			$this->error = $this->db->lasterror;
			$this->errors[] = $this->db->lasterror;
			return $langs->trans("NoEmailFound");
		}
		return $langs->trans("NoEmailFound");
	}

	function clickLink($code, $hash){
		global $langs;
		$langs->loadLangs(array("altemailing@altemailing", "other"));
		dol_syslog(get_class($this)."::clickLink code=".$code.' hash='.$hash, LOG_DEBUG);
		$sql = "SELECT rowid, fk_campagne, email from ".MAIN_DB_PREFIX."campagne_envois where hash = '".$hash."'";
		//print $sql.'<br>';
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql) > 0) {
				$res = $this->db->fetch_array($resql);
				$fk_campagne = $res['fk_campagne'];
				$fk_campagne_envoi = $res['rowid'];
				$email = $res['email'];
				$sql = "SELECT rowid, url from ".MAIN_DB_PREFIX."campagne_liens where fk_campagne = ".$fk_campagne." and code = '".$code."'";
				//print $sql;
				$resql = $this->db->query($sql);
				if ($resql) {
					if ($this->db->num_rows($resql) > 0) {
						$res = $this->db->fetch_array($resql);
						$url = $res['url'];
						$linkid = $res['rowid'];
						$sql = "INSERT INTO ".MAIN_DB_PREFIX."campagne_click(fk_campagne_envoi, fk_campagne_lien) VALUES(";
						$sql.= $fk_campagne_envoi.", ".$linkid.")";
						$this->db->begin();
						$ins = $this->db->query($sql);
						if (!$ins) {
							$error++;
							if ($this->db->lasterrno() == 'DB_ERROR_RECORD_ALREADY_EXISTS') {
								$this->errors[] = "ErrorRefAlreadyExists";
							} else {
								$this->errors[] = $this->db->lasterror();
							}
						}
						// Triggers
						if (!$error && !$notrigger) {
							// Call triggers
							$result = $this->call_trigger('CLICK_CREATE', new User($this->db));
							if ($result < 0) {
								$error++;
							}
							// End call triggers
						}
						// Commit or rollback
						if ($error) {
							foreach ($this->errors as $errmsg) {
								dol_syslog(get_class($this)."::clickLink ".$errmsg, LOG_ERR);
								$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
							}
							$this->db->rollback();
							return $this->error;
						} else {
							$this->db->commit();
							header("Location: ".str_replace('__THIRDPARTY_EMAIL__', $email, $url));
							exit;
						}
					}
				}else{
					dol_syslog(get_class($this)."::clickLink ".$langs->trans('LinkNotFound'), LOG_ERR);
					return $langs->trans('LinkNotFound');
				}
			}else{
				dol_syslog(get_class($this)."::clickLink ".$langs->trans('CampagnNotFound'), LOG_ERR);
				return $langs->trans('CampagnNotFound');
			}
		}
	
	}
	
	
	function pixel($hash){
		global $langs;
		$langs->loadLangs(array("altemailing@altemailing", "other"));
		dol_syslog(get_class($this)."::pixel hash=".$hash, LOG_DEBUG);
		$sql = "SELECT rowid, fk_campagne from ".MAIN_DB_PREFIX."campagne_envois where hash = '".$hash."'";
		//print $sql.'<br>';
		$resql = $this->db->query($sql);
		if ($resql) {
			
			$res = $this->db->fetch_array($resql);
			$fk_campagne_envoi = $res['rowid'];
			$sql = "INSERT INTO ".MAIN_DB_PREFIX."campagne_open(fk_campagne_envoi) VALUES(";
			$sql.= $fk_campagne_envoi.")";
			$this->db->begin();
			$ins = $this->db->query($sql);
			if (!$ins) {
				$error++;
				if ($this->db->lasterrno() == 'DB_ERROR_RECORD_ALREADY_EXISTS') {
					$this->errors[] = "ErrorRefAlreadyExists";
				} else {
					$this->errors[] = $this->db->lasterror();
				}
			}
			// Triggers
			if (!$error && !$notrigger) {
				// Call triggers
				$result = $this->call_trigger('OPENMAIL_CREATE', new User($this->db));
				if ($result < 0) {
					$error++;
				}
				// End call triggers
			}
			// Commit or rollback
			if ($error) {
				foreach ($this->errors as $errmsg) {
					dol_syslog(get_class($this)."::pixel ".$errmsg, LOG_ERR);
					$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
				}
				$this->db->rollback();
				return $this->error;
			} else {
				$this->db->commit();
				header('Content-Type: image/png');
				header("Location: pixel.png");
				exit;
			}
		}else{
			dol_syslog(get_class($this)."::clickLink ".$langs->trans('LinkNotFound'), LOG_ERR);
			return $langs->trans('LinkNotFound');
		}
	
	}
	
	/**
	 *	Validate object
	 *
	 *	@param		User	$user     		User making status change
	 *  @param		int		$notrigger		1=Does not execute triggers, 0= execute triggers
	 *	@return  	int						<=0 if OK, 0=Nothing done, >0 if KO
	 */
	public function validate($user, $notrigger = 0)
	{
		global $conf, $langs;
		dol_syslog(get_class($this)."::validate rowid=".$this->id, LOG_DEBUG);
		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

		$error = 0;

		// Protection
		if ($this->status == self::STATUS_VALIDATED) {
			dol_syslog(get_class($this)."::validate action abandonned: already validated", LOG_WARNING);
			return 0;
		}


		$now = dol_now();

		$this->db->begin();



		// Validate
		$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element;
		$sql .= " SET status = ".self::STATUS_VALIDATED;
		if (!empty($this->fields['datval'])) {
			$sql .= ", datval = '".$this->db->idate($now)."'";
		}
		if (!empty($this->fields['fk_user_valid'])) {
			$sql .= ", fk_user_valid = ".((int) $user->id);
		}
		$sql .= " WHERE rowid = ".((int) $this->id);

		dol_syslog(get_class($this)."::validate()", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (!$resql) {
			dol_print_error($this->db);
			$this->error = $this->db->lasterror();
			$error++;
		}

		if (!$error && !$notrigger) {
			// Call trigger
			$result = $this->call_trigger(strtoupper(get_class($this)).'_VALIDATE', $user);
			if ($result < 0) {
				$error++;
			}
			// End call triggers
		}

		// Set new ref and current status
		if (!$error) {
			$this->status = self::STATUS_VALIDATED;
		}

		if (!$error) {
			$this->db->commit();
			return 1;
		} else {
			$this->error = $this->db->lasterror();
			dol_syslog(get_class($this)."::delete ".$this->error, LOG_ERR);
			
			$this->db->rollback();
			return -1;
		}
	}


	/**
	 * 	Render list of categories linked to object with id $id and type $type
	 *
	 * 	@param		int		$id				Id of object
	 * 	@param		string	$type			Type of category ('member', 'customer', 'supplier', 'product', 'contact'). Old mode (0, 1, 2, ...) is deprecated.
	 *  @param		int		$rendermode		0=Default, use multiselect. 1=Emulate multiselect (recommended)
	 *  @param		int		$nolink			1=Do not add html links
	 * 	@return		string					String with categories
	 */
	public function showCategories($id, $type, $rendermode = 0, $nolink = 0)
	{
		$form = new Form($this->db);
		include_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';

		$cat = new Categorie($this->db);
		$categories = $this->containing($id, $type);

		if ($rendermode == 1) {
			$toprint = array();
			foreach ($categories as $c) {
				$ways = $c->print_all_ways(' &gt;&gt; ', ($nolink ? 'none' : ''), 0, 1); // $ways[0] = "ccc2 >> ccc2a >> ccc2a1" with html formated text
				foreach ($ways as $way) {
					$toprint[] = '<li class="select2-search-choice-dolibarr noborderoncategories"'.($c->color ? ' style="background: #'.$c->color.';"' : ' style="background: #bbb"').'>'.$way.'</li>';
				}
			}
			return '<div class="select2-container-multi-dolibarr"><ul class="select2-choices-dolibarr">'.implode(' ', $toprint).'</ul></div>';
		}

		if ($rendermode == 0) {
			$arrayselected = array();
			$cate_arbo = $form->select_all_categories($type, '', 'parent', 64, 0, 1);
			foreach ($categories as $c) {
				$arrayselected[] = $c->id;
			}

			return $form->multiselectarray('categories', $cate_arbo, $arrayselected, '', 0, '', 0, '100%', 'disabled', 'category');
		}

		return 'ErrorBadValueForParameterRenderMode'; // Should not happened
	}
	
		/**
	 * Return list of categories (object instances or labels) linked to element of id $id and type $type
	 * Should be named getListOfCategForObject
	 *
	 * @param   int    		$id     Id of element
	 * @param   string|int	$type   Type of category ('customer', 'supplier', 'contact', 'product', 'member') or (0, 1, 2, ...)
	 * @param   string 		$mode   'id'=Get array of category ids, 'object'=Get array of fetched category instances, 'label'=Get array of category
	 *                      	    labels, 'id'= Get array of category IDs
	 * @return  Categorie[]|int     Array of category objects or < 0 if KO
	 */
	public function containing($id, $type, $mode = 'object')
	{
		$cats = array();

		$sql = "SELECT ct.fk_categorie, c.label, c.rowid";
		$sql .= " FROM ".MAIN_DB_PREFIX."categorie_campagne as ct, ".MAIN_DB_PREFIX."categorie as c";
		$sql .= " WHERE ct.fk_categorie = c.rowid AND ct.fk_campagne = ".(int) $id;
		// This seems useless because the table already contains id of category of 1 unique type. So commented.
		// So now it works also with external added categories.
		//$sql .= " AND c.type = ".((int) $this->MAP_ID[$type]);
		$sql .= " AND c.entity IN (".getEntity('category').")";

		$res = $this->db->query($sql);
		if ($res) {
			while ($obj = $this->db->fetch_object($res)) {
				if ($mode == 'id') {
					$cats[] = $obj->rowid;
				} elseif ($mode == 'label') {
					$cats[] = $obj->label;
				} else {
					$cat = new Categorie($this->db);
					$cat->fetch($obj->fk_categorie);
					$cats[] = $cat;
				}
			}
		} else {
			dol_print_error($this->db);
			return -1;
		}


		return $cats;
	}
	
	
	public function getListEmailTemplates($type){
		global $conf;
		// Liste mail envoi propal
		$sql = "select rowid, label from ".MAIN_DB_PREFIX."c_email_templates where type_template = '".$type."' and private = '0' and enabled = 1 and entity = ".$conf->entity;
		//print $sql;
		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			$list = array();
			if ($num) {
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql);
					$list[$obj->rowid] = $obj->label;
					$i++;
				}
				
			}
			return $list;
		}
		return -1;
	
	}

	/**
	 * Sets object to supplied categories.
	 *
	 * Deletes object from existing categories not supplied.
	 * Adds it to non existing supplied categories.
	 * Existing categories are left untouch.
	 *
	 * @param 	int[]|int 	$categories 	Category ID or array of Categories IDs
	 * @param 	string 		$type_categ 			Category type ('customer' or 'supplier')
	 * @return	int							<0 if KO, >0 if OK
	 */
	public function setCategories($categories)
	{
		
		dol_syslog(get_class($this)."::setCategories rowid=".$this->id, LOG_DEBUG);
		$i = 0;
		$sql = "DELETE FROM ".MAIN_DB_PREFIX."categorie_campagne where fk_campagne = ".$this->id;
		$this->db->query($sql);	
		while($categories[$i]){
			$sql = "INSERT INTO ".MAIN_DB_PREFIX."categorie_campagne(fk_campagne, fk_categorie) VALUES(".$this->id.", ".$categories[$i].")";
			$this->db->query($sql);			
			$i++;
		}
	}

	/**
	 *	Set draft status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, >0 if OK
	 */
	public function setDraft($user, $notrigger = 0)
	{
		// Protection
		if ($this->status <= self::STATUS_DRAFT) {
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->mymodule->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->mymodule->mymodule_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_DRAFT, $notrigger, 'MYOBJECT_UNVALIDATE');
	}


	/**
	 *  Return a link to the object card (with optionaly the picto)
	 *
	 *  @param  int     $withpicto                  Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
	 *  @param  string  $option                     On what the link point to ('nolink', ...)
	 *  @param  int     $notooltip                  1=Disable tooltip
	 *  @param  string  $morecss                    Add more css on link
	 *  @param  int     $save_lastsearch_value      -1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
	 *  @return	string                              String with URL
	 */
	public function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $morecss = '', $save_lastsearch_value = -1)
	{
		global $conf, $langs, $hookmanager;

		if (!empty($conf->dol_no_mouse_hover)) {
			$notooltip = 1; // Force disable tooltips
		}

		$result = '';

		$label = img_picto('', $this->picto).' <u>'.$langs->trans("Campagne").'</u>';
		if (isset($this->status)) {
			$label .= ' '.$this->getLibStatut(5);
		}
		$label .= '<br>';
		$label .= '<b>'.$langs->trans('Ref').':</b> '.$this->ref;

		$url = dol_buildpath('/altemailing/card.php', 1).'?id='.$this->id;

		if ($option != 'nolink') {
			// Add param to save lastsearch_values or not
			$add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
			if ($save_lastsearch_value == -1 && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) {
				$add_save_lastsearch_values = 1;
			}
			if ($url && $add_save_lastsearch_values) {
				$url .= '&save_lastsearch_values=1';
			}
		}

		$linkclose = '';
		if (empty($notooltip)) {
			if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
				$label = $langs->trans("ShowMyObject");
				$linkclose .= ' alt="'.dol_escape_htmltag($label, 1).'"';
			}
			$linkclose .= ' title="'.dol_escape_htmltag($label, 1).'"';
			$linkclose .= ' class="classfortooltip'.($morecss ? ' '.$morecss : '').'"';
		} else {
			$linkclose = ($morecss ? ' class="'.$morecss.'"' : '');
		}

		if ($option == 'nolink' || empty($url)) {
			$linkstart = '<span';
		} else {
			$linkstart = '<a href="'.$url.'"';
		}
		$linkstart .= $linkclose.'>';
		if ($option == 'nolink' || empty($url)) {
			$linkend = '</span>';
		} else {
			$linkend = '</a>';
		}

		$result .= $linkstart;

		if (empty($this->showphoto_on_popup)) {
			if ($withpicto) {
				$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
			}
		} else {
			if ($withpicto) {
				require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

				list($class, $module) = explode('@', $this->picto);
				$upload_dir = $conf->$module->multidir_output[$conf->entity]."/$class/".dol_sanitizeFileName($this->ref);
				$filearray = dol_dir_list($upload_dir, "files");
				$filename = $filearray[0]['name'];
				if (!empty($filename)) {
					$pospoint = strpos($filearray[0]['name'], '.');

					$pathtophoto = $class.'/'.$this->ref.'/thumbs/'.substr($filename, 0, $pospoint).'_mini'.substr($filename, $pospoint);
					if (empty($conf->global->{strtoupper($module.'_'.$class).'_FORMATLISTPHOTOSASUSERS'})) {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><div class="photoref"><img class="photo'.$module.'" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div></div>';
					} else {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><img class="photouserphoto userphoto" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div>';
					}

					$result .= '</div>';
				} else {
					$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
				}
			}
		}

		if ($withpicto != 2) {
			$result .= $this->ref;
		}

		$result .= $linkend;
		//if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

		global $action, $hookmanager;
		$hookmanager->initHooks(array('myobjectdao'));
		$parameters = array('id'=>$this->id, 'getnomurl'=>$result);
		$reshook = $hookmanager->executeHooks('getNomUrl', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
		if ($reshook > 0) {
			$result = $hookmanager->resPrint;
		} else {
			$result .= $hookmanager->resPrint;
		}

		return $result;
	}

	/**
	 *  Return the label of the status
	 *
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string 			       Label of status
	 */
	public function getLabelStatus($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	/**
	 *  Return the label of the status
	 *
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string 			       Label of status
	 */
	public function getLibStatut($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Return the status
	 *
	 *  @param	int		$status        Id status
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return string 			       Label of status
	 */
	public function LibStatut($status, $mode = 0)
	{
		// phpcs:enable
		if (empty($this->labelStatus) || empty($this->labelStatusShort)) {
			global $langs;
			//$langs->load("mymodule@mymodule");
			$this->labelStatus[self::STATUS_DRAFT] = $langs->transnoentitiesnoconv('Draft');
			$this->labelStatus[self::STATUS_VALIDATED] = $langs->transnoentitiesnoconv('Enabled');
			$this->labelStatus[self::STATUS_CLOSED] = $langs->transnoentitiesnoconv('Closed');
			$this->labelStatusShort[self::STATUS_DRAFT] = $langs->transnoentitiesnoconv('Draft');
			$this->labelStatusShort[self::STATUS_VALIDATED] = $langs->transnoentitiesnoconv('Enabled');
			$this->labelStatusShort[self::STATUS_CLOSED] = $langs->transnoentitiesnoconv('Closed');
		}

		$statusType = 'status'.$status;
		
		return dolGetStatus($this->labelStatus[$status], $this->labelStatusShort[$status], '', $statusType, $mode);
	}

	/**
	 *	Load the info information in the object
	 *
	 *	@param  int		$id       Id of object
	 *	@return	void
	 */
	public function info($id)
	{
		$sql = "SELECT rowid, date_creation as datec, tms as datem,";
		$sql .= " fk_user_creat, fk_user_modif";
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
		$sql .= " WHERE t.rowid = ".((int) $id);

		$result = $this->db->query($sql);
		if ($result) {
			if ($this->db->num_rows($result)) {
				$obj = $this->db->fetch_object($result);
				$this->id = $obj->rowid;
				if (!empty($obj->fk_user_author)) {
					$cuser = new User($this->db);
					$cuser->fetch($obj->fk_user_author);
					$this->user_creation = $cuser;
				}

				if (!empty($obj->fk_user_valid)) {
					$vuser = new User($this->db);
					$vuser->fetch($obj->fk_user_valid);
					$this->user_validation = $vuser;
				}

				if (!empty($obj->fk_user_cloture)) {
					$cluser = new User($this->db);
					$cluser->fetch($obj->fk_user_cloture);
					$this->user_cloture = $cluser;
				}

				$this->date_creation     = $this->db->jdate($obj->datec);
				$this->date_modification = $this->db->jdate($obj->datem);
				$this->date_validation   = $this->db->jdate($obj->datev);
			}

			$this->db->free($result);
		} else {
			dol_print_error($this->db);
		}
	}

	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	public function initAsSpecimen()
	{
		// Set here init that are not commonf fields
		// $this->property1 = ...
		// $this->property2 = ...

		$this->initAsSpecimenCommon();
	}

	/**
	 * 	Create an array of lines
	 *
	 * 	@return array|int		array of lines if OK, <0 if KO
	 */
	public function getLinesArray()
	{
		dol_syslog(get_class($this)."::getLinesArray", LOG_DEBUG);
		$this->lines = array();
		$sql = "SELECT rowid, url, code from ".MAIN_DB_PREFIX.$this->table_element_line." l";
		$sql.= " WHERE l.fk_campagne = ".$this->id;
		$resql = $this->db->query($sql);
		if ($resql) {
			while ($obj = $this->db->fetch_object($resql)) {
				$objectline = new CampagneLink($this->db);
				$objectline->id = $obj->rowid;
				$objectline->url = $obj->url;
				$objectline->code = $obj->code;
				$this->lines[] = $objectline;
			}
		}
		
		return $this->lines;
		
	}
	
	/**
	* Formulaire ajout nouveau lien
	*/
	public function formAddObjectLine($dateSelector = 1, $seller = 1, $buyer = 1, $defaulttpldir = '/core/tpl'){
		dol_syslog(get_class($this)."::formAddObjectLine", LOG_DEBUG);
		global $langs;
		
		
		print '<tr class="pair nodrag nodrop nohoverpair">';
		print '<td class="nobottom linecoldescription minwidth500imp">';
		print '<input name="url" id="url" size="120" maxlength="300" class="flat">';
		print '</td>';
		print '<td class="linecoledit">';
		print '<input name="code" id="code" size="12" maxlength="12" class="flat">';
		print '</td>';
		print '<td class="nobottom linecoledit center valignmiddle" colspan="2">';
		print '<input type="submit" class="button reposition" value="'.$langs->trans('Add').'" name="addline" id="addline">';
		print '</td>';
		print '</tr>';
		
	}
	
	/**
	 *	Set cancel status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, 0=Nothing done, >0 if OK
	 */
	public function cancel($user, $notrigger = 0)
	{
		// Protection
		if ($this->status != self::STATUS_VALIDATED) {
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->mymodule->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->mymodule->mymodule_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_CLOSED, $notrigger, 'CAMPAGNE_CLOSE');
	}
	
	/**
	* Liste des liens
	*/
	public function printObjectLines($action, $mysoc = 1, $soc = 0, $selected = 0, $dateSelector = -1, $defaulttpldir = '/core/tpl'){
		dol_syslog(get_class($this)."::printObjectLines", LOG_DEBUG);
		global $langs, $user;
		print '<tr class="liste_titre nodrag nodrop">';
		print '<td class="linecoldescription minwidth500imp">';
		print $langs->trans("CampagneLien");
		print '</td>';
		print '<td class="linecoledit">';
		print $langs->trans("Code");
		print '</td>';
		if($user->rights->altemailing->campagne->modifier && $this->status <= self::STATUS_DRAFT){
			print '<td class="linecoledit">';
			print '</td>';
			print '<td class="linecoldelete">';
			print '</td>';
		}
		print '</tr>';
		
		$i = 0;
		while($this->lines[$i]){
			print '<tr id="row-'.$i.'" class="drag drop oddeven">';
			if($selected != $this->lines[$i]->id){
				print '<td class="linecoldescription minwidth300imp">';
				print '<a target="_blank" href="'.$this->lines[$i]->url.'" class="nowraponall classfortooltip">'.$this->lines[$i]->url.'</a>';
				print '</td>';
				print '<td>';
				print $this->lines[$i]->code;
				print '</td>';
				if($user->rights->altemailing->campagne->modifier && $this->status <= self::STATUS_DRAFT){
					print '<td class="linecoledit center">';
					print '<a class="editfielda reposition" href="'.$_SERVER["PHP_SELF"].'?id='.$this->id.'&action=editline&token='.newToken().'&lineid='.$this->lines[$i]->id.'#row-'.$i.'">';
					print img_edit().'</a>';
					print '</td>';
					print '<td class="linecoldelete center">';
					print '<a class="reposition" href="'.$_SERVER["PHP_SELF"].'?id='.$this->id.'&action=ask_deleteline&token='.newToken().'&lineid='.$this->lines[$i]->id.'">';
					print img_delete();
					print '</a>';
					print '</td>';
				}
			}else{
				print '<td class="linecoldescription minwidth300imp">';
				print '<input name="url" id="url" size="120" maxlength="300" class="flat" value="'.$this->lines[$i]->url.'">';
				print '</td>';
				print '<td>';
				print '<input name="code" id="code" size="12" maxlength="12" class="flat" value="'.$this->lines[$i]->code.'">';
				print '</td>';
			}
			if($selected == $this->lines[$i]->id){
				print '<td class="linecoledit center" colspan="2">';
				print '<input type="submit" class="button buttongen marginbottomonly button-save" id="savelinebutton marginbottomonly" name="save" value="'.$langs->trans("Save").'"><br>';
				print '<input type="button" class="button buttongen marginbottomonly button-cancel" id="cancellinebutton" name="cancel" value="'.$langs->trans("Cancel").'" onclick="document.location.href=\''.$action.'\'">';
				print '</td>';
			}
			print '</tr>';
			$i++;
		}
	}
	
	/**
	 *  Delete a line of object in database
	 *
	 *	@param  User	$user       User that delete
	 *  @param	int		$idline		Id of line to delete
	 *  @param 	bool 	$notrigger  false=launch triggers after, true=disable triggers
	 *  @return int         		>0 if OK, <0 if KO
	 */
	public function deleteline($user,$lineid){
		dol_syslog(get_class($this)."::deleteline", LOG_DEBUG);
		$sql = "DELETE FROM ".MAIN_DB_PREFIX.$this->table_element_line." where rowid = ".$lineid;
		$this->db->begin();
		$resql = $this->db->query($sql);
		if (!$resql) {
			$this->error = "Error ".$this->db->lasterror();
			$error++;
		}
		if (empty($error)) {
			$this->db->commit();
			return 1;
		} else {
			dol_syslog(get_class($this)."::deleteline ERROR:".$this->error, LOG_ERR);
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 *  Returns the reference to the following non used object depending on the active numbering module.
	 *
	 *  @return string      		Object free reference
	 */
	public function getNextNumRef()
	{
		global $langs, $conf;
		$langs->load("mymodule@mymodule");

		if (empty($conf->global->MYMODULE_MYOBJECT_ADDON)) {
			$conf->global->MYMODULE_MYOBJECT_ADDON = 'mod_myobject_standard';
		}

		if (!empty($conf->global->MYMODULE_MYOBJECT_ADDON)) {
			$mybool = false;

			$file = $conf->global->MYMODULE_MYOBJECT_ADDON.".php";
			$classname = $conf->global->MYMODULE_MYOBJECT_ADDON;

			// Include file with class
			$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);
			foreach ($dirmodels as $reldir) {
				$dir = dol_buildpath($reldir."core/modules/mymodule/");

				// Load file with numbering class (if found)
				$mybool |= @include_once $dir.$file;
			}

			if ($mybool === false) {
				dol_print_error('', "Failed to include file ".$file);
				return '';
			}

			if (class_exists($classname)) {
				$obj = new $classname();
				$numref = $obj->getNextValue($this);

				if ($numref != '' && $numref != '-1') {
					return $numref;
				} else {
					$this->error = $obj->error;
					//dol_print_error($this->db,get_class($this)."::getNextNumRef ".$obj->error);
					return "";
				}
			} else {
				print $langs->trans("Error")." ".$langs->trans("ClassNotFound").' '.$classname;
				return "";
			}
		} else {
			print $langs->trans("ErrorNumberingModuleNotSetup", $this->element);
			return "";
		}
	}

	/**
	 *  Create a document onto disk according to template module.
	 *
	 *  @param	    string		$modele			Force template to use ('' to not force)
	 *  @param		Translate	$outputlangs	objet lang a utiliser pour traduction
	 *  @param      int			$hidedetails    Hide details of lines
	 *  @param      int			$hidedesc       Hide description
	 *  @param      int			$hideref        Hide ref
	 *  @param      null|array  $moreparams     Array to provide more information
	 *  @return     int         				0 if KO, 1 if OK
	 */
	public function generateDocument($modele, $outputlangs, $hidedetails = 0, $hidedesc = 0, $hideref = 0, $moreparams = null)
	{
		global $conf, $langs;

		$result = 0;
		$includedocgeneration = 0;

		$langs->load("mymodule@mymodule");

		if (!dol_strlen($modele)) {
			$modele = 'standard_myobject';

			if (!empty($this->model_pdf)) {
				$modele = $this->model_pdf;
			} elseif (!empty($conf->global->MYOBJECT_ADDON_PDF)) {
				$modele = $conf->global->MYOBJECT_ADDON_PDF;
			}
		}

		$modelpath = "core/modules/mymodule/doc/";

		if ($includedocgeneration && !empty($modele)) {
			$result = $this->commonGenerateDocument($modelpath, $modele, $outputlangs, $hidedetails, $hidedesc, $hideref, $moreparams);
		}

		return $result;
	}

	
	public function getStats(){
		dol_syslog(get_class($this)."::getStats", LOG_DEBUG);
		$fields = array(
			'nbtiers'           => array('type'=>'integer', 'label'=>'NbTiers', 'enabled'=>1, 'visible'=>1, 'noteditable'=>0, 'default'=>'', 'notnull'=> 1, 'showoncombobox'=>1, 'index'=>1, 'position'=>600),
			'nbsend'           => array('type'=>'integer', 'label'=>'NbSend', 'enabled'=>1, 'visible'=>1, 'noteditable'=>0, 'default'=>'', 'notnull'=> 1, 'showoncombobox'=>1, 'index'=>1, 'position'=>601),
			'standby'           => array('type'=>'integer', 'label'=>'NbStandBy', 'enabled'=>1, 'visible'=>1, 'noteditable'=>0, 'default'=>'', 'notnull'=> 1, 'showoncombobox'=>1, 'index'=>1, 'position'=>602),
			'read'           => array('type'=>'integer', 'label'=>'NbOpen', 'enabled'=>1, 'visible'=>1, 'noteditable'=>0, 'default'=>'', 'notnull'=> 1, 'showoncombobox'=>1, 'index'=>1, 'position'=>603),
			'clicked'           => array('type'=>'integer', 'label'=>'NbClicked', 'enabled'=>1, 'visible'=>1, 'noteditable'=>0, 'default'=>'', 'notnull'=> 1, 'showoncombobox'=>1, 'index'=>1, 'position'=>604),
			'lost'           => array('type'=>'integer', 'label'=>'NbLost', 'enabled'=>1, 'visible'=>1, 'noteditable'=>0, 'default'=>'', 'notnull'=> 1, 'showoncombobox'=>1, 'index'=>1, 'position'=>605),
			'ignore'           => array('type'=>'integer', 'label'=>'NbIgnore', 'enabled'=>1, 'visible'=>1, 'noteditable'=>0, 'default'=>'', 'notnull'=> 1, 'showoncombobox'=>1, 'index'=>1, 'position'=>606),
		);
		$this->fields = array_merge($this->fields, $fields);
		
		$sql = "select count(distinct fk_soc) as nbtiers from ".MAIN_DB_PREFIX."v_campagne_thirdparties where fk_campagne = ".$this->id;
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql) > 0) {
				$res = $this->db->fetch_array($resql);
				$this->nbtiers = $res['nbtiers'];
			}
		}
		
		$sql = "select count(distinct fk_soc) as nbsend from ".MAIN_DB_PREFIX."v_campagne_thirdparties where fk_campagne = ".$this->id." AND status between ".CampagneThirdParties::STATUS_VALIDATED." and ".CampagneThirdParties::STATUS_CONSULTED;
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql) > 0) {
				$res = $this->db->fetch_array($resql);
				$this->nbsend = $res['nbsend'];
			}
		}
				
		$sql = "select count(distinct fk_soc) as standby from ".MAIN_DB_PREFIX."v_campagne_thirdparties where fk_campagne = ".$this->id." AND status = ".CampagneThirdParties::STATUS_STANDBY;
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql) > 0) {
				$res = $this->db->fetch_array($resql);
				$this->standby = $res['standby'];
			}
		}
		
		$sql = "select count(distinct fk_soc) as lu from ".MAIN_DB_PREFIX."v_campagne_thirdparties where fk_campagne = ".$this->id." AND status = ".CampagneThirdParties::STATUS_OPENED;
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql) > 0) {
				$res = $this->db->fetch_array($resql);
				$this->read = $res['lu'];
			}
		}
		
		$sql = "select count(distinct fk_soc) as clicked from ".MAIN_DB_PREFIX."v_campagne_thirdparties where fk_campagne = ".$this->id." AND status = ".CampagneThirdParties::STATUS_CONSULTED;
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql) > 0) {
				$res = $this->db->fetch_array($resql);
				$this->clicked = $res['clicked'];
			}
		}
		
		$sql = "select count(distinct fk_soc) as lost from ".MAIN_DB_PREFIX."v_campagne_thirdparties where fk_campagne = ".$this->id." AND status = ".CampagneThirdParties::STATUS_LOST;
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql) > 0) {
				$res = $this->db->fetch_array($resql);
				$this->lost = $res['lost'];
			}
		}
		
		$sql = "select count(distinct fk_soc) as ignored from ".MAIN_DB_PREFIX."v_campagne_thirdparties where fk_campagne = ".$this->id." AND status = ".CampagneThirdParties::STATUS_IGNORED;
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql) > 0) {
				$res = $this->db->fetch_array($resql);
				$this->ignore = $res['ignored'];
			}
		}
	}
}

/**
 * Class CampagneThirdParties. You can also remove this and generate a CRUD class for lines objects.
 */
class CampagneThirdParties extends CommonObject
{
	
	public $db;
	
	public $table_element = 'v_campagne_thirdparties';
	
	const STATUS_STANDBY = 0;
	const STATUS_ERROR = 500;
	const STATUS_IGNORED = 999;
	const STATUS_VALIDATED = 100;
	const STATUS_OPENED = 200;
	const STATUS_CONSULTED = 300;
	const STATUS_LOST = 998;
	
	
	public function __construct(DoliDB $db)
	{
		global $langs;
		$langs->loadLangs(array("altemailing@altemailing"));
		$this->db = $db;
		$this->fields = array(
			'fk_campagne'         => array('type'=>'integer:Campagne:altemailing/class/user.class.php', 'label'=>'Campagne', 'enabled'=>1, 'visible'=>0, 'noteditable'=>1, 'notnull'=> 1, 'index'=>1, 'position'=>1, 'comment'=>'Id campagn', 'css'=>'left'),
			'fk_soc'        => array('type'=>'integer:Societe:societe/class/societe.class.php', 'label'=>'Thirdparty', 'enabled'=>1, 'visible'=>1, 'notnull'=> 1, 'noteditable'=>1, 'default'=>1, 'index'=>1, 'position'=>2),
			'nom'           => array('type'=>'varchar(100)', 'label'=>'Name', 'enabled'=>1, 'visible'=>1, 'noteditable'=>1, 'notnull'=> 1, 'index'=>3, 'position'=>3, 'searchall'=>1, 'comment'=>'Thirdparty name', 'validate'=>1),
			'name_alias'           => array('type'=>'varchar(100)', 'label'=>'Name alias', 'enabled'=>1, 'visible'=>0, 'noteditable'=>1, 'notnull'=> 1, 'index'=>4, 'position'=>4, 'searchall'=>1, 'comment'=>'Thirdparty name alias'),
			'email'         => array('type'=>'varchar(256)', 'label'=>'Email', 'enabled'=>1, 'visible'=>1, 'position'=>5, 'searchall'=>1, 'noteditable'=>1/*'css'=>'minwidth300', 'cssview'=>'wordbreak', 'showoncombobox'=>2, 'validate'=>1*/),	
			'phone'         => array('type'=>'varchar(20)', 'label'=>'Phone', 'enabled'=>1, 'visible'=>1, 'position'=>6, 'searchall'=>1, 'noteditable'=>1/*'css'=>'minwidth300', 'cssview'=>'wordbreak', 'showoncombobox'=>2, 'validate'=>1*/),	
			'url'         => array('type'=>'varchar(256)', 'label'=>'Url', 'enabled'=>1, 'visible'=>0, 'position'=>7, 'searchall'=>1, 'noteditable'=>1/*'css'=>'minwidth300', 'cssview'=>'wordbreak', 'showoncombobox'=>2, 'validate'=>1*/),	
			'address' =>array('type'=>'varchar(255)', 'label'=>'Address', 'enabled'=>1, 'visible'=>-2, 'position'=>8, 'searchall'=>1),
			'zip' =>array('type'=>'varchar(10)', 'label'=>'Zip', 'enabled'=>1, 'visible'=>-2, 'position'=>9, 'searchall'=>1),
			'town' =>array('type'=>'varchar(50)', 'label'=>'Town', 'enabled'=>1, 'visible'=>-2, 'position'=>10, 'searchall'=>1),
			'fk_departement' =>array('type'=>'sellist:c_departements:nom:rowid', 'label'=>'State', 'position'=>11, 'enabled'=>1, 'visible'=>1),
			'fk_pays' =>array('type'=>'sellist:c_country:label:rowid', 'label'=>'Country', 'position'=>12, 'enabled'=>1, 'visible'=>1),
			'status'        => array('type'=>'integer', 'label'=>'StatusEnvoi', 'enabled'=>1, 'visible'=>1, 'notnull'=> 1, 'default'=>0, 'index'=>1, 'position'=>2000, 'arrayofkeyval'=>array(998=>$langs->trans('Lost'), 999=>$langs->trans('Ignored'), 100=>$langs->trans('Send'), 200=>$langs->trans('Readed'), 300=>$langs->trans('Consulted'), 500=>$langs->trans('Error'), 0=>$langs->trans('StandBy')), 'validate'=>1),
			'nbclick'        => array('type'=>'integer', 'label'=>'NbClick', 'enabled'=>1, 'visible'=>1, 'notnull'=> 0, 'default'=>0, 'index'=>1, 'position'=>2001),
		);
	}
	
	public function getLibStatut($mode = 0){
	
		global $langs;
		if ($this->status == self::STATUS_STANDBY) {
			$statusType = 'status1';
			$labelStatus = $langs->transnoentitiesnoconv("StandBy");
			$labelStatusShort = $langs->transnoentitiesnoconv("StandBy");
		}elseif($this->status == self::STATUS_ERROR){
			$statusType = 'status10';
			$labelStatus = $langs->transnoentitiesnoconv("Error");
			$labelStatusShort = $langs->transnoentitiesnoconv("Error");
		}elseif($this->status == self::STATUS_LOST){
			$statusType = 'status5';
			$labelStatus = $langs->transnoentitiesnoconv("Lost");
			$labelStatusShort = $langs->transnoentitiesnoconv("Lost");
		}elseif($this->status == self::STATUS_IGNORED){
			$statusType = 'status6';
			$labelStatus = $langs->transnoentitiesnoconv("Ignored");
			$labelStatusShort = $langs->transnoentitiesnoconv("Ignored");
		}elseif($this->status == self::STATUS_VALIDATED){
			$statusType = 'status3';
			$labelStatus = $langs->transnoentitiesnoconv("Send");
			$labelStatusShort = $langs->transnoentitiesnoconv("Send");
		}elseif($this->status == self::STATUS_OPENED){
			$statusType = 'status7';
			$labelStatus = $langs->transnoentitiesnoconv("Readed");
			$labelStatusShort = $langs->transnoentitiesnoconv("Readed");
		}elseif($this->status == self::STATUS_CONSULTED){
			$statusType = 'status4';
			$labelStatus = $langs->transnoentitiesnoconv("Consulted");
			$labelStatusShort = $langs->transnoentitiesnoconv("Consulted");
		}
		return dolGetStatus($labelStatus, $labelStatusShort, '', $statusType, $mode);
	}
}

/**
 * Class CampagneLien. You can also remove this and generate a CRUD class for lines objects.
 */
class CampagneLien extends CommonObject
{
	
	public $db;
	
	public $code;
	
	public $url;
	
	public $table_element = 'campagne_liens';
	
	public function __construct(DoliDB $db)
	{
		global $langs;
		$langs->loadLangs(array("altemailing@altemailing"));
		$this->db = $db;
		
		$this->fields = array(
			'fk_campagne'         => array('type'=>'integer:Campagne:altemailing/class/user.class.php', 'label'=>'Campagne', 'enabled'=>1, 'visible'=>0, 'noteditable'=>1, 'notnull'=> 1, 'index'=>1, 'position'=>1, 'comment'=>'Id campagn', 'css'=>'left'),
			'code'         => array('type'=>'varchar(12)', 'label'=>'Code', 'enabled'=>1, 'visible'=>1, 'position'=>7, 'searchall'=>0, 'noteditable'=>1/*'css'=>'minwidth300', 'cssview'=>'wordbreak', 'showoncombobox'=>2, 'validate'=>1*/),	
			'url'         => array('type'=>'varchar(256)', 'label'=>'Url', 'enabled'=>1, 'visible'=>0, 'position'=>7, 'searchall'=>0, 'noteditable'=>1, 'showoncombobox'=>3/*'css'=>'minwidth300', 'cssview'=>'wordbreak', 'showoncombobox'=>2, 'validate'=>1*/),	
		);
	}
	
	/**
	 * Load object in memory from the database
	 *
	 * @param int    $id   Id object
	 * @param string $ref  Ref
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null)
	{
		global $conf;
		dol_syslog(get_class($this)."::fetch", LOG_DEBUG);
		$sql = "SELECT t.code, t.url, fk_campagne";
		$sql.= " FROM ".MAIN_DB_PREFIX.$this->table_element." t";
		$sql.= " WHERE t.rowid = ".$id;
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql) > 0) {
				$res = $this->db->fetch_array($resql);

				$this->id = $id;
				$this->code = $res['code'];
				$this->url = $res['url'];
				$this->fk_campagne = $res['fk_campagne'];

				$this->db->free($resql);
				return 1;
			} else {
				$this->error = "No URL found";
				return 0;
			}
		} else {
			dol_print_error($this->db);
			$this->error = $this->db->lasterror;
			$this->errors[] = $this->db->lasterror;
			return -1;
		}
		$this->error = "No URL found";
		return 0;
	}
	
}


/**
 * Class CampagneClics. You can also remove this and generate a CRUD class for lines objects.
 */
class CampagneClics extends CommonObject
{
	
	public $db;
	
	public $table_element = 'v_campagne_detail_clics';
	
	
	
	public function __construct(DoliDB $db)
	{
		global $langs;
		$langs->loadLangs(array("altemailing@altemailing"));
		$this->db = $db;
		$this->fields = array(
			'fk_campagne_lien'         => array('type'=>'integer', 'label'=>'CampagneLien', 'enabled'=>1, 'visible'=>0, 'noteditable'=>1, 'notnull'=> 1, 'index'=>1, 'position'=>1, 'comment'=>'Id campagn link', 'css'=>'left'),
			'fk_campagne'         => array('type'=>'integer:Campagne:altemailing/class/campagne.class.php', 'label'=>'Campagne', 'enabled'=>1, 'visible'=>0, 'noteditable'=>1, 'notnull'=> 1, 'index'=>1, 'position'=>1, 'comment'=>'Id campagn', 'css'=>'left'),
			'fk_soc'        => array('type'=>'integer:Societe:societe/class/societe.class.php', 'label'=>'Thirdparty', 'enabled'=>1, 'visible'=>1, 'notnull'=> 1, 'noteditable'=>1, 'default'=>1, 'index'=>1, 'position'=>2),
			'datenvoi'           => array('type'=>'datetime', 'label'=>'DateEnvoi', 'enabled'=>1, 'visible'=>1, 'notnull'=> 1, 'position'=>1),
			'datclic'           => array('type'=>'datetime', 'label'=>'DateClic', 'enabled'=>1, 'visible'=>1, 'notnull'=> 1, 'position'=>2),
			//'nom'           => array('type'=>'varchar(100)', 'label'=>'Name', 'enabled'=>1, 'visible'=>1, 'noteditable'=>1, 'notnull'=> 1, 'index'=>3, 'position'=>3, 'searchall'=>1, 'comment'=>'Thirdparty name', 'validate'=>1),
			//'name_alias'           => array('type'=>'varchar(100)', 'label'=>'Name alias', 'enabled'=>1, 'visible'=>0, 'noteditable'=>1, 'notnull'=> 1, 'index'=>4, 'position'=>4, 'searchall'=>1, 'comment'=>'Thirdparty name alias'),
			'code'         => array('type'=>'varchar(12)', 'label'=>'Code', 'enabled'=>1, 'visible'=>1, 'position'=>5, 'searchall'=>1, 'noteditable'=>1/*'css'=>'minwidth300', 'cssview'=>'wordbreak', 'showoncombobox'=>2, 'validate'=>1*/),	
			'url'         => array('type'=>'integer:CampagneLien:altemailing/class/campagne.class.php', 'label'=>'Url', 'enabled'=>1, 'visible'=>1, 'position'=>6, 'searchall'=>0, 'search'=>0, 'noteditable'=>1/*'css'=>'minwidth300', 'cssview'=>'wordbreak', 'showoncombobox'=>2, 'validate'=>1*/),	
			'email'         => array('type'=>'varchar(256)', 'label'=>'Email', 'enabled'=>1, 'visible'=>1, 'position'=>7, 'searchall'=>1, 'noteditable'=>1/*'css'=>'minwidth300', 'cssview'=>'wordbreak', 'showoncombobox'=>2, 'validate'=>1*/),	
			'phone'         => array('type'=>'varchar(20)', 'label'=>'Phone', 'enabled'=>1, 'visible'=>1, 'position'=>8, 'searchall'=>1, 'noteditable'=>1/*'css'=>'minwidth300', 'cssview'=>'wordbreak', 'showoncombobox'=>2, 'validate'=>1*/),	
			'address' =>array('type'=>'varchar(255)', 'label'=>'Address', 'enabled'=>1, 'visible'=>-2, 'position'=>9, 'searchall'=>1),
			'zip' =>array('type'=>'varchar(10)', 'label'=>'Zip', 'enabled'=>1, 'visible'=>-2, 'position'=>10, 'searchall'=>1),
			'town' =>array('type'=>'varchar(50)', 'label'=>'Town', 'enabled'=>1, 'visible'=>-2, 'position'=>11, 'searchall'=>1),
			'fk_departement' =>array('type'=>'sellist:c_departements:nom:rowid', 'label'=>'State', 'position'=>12, 'enabled'=>1, 'visible'=>-2),
			'fk_pays' =>array('type'=>'sellist:c_country:label:rowid', 'label'=>'Country', 'position'=>13, 'enabled'=>1, 'visible'=>-2),
		);
	}
	
	/**
	 * Return HTML string to show a field into a page
	 * Code very similar with showOutputField of extra fields
	 *
	 * @param  array   $val		       Array of properties of field to show
	 * @param  string  $key            Key of attribute
	 * @param  string  $value          Preselected value to show (for date type it must be in timestamp format, for amount or price it must be a php numeric value)
	 * @param  string  $moreparam      To add more parametes on html input tag
	 * @param  string  $keysuffix      Prefix string to add into name and id of field (can be used to avoid duplicate names)
	 * @param  string  $keyprefix      Suffix string to add into name and id of field (can be used to avoid duplicate names)
	 * @param  mixed   $morecss        Value for css to define size. May also be a numeric.
	 * @return string
	 */
	public function showOutputField($val, $key, $value, $moreparam = '', $keysuffix = '', $keyprefix = '', $morecss = '')
	{
		global $langs;
		dol_syslog(get_class($this)."::showOutputField", LOG_DEBUG);
		if ($key == 'code') {
			$lien = new CampagneLien($this->db);
			$lien->fetch($this->fk_campagne_lien);
			return '<a href="'.$lien->url.'">'.$lien->code.'<a>';
		}
		if ($key == 'url') {
			$lien = new CampagneLien($this->db);
			$lien->fetch($this->fk_campagne_lien);
			return '<a href="'.$lien->url.'">'.$lien->url.'<a>';
		}
		return parent::showOutputField($val, $key, $value, $moreparam, $keysuffix, $keyprefix, $morecss);
	}
}

/**
 * Class CampagneLink. 
 */
class CampagneLink extends CommonObjectLine
{
	
	public $db;
	
	public $code;
	
	public $url;
	
	public $id;
	
	public $fk_campagne;
	
	public $table_element = 'campagne_liens';
	
	
	public function __construct(DoliDB $db)
	{
		global $langs;
		$langs->loadLangs(array("altemailing@altemailing"));
		$this->db = $db;
		$this->fields = array(
			'fk_campagne'         => array('type'=>'integer:Campagne:altemailing/class/user.class.php', 'label'=>'Campagne', 'enabled'=>1, 'visible'=>0, 'noteditable'=>1, 'notnull'=> 1, 'index'=>1, 'position'=>1, 'comment'=>'Id campagn', 'css'=>'left'),
			'code'           => array('type'=>'varchar(100)', 'label'=>'Code', 'enabled'=>1, 'visible'=>1, 'noteditable'=>0, 'notnull'=> 1, 'index'=>3, 'position'=>3, 'searchall'=>1, 'comment'=>'Thirdparty name', 'validate'=>1),
			'url'           => array('type'=>'varchar(300)', 'label'=>'URL', 'enabled'=>1, 'visible'=>1, 'noteditable'=>0, 'notnull'=> 1, 'index'=>4, 'position'=>4, 'searchall'=>1, 'comment'=>'Thirdparty name alias'),
		);
	}
	
	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		global $conf;
		dol_syslog(get_class($this)."::create", LOG_DEBUG);
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."campagne_liens(fk_campagne, code, url, fk_user_author) VALUES (";
		$sql.= $this->fk_campagne;
		$sql.= ", '".$this->code."'";
		$sql.= ", '".$this->url."'";
		$sql.= ", ".$user->id.")";
		$this->db->begin();
		$res = $this->db->query($sql);
		if (!$res) {
			$error++;
			if ($this->db->lasterrno() == 'DB_ERROR_RECORD_ALREADY_EXISTS') {
				$this->errors[] = "ErrorRefAlreadyExists";
			} else {
				$this->errors[] = $this->db->lasterror();
			}
		}
		if (!$error) {
			$this->id = $this->db->last_insert_id($this->db->prefix().$this->table_element);
		}
		// Triggers
		if (!$error && !$notrigger) {
			// Call triggers
			$result = $this->call_trigger(strtoupper(get_class($this)).'_CREATE', $user);
			if ($result < 0) {
				$error++;
			}
			// End call triggers
		}
		// Commit or rollback
		if ($error) {
			foreach ($this->errors as $errmsg) {
				dol_syslog(get_class($this)."::create ".$errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
			}
			$this->db->rollback();
			return -1;
		} else {
			$this->db->commit();
			return $this->id;
		}
	}
	
	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = false)
	{
		dol_syslog(get_class($this)."::update", LOG_DEBUG);
		$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element." SET";
		$sql.= " url = '".$this->url."'";
		$sql.= ", code = '".$this->code."'";
		$sql.= ", fk_user_modif = ".$user->id;
		$sql.= " WHERE rowid = ".$this->id;
		$this->db->begin();
		$resql = $this->db->query($sql);

		if (!$resql) {
			$error++;

			if ($this->db->errno() == 'DB_ERROR_RECORD_ALREADY_EXISTS') {
				$this->errors[] = $langs->trans('ErrorRefAlreadyExists');
			} else {
				$this->errors[] = "Error ".$this->db->lasterror();
			}
		}

		if (!$error) {
			if (!$notrigger) {
				// Call trigger
				$result = $this->call_trigger(strtoupper(get_class($this)).'_UPDATE', $user);
				if ($result < 0) {
					$error++;
				}
				// End call triggers
			}
		}

		// Commit or rollback
		if ($error) {
			foreach ($this->errors as $errmsg) {
				dol_syslog(get_class($this)."::update ".$errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
			}
			$this->db->rollback();
			return -1 * $error;
		} else {
			$this->db->commit();
			return 1;
		}
	}
}



/**
 * Class CampagneClics. You can also remove this and generate a CRUD class for lines objects.
 */
class CampagneRead extends CommonObject
{
	
	public $db;
	
	public $table_element = 'v_campagne_open';
	
	
	public function __construct(DoliDB $db)
	{
		global $langs;
		$langs->loadLangs(array("altemailing@altemailing"));
		$this->db = $db;
		$this->fields = array(
			'fk_campagne'         => array('type'=>'integer:Campagne:altemailing/class/campagne.class.php', 'label'=>'Campagne', 'enabled'=>1, 'visible'=>0, 'noteditable'=>1, 'notnull'=> 1, 'index'=>1, 'position'=>1, 'comment'=>'Id campagn', 'css'=>'left'),
			'fk_soc'        => array('type'=>'integer:Societe:societe/class/societe.class.php', 'label'=>'Thirdparty', 'enabled'=>1, 'visible'=>1, 'notnull'=> 1, 'noteditable'=>1, 'default'=>1, 'index'=>1, 'position'=>1),
			'dateo'           => array('type'=>'datetime', 'label'=>'DateRead', 'enabled'=>1, 'visible'=>1, 'notnull'=> 1, 'position'=>2),
			'datenvoi'           => array('type'=>'datetime', 'label'=>'DateEnvoi', 'enabled'=>1, 'visible'=>1, 'notnull'=> 1, 'position'=>3),
			'email'         => array('type'=>'varchar(256)', 'label'=>'Email', 'enabled'=>1, 'visible'=>1, 'position'=>7, 'searchall'=>1, 'noteditable'=>1/*'css'=>'minwidth300', 'cssview'=>'wordbreak', 'showoncombobox'=>2, 'validate'=>1*/),	
			
		);
	}
}

