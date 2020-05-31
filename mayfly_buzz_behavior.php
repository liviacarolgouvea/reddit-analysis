<div class="card">
  <div class="card-header">
    Comportamento Mayfly Buzz
    <i class="fa fa-question-circle-o" aria-hidden="true" data-toggle="modal" data-target="#modalMayflyBuzz"></i>
  </div>
  <div class="card-body">
    <p class="card-title">
      <?php if(!empty($row_caracteristica_conversa) && $row_caracteristica_conversa[0]['DURACAO'] == 1){
        // echo "This discussion lasted one day.";
        echo 'A discussão teve um comportamento "explosivo" durando apenas um dia';
      }else{
        // echo "This discussion lasted more than a day.";
        echo "A discussão durou mais de um dia";
      }?>  
    </p>
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