<div class="card">
  <div class="card-header">
    Comportamento monopolista
    <i class="fa fa-question-circle-o" aria-hidden="true" data-toggle="modal" data-target="#modalMonopolisticBehavior"></i>    

  </div>
  <div class="card-body">
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
      $contador[$row_score['id']] = ["body" => $row_score['body'], "author" => $row_score['author']];
      if($somatorio > $row_score['UM_QUARTO_VOTOS'] && count($contador) < $row_score['VINTE_PORCENTO_COMENT'] ){ 
        $retorno = $contador;
      break;
      }
    }?>
    <p class="card-title">Foi identificada a concentração de votos na seguinte resposta ao tópico.</p>
    <?php
    foreach ($retorno as $id => $value) {?>      
        
        <div id="#monop_<?php echo $id; ?>" >              
            <div id="#monop_brief_<?php echo $id; ?>" class="card-text">
              <?php echo substr($value['body'],0,80).'... ';?>                         
              <a  data-toggle="collapse" data-target="#monop_<?php echo $id; ?>" aria-expanded="false" aria-controls="collapseTwo">
                <i class="fa fa-plus-square-o" onclick="document.getElementById('#monop_brief_<?php echo $id; ?>').style.color = 'white'";></i>
                <i class="fa fa-minus-square-o" onclick="document.getElementById('#monop_brief_<?php echo $id; ?>').style.color = '#747373'";></i>                
              </a>
            </div>
            <div id="monop_<?php echo $id; ?>" class="collapse" aria-labelledby="headingTwo" data-parent="#monop_<?php echo $id; ?>">
              <?php echo $value['body']; ?>
            </div>
        </div>	        
    <?php
    }?>
        
  </div>
</div>


<!-- Modal -->
<div class="modal fade" id="modalMonopolisticBehavior" tabindex="-1" role="dialog" aria-labelledby="modalMonopolisticBehaviorLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalMonopolisticBehaviorLabel">Comportamento monopolista</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        xxxxxxxx
      </div>
    </div>
  </div>
</div>