<?php
include_once dirname(__FILE__).DIRECTORY_SEPARATOR."config/config.php";
include_once dirname(__FILE__).DIRECTORY_SEPARATOR."funTriagingManagement.php";

$iTriageID = Input::post('idTriageID',['addslashes' => true]) !== null ? Input::post('idTriageID',['addslashes' => true]) : "";
$sTriagingName = Input::post('idTriagingName',['addslashes' => true]) !== null ? Input::post('idTriagingName',['addslashes' => true]) : "";
$iTriagingColorID = Input::post('idTriagingColor',['addslashes' => true]) !== null ? Input::post('idTriagingColor',['addslashes' => true]) : "";
$sTriagingDescription = Input::post('idTriagingDescription',['addslashes' => true]) !== null ? Input::post('idTriagingDescription',['addslashes' => true]) : "";
$aNoOfGroups = Input::post('idTrigingGroupIndex',['addslashes' => true]);
$sTriageGroupID = Input::post('idTriagingGroupID') ? implode(", ", Input::post('idTriagingGroupID')) : "0";

if($iTriageID > 0){
	//! invalidate previous details for triaging..
	invalidateTriagingStatusDetails($iTriageID);

	//! Adding triaging details..
	$iResult = fAddTriagingDetails($iTriageID,$sTriagingName,$iTriagingColorID,$sTriagingDescription);

	if($iResult < 0){
		header('Location:manageTriagingStatus.php?iTriageID='.$iTriageID);
	}

	//! Adding rules for triaging..
	if(!empty($aNoOfGroups)){
		//! invalidate previous triage group master..
		invalidateTriagePreviousGroupMaster($iTriageID, $sTriageGroupID);

		//! invalidate previous triage rule master..
		invalidateTriagePreviousRuleMaster($iTriageID, $sTriageGroupID);

		//! invalidate previous triage rule details..
		invalidateTriagePreviousRuleDetails($iTriageID, $sTriageGroupID);

		//! invalidate previous prescription..
		invalidateTriagingPrescriptionDetails($sTriageGroupID);

		//! invalidate previous diagnosis..
		invalidateTriagingDiagnosisDetails($sTriageGroupID);

		foreach ($aNoOfGroups as $iIndex => $iGroupNo) {
			$aCategoryID = Input::post('idTriagingCategory_'.$iGroupNo,['addslashes' => true]);
			$iAgeGroupID = Input::post('idAgeGroup_'.$iGroupNo,['addslashes' => true]) ? Input::post('idAgeGroup_'.$iGroupNo,['addslashes' => true]) : 0;
			$aPrescription = Input::post('idPossibleDrugsName_'.$iGroupNo,['addslashes' => true]) ? Input::post('idPossibleDrugsName_'.$iGroupNo,['addslashes' => true]) : [];
			$aDiagnosis = Input::post('idPossibleDiagnosisName_'.$iGroupNo,['addslashes' => true]) ? Input::post('idPossibleDiagnosisName_'.$iGroupNo,['addslashes' => true]) : [];

			//! Vitals..
			$aVitalID = Input::post('idTriagingVitalName_'.$iGroupNo,['addslashes' => true]);			
			$aVitalOperator = Input::post('idTriagingVitalOperator_'.$iGroupNo,['addslashes' => true]);			
			$aVitalValue = Input::post('idTriagingVitalValue_'.$iGroupNo,['addslashes' => true]);			

			//! Chief complaints..
			$aChiefComplaints = Input::post('idTriagingChiefComplaintName_'.$iGroupNo,['addslashes' => true]);
			$aChiefComplaints = array_unique($aChiefComplaints);
			$aChiefComplaints = array_values($aChiefComplaints);

			if($iTriageID > 0 && !empty($aCategoryID)){
				//! Adding triage group master..
				$iTriageGroupID = fAddTriageGroupMaster($iTriageID,$iAgeGroupID);

				if($iTriageGroupID > 0){
					//! Adding rules details for vitals..
					if(!empty($aVitalID)){
						foreach ($aVitalID as $iRuleIndex => $iEntityID) {
							$sOperator = $aVitalOperator[$iRuleIndex] ? $aVitalOperator[$iRuleIndex] : '';
							$sValue = $aVitalValue[$iRuleIndex] ? $aVitalValue[$iRuleIndex] : '';

							//! Adding triage group master..
							$iRuleID = fAddTriageRuleMaster($iTriageGroupID,$iTriageID);

							$iTriageCategoryID = 1; //! For Vitals..
							if ($sOperator == "eq") {
								$sOperator = "=";
							} else if ($sOperator == "gteq") {
								$sOperator = ">=";
							} else if ($sOperator == "gt") {
								$sOperator = ">";
							} else if ($sOperator == "lteq") {
								$sOperator = "<=";
							} else if ($sOperator == "lt") {
								$sOperator = "<";
							} else if ($sOperator == "neq") {
								$sOperator = "!=";
							}
							
							//! Validation..
							if($sValue != "" && $sOperator != ""){
								//! Adding rule details..
								$iResult = fAddTriageRuleDetails($iRuleID,$iTriageGroupID,$iTriageID,$iTriageCategoryID,$iEntityID,$sOperator,$sValue);

								if ($iResult < 0){
									header('Location:manageTriagingStatus.php?iTriageID='.$iTriageID);		
								}
							}
						}						
					}

					//! Adding rules details for chief complaints..
					if(!empty($aChiefComplaints)){
						//! Adding triage group master..
						$iRuleID = fAddTriageRuleMaster($iTriageGroupID,$iTriageID);

						foreach ($aChiefComplaints as $iKey => $iEntityID) {
							if($iEntityID > 0){
								$iTriageCategoryID = 2; //! For Chief complaints..
								$sOperator = "";
								$sValue = "";
								$iResult = fAddTriageRuleDetails($iRuleID,$iTriageGroupID,$iTriageID,$iTriageCategoryID,$iEntityID,$sOperator,$sValue);

								if($iResult < 0){
									header('Location:manageTriagingStatus.php?iTriageID='.$iTriageID);		
								}
							}
						}						
					}

					if ($aPrescription) {
						$sPrescription = json_encode($aPrescription);
						
						if ($sPrescription) {
							addTriagePrescription($iTriageID, $iTriageGroupID, $sPrescription);
						}
					}

					if ($aDiagnosis) {
						$sDiagnosis = json_encode($aDiagnosis);
						if ($sDiagnosis) {
							addTriageDiagnosis($iTriageID, $iTriageGroupID, $sDiagnosis);
						}
					}
				}else{
					header('Location:manageTriagingStatus.php?iTriageID='.$iTriageID);
				}				
			}
		}
	}
}else{
	header('Location:manageTriagingStatus.php?iTriageID='.$iTriageID);
}

header('Location:manageTriagingStatus.php?iTriageID='.$iTriageID);
