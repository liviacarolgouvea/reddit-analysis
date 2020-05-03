
<div class="card-header border-0 text-white py-3" style="background: #ff4500;">
    <span class="font-weight-bold medium">Mayfly Buzz behavior</span>
    <div class="small mb-2">When the discussion has one day life</div>
</div>
<di v class="list-group list-group-flush">
  <div class="list-group-item list-group-item-action py-3">
    <?php if(!empty($row_caracteristica_conversa) && $row_caracteristica_conversa[0]['DURACAO'] == 1){
      echo "This discussion lasted one day.";
    }else{
      echo "This discussion lasted more than a day.";
    }?>  
  </div>
</div>




