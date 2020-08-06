<?php
$query = "	SELECT 	  A.id, A.body, B.MEDIA, B.DESVIO_PADRAO, (A.SCORE / B.DESVIO_PADRAO) PERCENT
            FROM 
                  (
                      SELECT    id, body, ABS(score) as SCORE
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

foreach($con->query($query) as $row) {
	if($row['PERCENT'] > 3){ 
		$concentracao_votos[$id] = $row['body'];
 		
	}
}	
?>

<div class="card">
  <div class="card-header indicators">  
    <i class="fa fa-question-circle-o" aria-hidden="true" data-toggle="modal" data-target="#modalConcentracaoVotos"></i>
    <!-- <b>Destaque de votos</b> -->
    <?php
    $count = count($concentracao_votos);
    if (!empty($concentracao_votos)) { 
      if(count($concentracao_votos) > 1){
        echo "<h4 class='card-title'>".$count." <b>comentários se destacaram por obterem muito votos.</b></h4>";
        echo "<input type='button' class='btn btn-primary' aria-hidden='true' data-toggle='modal' data-target='#modalCommentsVotes' value='Veja quais são'/>";
      }else{
        echo "<h4 class='card-title'>".$count." <b>comentário se destacou por obter muitos votos.</b></h4>";
        echo "<input type='button' class='btn btn-primary' aria-hidden='true' data-toggle='modal' data-target='#modalCommentsVotes' value='Veja qual é'/>";
      }      
      foreach($concentracao_votos as $id => $value) { 
        if($value == "[removed];"){
          $moda_votos = "O comentário foi removido pelo moderador por ser um possível gerador de conflito";
        }else{
          $moda_votos .= "
          <div id='#concentracao_votos". $id ."'>
            <div id='#brief_concentracao_votos". $id ."'>".
                substr($value,0,106).'... ' . "
                <a  data-toggle='collapse' data-target='#concentracao_votos". $id ."' aria-expanded='false' aria-controls='collapseTwo'>
                  <i class='fa fa-plus-square-o' onclick=\"document.getElementById('#brief_concentracao_votos" . $id ."').style.color = 'transparent'\";></i>
                  <i class='fa fa-minus-square-o' onclick=\"document.getElementById('#brief_concentracao_votos" . $id ."').style.color = '#0c0c0c'\";></i>
                </a>
            </div>
            <div id='concentracao_votos". $id ."' class='collapse' aria-labelledby='headingTwo' data-parent='#concentracao_votos". $id ."'>".
              $value ."
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
        <h5 class="modal-title" id="modalConcentracaoVotos">Concentração de votos</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Identifica se algum comentário concentrou os votos da discussão. (Se está a 3 vezes a cima do desvio padrão de votos).
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalCommentsVotes" tabindex="-1" role="dialog" aria-labelledby="modalCommentsVoteLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title" id="modalAnalysisInteractionLabel">Comentários</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <?php echo $moda_votos;?>
      </div>
    </div>
  </div>
</div>