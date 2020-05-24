<div class="card-header overflow-auto" style="height: 200px">
    <!-- <span class="font-weight-bold medium"><i class="far fa-books"></i>Monopolistic behavior</span> -->
    <span class="font-weight-bold medium">Resumo</span>
<!-- </div>		 -->
<!-- <div class="list-group list-group-flush"> -->
  <!-- <div class="list-group-item list-group-item-action py-3 overflow-auto" style="height: 100px"> -->
    <?php
    $query_summary = "SELECT REPLACE(body,'b','') as body, A.PARENT, B.id, A.REPLYS, SOM_SCORE
                      FROM
                      (
                        SELECT REPLACE(parent_id,'t1_','') AS PARENT, COUNT(id) AS REPLYS 
                        FROM ".$_GET['link_id']."
                        WHERE parent_id <> 't3_".$_GET['link_id']."'
                        GROUP BY parent_id
                        ORDER BY REPLYS DESC 
                      )A
                      LEFT JOIN
                      (
                        SELECT id, SUM(ABS(score)) SOM_SCORE
                        FROM ".$_GET['link_id']."
                        GROUP BY id
                        ORDER BY SOM_SCORE DESC
                      )B
                      ON A.PARENT = B.ID
                      
                      LEFT JOIN ".$_GET['link_id']." C
                      ON C.id = B.id
                      
                      ORDER BY A.REPLYS DESC, SOM_SCORE DESC LIMIT 3";
    // echo "<pre>".$query_score."</pre>";							

    foreach($con->query($query_summary) as $row_summary) {
      echo "<div>" . $row_summary['body'] . "</div>";
    }?>
    </div>
<!--   </div> -->
</div>
