<?php
include_once dirname(__FILE__).DIRECTORY_SEPARATOR."config/config.php";

$sPageTopTitle = "Clinical Support System";
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'mxcelHeaderB3.php'; ?>
    </head>

    <body class="classActivityDashboard stickyMedixcelFooter">
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
                        <div class="row">
                            <div class="col-md-4 classButtonContainer">
                                <a href="manageClinicalDecisionSupportManagement.php" class="btn btn-primary classMedicalManagementDashboardButton btn-lg btn-block" title="Clinical Decision Support Management"><span class="icon fa fa-sitemap fa-2x pull-left classIconContainer"></span><span class="title">Clinical Decision Support Management</span></a>
                            </div>

                            <div class="col-md-4 classButtonContainer">
                                <a href="manageTriageChecker.php" class="btn btn-primary classMedicalManagementDashboardButton btn-lg btn-block" title="Triage Checker"><span class="icon fa fa-hospital-o fa-2x pull-left classIconContainer"></span><span class="title">Triage Checker</span></a>
                            </div>         
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>