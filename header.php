<?php
//header('X-Frame-Options: ALLOW');
//header('X-Frame-Options: GOFORIT');
?>
<!-- CHAMAR PÁGINA: http://localhost/codigo/index.php?link_id=32czwg -->
<!-- RESTAURAR BANCO: mysql -u root -h localhost --protocol=tcp -p < reddit.sql -->
<!-- 
PEGAR DISCUSSÕES DE EXEMPLO USANDO A SEGUINTE QUERY:
SELECT archived, author, body, controversiality, downs, id, link_id, score, score_hidden, ups
FROM reddit.`2015_politics`
WHERE subreddit = 'politics'  -->


<!doctype html>
<html lang="en">
  	<head>
   	<!-- Required meta tags -->
   	
   		<meta charset="utf-8">
   		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta http-equiv="x-ua-compatible" content="ie=edge">
    	<!-- Bootstrap CSS -->
   		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	  	<!-- Font Awesome -->
	  	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css">		  
	  	<!-- Bootstrap core CSS -->
	  	<link href="css/bootstrap.css" rel="stylesheet">
	  	<!-- Material Design Bootstrap -->
	  	<link href="css/mdb.css" rel="stylesheet">
	  	<!-- Your custom styles (optional) -->
		<link href="css/style.css" rel="stylesheet">
		  
    <!--Load the AJAX API-->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
$(document).ready(function(){

   var screenwidth = screen.width;

	if(screenwidth <= 768){
		if (document.cookie.indexOf('first_visit') == -1) {
			document.cookie = "first_visit";
			window.location.href="index.php?link_id=<?php echo $_GET['link_id']?>&screenwidth="+screenwidth;
		}
	}
});

</script>
    	<title>Experimento</title>


  	</head>
	  <body style="font-size: 15px; background: #e6e6e6;">


