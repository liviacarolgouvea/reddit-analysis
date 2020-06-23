<?php 

include_once "header.php";
		
include_once "con.php";

if(isset($_GET['tipo'])){
	include_once "home_".$_GET['tipo'].".php";
}else{
	include_once "prototipo.php";
}
 include_once "footer.php";
 
 ?>		
