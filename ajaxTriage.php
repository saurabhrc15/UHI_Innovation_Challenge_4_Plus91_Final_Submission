<?php
include_once dirname(__FILE__).DIRECTORY_SEPARATOR."config/config.php";
include_once dirname(__FILE__).DIRECTORY_SEPARATOR."funTriagingManagement.php";

$sFlag = Input::request('sFlag',['addslashes' => true]) ? Input::request('sFlag',['addslashes' => true]) : '0';

if ($sFlag == '0') {
	echo false;
}
header("Content-Type: application/json");

//! For fetching available triaging color master..
if ($sFlag == "fGetAvailableTriagingColorMasters"){
	$aTriagingColor = array();

	//! Fetching available triaging colors..
	$aTriagingColor = fGetAvailableTriagingColorMasters();

	echo( json_encode($aTriagingColor));
}

//! For adding new triaging status..
if ($sFlag == "addNewTriagingStatus") {
	$sTriagingName = Input::request('sTriagingName',['addslashes' => true]);
	$iTriagingColor = Input::request('iTriagingColor',['addslashes' => true]);
	$sTriagingDescription = Input::request('sTriagingDescription',['addslashes' => true]);
	$bResult = false;

	//! Adding triaging master..
	$iTriageID = fAddTriagingMaster();

	if ($iTriageID > 0) {
		//! Adding triaging status details..
		$bResult = fAddTriagingDetails($iTriageID,$sTriagingName,$iTriagingColor,$sTriagingDescription);
	}

	echo( json_encode(['result' => $bResult]));
}

//! For fetching triaging listing..
if ($sFlag == "fGetAllTriagingMaster") {
	echo( json_encode(fGetAllTriagingMaster()));
}

//! For freeze/unfreeze triaging status..
if($sFlag == "fFreezeUnfreezeTriagingStatus"){
	$iTriageID = Input::request('iTriageID',['addslashes' => true]);
	$iStatusID = Input::request('iStatusID',['addslashes' => true]);

	$aTraigingDetails = fGetTriagingDetailsByTriageID($iTriageID);

	//! Adding triaging master..
	$iResult = fFreezeUnfreezeTriagingStatus($iTriageID,$iStatusID);

	if ($iResult > 0) {
		$bResult = true;
	} else {
		$bResult = false;
	}

	echo( json_encode(['result' => $bResult]));
}

//! For fetching triage details by triage_id..
if ($sFlag == "fGetTriagingDetailsByTriageID") {
	$iTriageID = Input::request('iTriageID',['addslashes' => true]);
	$aTriaging = array();

	//! Fetching triaging details..
	$aTriaging = fGetTriagingDetailsByTriageID($iTriageID);

	echo( json_encode($aTriaging));
}

//! For fetching rules for triage by id..
if($sFlag == "fGetTriagingRuleCategories"){
	$aCategories = array();

	//! Fetching triaging rule categories..
	$aCategories = fGetTriagingRuleCategories();

	echo( json_encode($aCategories));
}

//! For fetching rules for triage by id..
if($sFlag == "fGetTriageRuleDetails"){
	$iTriageID = Input::request('iTriageID') !== null ? Input::request('iTriageID',['addslashes' => true]) : 0;
	$iLimit = Input::request('iLimit',['addslashes' => true]) !== null ? Input::request('iLimit',['addslashes' => true]) : 20;
	$iOffset = Input::request('iOffset',['addslashes' => true]) !== null ? Input::request('iOffset',['addslashes' => true]) : 0;
	$aRuleDetails = array();

	if($iTriageID > 0){
		$aGroupID = getTriageGroupID($iTriageID,$iLimit,$iOffset);

		if ($aGroupID) {
			$sGroupID = implode(", ", $aGroupID);

			//! Fetching tews scale rule details..
			$aRuleDetails = fGetTriageRuleDetails($iTriageID,$sGroupID);
		}
	}
	
	echo( json_encode($aRuleDetails) );
}

