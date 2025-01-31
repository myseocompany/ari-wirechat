<?php 
header("Content-Type: application/csv");
header("Content-Disposition: attachment;Filename=data.csv");
?>
@yield('content');