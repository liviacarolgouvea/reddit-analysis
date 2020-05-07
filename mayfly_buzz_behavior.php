
<div class="card-header border-0 text-white py-3" style="background: #0071bc;">
    <!-- <span class="font-weight-bold medium">Mayfly Buzz behavior</span> -->
    <span class="font-weight-bold medium">Comportamento Mayfly Buzz</span>
    <!-- <div class="small mb-2">When the discussion has one day life</div> -->
    <div class="small mb-2">Identifica se a discussão durou apenas um dia</div>
</div>
<di v class="list-group list-group-flush">
  <div class="list-group-item list-group-item-action py-3">
    <?php if(!empty($row_caracteristica_conversa) && $row_caracteristica_conversa[0]['DURACAO'] == 1){
      // echo "This discussion lasted one day.";
      echo "A discussão durou apenas um dia";
    }else{
      // echo "This discussion lasted more than a day.";
      echo "A discussão durou mais de um dia";
    }?>  
  </div>
</div>




