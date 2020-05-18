<div class="card-header">
    <!-- <span class="font-weight-bold medium">Signatures of Controversies</span> -->
    <span class="font-weight-bold medium">Medida de controvérsia</span>
    <div class="small mb-2">Identifica o grau de controvérsia da discussão a partir da quantidade de comentários exluídos</div>
<!-- </div>
<di v class="list-group list-group-flush">
  <div class="list-group-item list-group-item-action py-3"> -->
  <div class="div-output">
    <?php
    $sql_controversy = "SELECT TOTAL_COMMENTS, DELETED, (DELETED / TOTAL_COMMENTS) * 100 AS PORCENTAGEM
                        
                        FROM
                        (
                            SELECT 	COUNT(id) AS DELETED 
                            FROM 	".$_GET['link_id']."
                            WHERE 	author = '[deleted]'
                        )A
                            
                            LEFT JOIN
                            
                        (	
                            SELECT 	COUNT(id) AS TOTAL_COMMENTS 
                            FROM 	".$_GET['link_id']."
                        )B	

                        ON 1 = 1";

    $result=$con->prepare($sql_controversy);
    $result->execute();
    $row_controversy = $result->fetchAll(\PDO::FETCH_ASSOC);

    echo "O grau de controvérsia desta discussão é de " . round($row_controversy[0]['PORCENTAGEM'],1)."%";?>
    </div>
<!-- </div> -->
</div>




