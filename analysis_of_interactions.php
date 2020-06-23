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

if ($_POST['link_id']) {
    $query = "
    SELECT id, author, REPLACE(body,'b','') as body,
          REPLACE(parent_id, 't1_', '') parent_id,
          1 AS total
    FROM ".$_POST['link_id'];

    $array = array();
    
    $stmt=$con->prepare($query);
    $stmt->execute();
    $array = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    $x = buildTree($array, 't3_'.$_POST['link_id']);
    $total = array_reduce($x, function($carry, $item){
        $carry += $item['total'];
        return $carry;
    });
    $limit = 0.25 * $total;
    foreach($x as $id => $item) {
        if ($item['total'] >= $limit) {
            $ultrapassou[$id] = ["body" => $item['body'], "author" => $item['author']];
        }
    }  
}?>

<div class="card">
  <div class="card-header">  
    <i class="fa fa-question-circle-o" aria-hidden="true" data-toggle="modal" data-target="#modalAnalysisInteraction"></i>
    <b>Análise da interação</b>
    <?php
    $count = count($ultrapassou);
    if (!empty($ultrapassou)) { 
      if(count($ultrapassou) > 1){?>
        <h4 class="card-title">Alguns comentários concentraram mais repostas:</h4>
      <?php }else{ ?>
        <h4 class="card-title">Um comentário concentrou mais repostas:</h4>
      <?php }?>
      
        <?php
        foreach($ultrapassou as $id => $value) { ?>      

          <div id="#<?php echo $id; ?>" >              
              <div id="#brief_<?php echo $id; ?>" class="card-text">
                <?php echo substr($value['body'],0,80).'... ';?>                         
                <a  data-toggle="collapse" data-target="#<?php echo $id; ?>" aria-expanded="false" aria-controls="collapseTwo">
                  <i class="fa fa-plus-square-o" onclick="document.getElementById('#brief_<?php echo $id; ?>').style.color = 'white'";></i>
                  <i class="fa fa-minus-square-o" onclick="document.getElementById('#brief_<?php echo $id; ?>').style.color = '#747373'";></i>                
                </a>
              </div>
              <div id="<?php echo $id; ?>" class="collapse" aria-labelledby="headingTwo" data-parent="#<?php echo $id; ?>">
                <?php echo substr($value['body'], 1, -1); ?>
              </div>
          </div>	
        <?php
        }
      }else{
        echo "<p class='card-title'>A respostas etão bem distribuídas aos comentários</p>";
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
        Identifica quando pelo menos 25% da discussão está centrada em um algum comentário de primeiro nível.
      </div>
    </div>
  </div>
</div>