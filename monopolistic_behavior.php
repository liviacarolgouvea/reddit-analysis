<div class="card-header border-0 text-white py-3" style="background: #ffd635">
    <span class="font-weight-bold medium"><i class="far fa-books"></i>Monopolistic behavior</span>
    <div class="small mb-2">At least a quarter of the votes focus on 20% of comments</div>
</div>		
<di v class="list-group list-group-flush">
  <div class="list-group-item list-group-item-action py-3 overflow-auto" style="height: 100px">
  <?php
  $query_score = "	
                            SELECT  

                            id, body, score, TOTAL_VOTOS, (0.25*TOTAL_VOTOS) UM_QUARTO_VOTOS, (0.2*TOTAL_COMENTARIOS) VINTE_PORC_COMENT, TOTAL_COMENTARIOS, ((0.25*TOTAL_VOTOS) / (0.2*TOTAL_COMENTARIOS)) SCORE_COMPARACAO
                            
                            FROM ".$_GET['link_id']." A
                            
                            LEFT JOIN
                            
                            (
                              SELECT SUM(ABS(score)) TOTAL_VOTOS
                              FROM ".$_GET['link_id']." WHERE link_id = 't3_".$_GET['link_id']."' 
                            )B
                            ON 1 = 1
                            
                            LEFT JOIN
                            (
                              SELECT COUNT(id) TOTAL_COMENTARIOS
                              FROM ".$_GET['link_id']." WHERE link_id = 't3_".$_GET['link_id']."' 
                            )C
                            
                            ON 1 = 1
                            
                            WHERE link_id = 't3_".$_GET['link_id']."' ";
  //echo "<pre>".$query_positive_score."</pre>";							

  foreach($con->query($query_score) as $row_score) {
    if($row_score['SCORE_COMPARACAO'] > 4){ ?>

      <i class="fa fa-sort" aria-hidden="true" data-toggle="modal" data-target="#<?php echo $row_score['id']; ?>" style="color:#ff4500; cursor: pointer;"></i>
    &nbsp;&nbsp;
    
      <!-- Modal -->
      <div class="modal fade" id="<?php echo $row_score['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document" style="max-width:100%">
          <div class="modal-content" style="width: 80%; margin: 0 auto">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body"> 
              <?php echo $row_score['body']; ?>
            </div>
          </div>
        </div>
      </div>		
    <?php	
    }
  }	
  ?>
  </div>
</div>
