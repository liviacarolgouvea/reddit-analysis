Os posts mais votos positivos foram:				
<?php
$query_positive_score = "	SELECT 		A.id, SUBSTRING(A.body,2,500) AS body, A.score_hidden, B.MEDIA, B.DESVIO_PADRAO
							FROM 
							            (
											SELECT id, body, score_hidden
											FROM reddit.2015_politics
											WHERE link_id = 't3_".$_GET['link_id']."' AND LENGTH(score_hidden) < 5 AND score_hidden > 0
										) A
							LEFT JOIN            
										(
											SELECT 		AVG(X.score_hidden) AS MEDIA,
															STD(X.score_hidden) AS DESVIO_PADRAO
											FROM        (
															SELECT id, score_hidden AS score_hidden
															FROM reddit.2015_politics
															WHERE link_id = 't3_".$_GET['link_id']."' AND LENGTH(score_hidden) < 5 AND score_hidden > 0
														) X
										) B
							                                    
							ON 			1 = 1";
//echo "<pre>".$query_positive_score."</pre>";							
$i = 1;

$result_positive_score = mysqli_query($con,$query_positive_score);				
while($row_positive_score = mysqli_fetch_assoc($result_positive_score)) {
	$score_percentage = $row_positive_score['score_hidden'] / $row_positive_score['DESVIO_PADRAO'];
	if($score_percentage > 10){ ?>
 		<!-- Button trigger modal -->
<!--  		<button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#<?php echo $row_positive_score['id']; ?>" style="padding: 5px">
 			<span class="fas fa-quote-right" data-toggle="tooltip" title="Post <?php echo $i ?>" style="font-size: 14px"></span>
 		</button>		 -->

 		<i class="fa fa-sort" aria-hidden="true" data-toggle="modal" data-target="#<?php echo $row_positive_score['id']; ?>" style="color:#ff4500; cursor: pointer;"></i>
	&nbsp;&nbsp;
	
 		<!-- Modal -->
 		<div class="modal fade" id="<?php echo $row_positive_score['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
 		  	<div class="modal-dialog" role="document" style="max-width:100%">
 		    <div class="modal-content" style="width: 80%; margin: 0 auto">
 		      <div class="modal-header">
 		        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
 		          <span aria-hidden="true">&times;</span>
 		        </button>
 		      </div>
 		      <div class="modal-body">
 		        <?php echo $row_positive_score['body']; ?>
 		      </div>
 		    </div>
 		  </div>
 		</div>		
	<?php	
	$i++;
	}
}	
?>

