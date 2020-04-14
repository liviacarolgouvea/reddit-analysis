
<?php
$query_media_parent_id = "			SELECT 		AVG(count_id) AS MEDIA,
												STD(count_id) AS DESVIO_PADRAO
									FROM 		(
													SELECT 		parent_id, COUNT(DISTINCT id) AS count_id
												    FROM 		2015_politics
												    WHERE 		link_id = 't3_".$_GET['link_id']."'
												        		AND parent_id <> 't3_".$_GET['link_id']."'
												    GROUP BY 	parent_id
												) a";
$result_media_parent_id = mysqli_query($con,$query_media_parent_id);
$row_media_parent_id = mysqli_fetch_assoc($result_media_parent_id);
$media_parent_id = $row_media_parent_id['MEDIA'];
$desvio_padrao_parent_id = $row_media_parent_id['DESVIO_PADRAO'];

$query_parent_id = "	SELECT 		A.parent_id, A.count_id, SUBSTRING(B.body,2,500) AS body, C.QTD_POSTS

						FROM 		(
										SELECT 		parent_id, 
													count(distinct id) as count_id,
													SUBSTR(parent_id,4,30) AS id_pai
										FROM 		2015_politics
										WHERE 		link_id = 't3_".$_GET['link_id']."' and parent_id <> 't3_".$_GET['link_id']."'
										GROUP BY 	parent_id
									) A

						LEFT JOIN 

									(
										SELECT		distinct id, body
										FROM 		2015_politics
										WHERE 		link_id = 't3_".$_GET['link_id']."'
									) B
						            
						ON			A.id_pai = B.id

						LEFT JOIN 	(
										SELECT 		count(distinct id) AS QTD_POSTS
										FROM 		2015_politics
										WHERE 		link_id = 't3_".$_GET['link_id']."'
									) C 

						ON 			1 = 1						

						WHERE 		B.body NOT LIKE '%[deleted]%'

						ORDER BY	count_id DESC";
$result_parent_id = mysqli_query($con,$query_parent_id);
$i = 0;
$posts_over_5_replys = "";
$over_5 = 0;
$between_5_1 = 0;
$lower_than_1 = 0;
$over_5_icon = "";

while($row_parent_id = mysqli_fetch_assoc($result_parent_id)) {
	$qtd_posts = $row_parent_id['QTD_POSTS'];
	$porcentagem_parent_id = ($row_parent_id['count_id'] / $desvio_padrao_parent_id);
	$round_porcentagem_parent_id = round($porcentagem_parent_id);
	$round_media_parent_id = round($desvio_padrao_parent_id);
	$i++;
 	if($porcentagem_parent_id > 10){ 		
		$posts_over_5_replys .= "<b>- " . $row_parent_id['body'] ." </b><br>";
		$over_5++; 
		$over_5_icon .= "<i style='color:#ff4500; cursor: pointer;' class='fa fa-comment' aria-hidden='true' data-toggle='modal' data-target='#comment_".$over_5."' ></i>&nbsp;&nbsp;";
		?>	
 		<div class="modal fade" id="<?php echo 'comment_'.$over_5;?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
 		  	<div class="modal-dialog" role="document" style="max-width:100%">
 		    <div class="modal-content" style="width: 80%; margin: 0 auto">
 		      <div class="modal-header">
 		        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
 		          <span aria-hidden="true">&times;</span>
 		        </button>
 		      </div>
 		      <div class="modal-body">
 		        <?php echo $row_parent_id['body']; ?>
 		      </div>
 		    </div>
 		  </div>
 		</div>	
	<?php

	}
	// elseif ($porcentagem_parent_id > 1 AND $porcentagem_parent_id <= 5) {
	// 	$between_5_1++;
	// }else{
	// 	$lower_than_1++;
	// }
}

if ($over_5 > 0) {
	if ($over_5 < (0.05 * $qtd_posts)) {
		// $porcentagem_dominates = round(($over_5 / $qtd_autores) * 100)."%";
		// echo "Detectamos dominância de falantes <a href='#'  data-toggle='modal' data-target='#dominantes'>". $porcentagem_dominates. " dos autores </a> postaram " .round(5*$desvio_padrao). " vezes mais que o restante. ";
		//echo "<b><a href='#' data-toggle='modal' data-target='#posts' style='color:blue'>".$over_5."</a></b> posts pois foram respondidos muito mais do que o restante. ";			
		echo "This posts <br>". $over_5_icon . "<br>were much more answered than the rest";
	}

}
// if ($lower_than_1 > 0) {
// 	if ($over_5 < (0.75 * $qtd_posts)) {

// 		// $porcentagem_falam_pouco = round(($lower_than_1 / $qtd_autores) * 100)."%";
// 		// echo $porcentagem_falam_pouco . " fizeram menos de ".round($desvio_padrao) . "posts. ";
// 		echo "A grande maioria dos participantes postou pouco ou somente uma vez.";
// 	}
// }
?>

	 		
 		<!-- Modal -->
 		<div class="modal fade" id="posts" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
 		  	<div class="modal-dialog" role="document" style="max-width:100%">
 		    <div class="modal-content" style="width: 80%; margin: 0 auto">
 		      <div class="modal-header">
 		        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
 		          <span aria-hidden="true">&times;</span>
 		        </button>
 		      </div>
 		      <div class="modal-body">
 		        <?php echo $posts_over_5_replys; ?>
 		      </div>
 		    </div>
 		  </div>
 		</div>