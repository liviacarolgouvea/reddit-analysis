
<div class="card-header border-0 text-white py-3" style="background: #da542e">
    <!-- <span class="font-weight-bold medium">Age dynamics</span> -->
    <span class="font-weight-bold medium">Dinâmica temporal</span>
    <div class="small mb-2">Identifica o volume de comentários ao longo da vida da discussão</div>
<!-- </div> -->
    <?php
    $query = " SELECT CASE 
                WHEN data_criacao = DATA_INICIAL THEN 'Bloomer inicial'
                WHEN data_criacao = DATA_FINAL THEN 'Bloomer final'
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
            $age = "Esta discussão é um " . $row['LOCALIZACAO'];
        }else{
            $age = "Esta discussão teve postagens constantes ao longo de sua duração.";
        }
    }
    ?>

    <!-- <div class="list-group list-group-flush"> -->
    <!-- <div class="list-group-item list-group-item-action py-3">-->
        <?php echo $age; ?>
<!--     </div> -->
</div>