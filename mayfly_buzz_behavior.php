<div class="card">
  <div class="card-header">
      <b> Mayfly Buzz:</b>
      <i class="fa fa-question-circle-o" aria-hidden="true" data-toggle="modal" data-target="#modalMayflyBuzz"></i>
      <?php if(!empty($row_caracteristica_conversa) && $row_caracteristica_conversa[0]['DURACAO'] == 0){
        // echo "This discussion lasted one day.";
        echo "<h4 class='card-title'> A discussão teve um comportamento explosivo durando apenas um dia</h4>";
      }else{
        // echo "This discussion lasted more than a day.";
        echo "<h4 class='card-title'>A discussão não teve comportamento explosivo.</h4>";
      }?>  
  </div>
</div>


<!-- Modal -->
<div class="modal fade" id="modalMayflyBuzz" tabindex="-1" role="dialog" aria-labelledby="modalMayflyBuzzLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalMayflyBuzzLabel">Comportamento Mayfly Buzz</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Identifica se a discussão teve um comportamento explosivo, durando somente um dia.
      </div>
    </div>
  </div>
</div>