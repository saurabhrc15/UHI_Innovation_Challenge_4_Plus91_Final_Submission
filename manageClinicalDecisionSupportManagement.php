<?php
include_once dirname(__FILE__).DIRECTORY_SEPARATOR."config/config.php";

$sPageTopTitle = "Clinical Decision Support Management";

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
                            <div class="panel panel-primary">
                                <div class="panel-heading classTriagingHeaderListing">
                                    <i class="fa fa-list-alt" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo $sPageTopTitle; ?>
                                    <a class="btn btn-sm btn-success classAddNewTriagingStatus pull-right" title="Add New Triaging" data-toggle="modal" data-target=".classModalTriagingContainer"><i aria-hidden="true" class="fa fa-plus"></i></a>
                                    <a href="manageAgeRuleGroups.php" class="btn btn-sm btn-warning classManageAgeRuleGroups pull-right" title="Manage Age Rule Groups"><i aria-hidden="true" class="fa fa-pencil"></i></a>
                                </div>
                                <div class="panel-body" id="idTriagingListing">
                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12"> 
                                        <table class="table table-hover table-condensed table-responsive" id="idTableTriagingListng" >
                                            <thead style="color:#884646;"> 
                                                <tr>
                                                    <th>#</th>
                                                    <th>Triage Name</th>
                                                    <th>Color Name</th>
                                                    <th>Description</th>
                                                    <th>Manage</th>
                                                </tr>
                                            </thead>
                                            <tbody id="idBodyTriagingListing"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal -->
                <div class="modal fade classModalTriagingContainer" id="idModalTriagingContainer" tabindex="-1" role="dialog" aria-labelledby="idModalTriagingContainerLabel">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h3 class="modal-title" id="idModalTriagingContainerLabel" style="text-align: center;"><strong>Add New Triage</strong></h3>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <label class="col-md-3"><strong>Triage Name<font color="red">*</font></strong></label>
                                    <div class="col-md-6">
                                        <input type="text" name="idTriagingName" id="idTriagingName" class="form-control classTriagingName" placeholder="Enter Triage Name..." />
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
                                        <textarea name="idTriagingDescription" id="idTriagingDescription" class="form-control classTriagingDescription" placeholder="Enter Description..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" id="idAddNewTriagingStatus" style="margin-top:0px;">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            $(document).ready(function(){
                //! Opening modal..
                $(document).on("click",".classAddNewTriagingStatus",function(){
                    //! Fetching available triaging color masters..
                    fGetAvailableTriagingColorMasters();
                });

                $("#idBodyTriagingListing").on("click",".classFreezeUnfreezeTriaging",function(){
                    var iTriageID = $(this).data("triage-id");
                    var iStatusID = $(this).data("status-id");
                    if(iStatusID == 1){
                        if(confirm("Are you really want to freeze this triaging status")){
                            fFreezeUnfreezeTriagingStatus(iTriageID,iStatusID);                        
                        }                        
                    }else{
                        if(confirm("Are you really want to unfreeze this triaging status")){
                            fFreezeUnfreezeTriagingStatus(iTriageID,iStatusID);                        
                        }
                    }
                });

                //! Fetching all triaging masters..
                fGetAllTriagingMaster();

                //! Adding new triaging status..
                $('.classModalTriagingContainer').on('click','#idAddNewTriagingStatus',function(){
                    var sTriagingName = $('#idTriagingName').val();
                    var iTriagingColor = $('#idTriagingColor').val();
                    var sTriagingDescription = $('#idTriagingDescription').val();

                    if(sTriagingName == ''){
                        pNotifyAlert('Please enter triaging name', "error");
                        return false;
                    }
                    if(iTriagingColor == ''){
                        pNotifyAlert('Please select triaging color', "error");
                        return false;
                    }

                    $.ajax({
                        url: "ajaxTriage.php?sFlag=addNewTriagingStatus",
                        data: {
                            sTriagingName:sTriagingName,
                            iTriagingColor:iTriagingColor,
                            sTriagingDescription:sTriagingDescription
                        },
                        beforeSend: function(){
                            $.LoadingOverlay("show");
                        },
                        success: function (data){
                            if(data.result){
                                pNotifyAlert("Triaging details added successfully", "success");
                                $(".classModalTriagingContainer").modal("hide");
                                $("#idTriagingName").val("");
                                $("#idTriagingColor").val("");
                                $("#idTriagingDescription").val("");
                                fGetAllTriagingMaster();
                            }else{
                                pNotifyAlert('Error while adding triaging details', "error");
                                return false;
                            }
                        },
                        complete: function(){
                            $.LoadingOverlay("hide");
                        }
                    });
            
                });

            });         

            //! Function for fetching triaging colors which has not added before to any triaging..
            function fGetAvailableTriagingColorMasters(){
                $.ajax({
                    url: "ajaxTriage.php?sFlag=fGetAvailableTriagingColorMasters",
                    success: function (data){
                        if($.trim(data) != false){
                            $("#idTriagingColor").empty();
                            if(data.length > 0){
                                for(var iii=0; iii<data.length;iii++){
                                    var iColorID = data[iii]['color_id'];
                                    var sColorName = data[iii]['color_name'];
                                    var sHexadecimalCode = data[iii]['hexadecimal_code'];
                                    var iIsDefault = data[iii]['is_default'];
                                    var sBackgroundStyle = 'background:'+sHexadecimalCode;
                                    $("#idTriagingColor").append('<option value="'+iColorID+'" style="'+sBackgroundStyle+'">'+sColorName+'</option>');
                                }
                            }
                        }
                    }
                });
            }

            //! Function for fetching triaging colors which has not added before to any triaging..
            function fFreezeUnfreezeTriagingStatus(iTriageID,iStatusID){
                $.ajax({
                    url: "ajaxTriage.php?sFlag=fFreezeUnfreezeTriagingStatus",
                    data:{
                        iTriageID:iTriageID,
                        iStatusID:iStatusID
                    },
                    beforeSend: function(){
                        $.LoadingOverlay("show");
                    },
                    success: function (data){
                        if(data.result){
                            if(iStatusID == 1){
                                pNotifyAlert("Triaging freeze successfully", "success");
                            }else{
                                pNotifyAlert("Triaging unfreeze successfully", "success");
                            }
                            fGetAllTriagingMaster();
                        }else{
                            if(iStatusID == 1){
                                pNotifyAlert('Error while freezing triaging details', "error");
                            }else{
                                pNotifyAlert('Error while unfreezing triaging details', "error");
                            }
                            return false;
                        }
                    },
                    complete: function(){
                        $.LoadingOverlay("hide");
                    }
                });
            }

            //! Function for fetching triaging colors which has not added before to any triaging..
            function fGetAllTriagingMaster(){
                $.ajax({
                    url: "ajaxTriage.php?sFlag=fGetAllTriagingMaster",
                    beforeSend: function(){
                        $.LoadingOverlay("show");
                    },
                    success: function (data){
                        if($.trim(data) != false){
                            if(data.length > 0){
                                var sTemplate = '';
                                var iSrNo = 1;
                                for(var iii=0; iii<data.length;iii++){
                                    var iTriageID = data[iii]['triage_id'];
                                    var sTriagingName = data[iii]['triage_name'];
                                    var sTriagingColor = data[iii]['color_name'];
                                    var sDescription = data[iii]['description'];
                                    if(sDescription == "" || sDescription == null){
                                        sDescription = "NA";
                                    }
                                    var iFreeze = data[iii]['freeze'];

                                    if(iFreeze == 0){
                                        var sPageName = "manageTriagingStatus.php";
                                        var sActionBtn = '<a href="'+sPageName+'?iTriageID='+iTriageID+'" class="btn btn-sm btn-primary classManageTriagingStatus" id="idManageTriagingStatus" title="Manage Triaging Status"><i aria-hidden="true" class="fa fa-tasks"></i></a>    <a class="btn btn-sm btn-danger classFreezeUnfreezeTriaging" data-status-id="1" data-triage-id="'+iTriageID+'" title="Freeze Triaging Status"><i class="fa fa-bell" aria-hidden="true"></i></a>';
                                    }else{
                                        var sActionBtn = '<a href="javascript:void(0)" class="btn btn-sm btn-primary classManageTriagingStatus" id="idManageTriagingStatus" title="Manage Triaging Status" disabled><i aria-hidden="true" class="fa fa-tasks"></i></a>    <a class="btn btn-sm btn-success classFreezeUnfreezeTriaging" data-status-id="0" data-triage-id="'+iTriageID+'" title="Unfreeze Triaging Status"><i class="fa fa-bell-slash" aria-hidden="true"></i></a>';
                                    }
                                    
                                    sTemplate += '<tr>';
                                        sTemplate += '<td>'+iSrNo+'</td>';
                                        sTemplate += '<td>'+sTriagingName+'</td>';
                                        sTemplate += '<td>'+sTriagingColor+'</td>';
                                        sTemplate += '<td>'+sDescription+'</td>';
                                        sTemplate += '<td>'+sActionBtn+'</td>';
                                    sTemplate += '</tr>';
                                    iSrNo++;
                                }
                                $("#idBodyTriagingListing").html(sTemplate);
                            }else{
                                var sTemplate = '<tr><td colspan="5" style="text-align:center;">No Data Found</td></tr>'
                                $("#idBodyTriagingListing").html(sTemplate);
                            }
                        }else{
                            var sTemplate = '<tr><td colspan="5" style="text-align:center;">No Data Found</td></tr>'
                            $("#idBodyTriagingListing").html(sTemplate);
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