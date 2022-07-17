<?php
//! Include configEhr.php file for constant variable values.
include_once dirname(__FILE__).DIRECTORY_SEPARATOR."config/config.php";
include_once dirname(__FILE__).DIRECTORY_SEPARATOR."classes/class.DBConnManager.php";

//! Function for fetching all available triaging color combinations..
function fGetAvailableTriagingColorMasters(){
    $aTriagingColor = array();
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();
    
    $sTriagingColorMaster = DATABASE_TABLE_PREFIX.'_triaging_color_master';
    $sTriagingDetails = DATABASE_TABLE_PREFIX.'_triaging_details';

    $sQuery = "SELECT 
				    `color_id`, `color_name`,`hexadecimal_code`,`is_default`
				FROM
				    `{$sTriagingColorMaster}`
				WHERE
				    `deleted` = 0
				        AND `color_id` NOT IN (SELECT 
				            `color_id`
				        FROM
				            `{$sTriagingDetails}`
				        WHERE
				            `deleted` = 0)";
				            
    $sSQueryR = $conn->query($sQuery);
    if($sSQueryR!==FALSE){
        while($aRow = $sSQueryR->fetch_assoc()){
            $aTriagingColor[] = $aRow;
        }
    }

    return $aTriagingColor;
}

//! Function for fetching all triagings..
function fGetAllTriagingMaster($aFilter = []){
    $aTriaging = array();
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();
    
    $sTriagingMaster = DATABASE_TABLE_PREFIX.'_triaging_master';
    $sTriagingDetails = DATABASE_TABLE_PREFIX.'_triaging_details';
    $sTriagingColorMaster = DATABASE_TABLE_PREFIX.'_triaging_color_master';

    $sQuery = "SELECT 
				    *
				FROM
				    `{$sTriagingMaster}` AS A 
				    	LEFT JOIN 
				    `{$sTriagingDetails}` AS B ON B.`triage_id` = A.`triage_id`
				    	LEFT JOIN
				    `{$sTriagingColorMaster}` AS C ON C.`color_id` = B.`color_id`
				WHERE
				    A.`deleted` = 0
				    	AND B.`deleted` = 0
				    	AND C.`deleted` = 0";
				            
    $sSQueryR = $conn->query($sQuery);
    if($sSQueryR!==FALSE){
        while($aRow = $sSQueryR->fetch_assoc()){
            $aTriaging[] = $aRow;
        }
    }

    return $aTriaging;
}

//! Function for adding new triaging master..
function fAddTriagingMaster($iAddedBy=0){
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();

    $sTable = DATABASE_TABLE_PREFIX.'_triaging_master';
    $dAddedOn = date("Y-m-d H:i:s");
    $iTriageID = 0;

    $sIQuery = "INSERT INTO `{$sTable}` (`triage_id`, `added_by`, `added_on`, `freeze`, `deleted`)
            VALUES (NULL,'{$iAddedBy}','{$dAddedOn}',0,0)";
    
    if($conn != false){
        $sIQueryR = $conn->query($sIQuery);
        if($sIQueryR > 0){
            $iTriageID = $conn->insert_id;
        }
    }

    return $iTriageID;
}

//! Function for adding new triaging details..
function fAddTriagingDetails($iTriageID,$sTriagingName,$sTriagingColorID,$sTriagingDescription,$iAddedBy=0){
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();

    $sTable = DATABASE_TABLE_PREFIX.'_triaging_details';
    $dAddedOn = date("Y-m-d H:i:s");
    $iInsertID = 0;

    $sIQuery = "INSERT INTO `{$sTable}` (`id`,`triage_id`, `triage_name`, `color_id`, `description`, `added_by`, `added_on`, `deleted`)
            VALUES (NULL,'{$iTriageID}','{$sTriagingName}','{$sTriagingColorID}','{$sTriagingDescription}','{$iAddedBy}','{$dAddedOn}',0)";
    
    if($conn != false){
        $sIQueryR = $conn->query($sIQuery);
        if($sIQueryR > 0){
            $iInsertID = $conn->insert_id;
        }
    }

    return $iInsertID;
}

//! Invalidate previous details of triaging status..
function invalidateTriagingStatusDetails($iTriageID){
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();
    
    $sTable = DATABASE_TABLE_PREFIX.'_triaging_details';

    $sQuery = "UPDATE `{$sTable}` SET `deleted` = 1 where `triage_id`= '{$iTriageID}' AND `deleted` = 0";

    $rResult = $conn->query($sQuery);
    if($rResult){
        return true;
    }else{
        return false;
    }
}

