<?php
include('includes/conf.php');
page_header('Error');

//TODO print better error

print "ERROR: ".$_SESSION['current_error'];
error_log($_SESSION['current_error']);
?>