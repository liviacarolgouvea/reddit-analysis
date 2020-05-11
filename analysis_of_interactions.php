<div class="card-header border-0 text-white py-3" style="background: #28b779">
    <!-- <span class="font-weight-bold medium">Analysis of interactions</span> -->
    <span class="font-weight-bold medium">Análise da interação</span>
    <!-- <div class="small mb-2">Identifies whether more than 25% of the discussion is centered on any comments</div> -->
    <div class="small mb-2">Identifica se mais de 25% da discussão está centrada algum comentário de primeiro nível</div>
<!-- </div> -->
<?php

function buildTree(array &$elements, string $parentId): array
{
    $branch = [];

    foreach ($elements as &$element) {
        if ($element['parent_id'] == $parentId) {
            $children = buildTree($elements, $element['id']);
            if ($children) {
                $element['children'] = $children;
                $element['total'] += array_reduce($children, function($carry, $item) {
                    $carry += $item['total'];
                    return $carry;
                });
            }
            $branch[$element['id']] = $element;
            unset($element);
        }
    }
    return $branch;
}

if ($_GET['link_id']) {
    $query = "
    SELECT id, body,
           REPLACE(parent_id, 't1_', '') parent_id,
           1 AS total
    FROM ".$_GET['link_id'];

    $array = array();
    
    $stmt=$con->prepare($query);
    $stmt->execute();
    $array = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    $x = buildTree($array, 't3_'.$_GET['link_id']);
    $total = array_reduce($x, function($carry, $item){
        $carry += $item['total'];
        return $carry;
    });
    $limit = 0.25 * $total;
    foreach($x as $id => $item) {
        if ($item['total'] >= $limit) {
            $ultrapassou[$id] = $item['body'];
        }
    }  
}

?>
<!-- <div class="list-group list-group-flush">
  <div class="list-group-item list-group-item-action py-3">       -->
      <?php
      if (!empty($ultrapassou)) { 
        foreach($ultrapassou as $id => $body) { ?>      
          <i class="fa fa-comment" aria-hidden="true" data-toggle="modal" data-target="#<?php echo $id; ?>" style="color:#ff4500; cursor: pointer;"></i>
          &nbsp;&nbsp;
          
            <!-- Modal -->
            <div class="modal fade" id="<?php echo $id; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document" style="max-width:100%">
                <div class="modal-content" style="width: 80%; margin: 0 auto">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body" style="color: black"> 
                    <?php echo $body; ?>
                  </div>
                </div>
              </div>
            </div>		
        <?php
        }
      }else{
        echo "Nenum comentário centralizou as respostas";
       }?>

        <?php	        
    //   if($i == 0){
    //     echo "No comments had a concentration of more than 25% of responses";
    //   }
      ?>            
  <!-- </div> -->
</div>