//! brief function to freeze/unfreeze
function fFreezeUnfreezeTriagingStatus($iTriageID,$iStatusID){

    $iUpdateID = 0;
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();
    
    $sTable = DATABASE_TABLE_PREFIX.'_triaging_master';

    $sUQuery = "UPDATE `{$sTable}` SET `freeze` = '{$iStatusID}' WHERE `triage_id` = '{$iTriageID}' AND `deleted` = 0";

    if($conn != false){

        $sUQueryR = $conn->query($sUQuery);
        if($sUQueryR!==FALSE){
            $iUpdateID = 1;
        }
    }

    return $iUpdateID;
}

//! Function for fetching triaging status by triage_id..
function fGetTriagingDetailsByTriageID($iTriageID){
    $aTriaging = array();
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();
    
    $sTriagingMaster = DATABASE_TABLE_PREFIX.'_triaging_master';
    $sTriagingDetails = DATABASE_TABLE_PREFIX.'_triaging_details';
    $sTriagingColorMaster = DATABASE_TABLE_PREFIX.'_triaging_color_master';

    $sQuery = "SELECT 
                    *
                FROM
                    `{$sTriagingMaster}` AS A 
                        LEFT JOIN 
                    `{$sTriagingDetails}` AS B ON B.`triage_id` = A.`triage_id`
                        LEFT JOIN
                    `{$sTriagingColorMaster}` AS C ON C.`color_id` = B.`color_id`
                WHERE
                    A.`triage_id` = '{$iTriageID}'
                        AND A.`deleted` = 0
                        AND B.`deleted` = 0
                        AND C.`deleted` = 0";
                            
    $sSQueryR = $conn->query($sQuery);
    if($sSQueryR!==FALSE){
        while($aRow = $sSQueryR->fetch_assoc()){
            $aTriaging = $aRow;
        }
    }

    return $aTriaging;
}

//! Function for fetching triaging rule categories..
function fGetTriagingRuleCategories(){
    $aTriagingCategories = array();
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();
    
    $sTable = DATABASE_TABLE_PREFIX.'_triaging_category_rule_master';

    $sQuery = "SELECT 
                    `triaging_category_id`, `category_name`
                FROM
                    `{$sTable}`
                WHERE
                    `deleted` = 0";
                            
    $sSQueryR = $conn->query($sQuery);
    if($sSQueryR!==FALSE){
        while($aRow = $sSQueryR->fetch_assoc()){
            $aTriagingCategories[] = $aRow;
        }
    }

    return $aTriagingCategories;
}


//! Function for adding new triaging rule master..
function fAddTriagingRuleMaster($iTriageID,$iAgeGroupID,$iAddedBy=0){
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();

    $sTable = DATABASE_TABLE_PREFIX.'_triaging_rules_master';
    $dAddedOn = date("Y-m-d H:i:s");
    $iTriageGroupID = 0;

    $sIQuery = "INSERT INTO `{$sTable}` (`triage_group_id`, `triage_id`, `age_group_id`, `added_by`, `added_on`, `deleted`)
            VALUES (NULL,'{$iTriageID}','{$iAgeGroupID}','{$iAddedBy}','{$dAddedOn}',0)";
    
    if($conn != false){
        $sIQueryR = $conn->query($sIQuery);
        if($sIQueryR > 0){
            $iTriageGroupID = $conn->insert_id;
        }
    }

    return $iTriageGroupID;
}

//! Invalidate previous details of triaging rule master..
function invalidateTriagingRuleMaster($iTriageID){
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();
    
    $sTable = DATABASE_TABLE_PREFIX.'_triaging_rules_master';

    $sQuery = "UPDATE `{$sTable}` SET `deleted` = 1 where `triage_id`= '{$iTriageID}' AND `deleted` = 0";

    $rResult = $conn->query($sQuery);
    if($rResult){
        return true;
    }else{
        return false;
    }
}

//! Function for adding new triaging rule master..
function fAddTriagingRuleDetails($iTriageGroupID,$iTriageID,$iTriageCategoryID,$sEntity,$sOperator,$sValue,$iAddedBy=0){
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();

    $sTable = DATABASE_TABLE_PREFIX.'_triaging_rules_details';
    $dAddedOn = date("Y-m-d H:i:s");
    $iInsertID = 0;

    $sIQuery = "INSERT INTO `{$sTable}` (`id`, `triage_group_id`, `triage_id`, `triaging_category_id`, `entity`, `operator`, `value`, `added_by`, `added_on`, `deleted`)
            VALUES (NULL,'{$iTriageGroupID}',{$iTriageID},'{$iTriageCategoryID}','{$sEntity}','{$sOperator}','{$sValue}','{$iAddedBy}','{$dAddedOn}',0)";
    
    if($conn != false){
        $sIQueryR = $conn->query($sIQuery);
        if($sIQueryR > 0){
            $iInsertID = $conn->insert_id;
        }
    }

    return $iInsertID;
}

