<?php
/* Copyright (C) 2023      Julien Canipel <jcanipel@alternative-erp.com>all
*/

require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/signature.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/CMailFile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/altemailing/class/campagne.class.php';

class altemailingbatch{

	public $output;
	public $nbMailSend = 0;
	public $nbMailKo = 0;
	public $elemok = 0;
	public $elemko = 0;
	public $mail_subject = "";
	public $mail_body = "";
	
	/*
		Complète le mail de rapport et enregistre dans les logs
	*/
	function completeMailBody($message, $type){
		$this->mail_body .= dol_print_date(dol_now(),'%d/%m/%Y %H:%M:%S')." ".$type." : ".$message."<br>";
		dol_syslog($type." : ".$message);
	}
	
	/* Automatise la substitution template email et effectue l'envoi */
	function sendMail($mailtemplate, $obj, $to, $from, $type, $listofpaths,  $listofmimes, $listofnames, $errors_to, $trackid, $cc){
		global $db,$user,$langs,$conf;
		$formmail = new FormMail($db);
		$langs->loadLangs(array("main", "bills"));
		$arraymessage = $formmail->getEMailTemplate($db, $type, $user, $langs, (is_numeric($mailtemplate) ? $mailtemplate : 0), 1, (is_numeric($template) ? '' : $template));
		if (is_numeric($arraymessage) && $arraymessage <= 0) {
			$langs->load("errors");
			$this->output .= $langs->trans('ErrorFailedToFindEmailTemplate', $mailtemplate)."<br>";
			$this->completeMailBody("Erreur ".$langs->trans('ErrorFailedToFindEmailTemplate'),"ERREUR");
			return -1;
		}
		$substitutionarray = getCommonSubstitutionArray($langs, 0, '', $obj);
		complete_substitutions_array($substitutionarray, $langs, $obj);
		// Topic
		$sendTopic = make_substitutions(empty($arraymessage->topic) ? $langs->transnoentitiesnoconv('InformationMessage') : $arraymessage->topic, $substitutionarray, $langs, 0);
		// Content
		$content = $langs->transnoentitiesnoconv($arraymessage->content);
		$sendContent = make_substitutions($content, $substitutionarray, $langs, 0);
		$sendContent .= "<br><br><br><br><br><br><br><br><br><br>";
		$sendContent .= '<img src="'.$conf->global->ALTEMAILING_URL_PUBLIC.'index.php?action=pixel&id='.$obj->trackid.'" style="width="1px" height="1px">';
		$this->completeMailBody("Envoi mail destinataire : ".$to." objet = ".$sendTopic, "INFO");
		if($cc != ""){
			$this->completeMailBody("Envoi mail copie : ".$cc, "INFO");
		}
		$cMailFile = new CMailFile($sendTopic, $to, $from, $sendContent, $listofpaths,  $listofmimes, $listofnames, $cc, "", 0, 1, $errors_to, '', $trackid, '', '', '');

		// Sending Mail
		if ($cMailFile->sendfile()) {
			$this->completeMailBody("Envoyé OK", "INFO");
			$this->nbMailSend++;
			return 200;
		} else {
			$this->output .= "ALTEMAILING sendMailError = ".$cMailFile->error.' : '.$email."<br>";
			$this->completeMailBody("Envoyé KO ".$cMailFile->error, "ERREUR");
			$this->nbMailKo++;
			return $cMailFile->error;
		}
	}
	
