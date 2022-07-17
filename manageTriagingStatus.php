<?php
include_once dirname(__FILE__).DIRECTORY_SEPARATOR."config/config.php";

$sPageTopTitle = "Manage Triage Rules";

$aBreadcrumb = [
    [
        "title"=>'Dashboard',
        "link"=>"medicalManagement.php"
    ],
    [
        "title"=>'Clinical Decision Support Management',
        "link"=>"manageClinicalDecisionSupportManagement.php"
    ],
    [
        "title"=>$sPageTopTitle,
        "link"=>"#",
        "isActive"=> true
    ]
];

$iTriageID = Input::request('iTriageID') ? Input::request('iTriageID') : 0;
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'mxcelHeaderB3.php'; ?>
    </head>
    <body class="flat-blue">
        <?php include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'mxcelNavbarB3.php'; ?>
        <div class="container-fluid classContainerBody">
            <div class="">
                <div class="page-title">
                </div>
                <div class="container-fluid">
                    <div class="panel panel-primary">
                        <div class="panel-heading classTriagingHeaderListing">
                            <i class="fa fa-list-alt" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo $sPageTopTitle; ?>
                        </div>
                        <div class="panel-body" id="idTriagingContainer">
                            <form id="idFormUpdateTriagingDetails" name="idFormUpdateTriagingDetails" action="updateTriagingDetailsEmulator.php" method="post">
                                <input type="hidden" name="idTriageID" id="idTriageID" value="<?php echo $iTriageID;?>" />
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <div class="row">
                                        <label class="col-md-3"><strong>Triage Name<font color="red">*</font></strong></label>
                                        <div class="col-md-6">
                                            <input type="text" name="idTriagingName" id="idTriagingName" class="form-control classTriagingName" />
                                        </div>
                                    </div>

                                    <div class="row">
                                        <label class="col-md-3"><strong>Triage Color<font color="red">*</font></strong></label>
                                        <div class="col-md-6">
                                            <select name="idTriagingColor" id="idTriagingColor" class="form-control classTriagingColor"></select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <label class="col-md-3"><strong>Description</strong></label>
                                        <div class="col-md-6">
                                            <textarea name="idTriagingDescription" id="idTriagingDescription" class="form-control classTriagingDescription"></textarea>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-md-6" style="font-size:25px; font-weight:normal;">Triage Rules</label>
                                        <div class="col-md-6">
                                            <a class="btn btn-md btn-success classAddNewTriagingRuleGroupCatetory pull-right" id="idAddNewTriagingRuleGroupCatetory" name="idAddNewTriagingRuleGroupCatetory" title="Add New Rule Group"><i aria-hidden="true" class="fa fa-plus"></i>&nbsp;&nbsp; Add New Rule Group</a>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="classTriagingRulesMainContainer"></div>
                                    <div class="classPaginationMainContainer"></div>
                                    <div class="">
                                        <a class="btn btn-sm btn-success classUpdateTriagingDetails" id="idUpdateTriagingDetails" title="Update Triaging Details">Update</a>
                                        <a href="manageTriagingListing.php" class="btn btn-sm btn-primary" title="Go To Manage Triaging" id="idGoToManageTriaging">Go To Manage Triaging</a>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h3><strong>Instructions</strong></h3>
                                            <div class="row">
                                                <ul>
                                                    <li>Triaging will be applied on one of the triage rule group satisfaction.</li>
                                                    <li>In case of more than one triage group satisfied, we will apply it as per it's highest priority.</li>
                                                    <li>Only same page group rules can be saved at the same time.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">   
            var iTriageID = "<?php echo $iTriageID; ?>";
            var iTriagingGroupIndex = 1;
            var iRuleRowIndex = 1;
            var aAllAgeGroups = [];
            var aRuleCategories = [];
            var aAllDynamicVitals = [];
            let iRecordLimit = 5;
            let iTotalPageCount = 0;
            let iCurrentPageNo = 1;
            $(document).ready(function(){

                //! Fetching all dynamic vitals..
                $.ajax({
                    url: "ajaxTriage.php?sFlag=getAllDynamicVitalList",
                    async: false,
                    success: function (data){
                        if($.trim(data) != false){
                            aAllDynamicVitals = data;
                        }
                    }
                });

                //! Fetching all age groups for medical management
                $.ajax({
                    url: "ajaxMedicalManagement.php?sFlag=fGetAllMedicalManagementAgeGroups",
                    async: false,
                    success: function (data){
                        if($.trim(data) != false){
                            aAllAgeGroups = data;
                        }
                    }
                });

                //! Fetching triaging rule categories..
                $.ajax({
                    url: "ajaxTriage.php?sFlag=fGetTriagingRuleCategories",
                    async: false,
                    success: function (data){
                        if($.trim(data) != false){
                            if(data.length > 0){
                                aRuleCategories = data;
                            }
                        }
                    }
                });

                //! Fetching triaging details..
                fGetTriagingDetailsByTriageID(iTriageID);

                fGetTriageRuleDetails(iTriageID);

                //! For adding new triaging rule group..
                $("#idFormUpdateTriagingDetails").on("click",".classAddNewTriagingRuleGroupCatetory",function(){
                    var blankClone = $([
                        '<div class="classTriagingRulesGroupContainer" id="idTriagingRuleGroupContainer_'+iTriagingGroupIndex+'">',
                            '<input type="hidden" id="idTrigingGroupIndex_'+iTriagingGroupIndex+'" name="idTrigingGroupIndex[]" value="'+iTriagingGroupIndex+'"/>',
                            '<div class="row">',
                                '<label class="col-md-3" style="font-size:20px;">Triaging Group '+iTriagingGroupIndex+'</label>',
                                '<div class="col-md-3"><select class="form-control classAgeGroup" id="idAgeGroup_'+iTriagingGroupIndex+'" name="idAgeGroup_'+iTriagingGroupIndex+'"></select></div>',
                                '<div class="col-md-6">',
                                    '<a class="btn btn-sm btn-danger classRemoveTriagingRuleGroup pull-right" id="idRemoveTriagingRuleGroup" name="idRemoveTriagingRuleGroup" data-group-id="'+iTriagingGroupIndex+'" title="Remove Triaging Group" style="margin-left: 10px;"><i aria-hidden="true" class="fa fa-trash" ></i></a>',
                                    '<a class="btn btn-sm btn-primary classAddNewTriagingRuleGroup pull-right" id="idAddNewTriagingRuleGroup" name="idAddNewTriagingRuleGroup" data-group-id="'+iTriagingGroupIndex+'" title="Add New Rule"><i aria-hidden="true" class="fa fa-plus" ></i>&nbsp;&nbsp; Add New Rule For Group '+iTriagingGroupIndex+'</a>',
                                '</div>',
                            '</div>',
                            '<br />',
                            '<div class="classTriagingRulesForGroup" id="idTriagingRulesForGroup_'+iTriagingGroupIndex+'">',
                                '<div class="row classTriagingGroupRules" id="idTriagingGroupRules_'+iRuleRowIndex+'">',
                                    '<div class="row">',
                                        '<label class="col-md-2"><strong>Category</strong></label>',
                                        '<div class="col-md-4">',
                                            '<select id="idTriagingCategory_'+iRuleRowIndex+'" name="idTriagingCategory_'+iTriagingGroupIndex+'[]" class="form-control classTriagingCategory" data-row-id="'+iRuleRowIndex+'"></select>',
                                        '</div>',
                                    '</div>',
                                    '<div class="row classVitalCategoryContainer" id="idVitalCategoryContainer_'+iRuleRowIndex+'" style="display:none;">',
                                        '<label class="col-md-2">',
                                            '<strong>Vital</strong>',
                                        '</label>',
                                        '<div class="col-md-3">',
                                            '<select id="idTriagingVitalName_'+iRuleRowIndex+'" name="idTriagingVitalName_'+iTriagingGroupIndex+'['+iRuleRowIndex+']" class="form-control classTriagingVitalName" style="width:100% !important;" data-group-id="'+iTriagingGroupIndex+'" data-row-id="'+iRuleRowIndex+'">',
                                            '</select>',
                                        '</div>',
                                        '<div class="col-md-3">',
                                            '<select id="idTriagingVitalOperator_'+iRuleRowIndex+'" name="idTriagingVitalOperator_'+iTriagingGroupIndex+'['+iRuleRowIndex+']" class="form-control classTriagingVitalOperator" style="width:100% !important;" data-group-id="'+iTriagingGroupIndex+'" data-row-id="'+iRuleRowIndex+'">',
                                                '<option value="eq"> = </option><option value="gt"> &gt; </option><option value="lt"> &lt; </option><option value="gteq"> &gt;= </option><option value="lteq"> &lt;= </option><option value="neq"> != </option>',
                                            '</select>',
                                        '</div>',
                                        '<div class="col-md-3">',
                                            '<input type="text" id="idTriagingVitalValue_'+iRuleRowIndex+'" name="idTriagingVitalValue_'+iTriagingGroupIndex+'['+iRuleRowIndex+']" class="form-control classTriagingVitalValue" style="width:100% !important;" data-group-id="'+iTriagingGroupIndex+'" data-row-id="'+iRuleRowIndex+'" />',
                                        '</div>',
                                    '</div>',
                                    '<div class="row classChiefComplaintCategoryContainer" id="idChiefComplaintCategoryContainer_'+iRuleRowIndex+'" style="display:none;">',
                                        '<label class="col-md-2">',
                                            '<strong>Symptoms</strong>',
                                        '</label>',
                                        '<div class="col-md-10">',
                                            '<select id="idTriagingChiefComplaintName_'+iRuleRowIndex+'" name="idTriagingChiefComplaintName_'+iTriagingGroupIndex+'[]" multiple class="form-control classTriagingChiefComplaintName" style="width:100% !important;">',
                                            '</select>',
                                        '</div>',
                                    '</div>',
                                    '<hr>',
                                '</div>',
                            '</div>',
                            '<div class="row classPossibleDrugContainer" id="idPossibleDrugsContainer_'+iTriagingGroupIndex+'">',
                                '<label class="col-md-2">',
                                    '<strong>Prescription</strong>',
                                '</label>',
                                '<div class="col-md-10">',
                                    '<select id="idPossibleDrugsName_'+iTriagingGroupIndex+'" name="idPossibleDrugsName_'+iTriagingGroupIndex+'[]" multiple class="form-control classPossibleDrugsName" style="width:100% !important;">',
                                    '</select>',
                                '</div>',
                            '</div>',
                            '<div class="row classPossibleDiagnosisContainer" id="idPossibleDiagnosisContainer_'+iTriagingGroupIndex+'">',
                                '<label class="col-md-2">',
                                    '<strong>Diagnosis</strong>',
                                '</label>',
                                '<div class="col-md-10">',
                                    '<select id="idPossibleDiagnosisName_'+iTriagingGroupIndex+'" name="idPossibleDiagnosisName_'+iTriagingGroupIndex+'[]" multiple class="form-control classPossibleDiagnosisName" style="width:100% !important;">',
                                    '</select>',
                                '</div>',
                            '</div>',
                            '<hr class="classGroupHorizontalHr">',
                        '</div>'
                    ].join(""));

                    var iClone = blankClone.clone();
                    $(".classTriagingRulesMainContainer").append(iClone);

                    //! Apply Select2..
                    $("#idTriagingVitalName_"+iRuleRowIndex).select2();
                    $("#idTriagingVitalOperator_"+iRuleRowIndex).select2();

                    fGetTriagingRuleCategories(iRuleRowIndex);
                    applyChiefComplaintSearch(iRuleRowIndex);
                    getAllDynamicVitalList(iRuleRowIndex);
                    fGetAllAgeGroups(iTriagingGroupIndex);
                    $("#idPossibleDrugsName_"+iTriagingGroupIndex).select2({
                        placeholder: 'Enter Prescription',
                        tags: true
                    });

                    $("#idPossibleDiagnosisName_"+iTriagingGroupIndex).select2({
                        placeholder: 'Enter Diagnosis',
                        tags: true
                    });

                    iTriagingGroupIndex++;
                    iRuleRowIndex++;
                });

                //! For Removing existing triaging rule group..
                $("#idFormUpdateTriagingDetails").on("click",".classRemoveTriagingRuleGroup",function(){
                    var iGroupIndex = $(this).data('group-id');
                    var iTriageGroupID = $(this).data('group-index');

                    //! Confirmation..
                    if(confirm("Are you really want to delete this group?")){

                        if (iTriageGroupID > 0) {
                            deleteTriageGroupDetails(iTriageGroupID);
                        }

                        $("#idTriagingRuleGroupContainer_"+iGroupIndex).remove();                        
                    }
                }); 

                //! For adding new triaging rule under group..
                $("#idFormUpdateTriagingDetails").on("click",".classAddNewTriagingRuleGroup",function(){
                    var iGroupIndex = $(this).data('group-id');

                    var blankClone = $([
                       '<div class="row classTriagingGroupRules" id="idTriagingGroupRules_'+iRuleRowIndex+'">',
                            '<div class="row">',
                                '<label class="col-md-2"><strong>Category</strong></label>',
                                '<div class="col-md-4">',
                                    '<select id="idTriagingCategory_'+iRuleRowIndex+'" name="idTriagingCategory_'+iGroupIndex+'[]" class="form-control classTriagingCategory" data-row-id="'+iRuleRowIndex+'"></select>',
                                '</div>',
                                '<div class="col-md-1">',
                                    '<a class="btn btn-sm btn-danger classGroupRemoveTriagingRule" data-row-id="'+iRuleRowIndex+'" id="idGroupRemoveTriagingRule_'+iRuleRowIndex+'" title="Remove Group Triage Rule"><i aria-hidden="true" class="fa fa-trash"></i></a>',
                                '</div>',
                            '</div>',
                            '<div class="row classVitalCategoryContainer" id="idVitalCategoryContainer_'+iRuleRowIndex+'" style="display:none;">',
                                '<label class="col-md-2">',
                                    '<strong>Vital</strong>',
                                '</label>',
                                '<div class="col-md-3">',
                                    '<select id="idTriagingVitalName_'+iRuleRowIndex+'" name="idTriagingVitalName_'+iGroupIndex+'['+iRuleRowIndex+']" class="form-control classTriagingVitalName" style="width:100% !important;" data-group-id="'+iGroupIndex+'" data-row-id="'+iRuleRowIndex+'">',
                                    '</select>',
                                '</div>',
                                '<div class="col-md-3">',
                                    '<select id="idTriagingVitalOperator_'+iRuleRowIndex+'" name="idTriagingVitalOperator_'+iGroupIndex+'['+iRuleRowIndex+']" class="form-control classTriagingVitalOperator" style="width:100% !important;" data-group-id="'+iGroupIndex+'" data-row-id="'+iRuleRowIndex+'">',
                                        '<option value="eq"> = </option><option value="gt"> &gt; </option><option value="lt"> &lt; </option><option value="gteq"> &gt;= </option><option value="lteq"> &lt;= </option><option value="neq"> != </option>',
                                    '</select>',
                                '</div>',
                                '<div class="col-md-3">',
                                    '<input type="text" id="idTriagingVitalValue_'+iRuleRowIndex+'" name="idTriagingVitalValue_'+iGroupIndex+'['+iRuleRowIndex+']" class="form-control classTriagingVitalValue" style="width:100% !important;" data-group-id="'+iGroupIndex+'" data-row-id="'+iRuleRowIndex+'" />',
                                '</div>',
                            '</div>',
                            '<div class="row classChiefComplaintCategoryContainer" id="idChiefComplaintCategoryContainer_'+iRuleRowIndex+'" style="display:none;">',
                                '<label class="col-md-2">',
                                    '<strong>Symptoms</strong>',
                                '</label>',
                                '<div class="col-md-10">',
                                    '<select id="idTriagingChiefComplaintName_'+iRuleRowIndex+'" name="idTriagingChiefComplaintName_'+iGroupIndex+'[]" multiple class="form-control classTriagingChiefComplaintName" style="width:100% !important;">',
                                    '</select>',
                                '</div>',
                            '</div>',
                            '<hr>',
                        '</div>'
                    ].join(""));

                    var iClone = blankClone.clone();
                    $("#idTriagingRulesForGroup_"+iGroupIndex).append(iClone);

                    //! Apply Select2..
                    $("#idTriagingVitalName_"+iRuleRowIndex).select2();
                    $("#idTriagingVitalOperator_"+iRuleRowIndex).select2();

                    fGetTriagingRuleCategories(iRuleRowIndex);
                    applyChiefComplaintSearch(iRuleRowIndex);
                    getAllDynamicVitalList(iRuleRowIndex);
                    iRuleRowIndex++;
                });

                //! For Removing existing triaging rule under group..
                $("#idFormUpdateTriagingDetails").on("click",".classGroupRemoveTriagingRule",function(){
                    var iRowIndex = $(this).data('row-id');

                    //! Confirmation..
                    if(confirm("Are you really want to delete this rule?")){
                        $("#idTriagingGroupRules_"+iRowIndex).remove();                        
                    }
                });


                //! if triaging category changes..
                $(".classTriagingRulesMainContainer").on("change",".classTriagingCategory",function(){
                    var iCategoryID = $(this).val();
                    var iRowIndex = $(this).data("row-id");

                    if(iCategoryID == 1){ //! For vitals
                        $("#idTriagingChiefComplaintName_"+iRowIndex).empty();

                        //! For fetching all vital list..
                        getAllDynamicVitalList(iRowIndex);

                        $("#idVitalCategoryContainer_"+iRowIndex).css("display","block");
                        $("#idChiefComplaintCategoryContainer_"+iRowIndex).css("display","none");
                    }else if(iCategoryID == 2){ //! For symptoms
                        $("#idTriagingVitalName_"+iRowIndex).empty();
                        $("#idTriagingVitalValue_"+iRowIndex).val('');

                        //! For fetching all chief complaint list..
                        applyChiefComplaintSearch(iRowIndex);

                        $("#idChiefComplaintCategoryContainer_"+iRowIndex).css("display","block");
                        $("#idVitalCategoryContainer_"+iRowIndex).css("display","none");
                    }else{
                        $("#idVitalCategoryContainer_"+iRowIndex).css("display","none");
                        $("#idChiefComplaintCategoryContainer_"+iRowIndex).css("display","none");
                    }                    
                });

                //! Submit data..
                $("#idFormUpdateTriagingDetails").on("click",".classUpdateTriagingDetails",function(){
                    var sTriagingName = $("#idTriagingName").val();
                    var sTriagingColor = $("#idTriagingColor").val();

                    if(sTriagingName == ''){
                        alert("Please enter triaging name");
                        return false;
                    }

                    if(sTriagingColor == ''){
                        alert("Please select triaging color");
                        return false;
                    }

                    if(confirm("Are you really want to update triaging details?")){
                        $("#idFormUpdateTriagingDetails").submit();
                    }
                });

                //! apply pagination
                $(document).on("click", ".classPageNo", function(e){

                    var iPageNo = $(this).data('page-no');
                    iCurrentPageNo = iPageNo;

                    //Apply Pagination
                    applyPagination(iPageNo);

                });

                //! apply pagination [previous]
                $(document).on("click", ".classPreviousPage", function(e){
                    if (iCurrentPageNo != 1) {
                        iCurrentPageNo = iCurrentPageNo - 1;
                    }

                    //Apply Pagination
                    applyPagination(iCurrentPageNo);

                });

                // apply pagination [next]
                $(document).on("click", ".classNextPage", function(e){
                    if (iCurrentPageNo != iTotalPageCount) {
                        iCurrentPageNo = iCurrentPageNo + 1;
                    }

                    //Apply Pagination
                    applyPagination(iCurrentPageNo);

                });
            });

            // Function to create dynmaic pagination
            function createPagination(iTriageID){
                $.ajax({
                    url: "ajaxTriage.php?sFlag=getTriageTotalGroupCount",
                    data:{iTriageID:iTriageID},
                    success: function (data){
                        if ($.trim(data) != false) {
                            var iTotalGroupCount = data['total_count'];
                            iTotalPageCount = Math.ceil(iTotalGroupCount/iRecordLimit);
                            var sPagination = "";
                            var iPageNo = 0;

                            if (parseInt(iTotalGroupCount) > 0) {
                                sPagination += '<nav aria-label="navigation">';
                                    sPagination += '<ul class="pagination justify-content-end">';
                                        if (iCurrentPageNo == 1) {
                                            sPagination += '<li class="page-item disabled"><a class="page-link" tabindex="-1">Previous</a></li>';
                                        } else {
                                            sPagination += '<li class="page-item"><a class="page-link classPreviousPage" tabindex="-1">Previous</a></li>';
                                        }

                                        for (var iii = 1 ; iii <= iTotalPageCount ; iii++) {
                                            if (iCurrentPageNo == iii) {
                                                sPagination += '<li class="page-item disabled active" data-page-no="'+iii+'"><a class="page-link">'+iii+'</a></li>';
                                            } else {
                                                sPagination += '<li class="page-item" data-page-no="'+iii+'"><a class="page-link classPageNo" data-page-no="'+iii+'">'+iii+'</a></li>';
                                            }
                                        }

                                        if (iCurrentPageNo == iTotalPageCount) {
                                            sPagination += '<li class="page-item disabled"><a class="page-link" tabindex="-1">Next</a></li>';
                                        } else {
                                            sPagination += '<li class="page-item"><a class="page-link classNextPage" tabindex="-1">Next</a></li>';
                                        }
                                    sPagination += '</ul>';
                                sPagination += '</nav>';
                            }

                            $(document).find(".classPaginationMainContainer").html(sPagination);
                        }
                    }
                });
            }

            //! Function to apply pagination..
            function applyPagination(iPageNo=1) {

                iCurrentPageNo = iPageNo;
                var iLimit = iRecordLimit;
                var iOffset = (iPageNo * iLimit) - iLimit;

                fGetTriageRuleDetails(iTriageID,iLimit,iOffset);
            }

            //! Function for fetching triaging rules details by triage_id..
            function fGetTriageRuleDetails(iTriageID,iLimit=iRecordLimit,iOffset=0){
                $(document).find(".classTriagingRulesMainContainer").html('');

                $.ajax({
                    type: 'POST',
                    url: "ajaxTriage.php?sFlag=fGetTriageRuleDetails",
                    data:{
                        iTriageID:iTriageID,
                        iLimit:iLimit,
                        iOffset:iOffset
                    },
                    beforeSend: function(){
                        $.LoadingOverlay("show");
                    },
                    success: function (data){
                        if($.trim(data) != false){
                            iRuleRowIndex = parseInt(iOffset) + 1;;
                            iTriagingGroupIndex = parseInt(iOffset) + 1;;                            
                            var iii = 0;
                            $.each(data, function(iGroupID,aCategoryRuleDetails){           
                                var sGroupTemplate = "";
                                var iAgeGroupID = 0;
                                sGroupTemplate += '<div class="classTriagingRulesGroupContainer" id="idTriagingRuleGroupContainer_'+iTriagingGroupIndex+'">';
                                    sGroupTemplate += '<input type="hidden" name="idTriagingGroupID[]" value="'+iGroupID+'"/>';
                                    sGroupTemplate += '<input type="hidden" id="idTriagingGroupID_'+iGroupID+'" value="'+iTriagingGroupIndex+'"/>';
                                    sGroupTemplate += '<input type="hidden" id="idTrigingGroupIndex_'+iTriagingGroupIndex+'" name="idTrigingGroupIndex[]" value="'+iTriagingGroupIndex+'"/>';
                                    sGroupTemplate += '<div class="row">';
                                        sGroupTemplate += '<label class="col-md-3" style="font-size:20px;">Triaging Group '+iTriagingGroupIndex+'</label>';
                                        sGroupTemplate += '<div class="col-md-3"><select class="form-control classAgeGroup" id="idAgeGroup_'+iTriagingGroupIndex+'" name="idAgeGroup_'+iTriagingGroupIndex+'"></select></div>';
                                        sGroupTemplate += '<div class="col-md-6">';
                                            sGroupTemplate += '<a class="btn btn-sm btn-danger classRemoveTriagingRuleGroup pull-right" id="idRemoveTriagingRuleGroup" name="idRemoveTriagingRuleGroup" data-group-id="'+iTriagingGroupIndex+'" title="Remove Triaging Group" style="'+(iii == 0 ? "display:none;" : "display:inline;margin-left: 10px;")+'" data-group-index="'+iGroupID+'"><i aria-hidden="true" class="fa fa-trash" ></i></a>';
                                            sGroupTemplate += '<a class="btn btn-sm btn-primary classAddNewTriagingRuleGroup pull-right" id="idAddNewTriagingRuleGroup" name="idAddNewTriagingRuleGroup" data-group-id="'+iTriagingGroupIndex+'" title="Add New Rule"><i aria-hidden="true" class="fa fa-plus" ></i>&nbsp;&nbsp; Add New Rule For Group '+iTriagingGroupIndex+'</a>';
                                        sGroupTemplate += '</div>';
                                    sGroupTemplate += '</div>';
                                    sGroupTemplate += '<br />';
                                    sGroupTemplate += '<div class="classTriagingRulesForGroup" id="idTriagingRulesForGroup_'+iTriagingGroupIndex+'">';
                                    sGroupTemplate += '</div>';
                                    sGroupTemplate += '<div class="row classPossibleDrugContainer" id="idPossibleDrugsContainer_'+iTriagingGroupIndex+'">';
                                        sGroupTemplate += '<label class="col-md-2">';
                                            sGroupTemplate += '<strong>Prescription</strong>';
                                        sGroupTemplate += '</label>';
                                        sGroupTemplate += '<div class="col-md-10">';
                                            sGroupTemplate += '<select id="idPossibleDrugsName_'+iTriagingGroupIndex+'" name="idPossibleDrugsName_'+iTriagingGroupIndex+'[]" multiple class="form-control classPossibleDrugsName" style="width:100% !important;">';
                                            sGroupTemplate += '</select>';
                                        sGroupTemplate += '</div>';
                                    sGroupTemplate += '</div>';
                                    sGroupTemplate += '<div class="row classPossibleDiagnosisContainer" id="idPossibleDiagnosisContainer_'+iTriagingGroupIndex+'">';
                                        sGroupTemplate += '<label class="col-md-2">';
                                            sGroupTemplate += '<strong>Diagnosis</strong>';
                                        sGroupTemplate += '</label>';
                                        sGroupTemplate += '<div class="col-md-10">';
                                            sGroupTemplate += '<select id="idPossibleDiagnosisName_'+iTriagingGroupIndex+'" name="idPossibleDiagnosisName_'+iTriagingGroupIndex+'[]" multiple class="form-control classPossibleDiagnosisName" style="width:100% !important;">';
                                            sGroupTemplate += '</select>';
                                        sGroupTemplate += '</div>';
                                    sGroupTemplate += '</div>';
                                    sGroupTemplate += '<hr class="classGroupHorizontalHr">';
                                sGroupTemplate += '</div>';
                                $(".classTriagingRulesMainContainer").append(sGroupTemplate);

                                var bVitalExist = false;
                                var bChiefComplaintExist = false;
                                $.each(aCategoryRuleDetails, function(iCategoryID, aRuleDetails){
                                    //! Appending Vitals..
                                    if(iCategoryID == 1){
                                        bVitalExist = true;
                                        var iAutoRowIndex = 0;
                                        $.each(aRuleDetails, function(iRuleID, aRules){
                                            var sVitalTemplate = "";
                                            var iVitalID = aRules[0]['iEntityID'];
                                            var sOperator = aRules[0]['sOperator'];
                                            var sValue = aRules[0]['sValue'];
                                            iAgeGroupID = aRules[0]['iAgeGroupID'];

                                            sVitalTemplate += '<div class="row classTriagingGroupRules" id="idTriagingGroupRules_'+iRuleRowIndex+'">';
                                                sVitalTemplate += '<div class="row">';
                                                    sVitalTemplate += '<label class="col-md-2"><strong>Category</strong></label>';
                                                    sVitalTemplate += '<div class="col-md-4">';
                                                        sVitalTemplate += '<select id="idTriagingCategory_'+iRuleRowIndex+'" name="idTriagingCategory_'+iTriagingGroupIndex+'[]" class="form-control classTriagingCategory" data-row-id="'+iRuleRowIndex+'"></select>';
                                                    sVitalTemplate += '</div>';
                                                    sVitalTemplate += '<div class="col-md-1">';
                                                        sVitalTemplate += '<a class="btn btn-sm btn-danger classGroupRemoveTriagingRule" data-row-id="'+iRuleRowIndex+'" id="idGroupRemoveTriagingRule_'+iRuleRowIndex+'" title="Remove Group Triage Rule" style="'+(iAutoRowIndex == 0 ? "display:none;" : "display:inline;")+'"><i aria-hidden="true" class="fa fa-trash"></i></a>';
                                                    sVitalTemplate += '</div>';
                                                sVitalTemplate += '</div>';
                                                sVitalTemplate += '<div class="row classVitalCategoryContainer" id="idVitalCategoryContainer_'+iRuleRowIndex+'" style="display:block;">';
                                                    sVitalTemplate += '<label class="col-md-2">';
                                                        sVitalTemplate += '<strong>Vital Name</strong>';
                                                    sVitalTemplate += '</label>';
                                                    sVitalTemplate += '<div class="col-md-3">';
                                                        sVitalTemplate += '<select id="idTriagingVitalName_'+iRuleRowIndex+'" name="idTriagingVitalName_'+iTriagingGroupIndex+'['+iRuleRowIndex+']" class="form-control classTriagingVitalName" style="width:100% !important;" data-group-id="'+iTriagingGroupIndex+'" data-row-id="'+iRuleRowIndex+'">';
                                                        sVitalTemplate += '</select>';
                                                    sVitalTemplate += '</div>';
                                                    sVitalTemplate += '<div class="col-md-3">';
                                                        sVitalTemplate += '<select id="idTriagingVitalOperator_'+iRuleRowIndex+'" name="idTriagingVitalOperator_'+iTriagingGroupIndex+'['+iRuleRowIndex+']" class="form-control classTriagingVitalOperator" style="width:100% !important;" data-group-id="'+iTriagingGroupIndex+'" data-row-id="'+iRuleRowIndex+'">';
                                                            sVitalTemplate += '<option value="eq"> = </option><option value="gt"> &gt; </option><option value="lt"> &lt; </option><option value="gteq"> &gt;= </option><option value="lteq"> &lt;= </option><option value="neq"> != </option>';
                                                        sVitalTemplate += '</select>';
                                                    sVitalTemplate += '</div>';
                                                    sVitalTemplate += '<div class="col-md-3">';
                                                        sVitalTemplate += '<input type="text" id="idTriagingVitalValue_'+iRuleRowIndex+'" name="idTriagingVitalValue_'+iTriagingGroupIndex+'['+iRuleRowIndex+']" class="form-control classTriagingVitalValue" style="width:100% !important;" data-group-id="'+iTriagingGroupIndex+'" data-row-id="'+iRuleRowIndex+'" value="'+sValue+'" />';
                                                    sVitalTemplate += '</div>';
                                                sVitalTemplate += '</div>';
                                                sVitalTemplate += '<div class="row classChiefComplaintCategoryContainer" id="idChiefComplaintCategoryContainer_'+iRuleRowIndex+'" style="display:none;">';
                                                    sVitalTemplate += '<label class="col-md-2">';
                                                        sVitalTemplate += '<strong>Symptoms</strong>';
                                                    sVitalTemplate += '</label>';
                                                    sVitalTemplate += '<div class="col-md-10">';
                                                        sVitalTemplate += '<select id="idTriagingChiefComplaintName_'+iRuleRowIndex+'" name="idTriagingChiefComplaintName_'+iTriagingGroupIndex+'[]" multiple class="form-control classTriagingChiefComplaintName" style="width:100% !important;">';
                                                        sVitalTemplate += '</select>';
                                                    sVitalTemplate += '</div>';
                                                sVitalTemplate += '</div>';
                                                sVitalTemplate += '<hr>';
                                            sVitalTemplate += '</div>';
                                            $("#idTriagingRulesForGroup_"+iTriagingGroupIndex).append(sVitalTemplate);

                                            //! Apply Select2..
                                            $("#idTriagingVitalName_"+iRuleRowIndex).select2();

                                            $("#idTriagingVitalOperator_"+iRuleRowIndex).select2();

                                            if (sOperator) {
                                                var sSelectedOperator = "";
                                                switch (sOperator) {
                                                    case '=':
                                                        sSelectedOperator = "eq";
                                                        break;
                                                    case '>':
                                                        sSelectedOperator = "gt";
                                                        break;
                                                    case '<':
                                                        sSelectedOperator = "lt";
                                                        break;
                                                    case '>=':
                                                        sSelectedOperator = "gteq";
                                                        break;
                                                    case '<=':
                                                        sSelectedOperator = "lteq";
                                                        break;
                                                    case '!=':
                                                        sSelectedOperator = "neq";
                                                        break;
                                                }

                                                if (sSelectedOperator) {
                                                    $(document).find("#idTriagingVitalOperator_"+iRuleRowIndex).val(sSelectedOperator).trigger('change');
                                                }
                                            }

                                            //! Fetching triaging categories..
                                            fGetTriagingRuleCategories(iRuleRowIndex);

                                            //! Apply select2..
                                            applyChiefComplaintSearch(iRuleRowIndex);

                                            //! Apply select2..

                                            //! For fetching all chief complaint list..
                                            getAllDynamicVitalList(iRuleRowIndex);

                                            $("#idTriagingCategory_"+iRuleRowIndex).val(iCategoryID).trigger('change');
                                            $("#idTriagingVitalName_"+iRuleRowIndex).val(iVitalID).trigger('change');                                            
                                            iRuleRowIndex++;
                                            iAutoRowIndex++;
                                        });                                               
                                    }
                                    
                                    //! Appending Chief Complaints..
                                    if(iCategoryID == 2){
                                        bChiefComplaintExist = true;
                                        var sChiefComplaintTemplate = "";
                                        sChiefComplaintTemplate += '<div class="row classTriagingGroupRules" id="idTriagingGroupRules_'+iRuleRowIndex+'">';
                                            sChiefComplaintTemplate += '<div class="row">';
                                                sChiefComplaintTemplate += '<label class="col-md-2"><strong>Category</strong></label>';
                                                sChiefComplaintTemplate += '<div class="col-md-4">';
                                                    sChiefComplaintTemplate += '<select id="idTriagingCategory_'+iRuleRowIndex+'" name="idTriagingCategory_'+iTriagingGroupIndex+'[]" class="form-control classTriagingCategory" data-row-id="'+iRuleRowIndex+'"></select>';
                                                sChiefComplaintTemplate += '</div>';
                                                sChiefComplaintTemplate += '<div class="col-md-1">';
                                                    sChiefComplaintTemplate += '<a class="btn btn-sm btn-danger classGroupRemoveTriagingRule" data-row-id="'+iRuleRowIndex+'" id="idGroupRemoveTriagingRule_'+iRuleRowIndex+'" title="Remove Group Triage Rule" style="'+(bVitalExist == false ? "display:none;" : "")+'"><i aria-hidden="true" class="fa fa-trash"></i></a>';
                                                sChiefComplaintTemplate += '</div>';
                                            sChiefComplaintTemplate += '</div>';
                                            sChiefComplaintTemplate += '<div class="row classVitalCategoryContainer" id="idVitalCategoryContainer_'+iRuleRowIndex+'" style="display:none;">';
                                                sChiefComplaintTemplate += '<label class="col-md-2">';
                                                    sChiefComplaintTemplate += '<strong>Vital Name</strong>';
                                                sChiefComplaintTemplate += '</label>';
                                                sChiefComplaintTemplate += '<div class="col-md-3">';
                                                    sChiefComplaintTemplate += '<select id="idTriagingVitalName_'+iRuleRowIndex+'" name="idTriagingVitalName_'+iTriagingGroupIndex+'['+iRuleRowIndex+']" class="form-control classTriagingVitalName" style="width:100% !important;" data-group-id="'+iTriagingGroupIndex+'" data-row-id="'+iRuleRowIndex+'">';
                                                    sChiefComplaintTemplate += '</select>';
                                                sChiefComplaintTemplate += '</div>';
                                                sChiefComplaintTemplate += '<div class="col-md-3">';
                                                    sChiefComplaintTemplate += '<select id="idTriagingVitalOperator_'+iRuleRowIndex+'" name="idTriagingVitalOperator_'+iTriagingGroupIndex+'['+iRuleRowIndex+']" class="form-control classTriagingVitalOperator" style="width:100% !important;" data-group-id="'+iTriagingGroupIndex+'" data-row-id="'+iRuleRowIndex+'">';
                                                        sChiefComplaintTemplate += '<option value="eq"> = </option><option value="gt"> &gt; </option><option value="lt"> &lt; </option><option value="gteq"> &gt;= </option><option value="lteq"> &lt;= </option><option value="neq"> != </option>';
                                                    sChiefComplaintTemplate += '</select>';
                                                sChiefComplaintTemplate += '</div>';
                                                sChiefComplaintTemplate += '<div class="col-md-3">';
                                                    sChiefComplaintTemplate += '<input type="text" id="idTriagingVitalValue_'+iRuleRowIndex+'" name="idTriagingVitalValue_'+iTriagingGroupIndex+'['+iRuleRowIndex+']" class="form-control classTriagingVitalValue" style="width:100% !important;" data-group-id="'+iTriagingGroupIndex+'" data-row-id="'+iRuleRowIndex+'" />';
                                                sChiefComplaintTemplate += '</div>';
                                            sChiefComplaintTemplate += '</div>';
                                            sChiefComplaintTemplate += '<div class="row classVitalFieldCategoryContainer" id="idVitalFieldCategoryContainer_'+iRuleRowIndex+'" style="display:none;"></div>';
                                            sChiefComplaintTemplate += '<div class="row classChiefComplaintCategoryContainer" id="idChiefComplaintCategoryContainer_'+iRuleRowIndex+'" style="display:block;">';
                                                sChiefComplaintTemplate += '<label class="col-md-2">';
                                                    sChiefComplaintTemplate += '<strong>Symptoms</strong>';
                                                sChiefComplaintTemplate += '</label>';
                                                sChiefComplaintTemplate += '<div class="col-md-10">';
                                                    sChiefComplaintTemplate += '<select id="idTriagingChiefComplaintName_'+iRuleRowIndex+'" name="idTriagingChiefComplaintName_'+iTriagingGroupIndex+'[]" multiple class="form-control classTriagingChiefComplaintName" style="width:100% !important;">';
                                                    sChiefComplaintTemplate += '</select>';
                                                sChiefComplaintTemplate += '</div>';
                                            sChiefComplaintTemplate += '</div>';
                                            sChiefComplaintTemplate += '<hr>';
                                        sChiefComplaintTemplate += '</div>';

                                        $("#idTriagingRulesForGroup_"+iTriagingGroupIndex).append(sChiefComplaintTemplate);

                                        //! Apply Select2..
                                        $("#idTriagingVitalName_"+iRuleRowIndex).select2();
                                        $("#idTriagingVitalOperator_"+iRuleRowIndex).select2();


                                        //! Fetching triaging categories..
                                        fGetTriagingRuleCategories(iRuleRowIndex);

                                        //! For fetching all chief complaint list..
                                        applyChiefComplaintSearch(iRuleRowIndex);

                                        //! For fetching all chief complaint list..
                                        getAllDynamicVitalList(iRuleRowIndex);

                                        $.each(aRuleDetails, function(index, aRules){
                                            $.each(aRules, function(key, aRule){
                                                iAgeGroupID = aRule['iAgeGroupID'];
                                                if(aRule['iEntityID'] > 0){
                                                    $("#idTriagingChiefComplaintName_"+iRuleRowIndex).append($("<option selected></option>").attr("value",aRule['iEntityID']).text(aRule['sEntityName']));
                                                }
                                            })
                                        });

                                        $("#idTriagingCategory_"+iRuleRowIndex).val(iCategoryID).trigger('change');

                                        iRuleRowIndex++;                                                 
                                    }
                                });

                                //! For fetching all age groups..
                                fGetAllAgeGroups(iTriagingGroupIndex,iAgeGroupID);
                                $("#idPossibleDiagnosisName_"+iTriagingGroupIndex).select2({
                                    placeholder: 'Enter Diagnosis',
                                    tags: true
                                });

                                $("#idPossibleDrugsName_"+iTriagingGroupIndex).select2({
                                    placeholder: 'Enter Prescription',
                                    tags: true
                                });

                                getPrescriptionForTriageGroup(iGroupID).then(function(response){
                                    response.forEach((sDrug) => {
                                        var iGroupRowIndex = $(document).find("#idTriagingGroupID_"+iGroupID).val();
                                        $(document).find("#idPossibleDrugsName_"+iGroupRowIndex).append('<option value="'+sDrug+'" selected>'+sDrug+'</option>');
                                    });
                                }).catch(e => {
                                    console.log(`Empty Prescription!! ${e}`);
                                });

                                getDiagnosisForTriageGroup(iGroupID).then(function(response){
                                    response.forEach((sDiagnosis) => {
                                        var iGroupRowIndex = $(document).find("#idTriagingGroupID_"+iGroupID).val();
                                        $(document).find("#idPossibleDiagnosisName_"+iGroupRowIndex).append('<option value="'+sDiagnosis+'" selected>'+sDiagnosis+'</option>');
                                    });
                                }).catch(e => {
                                    console.log(`Empty Diagnosis!! ${e}`);
                                });

                                iTriagingGroupIndex++;
                                iii++;
                            });
                        }else{
                            iRuleRowIndex = 1;
                            iTriagingGroupIndex = 1;
                            var blankClone = $([
                                '<div class="classTriagingRulesGroupContainer" id="idTriagingRuleGroupContainer_'+iTriagingGroupIndex+'">',
                                    '<input type="hidden" id="idTrigingGroupIndex_'+iTriagingGroupIndex+'" name="idTrigingGroupIndex[]" value="'+iTriagingGroupIndex+'"/>',
                                    '<div class="row">',
                                        '<label class="col-md-3" style="font-size:20px;">Triaging Group '+iTriagingGroupIndex+'</label>',
                                        '<div class="col-md-3"><select class="form-control classAgeGroup" id="idAgeGroup_'+iTriagingGroupIndex+'" name="idAgeGroup_'+iTriagingGroupIndex+'"></select></div>',
                                        '<div class="col-md-6">',
                                            '<a class="btn btn-sm btn-primary classAddNewTriagingRuleGroup pull-right" id="idAddNewTriagingRuleGroup" name="idAddNewTriagingRuleGroup" data-group-id="'+iTriagingGroupIndex+'" title="Add New Rule"><i aria-hidden="true" class="fa fa-plus" ></i>&nbsp;&nbsp; Add New Rule For Group '+iTriagingGroupIndex+'</a>',
                                        '</div>',
                                    '</div>',
                                    '<br />',
                                    '<div class="classTriagingRulesForGroup" id="idTriagingRulesForGroup_'+iTriagingGroupIndex+'">',
                                        '<div class="row classTriagingGroupRules" id="idTriagingGroupRules_'+iRuleRowIndex+'">',
                                            '<div class="row">',
                                                '<label class="col-md-2"><strong>Category</strong></label>',
                                                '<div class="col-md-4">',
                                                    '<select id="idTriagingCategory_'+iRuleRowIndex+'" name="idTriagingCategory_'+iTriagingGroupIndex+'[]" class="form-control classTriagingCategory" data-row-id="'+iRuleRowIndex+'"></select>',
                                                '</div>',
                                            '</div>',
                                            '<div class="row classVitalCategoryContainer" id="idVitalCategoryContainer_'+iRuleRowIndex+'" style="display:none;">',
                                                '<label class="col-md-2">',
                                                    '<strong>Vital</strong>',
                                                '</label>',
                                                '<div class="col-md-3">',
                                                    '<select id="idTriagingVitalName_'+iRuleRowIndex+'" name="idTriagingVitalName_'+iTriagingGroupIndex+'['+iRuleRowIndex+']" class="form-control classTriagingVitalName" style="width:100% !important;" data-group-id="'+iTriagingGroupIndex+'" data-row-id="'+iRuleRowIndex+'">',
                                                    '</select>',
                                                '</div>',
                                                '<div class="col-md-3">',
                                                    '<select id="idTriagingVitalOperator_'+iRuleRowIndex+'" name="idTriagingVitalOperator_'+iTriagingGroupIndex+'['+iRuleRowIndex+']" class="form-control classTriagingVitalOperator" style="width:100% !important;" data-group-id="'+iTriagingGroupIndex+'" data-row-id="'+iRuleRowIndex+'">',
                                                        '<option value="eq"> = </option><option value="gt"> &gt; </option><option value="lt"> &lt; </option><option value="gteq"> &gt;= </option><option value="lteq"> &lt;= </option><option value="neq"> != </option>',
                                                    '</select>',
                                                '</div>',
                                                '<div class="col-md-3">',
                                                    '<input type="text" id="idTriagingVitalValue_'+iRuleRowIndex+'" name="idTriagingVitalValue_'+iTriagingGroupIndex+'['+iRuleRowIndex+']" class="form-control classTriagingVitalValue" style="width:100% !important;" data-group-id="'+iTriagingGroupIndex+'" data-row-id="'+iRuleRowIndex+'" />',
                                                '</div>',
                                            '</div>',
                                            '<div class="row classChiefComplaintCategoryContainer" id="idChiefComplaintCategoryContainer_'+iRuleRowIndex+'" style="display:none;">',
                                                '<label class="col-md-2">',
                                                    '<strong>Symptoms</strong>',
                                                '</label>',
                                                '<div class="col-md-10">',
                                                    '<select id="idTriagingChiefComplaintName_'+iRuleRowIndex+'" name="idTriagingChiefComplaintName_'+iTriagingGroupIndex+'[]" multiple class="form-control classTriagingChiefComplaintName" style="width:100% !important;">',
                                                    '</select>',
                                                '</div>',
                                            '</div>',
                                            '<hr>',
                                        '</div>',
                                    '</div>',
                                    '<div class="row classPossibleDrugContainer" id="idPossibleDrugsContainer_'+iTriagingGroupIndex+'">',
                                        '<label class="col-md-2">',
                                            '<strong>Prescription</strong>',
                                        '</label>',
                                        '<div class="col-md-10">',
                                            '<select id="idPossibleDrugsName_'+iTriagingGroupIndex+'" name="idPossibleDrugsName_'+iTriagingGroupIndex+'[]" multiple class="form-control classPossibleDrugsName" style="width:100% !important;">',
                                            '</select>',
                                        '</div>',
                                    '</div>',
                                    '<div class="row classPossibleDiagnosisContainer" id="idPossibleDiagnosisContainer_'+iTriagingGroupIndex+'">',
                                        '<label class="col-md-2">',
                                            '<strong>Diagnosis</strong>',
                                        '</label>',
                                        '<div class="col-md-10">',
                                            '<select id="idPossibleDiagnosisName_'+iTriagingGroupIndex+'" name="idPossibleDiagnosisName_'+iTriagingGroupIndex+'[]" multiple class="form-control classPossibleDiagnosisName" style="width:100% !important;">',
                                            '</select>',
                                        '</div>',
                                    '</div>',
                                    '<hr class="classGroupHorizontalHr">',
                                '</div>'
                            ].join(""));

                            var iClone = blankClone.clone();
                            $(".classTriagingRulesMainContainer").append(iClone);

                            //! Apply Select2..
                            $("#idTriagingVitalName_"+iRuleRowIndex).select2();
                            $("#idTriagingVitalOperator_"+iRuleRowIndex).select2();

                            fGetTriagingRuleCategories(iRuleRowIndex);
                            applyChiefComplaintSearch(iRuleRowIndex);
                            getAllDynamicVitalList(iRuleRowIndex);
                            fGetAllAgeGroups(iTriagingGroupIndex);
                            $("#idPossibleDiagnosisName_"+iTriagingGroupIndex).select2({
                                placeholder: 'Enter Diagnosis',
                                tags: true
                            });

                            $("#idPossibleDrugsName_"+iTriagingGroupIndex).select2({
                                placeholder: 'Enter Prescription',
                                tags: true
                            });

                            iTriagingGroupIndex++;
                            iRuleRowIndex++;
                        }

                        createPagination(iTriageID);
                    },
                    complete: function(){
                        $.LoadingOverlay("hide");
                    }
                });
            }

            //! Function for fetching triaging details by triage_id..
            function fGetTriagingDetailsByTriageID(iTriageID){
                $.ajax({
                    url: "ajaxTriage.php?sFlag=fGetTriagingDetailsByTriageID",
                    data:{
                        iTriageID:iTriageID
                    },
                    beforeSend: function(){
                        $.LoadingOverlay("show");
                    },
                    success: function (data){
                        if($.trim(data) != false){
                            $("#idTriagingName").val(data.triage_name);
                            $("#idTriagingDescription").val(data['description']);
                            var sBackgroundStyle = 'background:'+data['hexadecimal_code'];
                            $("#idTriagingColor").append('<option value="'+data['color_id']+'" style="'+sBackgroundStyle+'">'+data['color_name']+'</option>');
                            $("#idTriagingColor").css("background",data['hexadecimal_code']);
                        }
                    },
                    complete: function(){
                        $.LoadingOverlay("hide");
                    }
                });
            }

            //! Function for fetching triaging rule categories..
            function fGetTriagingRuleCategories(iRowID){
                $("#idTriagingCategory_"+iRowID).empty();
                if(aRuleCategories.length > 0){
                    $("#idTriagingCategory_"+iRowID).append('<option value="">Select</option>');
                    for(var iii=0; iii<aRuleCategories.length;iii++){
                        var iCategoryID = aRuleCategories[iii]['triaging_category_id'];
                        var sCategoryName = aRuleCategories[iii]['category_name'];
                        $("#idTriagingCategory_"+iRowID).append('<option value="'+iCategoryID+'">'+sCategoryName+'</option>');
                    }
                    $("#idTriagingCategory_"+iRowID).select2();
                }       
            }

            //! Function for fetching chief complaints list..
            function getAllDynamicVitalList(iRowID){
                $("#idTriagingVitalName_"+iRowID).empty();
                $("#idTriagingVitalName_"+iRowID).append('<option value="">Select Any</option>');
                if(!$.isEmptyObject(aAllDynamicVitals)){
                    $.each(aAllDynamicVitals, function( iIndex, aVital ) {
                        var iVitalID = aVital.vital_id,
                            sVitalLabel = aVital.vital_name;
                        $("#idTriagingVitalName_"+iRowID).append('<option value="'+iVitalID+'">'+sVitalLabel+'</option>');
                    });
                    $("#idTriagingVitalName_"+iRowID).select2();
                }
            }

            //! Function for fetching chief complaints list..
            function fGetAllAgeGroups(iGroupIndex,iAgeGroupID=0){
                $("#idAgeGroup_"+iGroupIndex).empty();
                $("#idAgeGroup_"+iGroupIndex).append('<option value="0">Select Any</option>');
                if(!$.isEmptyObject(aAllAgeGroups)){
                    $.each(aAllAgeGroups, function( index, value ) {
                        var iID = value.iID;
                        var sValue = value.sAgeGroupName+" ["+value.sGender+"]";
                        if(iAgeGroupID == iID){
                            $("#idAgeGroup_"+iGroupIndex).append('<option value="'+iID+'" selected>'+sValue+'</option>');
                        }else{
                            $("#idAgeGroup_"+iGroupIndex).append('<option value="'+iID+'">'+sValue+'</option>');
                        }
                    });
                    $("#idAgeGroup_"+iGroupIndex).select2();
                }
            }

            // Select 2..
            function applyChiefComplaintSearch(iRowID){       
                $('#idTriagingChiefComplaintName_'+iRowID).select2({
                    width: '100%',
                    placeholder: 'Search Symptoms',
                    minimumInputLength: 3,
                    allowClear: true,
                    ajax: {
                        url: "ajaxTriage.php?sFlag=fGetChiefComplaintsByName",
                        data: function (params) {
                            return {
                                sComplaintName: params.term                     
                            };
                        },
                        processResults: function (data, params) {
                            var results = [];                   
                            $.each(data, function(key, ChiefComplaints) {
                                results.push({
                                    id: ChiefComplaints.chief_complaint_id,
                                    text: ChiefComplaints.chief_complaint
                                });
                            });
                            return { results: results };
                        }
                    }
                });
            }

            //! Function for deleting triage group..
            function deleteTriageGroupDetails(iTriageGroupID) {
                $.ajax({
                    url: "ajaxTriage.php?sFlag=deleteTriageGroupDetails",
                    type:"POST",
                    data:{iTriageID:iTriageID,iTriageGroupID:iTriageGroupID},
                    success: function (data){
                        if (data.result) {
                            pNotifyAlert('Group deleted successfully', 'success');
                        } else {
                            pNotifyAlert('Error while deleting triaging group', 'error');
                        }
                    }
                });
            }

            const getPrescriptionForTriageGroup = async(iTriageGroupID) => {
                return await new Promise((resolve, reject) => {
                    $.ajax({
                        type: 'POST',
                        url: 'ajaxTriage.php?sFlag=getPrescriptionForTriageGroup',
                        data: {iTriageGroupID:iTriageGroupID},
                        beforeSend: function(){
                            $.LoadingOverlay("show");
                        },
                        success: function(data) {
                            if (data.length) {
                                resolve(data);
                            } else {
                                reject(false);
                            }
                        },
                        complete: function(){
                            $.LoadingOverlay("hide");
                        }
                    });
                });
            }

            const getDiagnosisForTriageGroup = async(iTriageGroupID) => {
                return await new Promise((resolve, reject) => {
                    $.ajax({
                        type: 'POST',
                        url: 'ajaxTriage.php?sFlag=getDiagnosisForTriageGroup',
                        data: {iTriageGroupID:iTriageGroupID},
                        beforeSend: function(){
                            $.LoadingOverlay("show");
                        },
                        success: function(data) {
                            if (data.length) {
                                resolve(data);
                            } else {
                                reject(false);
                            }
                        },
                        complete: function(){
                            $.LoadingOverlay("hide");
                        }
                    });
                });
            }
        </script>
    </body>
</html>