//! Invalidate previous of triaging rule details..
function invalidateTriagingRuleDetails($iTriageID){
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();
    
    $sTable = DATABASE_TABLE_PREFIX.'_triaging_rules_details';

    $sQuery = "UPDATE `{$sTable}` SET `deleted` = 1 where `triage_id`= '{$iTriageID}' AND `deleted` = 0";

    $rResult = $conn->query($sQuery);
    if($rResult){
        return true;
    }else{
        return false;
    }
}

//! Function for fetching all rules for triagings..
function fGetTriagingRulesDetails(){
    $aTriagingData = array();
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();
    
    $sTriagingMaster = DATABASE_TABLE_PREFIX.'_triaging_master';
    $sTriagingDetails = DATABASE_TABLE_PREFIX.'_triaging_details';
    $sTriagingColorMaster = DATABASE_TABLE_PREFIX.'_triaging_color_master';
    $sTriagingRulesMaster = DATABASE_TABLE_PREFIX.'_triaging_rules_master';
    $sTriagingRulesDetails = DATABASE_TABLE_PREFIX.'_triaging_rules_details';

    $sSQuery = "SELECT 
                    A.`triage_id`,
                    B.`triaging_category_id`,
                    A.`triage_group_id`,
                    B.`entity`,
                    B.`operator`,
                    B.`value`,
                    D.`triage_name`,
                    D.`description`,
                    E.`color_name`,
                    E.`hexadecimal_code`,
                    E.`is_default`,
                    E.`priority`
                FROM
                    `{$sTriagingRulesMaster}` AS A
                        LEFT JOIN
                    `{$sTriagingRulesDetails}` AS B ON B.`triage_group_id` = A.`triage_group_id`
                        LEFT JOIN
                    `{$sTriagingMaster}` AS C ON C.`triage_id` = A.`triage_id`
                        LEFT JOIN
                    `{$sTriagingDetails}` AS D ON D.`triage_id` = A.`triage_id`
                        LEFT JOIN
                    `{$sTriagingColorMaster}` AS E ON E.`color_id` = D.`color_id`
                WHERE
                    A.`deleted` = 0 AND B.`deleted` = 0
                        AND C.`deleted` = 0
                        AND C.`freeze` = 0
                        AND D.`deleted` = 0
                        AND E.`deleted` = 0";

    $sSQueryR = $conn->query($sSQuery);
    if($sSQueryR!==FALSE){
        while($aRow = $sSQueryR->fetch_assoc()){
            $aTriagingData[] = $aRow;            
        }
    }

    //! For categorizing rules of triaging..
    $aTriagingRules = array();
    if(!empty($aTriagingData)){
        for($iii=0;$iii<count($aTriagingData);$iii++){
            $aTriagingRules[$aTriagingData[$iii]['triage_id']]['triage_id'] = $aTriagingData[$iii]['triage_id'];
            $aTriagingRules[$aTriagingData[$iii]['triage_id']]['triage_name'] = $aTriagingData[$iii]['triage_name'];
            $aTriagingRules[$aTriagingData[$iii]['triage_id']]['description'] = $aTriagingData[$iii]['description'];
            $aTriagingRules[$aTriagingData[$iii]['triage_id']]['color_name'] = $aTriagingData[$iii]['color_name'];
            $aTriagingRules[$aTriagingData[$iii]['triage_id']]['hexadecimal_code'] = $aTriagingData[$iii]['hexadecimal_code'];
            $aTriagingRules[$aTriagingData[$iii]['triage_id']]['is_default'] = $aTriagingData[$iii]['is_default'];
            $aTriagingRules[$aTriagingData[$iii]['triage_id']]['priority'] = $aTriagingData[$iii]['priority'];

            $aTriagingRules[$aTriagingData[$iii]['triage_id']]['rules'][$aTriagingData[$iii]['triage_group_id']][] = array(
                'triaging_category_id' => $aTriagingData[$iii]['triaging_category_id'],
                'triage_group_id' => $aTriagingData[$iii]['triage_group_id'],
                'entity' => $aTriagingData[$iii]['entity'],
                'operator' => $aTriagingData[$iii]['operator'],
                'value' => $aTriagingData[$iii]['value']
            );
        }
    }

    return $aTriagingRules;
}

//! Function for fetching all medical management age groups..
function fGetAllMedicalManagementAgeGroup(){
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();
    
    $sTable = DATABASE_TABLE_PREFIX.'_medical_management_age_group_master';
    $aAgeGroups = array();

    $sQuery = "SELECT 
                    `id` AS iID,
                    `age_group_name` AS sAgeGroupName,
                    `age_from` AS iAgeFrom,
                    `age_to` AS iAgeTo,
                    (CASE `gender` WHEN 1 THEN 'Male' WHEN 2 THEN 'Female' WHEN 3 THEN 'Other' ELSE 'All' END) AS sGender,
                    `added_by` AS iAddedBy,
                    `added_on` AS dAddedOn
                FROM
                    `{$sTable}`
                WHERE
                    `deleted` = 0";
                            
    $sSQueryR = $conn->query($sQuery);
    if($sSQueryR!==FALSE){
        while($aRow = $sSQueryR->fetch_assoc()){
            $aRow['dAddedOn'] = date('d-m-Y H:i A',strtotime($aRow['dAddedOn']));
            $aAgeGroups[] = $aRow;
        }
    }

    return $aAgeGroups;
}

