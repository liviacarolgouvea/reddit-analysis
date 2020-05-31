<div class="card">
    <div class="card-header">
        Dinâmica temporal
        <i class="fa fa-question-circle-o" aria-hidden="true" data-toggle="modal" data-target="#modalAgeDynamics"></i>    
    </div>
    <div class="card-body">
    <?php
    $query = " SELECT CASE 
                WHEN data_criacao = DATA_INICIAL THEN 'Esta discussão teve mais postagens no início'
                WHEN data_criacao = DATA_FINAL THEN 'Esta discussão teve mais postagens no início'
            ELSE 
                'MEIO'
            END LOCALIZACAO,
            data_criacao, COMENTARIOS_DIA, TOTAL_COMENTARIOS, (COMENTARIOS_DIA/TOTAL_COMENTARIOS) AS PORCENTAGEM 
            FROM 
            (
                SELECT 		DATE_FORMAT(nullif(from_unixtime(created_utc,'%Y-%m-%d'),'31/12/1969'),'%Y-%m-%d') AS data_criacao, count(id) AS COMENTARIOS_DIA	
                FROM 		".$_GET['link_id']."
                GROUP BY 	nullif(from_unixtime(created_utc,'%Y-%m-%d'),'31/12/1969')
                ORDER BY 	created_utc
            )A
            LEFT JOIN 
            (
                SELECT 		 count(id) AS TOTAL_COMENTARIOS
                FROM 		".$_GET['link_id']."
            )B
            ON 1 = 1 
            LEFT JOIN
            (
                SELECT 		MIN(DATE_FORMAT(nullif(from_unixtime(created_utc,'%Y-%m-%d'),'31/12/1969'),'%Y-%m-%d')) DATA_INICIAL, MAX(DATE_FORMAT(nullif(from_unixtime(created_utc,'%Y-%m-%d'),'31/12/1969'),'%Y-%m-%d')) DATA_FINAL
                FROM 		".$_GET['link_id']."
            )C
            ON 1 = 1
            ORDER BY PORCENTAGEM DESC LIMIT 1"; 
    foreach($con->query($query) as $row) {
        if ($row['PORCENTAGEM'] >= 0.75) {
            $age = $row['LOCALIZACAO'];
        }else{
            $age = "Esta discussão teve postagens constantes ao longo de sua duração.";
        }
    }
    ?>
    <p class="card-title"><?php echo $age; ?></p>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalAgeDynamics" tabindex="-1" role="dialog" aria-labelledby="modalAgeDynamicsLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalAgeDynamicsLabel">Dinâmica temporal</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Pode-se identificar três tipos de comportamento nas evolução das postagens na discussão:
            <ul>
                <li>
                    Bloomers iniciais: identifica se os comentários (mais de 75%) se concentraram no primeiro dia da discussão.
                </li>
                <li>
                    Postagens constantes: identifica se a discussão evoluiu de forma constante ao longo da vida útil.
                </li>
                <li>
                    Bloomers finais: identifica se a discussão concentrou os comentários no final de sua vida.
                </li>
            </ul>
      </div>
    </div>
  </div>
</div>