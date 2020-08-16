<div class="card">
  <div class="card-header indicators" style="display: table; vertical-align: middle;">
    <i class="fa fa-question-circle-o" aria-hidden="true" data-toggle="modal" data-target="#modalSignaturesOfControversies"></i>    
    <!-- <b>Medida de controvérsia:</b> -->
      <?php
      $sql_controversy = "SELECT TOTAL_COMMENTS, DELETED, (DELETED / TOTAL_COMMENTS) * 100 AS PORCENTAGEM
                          
                          FROM
                          (
                              SELECT 	COUNT(id) AS DELETED 
                              FROM 	  ".$_GET['link_id']."
                              WHERE 	body = '[removed];' OR body LIKE 'Your comment has been removed%' OR body  = '[deleted]'
                          )A
                              
                              LEFT JOIN
                              
                          (	
                              SELECT 	COUNT(id) AS TOTAL_COMMENTS 
                              FROM 	".$_GET['link_id']."
                          )B	

                          ON 1 = 1";
      //echo "<pre>".$sql_controversy."</pre>";

      $result=$con->prepare($sql_controversy);
      $result->execute();
      $row_controversy = $result->fetchAll(\PDO::FETCH_ASSOC);
      if(round($row_controversy[0]['PORCENTAGEM'],1) == 0){
        echo "<h4 class='card-title'><b>Nenhum comentário ofensivo foi detectado.</b></h4>";
      }elseif(round($row_controversy[0]['PORCENTAGEM'],1) >= 20){
        echo "<h4 class='card-title'>Esta conversa está bastante controversa</h4>";
      }else{
        echo "<h4 class='card-title'>Esta discussão está um pouco controversa</h4>";
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
        <p> No Reddit alguns comentários são excluídos pelos moderadores caso violem as regras do fórum, podendo causar controvérsia.</p>
        
        <p>A pontuação de de controvérsia da discussão é calculada pela proporção entre o número de comentários excluídos e o número total de comentários da discussão.</p>
        
        <p>Uma conversa é considerada controversa se esta pontuação for maior que 20.</p>
        
        <p>A pontuação de controvérsia desta discussão é <?php echo round($row_controversy[0]['PORCENTAGEM'],1); ?></p>
      </div>
    </div>
  </div>
</div>