//! Function for adding new medical management age group..
function fAddMedicalManagementAgeGroup($sAgeGroupName,$iAgeFrom,$iAgeTo,$sGender,$iAddedBy=0){
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();

    $sTable = DATABASE_TABLE_PREFIX.'_medical_management_age_group_master';
    $dAddedOn = date("Y-m-d H:i:s");
    $iInsertID = 0;

    $sIQuery = "INSERT INTO `{$sTable}` (`id`, `age_group_name`, `age_from`, `age_to`, `gender`, `added_by`, `added_on`, `deleted`)
            VALUES (NULL,'{$sAgeGroupName}','{$iAgeFrom}','{$iAgeTo}','{$sGender}','{$iAddedBy}','{$dAddedOn}',0)";
    
    if($conn != false){
        $sIQueryR = $conn->query($sIQuery);
        if($sIQueryR > 0){
            $iInsertID = $conn->insert_id;
        }
    }

    return $iInsertID;
}

//! Invalidate age group..
function invalidateMedicalManagementAgeGroup($iID){
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();
    
    $sTable = DATABASE_TABLE_PREFIX.'_medical_management_age_group_master';

    $sQuery = "UPDATE `{$sTable}` SET `deleted` = 1 WHERE `id`= '{$iID}' AND `deleted` = 0";

    $rResult = $conn->query($sQuery);
    if($rResult){
        return true;
    }else{
        return false;
    }
}

//! Function for fetching all medical management age groups..
function fGetTriagingGroupAgeGroupID($iTriageID,$iTriageGroupID){
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();
    
    $sTable = DATABASE_TABLE_PREFIX.'_triaging_rules_master';
    $iAgeGroupID = array();

    $sQuery = "SELECT 
                    `age_group_id` AS iAgeGroupID
                FROM
                    `{$sTable}`
                WHERE
                    `deleted` = 0";
                            
    $sSQueryR = $conn->query($sQuery);
    if($sSQueryR!==FALSE){
        while($aRow = $sSQueryR->fetch_assoc()){
            $iAgeGroupID = $aRow['iAgeGroupID'];
        }
    }

    return $iAgeGroupID;
}

//! Function for fetching medical management age group details..
function fGetMedicalManagementAgeGroupDetails($iID){
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();
    
    $sTable = DATABASE_TABLE_PREFIX.'_medical_management_age_group_master';
    $aAgeGroupDetails = array();

    $sQuery = "SELECT 
                    `id` AS iID,
                    `age_group_name` AS sAgeGroupName,
                    `age_from` AS iAgeFrom,
                    `age_to` AS iAgeTo,
                    `gender` AS sGender,
                    `added_by` AS iAddedBy,
                    `added_on` AS dAddedOn
                FROM
                    `{$sTable}`
                WHERE
                    `deleted` = 0 AND `id` = '{$iID}'";
                            
    $sSQueryR = $conn->query($sQuery);
    if($sSQueryR!==FALSE){
        $aRow = $sSQueryR->fetch_assoc();
        $aRow['dAddedOn'] = date('d-m-Y H:i A',strtotime($aRow['dAddedOn']));
        $aAgeGroupDetails = $aRow;
    }

    return $aAgeGroupDetails;
}

//! Function for fetching all triaging color combinations..
function fGetAllTriagingColorMasters(){
    $aTriagingColor = array();
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();
    
    $sTriagingColorMaster = DATABASE_TABLE_PREFIX.'_triaging_color_master';

    $sQuery = "SELECT 
                    `color_id`, `color_name`,`hexadecimal_code`
                FROM
                    `{$sTriagingColorMaster}`
                WHERE
                    `deleted` = 0";
                            
    $sSQueryR = $conn->query($sQuery);
    if($sSQueryR!==FALSE){
        while($aRow = $sSQueryR->fetch_assoc()){
            $aRow['color_id'] = $aRow['color_id']."-1"; //! For triaging
            $aRow['color_name'] = $aRow['color_name']." - ".TRIAGING_LABEL; //! For triaging
            $aTriagingColor[] = $aRow;
        }
    }

    return $aTriagingColor;
}