	function TraiterCampagnes(){
		global $db, $user, $langs, $conf;
		
		$langs->loadLangs(array("altemailing@altemailing", "bills"));
		$this->mail_subject = "Rapport campagne mailing du ".dol_print_date(dol_now(),'%d/%m/%Y %H:%M');
		$this->completeMailBody("Mailing campagne DEBUT", "INFO");
		
		$sql = "select rowid from ".MAIN_DB_PREFIX."campagne c";
		$sql .= " where entity = ".$conf->entity;
		$sql .= " and c.datdeb <= sysdate()";
		$sql .= " and (c.datfin >= sysdate() or c.datfin is null)";
		$sql .= " and status = ".Campagne::STATUS_VALIDATED;
		$sql .= " ORDER by rowid";
		//print $sql;
		$i = 0;
		$resql = $db->query($sql);
		if ($resql) {
			$num = $db->num_rows($resql);
			if ($num) {
				$this->completeMailBody($num." campagne(s) trouvée(s)", "INFO");
				while ($i < $num) {
					$obj = $db->fetch_object($resql);
					$this->completeMailBody("traitement campagne n°".$obj->rowid, "INFO");
					$retour = $this->execCampagne($obj->rowid);
					if($retour > 0){
						$this->completeMailBody("Campagne ".$obj->rowid." traitée sans erreur", "INFO");
						$this->elemok++;
					}else{
						$this->completeMailBody("Campagne ".$obj->rowid." rien à traiter", "INFO");
						$this->elemko++;
					}
					$i++;
				}
			}else{
				$this->completeMailBody("Aucune campagne trouvée", "INFO");
			}
		}		
		
		$this->completeMailBody("Mailing campagne FIN", "INFO");
		$retour = 0;
		//Mail raport
		if($conf->global->ALTEMAILING_SEND_LOG_TO){
			$from = $conf->global->MAIN_MAIL_EMAIL_FROM;
			if (empty($from)) {
				$errormesg = "Failed to get sender into global setup MAIN_MAIL_EMAIL_FROM<br>";
				$this->output .=  "Failed to get sender into global setup MAIN_MAIL_EMAIL_FROM<br>";
				$error++;
			}
			$errors_to = $conf->global->MAIN_MAIL_ERRORS_TO;
			$trackid = 'batch_trt_campagnes'.dol_print_date(dol_now(),'%d/%m/%Y %H:%M:%S');
			$cMailFile = new CMailFile($this->mail_subject, $conf->global->ALTEMAILING_SEND_LOG_TO, $from, $this->mail_body, '',  '', '', '', "", 0, 1, $errors_to, '', $trackid, '', '', '');
			// Sending Mail
			if ($cMailFile->sendfile()) {
				$this->nbMailSend++;
			} else {
				$this->output .= "sendMailError = ".$cMailFile->error.' : '.$email."<br>";
				$this->nbMailKo++;
				$retour = -1;
			}
		}
		
		$message = $i." campagne(s) parcourue(s)<br>";
		if($this->nbMailSend > 0){
			$message .= $this->nbMailSend." email(s) envoyé(s)<br>";
		}
		if($this->nbMailKo > 0){
			$message .= $this->nbMailKo." email(s) KO<br>";
		}
		$message .= $this->output;
		$this->output = $message;
		return $retour;
	}
	
	function execCampagne($id){
		
		global $db, $conf, $langs;
		$campagne = new Campagne($db);
		$campagne->fetch($id);
		$campagne->fetchThirdparties("4send");
		$sendid = uniqid(dol_now()."c".$campagne->id);		
		$num = count($campagne->thirdparties);
		$this->completeMailBody($num." Tiers liés", "INFO");
		$i = 0;
		while($i < $num){
			$soc = $campagne->thirdparties[$i];
			$uniqid = uniqid(dol_now()."c".$campagne->id."s".$soc->id);		
			$this->completeMailBody("Process tiers ".$soc->name." / id = ".$soc->id, "INFO");
			// Errors Recipient
			$errors_to = $conf->global->MAIN_MAIL_ERRORS_TO;
			// Recipient
			$to = $soc->email;
			if($conf->global->ALTEMAILING_SEND_ALL_TO){
				$to = $conf->global->ALTEMAILING_SEND_ALL_TO;
			}
			if($to){
				$trackid = $uniqid;
				$soc->trackid = $trackid;
				$soc->campagne = $campagne;
				$send = $this->sendMail($campagne->fk_email_template,$soc,$to,$conf->global->MAIN_MAIL_EMAIL_FROM,'thirdparty',$listofpaths,  $listofmimes, $listofnames, $errors_to, $trackid, $cc);
				$campagne->histoEnvoi($to, $uniqid, $sendid, $send, $soc->id, false);
			}else{
				$this->completeMailBody("Ignoré, pas d'e-mail renseigné sur le tiers.", "INFO");
			}
			$i++;
		}
		return $i;
	}	
}


?>