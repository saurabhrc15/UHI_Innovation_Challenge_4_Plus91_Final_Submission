<?php
include_once dirname(__FILE__).DIRECTORY_SEPARATOR."config/config.php";

$sPageTopTitle = "Triage Checker";

$aBreadcrumb = [
    [
        "title"=>'Dashboard',
        "link"=>"medicalManagement.php"
    ],
    [
        "title"=>$sPageTopTitle,
        "link"=>"#",
        "isActive"=> true
    ]
];
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
                    <div class="col-lg-12">
                        <h2 class="" style="margin: 10px 0px 30px 14px;"><?php echo $sPageTopTitle; ?></h2>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="container-fluid classTriagingListingPanel">
                            <div class="row">
                                <form method="post" id="idFormCheckTriage" name="idFormCheckTriage" class="form-horizontal">
                                    <div class="form-group">
                                        <label class="col-md-2">Birth Year<span class='text-danger'>*</span></label>
                                        <div class="col-md-4">
                                            <select class="form-control classBirthYear" id="idBirthYear" name="idBirthYear"></select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2">Gender<span class='text-danger'>*</span></label>
                                        <div class="col-md-4">
                                            <select class="form-control classGender" id="idGender" name="idGender">
                                                <option value="1">Male</option>
                                                <option value="2">Female</option>
                                                <option value="3">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2">Symptoms<span class='text-danger'>*</span></label>
                                        <div class="col-md-8">
                                            <select class="form-control classSymptoms" id="idSymptoms" name="idSymptoms[]" multiple>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="classVitalMainContainer">
                                        <div class="form-group classVitalChildContainer">
                                            <label class="col-md-2">Vitals</label>
                                            <div class="col-md-4">
                                                <select class="form-control classVitalID" id="idVitalID_1" name="idVitalID[]">
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" class="form-control classVitalValue" id="idVitalValue_1" name="idVitalValue[]" />
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <a class="btn btn-xs btn-success classAddAnotherVital" id="idAddAnotherVital" name="idAddAnotherVital" onclick="addNewVital()">Add Another</a>  
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2"></label>
                                        <div class="col-md-4">
                                            <a class="btn btn-md btn-primary classBtnCheckTriage" id="idBtnCheckTriage">Generate Triage</a>
                                        </div>
                                    </div>
                                </form>
                                <div class="classShowTriage">
                                    <h3 style="text-align:center;"></h3>
                                    <div class="classShowOtherInfo"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            let aAllDynamicVitals = [];
            let iVitalRowIndex = 1;
            let blankClone = $([
                '<div class="form-group classVitalChildContainer">',
                    '<label class="col-md-2"></label>',
                    '<div class="col-md-4">',
                        '<select class="form-control classVitalID" id="idVitalID_{iRowIndex}" name="idVitalID[]">',
                        '</select>',
                    '</div>',
                    '<div class="col-md-4">',
                        '<input type="text" class="form-control classVitalValue" id="idVitalValue_{iRowIndex}" name="idVitalValue[]" />',
                    '</div>',
                    '<div class="col-md-2">',
                        '<a class="btn btn-xs btn-danger classRemoveVital">Move to Trash</a>',
                    '</div>',
                '</div>'
            ].join(""));

            $(function() {
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

                let iCurrentYear = new Date().getFullYear();
                let iStartYear = iCurrentYear - 120;
                let iSelectYear = iCurrentYear - 30;

                $.each(range(iStartYear, iCurrentYear), function(iIndex, iYear){
                    $(document).find("#idBirthYear").append($("<option></option>").attr("value",iYear).text(iYear));
                });

                $(document).find("#idBirthYear").val(iSelectYear);
                $(document).find("#idBirthYear").select2();
                $(document).find("#idGender").select2();

                function range(start, end) {
                    var ans = [];
                    for (let i = start; i <= end; i++) {
                        ans.push(i);
                    }
                    return ans;
                }

                getAllDynamicVitalList(iVitalRowIndex);
                iVitalRowIndex++;

                $('.classSymptoms').select2({
                    width: '100%',
                    placeholder: 'Select Symptoms',
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

                $(document).on('click', '.classRemoveVital', function(){
                    $(this).parent().parent().parent().remove();
                });

                $(document).on('click', '.classBtnCheckTriage', function(){
                    var iBirthYear = $(document).find("#idBirthYear").val() ? $(document).find("#idBirthYear").val() : 0;
                    var aSymptoms = $(document).find("#idSymptoms").val() ? $(document).find("#idSymptoms").val() : [];
                    var sGender = $(document).find("#idGender").val() ? $(document).find("#idGender").val() : '';
                    $(document).find(".classShowTriage").find("h3").html("");
                    $(document).find(".classShowTriage").find(".classShowOtherInfo").html("");

                    if (iBirthYear == 0) {
                        pNotifyAlert('Please select patient birth year');
                        return false;
                    }

                    if (aSymptoms.length == 0) {
                        pNotifyAlert('Please select symptoms');
                        return false;
                    }

                    if (sGender == '') {
                        pNotifyAlert('Please select patient gender');
                        return false;
                    }

                    data = new FormData($('#idFormCheckTriage')[0]);

                    generateTriage(data).then(function(response){
                        let iHighestPriorityTriage = response.highest_priority_triage;
                        const aPickedTriage = jQuery.grep(response.satisfied_triage, function( n, i ) {
                            return (n.triage_id == iHighestPriorityTriage);
                        });

                        if (aPickedTriage.length > 0) {
                            const oPickedTriage = Object.assign({}, ...aPickedTriage);
                            $(document).find(".classShowTriage").find("h3").html(oPickedTriage.triage_name);
                            $(document).find(".classShowTriage").find("h3").css('background-color', oPickedTriage.hexadecimal_code);
                            if (response.possible_prescription) {
                                $(document).find(".classShowTriage").find(".classShowOtherInfo").append(`
                                    <br>
                                    <h4><u><strong>Possible Prescription</strong></u></h4>
                                    ${response.possible_prescription.join("&nbsp;&#8226;&nbsp;")}
                                    <br>
                                `);
                            }

                            if (response.possible_diagnosis) {
                                $(document).find(".classShowTriage").find(".classShowOtherInfo").append(`
                                    <br>
                                    <h4><u><strong>Possible Diagnosis</strong></u></h4>
                                    ${response.possible_diagnosis.join("&nbsp;&#8226;&nbsp;")}
                                `);
                            }
                        } else {
                            $(document).find(".classShowTriage").find("h3").html("No Possible Triage Found");
                            $(document).find(".classShowTriage").find("h3").css('background-color', '');
                        }
                    }).catch(e => {
                        console.log(e);
                        $(document).find(".classShowTriage").find("h3").html("No Triage Found");
                    });
                });
            });

            //! Function for inserting blank row..
            function addNewVital() {
                var iClone = blankClone.clone();
                iClone.html(function(i, oldHTML) {
                    return oldHTML.replace(/\{iRowIndex}/g, iVitalRowIndex);
                });
                $(document).find(".classVitalMainContainer").append(iClone);

                //! Append values..
                getAllDynamicVitalList(iVitalRowIndex);

                iVitalRowIndex++;
            }

            //! Function for fetching chief complaints list..
            function getAllDynamicVitalList(iRowID){
                $(document).find("#idVitalID_"+iRowID).empty();
                $(document).find("#idVitalID_"+iRowID).append('<option value="">Select Any</option>');
                if(aAllDynamicVitals.length > 0){
                    $.each(aAllDynamicVitals, function( iIndex, aVital ) {
                        var iVitalID = aVital.vital_id,
                            sVitalLabel = aVital.vital_name;
                        $(document).find("#idVitalID_"+iRowID).append('<option value="'+iVitalID+'">'+sVitalLabel+'</option>');
                    });
                    $(document).find("#idVitalID_"+iRowID).select2();
                }
            }

            const generateTriage = async(aFormData) => {
                return await new Promise((resolve, reject) => {
                    $.ajax({
                        type: 'POST',
                        url: 'ajaxTriage.php?sFlag=generateTriage',
                        data: aFormData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        beforeSend: function(){
                            $.LoadingOverlay("show");
                        },
                        success: function(data) {
                            if (data.highest_priority_triage > 0) {
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