//! Function for checking default color status..
function fCheckDefaultTriageColor($iTriageColorID){
    $bDefaultTriageColor = false;
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();
    
    $sTriagingColorMaster = DATABASE_TABLE_PREFIX.'_triaging_color_master';

    $sQuery = "SELECT 
                    COUNT(*) AS iCount
                FROM
                    `{$sTriagingColorMaster}`
                WHERE
                    `deleted` = 0 AND `is_default` = 1 AND `color_id` = '{$iTriageColorID}'";
                            
    $sSQueryR = $conn->query($sQuery);
    if($sSQueryR!==FALSE){
        $aRow = $sSQueryR->fetch_assoc();
        if($aRow['iCount'] != "" && $aRow['iCount'] != NULL && $aRow['iCount'] > 0){
            $bDefaultTriageColor = true;
        }
    }

    return $bDefaultTriageColor;
}

//! Invalidate previous details of triaging group master..
function invalidateTriagePreviousGroupMaster($iTriageID, $sGroupID = ""){
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();
    
    $sTable = DATABASE_TABLE_PREFIX.'_triage_group_master';

    $sQuery = "UPDATE `{$sTable}` SET `deleted` = 1 where `triage_id`= '{$iTriageID}' AND `deleted` = 0";

    if ($sGroupID) {
        $sQuery .=" AND `triage_group_id` = ({$sGroupID})";
    }

    $rResult = $conn->query($sQuery);
    if($rResult){
        return true;
    }else{
        return false;
    }
}

//! Invalidate previous details of triaging rule master..
function invalidateTriagePreviousRuleMaster($iTriageID, $sGroupID = ""){
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();
    
    $sTable = DATABASE_TABLE_PREFIX.'_triage_rule_master';

    $sQuery = "UPDATE `{$sTable}` SET `deleted` = 1 where `triage_id`= '{$iTriageID}' AND `deleted` = 0";

    if ($sGroupID) {
        $sQuery .=" AND `triage_group_id` = ({$sGroupID})";
    }

    $rResult = $conn->query($sQuery);
    if($rResult){
        return true;
    }else{
        return false;
    }
}

//! Invalidate previous details of triaging rule details..
function invalidateTriagePreviousRuleDetails($iTriageID, $sGroupID = ""){
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();
    
    $sTable = DATABASE_TABLE_PREFIX.'_triage_rule_details';

    $sQuery = "UPDATE `{$sTable}` SET `deleted` = 1 where `triage_id`= '{$iTriageID}' AND `deleted` = 0";

    if ($sGroupID) {
        $sQuery .=" AND `triage_group_id` = ({$sGroupID})";
    }

    $rResult = $conn->query($sQuery);
    if($rResult){
        return true;
    }else{
        return false;
    }
}

//! Function for adding new triage group master..
function fAddTriageGroupMaster($iTriageID,$iAgeGroupID,$iAddedBy=0){
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();

    $sTable = DATABASE_TABLE_PREFIX.'_triage_group_master';
    $dAddedOn = date("Y-m-d H:i:s");
    $iTriageGroupID = 0;

    $sIQuery = "INSERT INTO `{$sTable}` (`triage_group_id`, `triage_id`, `age_group_id`, `added_by`, `added_on`, `deleted`)
            VALUES (NULL,'{$iTriageID}','{$iAgeGroupID}','{$iAddedBy}','{$dAddedOn}',0)";
    
    if($conn != false){
        $sIQueryR = $conn->query($sIQuery);
        if($sIQueryR > 0){
            $iTriageGroupID = $conn->insert_id;
        }
    }

    return $iTriageGroupID;
}

//! Function for adding triage rule master..
function fAddTriageRuleMaster($iTriageGroupID,$iTriageID,$iAddedBy=0){
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();

    $sTable = DATABASE_TABLE_PREFIX.'_triage_rule_master';
    $dAddedOn = date("Y-m-d H:i:s");
    $iRuleID = 0;

    $sIQuery = "INSERT INTO `{$sTable}` (`rule_id`, `triage_group_id`, `triage_id`, `added_by`, `added_on`, `deleted`)
            VALUES (NULL,'{$iTriageGroupID}','{$iTriageID}','{$iAddedBy}','{$dAddedOn}',0)";
    
    if($conn != false){
        $sIQueryR = $conn->query($sIQuery);
        if($sIQueryR > 0){
            $iRuleID = $conn->insert_id;
        }
    }

    return $iRuleID;
}

