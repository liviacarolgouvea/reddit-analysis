<?php
header('X-Frame-Options: ALLOW');
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
		
		// header('X-Frame-Options: GOFORIT');

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
<!-- 	 
		<table class="table" style="max-width: 600px; position: relative; min-width: 200px; margin: 5px auto;">
	 
		<thead>
		    <tr>
		      <th scope="col"colspan="5" style="text-align: center;">DEBATE DESCRIPTION</th>
		    </tr>
		 </thead>		

		  <tbody>
		    
			<tr>
		      <td scope="row" style="text-align: center;">
		      	<font size="4"><b><?php // echo $row_caracteristica_conversa['TOTAL_AUTORES']; ?></b></font>
		      	<br> 		      	
		      	<font size="2">Participants</font> 
		      </td>
		      
		      <td scope="row" style="text-align: center;">
		      	<font size="4"><b><?php // echo $row_caracteristica_conversa['TOTAL_POSTS']; ?></b></font>
		      	<br>
		      	<font size="2">Posts</font> 		      	
		      </td>		      

		      <td scope="row" style="text-align: center;">
		      	<font size="4"><b><?php // echo round($row_caracteristica_conversa['TOTAL_POSTS']/$row_caracteristica_conversa['TOTAL_AUTORES']); ?></b></font>
		      	<br>
		      	<font size="2">Average of posts per participant</font> 		      	
		      </td>

		      <td scope="row" style="text-align: center;">
		      	<font size="4"><b><?php // echo $row_caracteristica_conversa['TAMANHO_MEDIO_POSTS']; ?></b></font>
		      	<br> 
		      	<font size="2">Average posts size (characters)</font> 		      	
		      </td>
		    </tr> 
		-->
		    <!-- <tr>
		      <td scope="row" style="text-align: center;">
		      	<font size="4"><b><?php // echo $row_caracteristica_conversa['INICIO']; ?></b></font>
		      	<br>
		      	<font size="2">Start</font>  	
		      </td>
		      
		      <td scope="row" style="text-align: center;">
		      	<font size="4"><b><?php // echo $row_caracteristica_conversa['FIM']; ?></b></font>
		      	<br>
		      	<font size="2">End</font> 		      	
		      </td> 		      		      
			<td scope="row" style="text-align: center;">
		      	<font size="4"><b><?php // echo  round($row_caracteristica_conversa['TOTAL_POSTS']/$row_caracteristica_conversa['DURACAO']); ?> </b></font>
		      	<br>
		      	<font size="2">Average of posts per day</font> 		      	
		     </td> 
		      <td><?php  // include_once "dinamica_temporal.php"; ?> </td>
		    </tr>		 
		    
		  </tbody>
		</table>
		-->
		<!-- <table style="max-width: 600px; padding: 10px; position: relative; min-width: 200px; margin: 5px auto; height: 200px">
			<tr >
				<td style="width: 33%">
					<div style="background: #E6E6E6; margin: 5px;border-radius: 5px; padding: 10px">
						<?php // include_once "dominancia_falantes.php";?>
					</div>
				</td>
				<td style="width: 33%">
					<div style="background: #A9D0F5; margin: 5px;border-radius: 5px; padding: 10px">
						<?php // include_once "concentracao_replys.php";?>
					</div>					
				</td>
				<td style="width: 33%">
					<div style="background: #E6E6E6; margin: 5px;border-radius: 5px; padding: 10px">
						<?php // include_once "concentracao_votos2.php";?>
					</div>					
				</td>
			</tr>
		</table> -->

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
				<td style="width: 50%; ">
					<div>
						<blockquote class="reddit-card" data-card-created="1556072778" >
							<a href="https://www.reddit.com/r/coronabr/comments/<?php echo $_GET['link_id']?>/"></a>
						</blockquote>
						<script async src="//embed.redditmedia.com/widgets/platform.js" charset="UTF-8"></script>	
					</div>
					<div style="max-width: 600px; padding: 10px; position: relative; min-width: 200px; margin: 5px auto;">
						<a class="btn btn-primary btn-lg btn-block"  href="https://www.reddit.com/r/coronabr/comments/<?php echo $_GET['link_id']?>/" target="_blank">Go to discussion</a>					
					</div>									
				</td>
			</tr>
		</table>		
		
			 			
		<!-- 			
			<li>
				<div style="background: #A9D0F5; border-radius: 5px; padding: 10px">
					<?php //include_once "concentracao_votos.php"; ?>
				</div>				
			</li>  
		-->
						
	  <!-- SCRIPTS -->
	  <!-- JQuery -->
	  <script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
	  <!-- Bootstrap tooltips -->
	  <script type="text/javascript" src="js/popper.min.js"></script>
	  <!-- Bootstrap core JavaScript -->
	  <script type="text/javascript" src="js/bootstrap.min.js"></script>
	  <!-- MDB core JavaScript -->
	  <script type="text/javascript" src="js/mdb.min.js"></script>

		<script>
			//var array_data = <?php //echo json_encode($author_array_chart) ?>;	

			var array_data_created_utc = <?php // echo json_encode($created_utc_array) ?>;	
			var array_data_created_utc_count = <?php // echo json_encode($count_created_utc_array) ?>;	
			
			 // var array_aux = new Array();
			//  var array_aux = [];
			//  var array_aux2 = [];
			
			// for (var data in array_data) {
			//   // array_aux += '{label:"'+data+'", data:[{x:'+array_data[data]+',y:'+array_data[data]+',r:'+array_data[data]+'}]}'; 
			//   array_aux['label'] = array_data[data];
			//   array_aux2[array_aux] = array_data[data];
			  
			// };
			// aux_datasets = [{label: 'Sempais_nutrients',data: [{x: 116, y: 116, r: 116}]}, {label: 'Basic_Becky',data: [{x: 40, y: 40, r: 40}]}];
			// console.log(array_data_created_utc_count);
			// console.log(aux_datasets);

		  // 	var ctxBc = document.getElementById('bubbleChart').getContext('2d');
		  // 	var bubbleChart = new Chart(ctxBc, {
		  //   	type: 'bubble',
		  //   	data: {
		  //     datasets: array_data		
		  //   },
		  //  options: {
		  //        legend: {
		  //           display: false
		  //        }
		  //  }      
		  // })



		  //line
		  var ctxL = document.getElementById("lineChart").getContext('2d');
		  var myLineChart = new Chart(ctxL, {
		   type: 'line',
		   data: {
		      labels: array_data_created_utc,
		      datasets: [{
		          data: array_data_created_utc_count,
		          backgroundColor: [
		            'rgba(105, 0, 132, .2)',
		          ],
		          borderColor: [
		            'rgba(200, 99, 132, .7)',
		          ],
		          borderWidth: 2
		        }
		      ]
		   },
		   options: {
		         legend: {
		            display: false
		         }
		   }    
		  });


			$(document).ready(function(){
			  $('[data-toggle="tooltip"]').tooltip(); 
			});
		</script>
  </body>
</html>

