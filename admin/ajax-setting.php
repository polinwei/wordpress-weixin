<?php

   $AppID=$_POST['AppID'];
   echo $AppID;
   update_option('wpwx-AppID',$AppID);

   $AppSecret=$_POST['AppSecret'];
   echo $AppSecret;
   update_option('wpwx-AppSecret',$AppSecret);

?>