//! Function for adding triage rule details..
function fAddTriageRuleDetails($iRuleID,$iTriageGroupID,$iTriageID,$iCategoryID,$iEntityID,$sOperator,$sValue,$iAddedBy=0){
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();

    $sTable = DATABASE_TABLE_PREFIX.'_triage_rule_details';
    $dAddedOn = date("Y-m-d H:i:s");
    $iInsertID = 0;

    $sIQuery = "INSERT INTO `{$sTable}` (`id`, `rule_id`, `triage_group_id`, `triage_id`, `catetory_id`, `entity_id`, `operator`, `value`, `added_by`, `added_on`, `deleted`)
            VALUES (NULL,'{$iRuleID}','{$iTriageGroupID}','{$iTriageID}','{$iCategoryID}','{$iEntityID}','{$sOperator}','{$sValue}','{$iAddedBy}','{$dAddedOn}',0)";
    
    if($conn != false){
        $sIQueryR = $conn->query($sIQuery);
        if($sIQueryR > 0){
            $iInsertID = $conn->insert_id;
        }
    }

    return $iInsertID;
}

//! Function for fetching triage rule details..
function fGetTriageRuleDetails($iTriageID,$sGroupID){
    $aRuleDetail = array();
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();
    
    $sTriageGroupMasterTbl = DATABASE_TABLE_PREFIX.'_triage_group_master';
    $sTriageRuleMasterTbl = DATABASE_TABLE_PREFIX.'_triage_rule_master';
    $sTriageRuleDetailsTbl = DATABASE_TABLE_PREFIX.'_triage_rule_details';
    $sChiefComplaintTbl = DATABASE_TABLE_PREFIX.'_chief_complaint_list_master';

    $sQuery = "SELECT 
                    A.`triage_group_id` AS iTriageGroupID,
                    A.`age_group_id` AS iAgeGroupID,
                    B.`rule_id` AS iRuleID,
                    C.`catetory_id` AS iCategoryID,
                    C.`entity_id` AS iEntityID,
                    @sEntityName:=(CASE WHEN C.`catetory_id` = 2 THEN D.`chief_complaint` ELSE '' END) AS sEntityName,
                    C.`operator` AS sOperator,
                    C.`value` AS sValue
                FROM
                    `{$sTriageGroupMasterTbl}` AS A
                        LEFT JOIN
                    `{$sTriageRuleMasterTbl}` AS B ON B.`triage_group_id` = A.`triage_group_id`
                        LEFT JOIN
                    `{$sTriageRuleDetailsTbl}` AS C ON C.`rule_id` = B.`rule_id`
                        LEFT JOIN 
                    `{$sChiefComplaintTbl}` AS D ON D.`chief_complaint_id` = C.`entity_id` AND C.`catetory_id` = 2 AND D.`deleted` = 0
                WHERE
                    A.`deleted` = 0 AND B.`deleted` = 0
                        AND C.`deleted` = 0
                        AND A.`triage_id` = '{$iTriageID}'
                        AND A.`triage_group_id` IN ({$sGroupID})
                        ORDER BY A.`added_on` DESC";
          
    $sSQueryR = $conn->query($sQuery);
    if($sSQueryR!==FALSE){
        while($aRow = $sSQueryR->fetch_assoc()){
            $aRuleDetail[$aRow['iTriageGroupID']][$aRow['iCategoryID']][$aRow['iRuleID']][] = $aRow;
        }
    }

    return $aRuleDetail;  
}

