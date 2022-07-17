<style type="text/css">
	/* Important part */
	.modal-dialog{
	    overflow-y: initial !important
	}
	#idModalMIS .modal-body{
		margin-top: 10%;
	    height: 400px;
	    overflow-y: auto;
	}
	#idModalMIS{
		margin-top: 6%;
	}
</style>

<div class="app-container">
 	<div class="row content-container">

		<nav class="navbar navbar-custom navbar-fixed-top">
			<div class="container-fluid">
				<div class="navbar-header classBaseTopHeader" style="display: inline-block;width: 80%;">
				  <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
				    <span class="sr-only"><?php echo($sPageTopTitle) ?></span>
				    <span class="icon-bar"></span>
				    <span class="icon-bar"></span>
				    <span class="icon-bar"></span>
				  </button>
				 	<a class="navbar-brand" href="#">
				        <span class="title"><?php echo(CONFIG_BRAND_NAME) ?></span>
				        <ol class="breadcrumb navbar-breadcrumb classOLBreadCrumbs" style="margin-bottom: 0;padding: 0;background-color: #3e4651;line-height: 50px;">
		                    <?php echo parseBreadcrumb($aBreadcrumb); ?>
		                </ol>
					</a>
				</div>

				<!-- Collect the nav links, forms, and other content for toggling -->
				<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
					<ul class="nav navbar-nav navbar-right">
						<?php 
						if (!empty($aRightNabvarLinks)) {
							foreach ($aRightNabvarLinks as $aCurrentLink) {
								?>
				        		<li>
				        			<a title="<?php echo($aCurrentLink['title']) ?>" href="<?php echo($aCurrentLink['link']) ?>">
				        				<i class="<?php echo($aCurrentLink['icons']) ?>"></i>
				        				<?php echo($aCurrentLink['text']) ?>
				        			</a>
				        		</li>
								<?php
							}
						}
						?>
				        <li><a title="Home" href="medicalManagement.php"><i class="fa fa-1x5x fa-home"></i></a></li>
					</ul>
				</div>
			</div>
		</nav>

		<!-- For slider -->
		<div class="side-menu">
		    <nav class="navbar navbar-default" role="navigation">
		        <div class="side-menu-container">
		        	<div class="navbar-header classSliderHeader">
                        <a class="navbar-brand" href="#">
                            <div class="icon fa fa fa-bars icon classTextColor"></div>
                            <div class="title classTextColor"><?php echo(CONFIG_BRAND_NAME) ?></div>
                        </a>
                        <button type="button" class="navbar-expand-toggle pull-right visible-xs">
                            <i class="fa fa-times icon"></i>
                        </button>
                    </div>
                        
		            <ul class="nav navbar-nav classCollapse" style="overflow: auto;max-height: 95vh;">
		                <li>
		                    <a href="medicalManagement.php" class="classDropdownTextColor">
		                        <span class="icon fa fa-tachometer classDropdownTextColor"></span><span class="title">Dashboard</span>
		                    </a>
		                </li>
		            </ul>
		        </div>
		    </nav>
		</div>
