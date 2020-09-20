<?php
$query = "	SELECT 	  A.id, A.body, A.body_html,  B.MEDIA, B.DESVIO_PADRAO, (A.SCORE / B.DESVIO_PADRAO) PERCENT
            FROM 
                  (
                      SELECT    id, body, body_html, ABS(score) as SCORE
                      FROM      ".$_GET['link_id']."
                  ) A
            LEFT JOIN            
                  (
                      SELECT 		AVG(ABS(score)) AS MEDIA,
                                STD(ABS(score)) AS DESVIO_PADRAO							
                      FROM 		  ".$_GET['link_id']."
                  ) B
                              
            ON 			1 = 1";
//echo "<pre>".$query."</pre>";							
$concentracao_votos = array();
foreach($con->query($query) as $row) {
	if($row['PERCENT'] > 3){ 
		$concentracao_votos[$id] = ["body" => $row['body'], "body_html" => $row['body_html']];
 		
	}
}	
?>

<div class="card">
  <div class="card-header indicators">  
    <i class="fa fa-question-circle-o" aria-hidden="true" data-toggle="modal" data-target="#modalConcentracaoVotos"></i>
    <!-- <b>Destaque de votos</b> -->
    <?php
    
    $modal_votos = "";
    if (!empty($concentracao_votos)) { 
      if(count($concentracao_votos) > 1){
        
        /* echo "<h4 class='card-title'>".count($concentracao_votos)." <b>comentários se destacaram por obterem muito votos.</b></h4>"; */
        echo "<h4 class='card-title'>There are some much <b> popular </b> comments in votes.</h4>";
        echo "<input type='button' class='btn btn-primary' aria-hidden='true' data-toggle='modal' data-target='#modalCommentsVotes' value='View'/>";
      }else{
        /* echo "<h4 class='card-title'>".count($concentracao_votos)." <b>comentário se destacou por obter muitos votos.</b></h4>"; */
        echo "<h4 class='card-title'>There is a much <b> popular </b> comment in votes.</h4>";
        echo "<input type='button' class='btn btn-primary' aria-hidden='true' data-toggle='modal' data-target='#modalCommentsVotes' value='View'/>";
      }      
      foreach($concentracao_votos as $id => $value) { 
        if($value == "[removed];"){
          $modal_votos = "O comentário foi removido pelo moderador por ser um possível gerador de conflito";
        }else{
          $modal_votos .= "
          <div id='#concentracao_votos". $id ."'>
            <div id='#brief_concentracao_votos". $id ."'>
            ".substr($value['body'],0,95).'... ' . "
              <a  data-toggle='collapse' data-target='#concentracao_votos". $id ."' aria-expanded='false' aria-controls='collapseTwo'>
                <i class='fa fa-plus-square-o' onclick=\"document.getElementById('#brief_concentracao_votos" . $id ."').style.color = 'transparent'\";></i>
                <i class='fa fa-minus-square-o' onclick=\"document.getElementById('#brief_concentracao_votos" . $id ."').style.color = '#0c0c0c'\";></i>
              </a>
            </div>
            <div id='concentracao_votos". $id ."' class='collapse' aria-labelledby='headingTwo' data-parent='#concentracao_votos". $id ."'>".
              $value['body_html'] ."
            </div>
          </div><br>";
        }
      }?>
      
    <?php }else{
      echo "<p class='card-title'>A respostas etão bem distribuídas aos comentários</p>";
    }?>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalConcentracaoVotos" tabindex="-1" role="dialog" aria-labelledby="modalConcentracaoVotos" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <!-- <h5 class="modal-title" id="modalConcentracaoVotos">Concentração de votos</h5> -->
        <h5 class="modal-title" id="modalConcentracaoVotos">Concentration of votes</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Identifica se algum comentário concentrou os votos da discussão. (Se está 3 vezes a cima do desvio padrão de votos). -->
        Identifies whether any comments concentrated the votes of the discussion. (If it is 3 times above the standard deviation of votes).
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalCommentsVotes" tabindex="-1" role="dialog" aria-labelledby="modalCommentsVoteLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
    <div class="modal-header">
        <!-- <h5 class="modal-title" id="modalAnalysisInteractionLabel">Comentários</h5> -->
        <h5 class="modal-title" id="modalAnalysisInteractionLabel">Comments</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <?php echo $modal_votos;?>
      </div>
    </div>
  </div>
</div>