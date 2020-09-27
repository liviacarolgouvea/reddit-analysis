<div class="card">
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
        echo "<div class='card-header indicators' >";
        echo "<i class='fa fa-question-circle-o' aria-hidden='true' data-toggle='modal' data-target='#modalSignaturesOfControversies'></i>";
        /* echo "<h4 class='card-title'><b>Nenhum comentário ofensivo foi detectado.</b></h4>"; */
        echo "No controversial comments";
        echo "</div>";
        echo "<div class='card-body'>";
        echo "Moderators didn't have to <b> remove </b> any comment.";
        echo "<br><i class='fa fa-smile'></i><br>";
        echo "</div>";

      }elseif(round($row_controversy[0]['PORCENTAGEM'],1) > 0 && round($row_controversy[0]['PORCENTAGEM'],1) <= 20){
        echo "<div class='card-header indicators' >";
        echo "<i class='fa fa-question-circle-o' aria-hidden='true' data-toggle='modal' data-target='#modalSignaturesOfControversies'></i>";
        /* echo "<h4 class='card-title'>Esta conversa está bastante controversa</h4>"; */
        echo "Some controversial comments";
        echo "</div>";
        echo "<div class='card-body'>";
        echo "Moderators had to <b> remove </b> some comments";
        echo "<br><i class='fa fa-frown'></i><br>";
        echo "</div>";
      }else{
        echo "<div class='card-header indicators' >";
        echo "<i class='fa fa-question-circle-o' aria-hidden='true' data-toggle='modal' data-target='#modalSignaturesOfControversies'></i>";
        /* echo "<h4 class='card-title'>Esta discussão está um pouco controversa</h4>"; */
        echo "Many controversial comments";
        echo "</div>";
        echo "<div class='card-body'><br>";
        echo "Moderators had to remove <b> many </b> comments";
        echo "<i class='fa fa-frown'></i>";
        echo "</div>";
      }?>
</div>

<!-- Modal -->
<div class="modal fade" id="modalSignaturesOfControversies" tabindex="-1" role="dialog" aria-labelledby="modalSignaturesOfControversiesLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <!-- <h5 class="modal-title" id="modalSignaturesOfControversiesLabel">Medida de controvérsia</h5> -->
        <h5 class="modal-title" id="modalSignaturesOfControversiesLabel">Controversy measure</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- <p> No Reddit alguns comentários são excluídos pelos moderadores caso violem as regras do fórum, podendo causar controvérsia.</p> -->
        <p>On Reddit some comments are deleted by moderators if they violate the forum rules, which may cause controversy.</p>

        <!-- <p>A pontuação de controvérsia da discussão é calculada pela proporção entre o número de comentários excluídos e o número total de comentários da discussão.</p> -->
        <p>The controversy score of the discussion is calculated by the ratio between the number of comments deleted and the total number of comments in the discussion.</p>

        <!-- <p>Uma conversa é considerada controversa se esta pontuação for maior que 20.</p> -->
        <p>A conversation is considered controversial if this score is greater than 20.</p>

        <!-- <p>A pontuação de controvérsia desta discussão é <?php // echo round($row_controversy[0]['PORCENTAGEM'],1); ?></p> -->
        <p>The controversial score for this discussion is <?php echo round($row_controversy[0]['PORCENTAGEM'],1); ?></p>
      </div>
    </div>
  </div>
</div>