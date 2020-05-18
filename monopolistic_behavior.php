<div class="card-header">
    <!-- <span class="font-weight-bold medium"><i class="far fa-books"></i>Monopolistic behavior</span> -->
    <span class="font-weight-bold medium">Comportamento monopolista</span>
    <!-- <div class="small mb-2">At least a quarter of the votes focus on 20% of comments</div> -->
    <div class="small mb-2">Identifica quais comentários cocentraram mais votos na discussão.</div>
<!-- </div>		 -->
<!-- <div class="list-group list-group-flush"> -->
  <!-- <div class="list-group-item list-group-item-action py-3 overflow-auto" style="height: 100px"> -->
    <?php
    $query_score = "SELECT id, REPLACE(body,'b','') as body, author, SCORE, VINTE_PORCENTO_COMENT, UM_QUARTO_VOTOS

                    FROM 
                    
                      (
                        SELECT id, body, author, ABS(SCORE) AS SCORE 
                        FROM ".$_GET['link_id']."
                      )X
                      LEFT JOIN
                      (
                        SELECT (COUNT(id) * 0.2) AS VINTE_PORCENTO_COMENT
                        FROM ".$_GET['link_id']."
                      )A
                      ON 1=1
                      LEFT JOIN 
                      (
                        SELECT (SUM(abs(score)) * 0.25) AS UM_QUARTO_VOTOS
                        FROM ".$_GET['link_id']."
                      )B
                      ON 1=1
                    
                    ORDER BY SCORE DESC";
    // echo "<pre>".$query_score."</pre>";							
    $retorno = "Nenhum comentários monopolizou os votos";
    $somatorio = 0;
    foreach($con->query($query_score) as $row_score) {
      $somatorio += $row_score['SCORE'];          
      $contador[$row_score['id']] = $row_score['body'];
      if($somatorio > $row_score['UM_QUARTO_VOTOS'] && count($contador) < $row_score['VINTE_PORCENTO_COMENT'] ){ 
        $retorno = $contador;
      break;
      }
    }?>
    <div class="div-output">
    <?php
    foreach ($retorno as $id => $body) {?>      
        <img src="img/score.png" style="width: 10px; cursor: pointer;" data-toggle="modal" data-target="#<?php echo $row_score['id']; ?>" />
        <!-- <i class="fa fa-sort" aria-hidden="true" data-toggle="modal" data-target="#<?php // echo $row_score['id']; ?>" style="color:#ff4500; cursor: pointer;"></i> -->
        &nbsp;&nbsp;
      
        <!-- Modal -->
        <div class="modal fade" id="<?php echo $row_score['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document" style="max-width:100%">
            <div class="modal-content">
              <div class="modal-header">
              <b>Autor:</b> <?php echo $row_score['author']; ?>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body"> 
                <b>Comentário:</b><br>
                <?php echo substr($row_score['body'], 1, -1); ?>
              </div>
            </div>
          </div>
        </div>		      
    <?php
    }?>
    </div>
<!--   </div> -->
</div>