//! Function for fetching all triage rule details..
function fGetAllTriageRulesDetails(){
    $aTriagingData = array();
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();
    
    $sTriageMaster = DATABASE_TABLE_PREFIX.'_triaging_master';
    $sTriageDetails = DATABASE_TABLE_PREFIX.'_triaging_details';
    $sTriageColorMaster = DATABASE_TABLE_PREFIX.'_triaging_color_master';
    $sTriageGroupMasterTbl = DATABASE_TABLE_PREFIX.'_triage_group_master';
    $sTriageRulesMaster = DATABASE_TABLE_PREFIX.'_triage_rule_master';
    $sTriageRulesDetails = DATABASE_TABLE_PREFIX.'_triage_rule_details';

    $sSQuery = "SELECT 
                    A.`triage_id`,
                    B.`triage_name`,
                    B.`description`,
                    C.`color_name`,
                    C.`hexadecimal_code`,
                    C.`is_default`,
                    C.`priority`,
                    D.`triage_group_id`,
                    D.`age_group_id`,
                    E.`rule_id`,
                    F.`operator`,
                    F.`value`,
                    F.`entity_id`,
                    F.`catetory_id`
                FROM
                    `{$sTriageMaster}` AS A
                        LEFT JOIN
                    `{$sTriageDetails}` AS B ON B.`triage_id` = A.`triage_id`
                        LEFT JOIN
                    `{$sTriageColorMaster}` AS C ON C.`color_id` = B.`color_id`
                        LEFT JOIN
                    `{$sTriageGroupMasterTbl}` AS D ON D.`triage_id` = A.`triage_id`
                        LEFT JOIN
                    `{$sTriageRulesMaster}` AS E ON E.`triage_group_id` = D.`triage_group_id`
                        LEFT JOIN
                    `{$sTriageRulesDetails}` AS F ON F.`rule_id` = E.`rule_id`
                WHERE
                    A.`deleted` = 0 AND A.`freeze` = 0
                        AND B.`deleted` = 0
                        AND C.`deleted` = 0
                        AND D.`deleted` = 0
                        AND E.`deleted` = 0
                        AND F.`deleted` = 0";
    
    $sSQueryR = $conn->query($sSQuery);
    if($sSQueryR!==FALSE){
        while($aRow = $sSQueryR->fetch_assoc()){
            $aTriagingData[] = $aRow;            
        }
    }
    //! For categorizing rules of triaging..
    $aTriagingRules = array();
    if(!empty($aTriagingData)){
        $iGroupID = $aTriagingData[0]['triage_group_id'];
        for($iii=0;$iii<count($aTriagingData);$iii++){
            $aTriagingRules[$aTriagingData[$iii]['triage_id']]['triage_id'] = $aTriagingData[$iii]['triage_id'];
            $aTriagingRules[$aTriagingData[$iii]['triage_id']]['triage_name'] = $aTriagingData[$iii]['triage_name'];
            $aTriagingRules[$aTriagingData[$iii]['triage_id']]['description'] = $aTriagingData[$iii]['description'];
            $aTriagingRules[$aTriagingData[$iii]['triage_id']]['color_name'] = $aTriagingData[$iii]['color_name'];
            $aTriagingRules[$aTriagingData[$iii]['triage_id']]['hexadecimal_code'] = $aTriagingData[$iii]['hexadecimal_code'];
            $aTriagingRules[$aTriagingData[$iii]['triage_id']]['is_default'] = $aTriagingData[$iii]['is_default'];
            $aTriagingRules[$aTriagingData[$iii]['triage_id']]['priority'] = $aTriagingData[$iii]['priority'];
            $aTriagingRules[$aTriagingData[$iii]['triage_id']]['age_group_id'] = $aTriagingData[$iii]['age_group_id'];

            //! Categoried rule..
            $aRule = array(
                'catetory_id' => $aTriagingData[$iii]['catetory_id'],
                'triage_group_id' => $aTriagingData[$iii]['triage_group_id'],
                'entity_id' => $aTriagingData[$iii]['entity_id'],
                'operator' => $aTriagingData[$iii]['operator'],
                'value' => $aTriagingData[$iii]['value'],
                'possible_prescription' => $aPossiblePrescription ? $aPossiblePrescription : [],
                'possible_diagnosis' => $aPossibleDiagnosis ? $aPossibleDiagnosis : [],
            );

            $aTriagingRules[$aTriagingData[$iii]['triage_id']]['rules'][$aTriagingData[$iii]['triage_group_id']][] = $aRule;
        }
    }
    
    return $aTriagingRules;
}

function getTriageGroupID($iTriageID,$iLimit=0,$iOffset=0){
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();
    $aGroupID = [];
    $sTable = DATABASE_TABLE_PREFIX.'_triage_group_master';

    $sQuery = "SELECT `triage_group_id`
                FROM
                    `{$sTable}`
                WHERE
                    `deleted` = 0
                    AND `triage_id` = '{$iTriageID}' 
                    ORDER BY `added_on` DESC";

    if ($iLimit > 0) {
        $sQuery .= " LIMIT {$iOffset}, {$iLimit}";
    }

    $sSQueryR = $conn->query($sQuery);
    if ($sSQueryR !== FALSE) {
        while ($aRow = $sSQueryR->fetch_assoc()) {
            if ($aRow['triage_group_id']) {
                $aGroupID[] = $aRow['triage_group_id'];
            }
        }
    }

    return $aGroupID;
}

function fGetAllVitalList() {
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();
    $aVitalList = [];
    $sTable = DATABASE_TABLE_PREFIX.'_vital_master';

    $sQuery = "SELECT `vital_id`, `vital_name`
                FROM
                    `{$sTable}`
                WHERE
                    `deleted` = 0";

    $sSQueryR = $conn->query($sQuery);
    if ($sSQueryR !== FALSE) {
        while ($aRow = $sSQueryR->fetch_assoc()) {
            $aVitalList[] = $aRow;
        }
    }

    return $aVitalList;
}

