This comments were much more voted than the rest:<br>
<?php
$query = "	SELECT 		a.id, a.author, a.body, ABS(a.SCORE), a.SCORE, a.ups, a.downs, a.score_hidden, b.QTD_AUTHOR/2, controversiality
							FROM
							(
								SELECT 		id, author, body, ABS(SCORE), SCORE, ups, downs,score_hidden, controversiality
								FROM 		2015_politics 
								WHERE 		link_id = 't3_".$_GET['link_id']."'
							) a
							LEFT JOIN    
							(	SELECT 	COUNT(DISTINCT author) AS QTD_AUTHOR
								FROM 	2015_politics 
								WHERE 	link_id = 't3_".$_GET['link_id']."'
							) b
							ON 1 = 1
							WHERE 		ABS(a.score_hidden) > (b.QTD_AUTHOR/3) ";
//echo "<pre>".$query."</pre>";							
$result = mysqli_query($con,$query);
				
while($row = mysqli_fetch_assoc($result)) {?>	
	<!-- Button trigger modal -->
	<i class="fa fa-sort" aria-hidden="true" data-toggle="modal" data-target="#<?php echo $row['id']; ?>" style="color:#ff4500; cursor: pointer;"></i>
	&nbsp;&nbsp;
	
<!-- 
	<button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#<?php echo $row['id']; ?>" style="padding: 5px">
		<span class="fas fa-quote-right" data-toggle="tooltip" title="Post <?php echo $i ?>" style="font-size: 14px"></span>
	</button>	 
-->		 		
	<!-- Modal -->
	<div class="modal fade" id="<?php echo $row['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	  	<div class="modal-dialog" role="document" style="max-width:100%">
	    <div class="modal-content" style="width: 80%; margin: 0 auto">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span aria-hidden="true">&times;</span>
	        </button>
	      </div>
	      <div class="modal-body">
	        <?php echo $row['body']; ?>
	      </div>
	    </div>
	  </div>
	</div>			
<?php }	?> 