if ($sFlag == "getTriageTotalGroupCount") {
	$iTriageID = Input::request('iTriageID') ? Input::request('iTriageID') : 0;
	$iTotalGroupCount = 0;

	if ($iTriageID > 0) {
		$iTotalGroupCount = count(getTriageGroupID($iTriageID));
	}

	header("Content-Type: application/json");
	echo(json_encode(['total_count' => $iTotalGroupCount]));
}

//! For fetching dynamic vital list..
if ($sFlag == "getAllDynamicVitalList") {
	header("Content-Type: application/json");
	echo( json_encode(fGetAllVitalList()));
}

//! For searching symptoms..
if ($sFlag == "fGetChiefComplaintsByName") {
	$sComplaintName = Input::get('sComplaintName',['addslashes' => true]) ? Input::get('sComplaintName',['addslashes' => true]) : '';
	$aChiefComplaints = array();

	if ($sComplaintName) {
		$aChiefComplaints = fGetChiefComplaintsByName($sComplaintName);
	}

	echo( json_encode($aChiefComplaints));
}

if ($sFlag == "getPrescriptionForTriageGroup") {
	$iTriageGroupID = Input::request('iTriageGroupID',['addslashes' => true]) ? Input::request('iTriageGroupID',['addslashes' => true]) : '';
	$aPrescription = array();

	if ($iTriageGroupID) {
		$aPrescription = getTriageGroupPrescription($iTriageGroupID);
	}

	echo( json_encode($aPrescription));
}

if ($sFlag == "getDiagnosisForTriageGroup") {
	$iTriageGroupID = Input::request('iTriageGroupID',['addslashes' => true]) ? Input::request('iTriageGroupID',['addslashes' => true]) : '';
	$aDiagnosis = array();

	if ($iTriageGroupID) {
		$aDiagnosis = getTriageGroupDiagnosis($iTriageGroupID);
	}

	echo( json_encode($aDiagnosis));
}

