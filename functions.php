<?php

function parseBreadcrumb($aBreadcrumb) {
    if(!$aBreadcrumb) {
        $aBreadcrumb = [];
    }

    foreach ($aBreadcrumb as $aBreadcrumbItem) {
        $sClass = "";
        if(isset($aBreadcrumbItem['isActive']) && $aBreadcrumbItem['isActive']) {
            $sClass .= "active";
        }
        $sTitle = $aBreadcrumbItem['title'];
        $sLink = $aBreadcrumbItem['link'];
        if(!isset($aBreadcrumbItem['isActive']) || !$aBreadcrumbItem['isActive']) {
            $sTitle = "<small>{$sTitle}</small>";
        }
        ?>
            <li class="<?php echo $sClass; ?>"><a href="<?php echo $sLink; ?>" style="color: #fff;"><?php echo $sTitle; ?></a></li>
        <?php
    }
}