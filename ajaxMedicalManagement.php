<?php
include_once dirname(__FILE__).DIRECTORY_SEPARATOR."config/config.php";
include_once dirname(__FILE__).DIRECTORY_SEPARATOR."funTriagingManagement.php";

$sFlag = Input::request('sFlag') !== null ? Input::request('sFlag',['addslashes' => true]) : '0';

if ($sFlag == '0') {
	echo false;
}

header("Content-Type: application/json");

//! For adding new age group..
if ($sFlag == "addNewMedicalManagementAgeGroup") {
	$sAgeGroupName = Input::request('sAgeGroupName') !== null ? Input::request('sAgeGroupName',['addslashes' => true]) : '';
	$iAgeFrom = Input::request('iAgeFrom') !== null ? Input::request('iAgeFrom',['addslashes' => true]) : 0;
	$iAgeTo = Input::request('iAgeTo') !== null ? Input::request('iAgeTo',['addslashes' => true]) : 0;
	$sGender = Input::request('sGender') !== null ? Input::request('sGender',['addslashes' => true]) : 0;
	$bResult = false;

	$iResultID = fAddMedicalManagementAgeGroup($sAgeGroupName,$iAgeFrom,$iAgeTo,$sGender);

	if ($iResultID > 0) {
		$bResult = true;
	}

	echo( json_encode(['result' => $bResult]) );
}

//! For fetching all age groups..
if ($sFlag == "fGetAllMedicalManagementAgeGroups") {
	$aAgeGroups = array();

	$aAgeGroups = fGetAllMedicalManagementAgeGroup();

	echo( json_encode($aAgeGroups) );
}

//! For deleting age group..
if ($sFlag == "deleteMedicalManagementAgeGroup") {
	$iID = Input::request('iID') !== null ? Input::request('iID',['addslashes' => true]) : 0;

	$iResultID = invalidateMedicalManagementAgeGroup($iID);

	echo( json_encode($iResultID) );
}