//! for generating triage
if ($sFlag == "generateTriage") {
	$iBirthYear = Input::post('idBirthYear') ? Input::post('idBirthYear') : 0;
	$sPatientGender = Input::post('idGender') ? Input::post('idGender') : '';
	$aSymptoms = Input::post('idSymptoms') ? Input::post('idSymptoms') : [];
	$aResponse = [
		'satisfied_triage' => [],
		'possible_prescription' => [],
		'possible_diagnosis' => [],
		'highest_priority_triage' => 0,
	];

	if ($iBirthYear && $sPatientGender && $aSymptoms) {
		//! Get age of patient..
		$dDateOfBirth = "01-06-".$iBirthYear;
		$dToday = date("Y-m-d");
		$oDiff = date_diff(date_create($dDateOfBirth), date_create($dToday));
		$iPatientAge = $oDiff->format('%y');

		$aVitalID = Input::request('idVitalID') ? Input::request('idVitalID') : [];
		$aVitalValue = Input::request('idVitalValue') ? Input::request('idVitalValue') : [];
		$aVitalData = [];
		if ($aVitalID) {
			foreach ($aVitalID as $iIndex => $iVitalID) {
				if ($aVitalValue[$iIndex] && $iVitalID) {
					$aVitalData[$iVitalID] = $aVitalValue[$iIndex];
				}
			}
		}

		//! Fetching triaging rules if exist..
		$aTriagingRules = fGetAllTriageRulesDetails();

		//! checking satisfied triaging rules..
		$aSatisfiedTriage = array();
		$aSatisfiedGroupID = [];
		if(!empty($aTriagingRules)){
			foreach ($aTriagingRules as $iIndex => $aTriagingData) {
				$iTriageID = $aTriagingData['triage_id'];
				$iAgeGroupID = $aTriagingData['age_group_id'];
				$bSatisfy = false;
				foreach ($aTriagingData['rules'] as $iKey => $aGroupRules) {
					$bGroupSatisfy = false;
					foreach ($aGroupRules as $iKeyIndex => $aRules) {
						$iTriageGroupID = $aRules['triage_group_id'];
						$iFromAge = 0;
						$iToAge = 0;
						$sGender = "";
						$bApplyAgeGroup = false;
						$bSatisfiedAgeGroups = false;
						if ($iAgeGroupID > 0) {
							$aAgeGroupDetails = fGetMedicalManagementAgeGroupDetails($iAgeGroupID);
							if(isset($aAgeGroupDetails['iID']) && $aAgeGroupDetails['iID'] > 0 && round($aAgeGroupDetails['iAgeFrom']) <= round($aAgeGroupDetails['iAgeTo'])){
								$bApplyAgeGroup = true;
								$iFromAge = round($aAgeGroupDetails['iAgeFrom']);
								$iToAge = round($aAgeGroupDetails['iAgeTo']);
								$sGender = round($aAgeGroupDetails['sGender']);
							}
						}

						if(round($iPatientAge) >= round($iFromAge) && round($iPatientAge) <= round($iToAge) && $sGender = $sPatientGender){
							$bSatisfiedAgeGroups = true;
						}

						if($aRules['catetory_id'] == 1){ //! Vitals..
							if($aVitalData[$aRules['entity_id']]){
								$sEntity = $aVitalData[$aRules['entity_id']];
								$sOperator = $aRules['operator'];
								$sValue = $aRules['value'];
								switch($sOperator){
								    case ">=":
								    	if($bApplyAgeGroup == true){
								    		if($sEntity >= $sValue && $bSatisfiedAgeGroups == true){
									            $bGroupSatisfy = true; //! if rule satisfied with selected vital..
									        }else{
									        	$bGroupSatisfy = false; //! if rule not satisfied with selected vital..
									        	break;
									        }
								    	}else{
									        if($sEntity >= $sValue){
									            $bGroupSatisfy = true; //! if rule satisfied with selected vital..
									        }else{
									        	$bGroupSatisfy = false; //! if rule not satisfied with selected vital..
									        	break;
									        }
								    	}
								    break;
								    case "<=":
								    	if($bApplyAgeGroup == true){
									        if($sEntity <= $sValue  && $bSatisfiedAgeGroups == true){
									            $bGroupSatisfy = true; //! if rule satisfied with selected vital..
									        }else{
									        	$bGroupSatisfy = false; //! if rule not satisfied with selected vital..
									        	break;
									        }
									    }else{
									    	if($sEntity <= $sValue){
									            $bGroupSatisfy = true; //! if rule satisfied with selected vital..
									        }else{
									        	$bGroupSatisfy = false; //! if rule not satisfied with selected vital..
									        	break;
									        }
									    }
								    break;
								    case "=":
								    	if($bApplyAgeGroup == true){
								    		if($sEntity == $sValue && $bSatisfiedAgeGroups == true){
									            $bGroupSatisfy = true; //! if rule satisfied with selected vital..
									        }else{
									        	$bGroupSatisfy = false; //! if rule not satisfied with selected vital..
									        	break;
									        }
								    	}else{
									        if($sEntity == $sValue){
									            $bGroupSatisfy = true; //! if rule satisfied with selected vital..
									        }else{
									        	$bGroupSatisfy = false; //! if rule not satisfied with selected vital..
									        	break;
									        }							    		
								    	}
								    break;
								    case "!=":
								    	if($bApplyAgeGroup == true){
								    		if($sEntity != $sValue && $bSatisfiedAgeGroups == true){
									            $bGroupSatisfy = true; //! if rule satisfied with selected vital..
									        }else{
									        	$bGroupSatisfy = false;
									        	break;
									        }
								        }else{
								        	if($sEntity != $sValue){
									            $bGroupSatisfy = true; //! if rule satisfied with selected vital..
									        }else{
									        	$bGroupSatisfy = false; //! if rule not satisfied with selected vital..
									        	break;
									        }
								        }
								    break;
								    case ">":
								    	if($bApplyAgeGroup == true){
								    		if($sEntity > $sValue && $bSatisfiedAgeGroups == true){
									            $bGroupSatisfy = true; //! if rule satisfied with selected vital..
									        }else{
									        	$bGroupSatisfy = false; //! if rule not satisfied with selected vital..
									        	break;
									        }
								        }else{
								        	if($sEntity > $sValue){
									            $bGroupSatisfy = true; //! if rule satisfied with selected vital..
									        }else{
									        	$bGroupSatisfy = false; //! if rule not satisfied with selected vital.. 
									        	break;
									        }
								        }
								    break;
								    case "<":
								    	if($bApplyAgeGroup == true){
								    		if($sEntity < $sValue && $bSatisfiedAgeGroups == true){
									            $bGroupSatisfy = true; //! if rule satisfied with selected vital..
									        }else{
									        	$bGroupSatisfy = false; //! if rule not satisfied with selected vital..
									        	break;
									        }
								        }else{
								        	if($sEntity < $sValue){
									            $bGroupSatisfy = true; //! if rule satisfied with selected vital..
									        }else{
									        	$bGroupSatisfy = false; //! if rule not satisfied with selected vital..
									        	break;
									        }
								        }
								    break;
								    default:
			                        	$bGroupSatisfy = false; //! if rule is not satisfied with selected vital..
			                        	break;
								}
							}else{
								$bGroupSatisfy = false; //! if rule is not satisfied with selected vital..
								break;
							}
						}elseif($aRules['catetory_id'] == 2){ //! Chief Complaints..
							if($bApplyAgeGroup == true){
								if(in_array($aRules['entity_id'],$aSymptoms) && $bSatisfiedAgeGroups == true){
									$bGroupSatisfy = true; //! if rule satisfied with selected chief complaints..
								}else{
									$bGroupSatisfy = false; //! if rule is not satisfied with selected chief complaints..
									break;
								}
							}else{
								if(in_array($aRules['entity_id'],$aSymptoms)){
									$bGroupSatisfy = true; //! if rule satisfied with selected chief complaints..
								}else{
									$bGroupSatisfy = false; //! if rule is not satisfied with selected chief complaints..
									break;
								}							
							}
						}

						if ($bGroupSatisfy == false) {
							break;
						}	
					}

					//! if atleast one group satisfy then true..
					if($bGroupSatisfy){
						$aSatisfiedGroupID[] = $iKey;
						$bSatisfy = true;
					}
				}
				//! if all rules satisfies..
				if($bSatisfy){
					$aSatisfiedTriage[] = $aTriagingRules[$iIndex];
				}
			}
			
			$iSatisfiedTriageID = 0;
			if(!empty($aSatisfiedTriage)){
				if(count($aSatisfiedTriage) == 1){ //! if only triage rule satisfied..
					$iSatisfiedTriageID = $aSatisfiedTriage[0]['triage_id'];
				}elseif(count($aSatisfiedTriage) > 1){ //! if more than one triage rule satisfied..
					$iPriority = array_column($aSatisfiedTriage, 'priority');
					$aHighestPriority = $aSatisfiedTriage[array_search(min($iPriority), $iPriority)];
					$iSatisfiedTriageID = $aHighestPriority['triage_id'];
				}
			}

			if ($iSatisfiedTriageID > 0) {
				$aResponse['satisfied_triage'] = $aSatisfiedTriage;
				$aResponse['highest_priority_triage'] = $iSatisfiedTriageID;
				
				if ($aSatisfiedGroupID) {
					foreach ($aSatisfiedGroupID as $iPIndex => $iTriageGroupID) {
						if ($aPossiblePrescription = getTriageGroupPrescription($iTriageGroupID)) {
							foreach ($aPossiblePrescription as $iKeyP => $sDrugName) {
								if ($sDrugName) {
									$aResponse['possible_prescription'][] = $sDrugName;
								}
							}
						}

						if ($aPossibleDiagnosis = getTriageGroupDiagnosis($iTriageGroupID)) {
							foreach ($aPossibleDiagnosis as $iKeyD => $sDianosisName) {
								if ($sDianosisName) {
									$aResponse['possible_diagnosis'][] = $sDianosisName;
								}
							}
						}
					}
				}
			}
		}	
	}

	echo( json_encode($aResponse) );
}