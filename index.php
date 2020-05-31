<?php
header('X-Frame-Options: ALLOW');
header('X-Frame-Options: GOFORIT');
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
	  	<link href="css/bootstrap.min.css" rel="stylesheet">
	  	<!-- Material Design Bootstrap -->
	  	<link href="css/mdb.min.css" rel="stylesheet">
	  	<!-- Your custom styles (optional) -->
	  	<link href="css/style.css" rel="stylesheet">
    	<title>Dissertação</title>


  	</head>
  	<body style="font-size: 15px; background: #e6e6e6;">
		<?php
		
		

		// ini_set('display_errors', 1);
		// ini_set('display_startup_errors', 1);
		// error_reporting(E_ALL);
		ini_set('memory_limit', '-1');
		ini_set('max_execution_time', 300);

		// require_once "src/KMeans/Space.php";
		// require_once "src/KMeans/Point.php";
		// require_once "src/KMeans/Cluster.php";
	
		try {
			$con = new \PDO(
				getenv('DB_ADAPTER').':dbname='.getenv('DB_NAME').';host='.getenv('DB_HOST'),
				getenv('DB_USER'),
				getenv('DB_PASSWD')
			);
		} catch(\Exception $e) {
			echo $e->getMessage()."\n";
			return false;
		}
	
		// $con=mysqli_connect("127.0.0.1:3306","u670012130_root","S7yffi37feyr","u670012130_reddi");

		$query = "	SELECT 	count(distinct id) AS TOTAL_POSTS,
													count(distinct author) AS TOTAL_AUTORES,
													DATE_FORMAT(MIN(nullif(from_unixtime(created_utc,'%Y-%m-%d'),'31/12/1969')),'%d/%b/%Y') AS INICIO,
													DATE_FORMAT(MAX(nullif(from_unixtime(created_utc,'%Y-%m-%d'),'31/12/1969')),'%d/%b/%Y') AS FIM,
									        		ROUND(AVG(CHAR_LENGTH(body))) TAMANHO_MEDIO_POSTS,
									        		DATEDIFF(date(now()),MAX(nullif(from_unixtime(created_utc,'%Y-%m-%d'),'31/12/1969'))) DIAS_ULTIMO_POST,
									        		DATEDIFF(MAX(nullif(from_unixtime(created_utc,'%Y-%m-%d'),'31/12/1969')),MIN(nullif(from_unixtime(created_utc,'%Y-%m-%d'),'31/12/1969'))) DURACAO
											FROM 	".$_GET['link_id']."
											WHERE 	link_id = 't3_".$_GET['link_id']."'";
		$stmt=$con->prepare($query);
		$stmt->execute();
		$row_caracteristica_conversa = $stmt->fetchAll();
		?>

		<table style="width:90%; position: relative; min-width: 200px; margin: 5px auto; background: white; border:1px solid #cccccc">
			<tr>				
				<td style="width:45%; padding: 10px;">
					<div>
						<?php include_once "analysis_of_interactions.php";?>
						<br>
							<?php include_once "monopolistic_behavior.php";?>												
						<br>
							<?php include_once "signatures_of_controversies.php";?>
						<br>
							<?php include_once "age_dynamics.php";?>
						<br>
							<?php include_once "mayfly_buzz_behavior.php";?>
					</div>														
				</td> 
				<td style="width:45%; padding: 10px;vertical-align: top">					
					<?php include_once "summarization.php";?>
					<br>
					<div>
						<blockquote class="reddit-card" data-card-created="1556072778" >
							<a href="https://www.reddit.com/r/brasil/comments/<?php echo $_GET['link_id']?>/"></a>
						</blockquote>
						<script async src="https://embed.redditmedia.com/widgets/platform.js" charset="UTF-8"></script>	
					</div>
					<div style="max-width: 600px; padding: 10px; position: relative; min-width: 200px; margin: 5px auto;">
						<a class="btn btn-primary btn-lg btn-block"  href="https://www.reddit.com/r/brasil/comments/<?php echo $_GET['link_id']?>/" target="_blank">Ir para a discussão</a>					
					</div>									
				</td>
			</tr>
		</table>
						
	  <!-- SCRIPTS -->
	  <!-- JQuery -->
	  <script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
	  <!-- Bootstrap tooltips -->
	  <script type="text/javascript" src="js/popper.min.js"></script>
	  <!-- Bootstrap core JavaScript -->
	  <script type="text/javascript" src="js/bootstrap.min.js"></script>
	  <!-- MDB core JavaScript -->
	  <script type="text/javascript" src="js/mdb.min.js"></script>

	  <script src="https://use.fontawesome.com/eba96d6937.js"></script>
  </body>
</html>

