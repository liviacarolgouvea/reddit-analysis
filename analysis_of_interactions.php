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
    SELECT id, author, body,
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
    $ultrapassou = array();
    foreach($x as $id => $item) {
        if ($item['total'] >= $limit) {
            $ultrapassou[$id] = ["body" => $item['body'], "author" => $item['author']];
        }
    }  
}?>

<div class="card">
  <div class="card-header indicators">
    <i class="fa fa-question-circle-o" aria-hidden="true" data-toggle="modal" data-target="#modalAnalysisInteraction"></i>
    <!-- <b>Análise da interação</b> -->
    <?php
    $count = count($ultrapassou);
    if (!empty($ultrapassou)) { 
      if(count($ultrapassou) > 1){?>
        <h4 class="card-title"><?php echo $count;?> <b>comentários concentraram mais repostas.</b></h4>
      <?php }else{ ?>
        <h4 class="card-title"><?php echo $count;?>  <b>comentário concentrou mais repostas.</b></h4>
      <?php }?>
      
        <?php
        foreach($ultrapassou as $id => $value) {
        $modal .= "
          <div id='#" . $id . "'>
            <div id='#brief_". $id ."'>" .
                 substr($value['body'],0,106).'... ' . "
                <a  data-toggle='collapse' data-target='#". $id ."' aria-expanded='false' aria-controls='collapseTwo'>
                  <i class='fa fa-plus-square-o' onclick=\"document.getElementById('#brief_". $id ."').style.color = 'transparent'\";></i>
                  <i class='fa fa-minus-square-o' onclick=\"document.getElementById('#brief_". $id ."').style.color = '#0c0c0c'\";></i>
                </a>
            </div>
            <div id=". $id ." class='collapse' aria-labelledby='headingTwo' data-parent='#". $id ."'>" .
               $value['body'] ."
            </div>
          </div><br>";
        }?>
        <input type="button" class="btn btn-primary" aria-hidden="true" data-toggle="modal" data-target="#modalComments" value="Veja quais são"/>

      <?php }else{
        echo "<h4 class='card-title'>A respostas estão bem distribuídas entre os comentários</h4>";
      }?>
  </div>
</div>



<!-- Modal -->
<div class="modal fade" id="modalAnalysisInteraction" tabindex="-1" role="dialog" aria-labelledby="modalAnalysisInteractionLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalAnalysisInteractionLabel">Análise da interação</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Identifica se a discussão está concentrada em algum comentário de primeiro nível. (Quando pelo menos 25% dos comentários estão respondendo a algum comentário de primeiro nível.)
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalComments" tabindex="-1" role="dialog" aria-labelledby="modalCommentsLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title" id="modalAnalysisInteractionLabel">Comentários</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <?php echo $modal;?>
      </div>
    </div>
  </div>
</div>