//function to fetch chief complaint by partial / complete name
function fGetChiefComplaintsByName($sComplaintName,$iStrictMatching=0){

    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();
    
    $sTable = DATABASE_TABLE_PREFIX.'_chief_complaint_list_master';
    $sQuery = "SELECT * FROM `{$sTable}` WHERE `chief_complaint` LIKE '%{$sComplaintName}%' AND deleted=0";

    if($iStrictMatching == 1){
        $sQuery = "SELECT * FROM `{$sTable}` WHERE `chief_complaint` LIKE '{$sComplaintName}' AND deleted=0";        
    }

    if (!$iStrictMatching) {
        $sQuery .= " ORDER BY (CASE 
                WHEN `chief_complaint` LIKE '{$sComplaintName}' THEN 1 
                WHEN `chief_complaint` LIKE '{$sComplaintName}%'  THEN 2 
                WHEN `chief_complaint` LIKE '%{$sComplaintName}%' THEN 3  
                WHEN `chief_complaint` LIKE '%{$sComplaintName}'  THEN 4 
        END) LIMIT 500";
    }

    $aList = array();
    
    if($conn != false){

        $rResult = $conn->query($sQuery);

        if($rResult != false){
            
            while($aRow = $rResult->fetch_assoc()) {
                $aList[] = $aRow;
            }
        }
    }
    
    return $aList;
}

function invalidateTriagingPrescriptionDetails($sGroupID){
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();
    
    $sTable = DATABASE_TABLE_PREFIX.'_triage_prescription_details';

    $sQuery = "UPDATE `{$sTable}` SET `deleted` = 1 where `triage_group_id` IN ({$sGroupID}) AND `deleted` = 0";

    $rResult = $conn->query($sQuery);
    if($rResult){
        return true;
    }else{
        return false;
    }
}

function invalidateTriagingDiagnosisDetails($sGroupID){
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();
    
    $sTable = DATABASE_TABLE_PREFIX.'_triage_diagnosis_details';

    $sQuery = "UPDATE `{$sTable}` SET `deleted` = 1 where `triage_group_id` IN ({$sGroupID}) AND `deleted` = 0";

    $rResult = $conn->query($sQuery);
    if($rResult){
        return true;
    }else{
        return false;
    }
}

function getTriageGroupPrescription($iTriageGroupID){
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();
    $aTriagePrescription = [];
    $sTable = DATABASE_TABLE_PREFIX.'_triage_prescription_details';

    $sQuery = "SELECT `prescription`
                FROM
                    `{$sTable}`
                WHERE
                    `deleted` = 0
                    AND `triage_group_id` = '{$iTriageGroupID}'";

    $sSQueryR = $conn->query($sQuery);
    if ($sSQueryR !== FALSE) {
        $aRow = $sSQueryR->fetch_assoc();
        if ($aRow['prescription']) {
            $aTriagePrescription = json_decode($aRow['prescription'], true);
        }
    }

    return $aTriagePrescription;
}

function getTriageGroupDiagnosis($iTriageGroupID){
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();
    $aTriageDiagnosis = [];
    $sTable = DATABASE_TABLE_PREFIX.'_triage_diagnosis_details';

    $sQuery = "SELECT `diagnosis`
                FROM
                    `{$sTable}`
                WHERE
                    `deleted` = 0
                    AND `triage_group_id` = '{$iTriageGroupID}'";

    $sSQueryR = $conn->query($sQuery);
    if ($sSQueryR !== FALSE) {
        $aRow = $sSQueryR->fetch_assoc();
        if ($aRow['diagnosis']) {
            $aTriageDiagnosis = json_decode($aRow['diagnosis'], true);
        }
    }

    return $aTriageDiagnosis;
}

function addTriagePrescription($iTriageID, $iTriageGroupID, $sPrescription){
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();

    $sTable = DATABASE_TABLE_PREFIX.'_triage_prescription_details';
    $dAddedOn = date("Y-m-d H:i:s");
    $iInsertID = 0;

    $sIQuery = "INSERT INTO `{$sTable}` (`id`, `triage_id`, `triage_group_id`, `added_on`, `prescription`, `deleted`)
            VALUES (NULL,'{$iTriageID}','{$iTriageGroupID}','{$dAddedOn}','{$sPrescription}',0)";

    if($conn != false){
        $sIQueryR = $conn->query($sIQuery);
        if($sIQueryR > 0){
            $iInsertID = $conn->insert_id;
        }
    }

    return $iInsertID;
}

function addTriageDiagnosis($iTriageID, $iTriageGroupID, $sDiagnosis){
    $DBMan = new DBConnManager();
    $conn =  $DBMan->getConnInstance();

    $sTable = DATABASE_TABLE_PREFIX.'_triage_diagnosis_details';
    $dAddedOn = date("Y-m-d H:i:s");
    $iInsertID = 0;

    $sIQuery = "INSERT INTO `{$sTable}` (`id`, `triage_id`, `triage_group_id`, `added_on`, `diagnosis`, `deleted`)
            VALUES (NULL,'{$iTriageID}','{$iTriageGroupID}','{$dAddedOn}','{$sDiagnosis}',0)";
    
    if($conn != false){
        $sIQueryR = $conn->query($sIQuery);
        if($sIQueryR > 0){
            $iInsertID = $conn->insert_id;
        }
    }

    return $iInsertID;
}