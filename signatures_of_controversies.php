<div class="card">
  <div class="card-header">
    <i class="fa fa-question-circle-o" aria-hidden="true" data-toggle="modal" data-target="#modalSignaturesOfControversies"></i>    
    <b>Medida de controvérsia:</b>
      <?php
      $sql_controversy = "SELECT TOTAL_COMMENTS, DELETED, (DELETED / TOTAL_COMMENTS) * 100 AS PORCENTAGEM
                          
                          FROM
                          (
                              SELECT 	COUNT(id) AS DELETED 
                              FROM 	  ".$_POST['link_id']."
                              WHERE 	body = '[removed];' OR body LIKE 'Your comment has been removed%'
                          )A
                              
                              LEFT JOIN
                              
                          (	
                              SELECT 	COUNT(id) AS TOTAL_COMMENTS 
                              FROM 	".$_POST['link_id']."
                          )B	

                          ON 1 = 1";

      $result=$con->prepare($sql_controversy);
      $result->execute();
      $row_controversy = $result->fetchAll(\PDO::FETCH_ASSOC);
      if(round($row_controversy[0]['PORCENTAGEM'],1) == 0){
        echo "<h4 class='card-title'>Não foi detectada controvérsia nesta discussão</h4>";
      }else{        
        echo "<h4 class='card-title'>O grau de controvérsia desta discussão é de ". round($row_controversy[0]['PORCENTAGEM'],1)."%</h4>";
      }?>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalSignaturesOfControversies" tabindex="-1" role="dialog" aria-labelledby="modalSignaturesOfControversiesLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalSignaturesOfControversiesLabel">Medida de controvérsia</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        No Reddit alguns comentários são excluídos pelo autor do comentário ou pelos moderadores, caso viole as regras do subreddit, podendo causar controvérsia.
        <br>
        A medida de controvérsia calcula a proporção entre o número de comentários excluídos e o número total de comentários da discussão.
        
      </div>
    </div>
  </div>
</div>