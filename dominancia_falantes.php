<?php		
$query_author = "		SELECT			A.author, 
										A.count_id, 
										B.MEDIA, 
										B.DESVIO_PADRAO, 
										C.QTD_AUTORES,
										A.count_id / B.DESVIO_PADRAO AS PERCENTAGE
								
						FROM 		(                        
										SELECT 		author, count(distinct id) AS count_id 
										FROM 		2015_politics
										WHERE 		link_id = 't3_".$_GET['link_id']."' AND author <> '[deleted]'
										GROUP BY 	author     
										ORDER BY 	count_id desc
									) A

						LEFT JOIN 	(
										SELECT 		AVG(X.count_id) AS MEDIA,
													STD(X.count_id) AS DESVIO_PADRAO
										FROM 		(
														SELECT 		author, COUNT(DISTINCT id) AS count_id
														FROM 		2015_politics
														WHERE 		link_id = 't3_".$_GET['link_id']."' AND author <> '[deleted]'
														GROUP BY 	author
													) X
									) B

						ON			1 = 1            

						LEFT JOIN 	(
										SELECT 		count(distinct author) AS QTD_AUTORES
										FROM 		2015_politics
										WHERE 		link_id = 't3_".$_GET['link_id']."' AND author <> '[deleted]'
									) C 

						ON 			1 = 1";

$result_author = mysqli_query($con,$query_author);
$authors_over_5 = "";
$over_5 = 0;


$between_5_1 = 0;
$lower_than_1 = 0;
$over_5_icon = "";
while($row_author = mysqli_fetch_assoc($result_author)) {
	
	$qtd_autores = $row_author['QTD_AUTORES'];
	$desvio_padrao = $row_author['DESVIO_PADRAO'];
	if ($row_author['PERCENTAGE'] > 10) {
		$over_5_icon .= "<i style='color:#ff4500' class='fa fa-user' aria-hidden='true' data-toggle='tooltip' data-placement='top' title='".$row_author['author']."'></i>&nbsp;&nbsp;";
		$authors_over_5 .= "<b>- " . $row_author['author'] ." </b><br>";
		$over_5++;		
	}
	// elseif ($row_author['PERCENTAGE'] > 1 AND $row_author['PERCENTAGE'] <= 5) {
	// 	$between_5_1++;
	// }else{
	// 	$lower_than_1++;
	// }
}	
// echo "Este tópico contém ".$qtd_autores." participantes. ";	

if ($over_5 > 0) {
	if ($over_5 < (0.05 * $qtd_autores)) {
		// $porcentagem_dominates = round(($over_5 / $qtd_autores) * 100)."%";
		// echo "Detectamos dominância de falantes <a href='#'  data-toggle='modal' data-target='#dominantes'>". $porcentagem_dominates. " dos autores </a> postaram " .round(5*$desvio_padrao). " vezes mais que o restante. ";
		// echo "Detectamos dominância <b><a href='#'  data-toggle='modal' data-target='#dominantes'>".$over_5."</a></b> autor(es) pois ele(s) postou(aram) muito mais vezes que o restante. ";		
		echo "This authors <br>". $over_5_icon . "<br>posted a lot more than the rest";
	}

}
if ($lower_than_1 > 0) {
	if ($over_5 < (0.75 * $qtd_autores)) {

		// $porcentagem_falam_pouco = round(($lower_than_1 / $qtd_autores) * 100)."%";
		// echo $porcentagem_falam_pouco . " fizeram menos de ".round($desvio_padrao) . "posts. ";
		echo "A grande maioria dos participantes postou pouco ou somente uma vez.";
	}
}

?>

	<!-- Modal -->
	<div class="modal fade" id="dominantes" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	  	<div class="modal-dialog" role="document" style="max-width:100%">
	    	<div class="modal-content" style="width: 80%; margin: 0 auto">
	      	<div class="modal-header">
	      		Autores que mais postaram
	        	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
	         <span aria-hidden="true">&times;</span>
	        	</button>
	      	</div>
	      	<div class="modal-body">
	        		<?php echo $authors_over_5; ?>
	      	</div>
	    	</div>
	  	</div>
	</div>		
	<br>

<?php


// echo ">>>".$between_5_1.">>>".$lower_than_1;




// $query_author2 = "	SELECT author, count(distinct id) AS count_id FROM 2015_politics
// 						WHERE link_id = 't3_".$_GET['link_id']."' AND author <> '[deleted]'
// 						GROUP BY author
// 						ORDER BY count_id DESC";
// $result_author2 = mysqli_query($con,$query_author2);
// $author_array = array();
// $author_array_chart = array();
// $author_array_chart2 = array();			
// while($row_author2 = mysqli_fetch_assoc($result_author2)) {
// 	$author_array[] = array(0,(int)$row_author2['count_id']);
// 	$author_array_chart2[] = array("x" => (int)$row_author2['count_id'], "y" => (int)$row_author2['count_id'], "r" => (int)$row_author2['count_id']);
// 	$author_array_chart[] = array("label" => $row_author['author'], "data" => $author_array_chart2); 	

// }		

//  // var_dump($author_array_chart);

// for ($i=0; $i < $n = 100; $i++) {
// 	$author_array[] = [mt_rand(0, 10), mt_rand(0, 10)];
// //	 printf("\r%.2f%%", ($i / $n) * 100);
// }

// $space = new KMeans\Space(2);

// foreach ($author_array as $i => $coordinates) {
// 	$space->addPoint($coordinates);
// 	//sprintf("\r%.2f%%", ($i / $n) * 100);
// }

// // cluster these 50 points in 3 clusters
// $clusters = $space->solve(3, KMeans\Space::SEED_DEFAULT, function () {
// 	echo ".";
// });

// // display the cluster centers and attached points
// // echo $clusters[0][0];
// foreach ($clusters as $i => $cluster){
// 	printf("Cluster %s [%d,%d]: %d points<br>", $i, $cluster[0], $cluster[1], count($cluster));
// 	// echo ">>>".$cluster[1].">>".count($cluster)."<<<br>";
// }
?>

<!--  		<button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#author" style="padding: 5px">
 			<span class="fas fa-chart-bar" data-toggle="tooltip" title="Autores que mais falaram" style="font-size: 14px"></span>
 		</button> -->

 		<!-- Modal -->
 		<div class="modal fade" id="author" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
 		  	<div class="modal-dialog" role="document" style="max-width:100%">
 		    	<div class="modal-content" style="width: 80%; margin: 0 auto">
 		      	<div class="modal-header">
 		      		Autores
 		        	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
 		         <span aria-hidden="true">&times;</span>
 		        	</button>
 		      	</div>
 		      	<div class="modal-body">
 		        		<canvas id="bubbleChart" ></canvas>
 		      	</div>
 		    	</div>
 		  	</div>
 		</div>			

