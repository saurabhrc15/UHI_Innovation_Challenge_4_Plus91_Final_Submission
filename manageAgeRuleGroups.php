<?php
include_once dirname(__FILE__).DIRECTORY_SEPARATOR."config/config.php";

$sPageTopTitle = "Manage Age Groups";

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
                <div class="container-fluid classTriagingListingPanel">
                    <div class="panel panel-primary">
                        <div class="panel-heading classTriagingHeaderListing">
                            <i class="fa fa-list-alt" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo $sPageTopTitle;?>
                            <a class="btn btn-sm btn-success classAddNewAgeGroupStatus pull-right" title="Add New Triaging" data-toggle="modal" data-target=".classModalMedicalManagementAgeGroupsContainer"><i aria-hidden="true" class="fa fa-plus"></i></a>
                        </div>
                        <div class="panel-body" id="idMedicalManagementAgeGroupListing">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12"> 
                                <table class="table table-hover" id="idTableMedicalManagementAgeGroupListng" >
                                    <thead style="color:#884646;" > 
                                        <tr>
                                            <th width="">#</th>
                                            <th width="">Age Group Name</th>
                                            <th width="">Age From</th>
                                            <th width="">Age To</th>
                                            <th width="">Gender</th>
                                            <th width="">Added On</th>
                                            <th width="">Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody id="idBodyMedicalManagementAgeGroupListing"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade classModalMedicalManagementAgeGroupsContainer" id="idModalMedicalManagementAgeGroupContainer" tabindex="-1" role="dialog" aria-labelledby="idModalMedicalManagementAgeGroupContainerLabel">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="idModalMedicalManagementAgeGroupContainerLabel">Add New Medical Management Age Group</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <label class="col-md-3"><strong>Name<font color="red">*</font></strong></label>
                            <div class="col-md-6">
                                <input type="text" name="idAgeGroupName" id="idAgeGroupName" class="form-control classTriagingName" placeholder="Enter Age Group Name" />
                            </div>
                        </div>

                        <div class="row">
                            <label class="col-md-3"><strong>Gender<font color="red">*</font></strong></label>
                            <div class="col-md-6">
                                <select name="idAgeGroupGender" id="idAgeGroupGender" class="form-control">
                                    <option value="0">All</option>
                                    <option value="1">Male</option>
                                    <option value="2">Female</option>
                                    <option value="3">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <label class="col-md-3"><strong>Age From<font color="red">*</font></strong></label>
                            <div class="col-md-6">
                                <input type="number" name="idAgeFrom" id="idAgeFrom" class="form-control" placeholder="Enter Age Group From Age" />
                            </div>
                        </div>

                        <div class="row">
                            <label class="col-md-3"><strong>Age To<font color="red">*</font></strong></label>
                            <div class="col-md-6">
                                <input type="number" name="idAgeTo" id="idAgeTo" class="form-control" placeholder="Enter Age Group To Age" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="idAddNewMedicalManagementAgeGroup">Save</button>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">   
            $(document).ready(function(){               

                //! Fetching all triaging masters..
                fGetAllMedicalManagementAgeGroups();

                //! Delete age group..
                $("#idBodyMedicalManagementAgeGroupListing").on("click",".classDeleteAgeGroup",function(){
                    var iID = $(this).data("id");
                    if(confirm("Are you really want to delte this age group")){
                        deleteMedicalManagementAgeGroup(iID);                        
                    }
                });


                //! Adding new triaging status..
                $('.classModalMedicalManagementAgeGroupsContainer').on('click','#idAddNewMedicalManagementAgeGroup',function(){
                    var sAgeGroupName = $('#idAgeGroupName').val();
                    var iAgeFrom = $('#idAgeFrom').val();
                    var iAgeTo = $('#idAgeTo').val();
                    var sGender = $('#idAgeGroupGender').val();

                    if(sAgeGroupName == ''){
                        pNotifyAlert('Please enter age group name',"error");
                        return false;
                    }

                    if(iAgeFrom == ''){
                        pNotifyAlert('Please enter from age',"error");
                        return false;
                    }

                    if(iAgeTo == ''){
                        pNotifyAlert('Please enter to age',"error");
                        return false;
                    }

                    if(sGender == ''){
                        pNotifyAlert('Please enter gender',"error");
                        return false;
                    }

                    if(parseInt(iAgeFrom) > parseInt(iAgeTo) || parseInt(iAgeTo) < parseInt(iAgeFrom) || parseInt(iAgeTo) == 0 || parseInt(iAgeTo) < 0){
                        pNotifyAlert('Please enter valid age groups',"error");
                        return false;
                    }

                    $.ajax({
                        type: 'POST',
                        url: "ajaxMedicalManagement.php?sFlag=addNewMedicalManagementAgeGroup",
                        data: {
                            sAgeGroupName:sAgeGroupName,
                            iAgeFrom:iAgeFrom,
                            iAgeTo:iAgeTo,
                            sGender:sGender,
                        },
                        beforeSend: function(){
                            $.LoadingOverlay("show");
                        },
                        success: function (data){
                            if(data.result){
                                pNotifyAlert("Age groups details added successfully","success");
                                $("#idAgeGroupName").val("");
                                $("#idAgeFrom").val("");
                                $("#idAgeTo").val("");
                                $("#idAgeGroupGender").val("0");
                                $(".classModalMedicalManagementAgeGroupsContainer").modal('hide');
                                fGetAllMedicalManagementAgeGroups();
                            }else{
                                pNotifyAlert('Error while adding age group details',"error");
                                return false;
                            }
                        },
                        complete: function(){
                            $.LoadingOverlay("hide");
                        }
                    });
            
                });

            });

            //! Function for deleting medical management age group..
            function deleteMedicalManagementAgeGroup(iID){
                $.ajax({
                    type: 'POST',
                    url: "ajaxMedicalManagement.php?sFlag=deleteMedicalManagementAgeGroup",
                    data: {iID:iID},
                    beforeSend: function(){
                        $.LoadingOverlay("show");
                    },
                    success: function (data){
                        if($.trim(data) != false){
                            pNotifyAlert('Age group deleted successfully.',"success");
                            fGetAllMedicalManagementAgeGroups();
                        }else{
                            pNotifyAlert('Something went wrong!',"error");
                            return false;                            
                        }
                    },
                    complete: function(){
                        $.LoadingOverlay("hide");
                    }
                });
            }


            //! Function for fetching triaging colors which has not added before to any triaging..
            function fGetAllMedicalManagementAgeGroups(){
                $.ajax({
                    url: "ajaxMedicalManagement.php?sFlag=fGetAllMedicalManagementAgeGroups",
                    beforeSend: function(){
                        $.LoadingOverlay("show");
                    },
                    success: function (data){
                        if($.trim(data) != false){
                            if(data.length > 0){
                                var sTemplate = '';
                                var iSrNo = 1;
                                for(var iii=0; iii< data.length;iii++){
                                    var iID = data[iii]['iID'];
                                    var sAgeGroupName = data[iii]['sAgeGroupName'];
                                    var iAgeFrom = data[iii]['iAgeFrom'];
                                    var iAgeTo = data[iii]['iAgeTo'];
                                    var sGender = data[iii]['sGender'];
                                    var dAddedOn = data[iii]['dAddedOn'];
                                    var sAction = '<a class="btn btn-sm btn-danger classDeleteAgeGroup" data-id="'+iID+'" title="Delete Age Group" style="margin-top: 0px;"><i class="fa fa-trash"></i></a>';

                                    sTemplate += '<tr>';
                                        sTemplate += '<td>'+iSrNo+'</td>';
                                        sTemplate += '<td>'+sAgeGroupName+'</td>';
                                        sTemplate += '<td>'+iAgeFrom+'</td>';
                                        sTemplate += '<td>'+iAgeTo+'</td>';
                                        sTemplate += '<td>'+sGender+'</td>';
                                        sTemplate += '<td>'+dAddedOn+'</td>';
                                        sTemplate += '<td>'+sAction+'</td>';
                                    sTemplate += '</tr>';
                                    iSrNo++;
                                }
                                $("#idBodyMedicalManagementAgeGroupListing").html(sTemplate);
                            }else{
                                var sTemplate = '<tr><td colspan="6" style="text-align:center;">No Data Found</td></tr>'
                                $("#idBodyMedicalManagementAgeGroupListing").html(sTemplate);
                            }
                        }else{
                            var sTemplate = '<tr><td colspan="6" style="text-align:center;">No Data Found</td></tr>'
                            $("#idBodyMedicalManagementAgeGroupListing").html(sTemplate);
                        }
                    },
                    complete: function(){
                        $.LoadingOverlay("hide");
                    }
                });
            }
        </script>
    </body>
</html>