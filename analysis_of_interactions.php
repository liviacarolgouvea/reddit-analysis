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
    SELECT id, author, body, body_html,
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
            $ultrapassou[$id] = ["body" => $item['body'], "body_html" => $item['body_html'], "author" => $item['author']];
        }
    }
}?>

<div class="card">
    <!-- <b>Análise da interação</b> -->
    <?php
    $count = count($ultrapassou);
    if (!empty($ultrapassou)) {
      if(count($ultrapassou) > 1){?>
        <div class="card-header indicators">
          <!-- <h4 class="card-title"><?php// echo $count;?> <b>comentários concentraram mais repostas.</b></h4> -->
          <i class="fa fa-question-circle-o" aria-hidden="true" data-toggle="modal" data-target="#modalAnalysisInteraction"></i>
          Discussion is focused in some comments
        </div>
        <div class="card-body">
          Much answer in a few comments
          <br>
          <i class="fa fa-frown"></i>
          <br>
          <!-- <input type="button" class="btn btn-primary" aria-hidden="true" data-toggle="modal" data-target="#modalComments" value="Veja quais são"/> -->
          <input type="button" class="btn btn-primary" aria-hidden="true" data-toggle="modal" data-target="#modalComments" value="View"/>
        </div>
      <?php }else{ ?>
        <div class="card-header indicators">
          <!-- <h4 class="card-title"><?php // echo $count;?>  <b>comentário concentrou mais repostas.</b></h4> -->
          <i class="fa fa-question-circle-o" aria-hidden="true" data-toggle="modal" data-target="#modalAnalysisInteraction"></i>
          Discussion focused on a comment
        </div>
        <div class="card-body">

          One comment had much more <b> replies </b> than the rest
          <br>
          <i class="fa fa-frown"></i>
          <br>
          <input type="button" class="btn btn-primary" aria-hidden="true" data-toggle="modal" data-target="#modalComments" value="View"/>
        </div>
      <?php }?>

        <?php
        foreach($ultrapassou as $id => $value) {
        $modal .= "
          <div id='#" . $id . "'>
            <div id='#brief_". $id ."'>
              " .substr($value['body'],0,95).'... ' . "
              <a  data-toggle='collapse' data-target='#". $id ."' aria-expanded='false' aria-controls='collapseTwo'>
                <i class='fa fa-plus-square-o' onclick=\"document.getElementById('#brief_". $id ."').style.color = 'transparent'\";></i>
                <i class='fa fa-minus-square-o' onclick=\"document.getElementById('#brief_". $id ."').style.color = '#0c0c0c'\";></i>
              </a>
            </div>
            <div id=". $id ." class='collapse' aria-labelledby='headingTwo' data-parent='#". $id ."'>" .
               $value['body_html'] ."
            </div>
          </div><br>";
        }?>
      <?php }else{
        echo "<h4 class='card-title'>A respostas estão bem distribuídas entre os comentários</h4>";
      }?>
</div>



<!-- Modal -->
<div class="modal fade" id="modalAnalysisInteraction" tabindex="-1" role="dialog" aria-labelledby="modalAnalysisInteractionLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <!-- <h5 class="modal-title" id="modalAnalysisInteractionLabel">Análise da interação</h5> -->
        <h5 class="modal-title" id="modalAnalysisInteractionLabel">Interaction analysis</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Identifica se a discussão está concentrada em algum comentário de primeiro nível. (Quando pelo menos 25% dos comentários estão respondendo a algum comentário de primeiro nível.) -->
        Identifies whether the discussion is focused on any first-level comments. (When at least 25% of comments are responding to some top-level comments.)
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalComments" tabindex="-1" role="dialog" aria-labelledby="modalCommentsLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
    <div class="modal-header">
        <!-- <h5 class="modal-title" id="modalAnalysisInteractionLabel">Comentários</h5> -->
        <h5 class="modal-title" id="modalAnalysisInteractionLabel">Comments</h5>
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