<div class="card">
    <div class="card-header">
        <i class="fa fa-question-circle-o" aria-hidden="true" data-toggle="modal" data-target="#modalAgeDynamics"></i>
        <b>Dinâmica temporal:</b>        
        <?php 
        if(!empty($row_caracteristica_conversa) && $row_caracteristica_conversa[0]['DURACAO'] == 0){
            $age = "A discussão teve um comportamento explosivo durando apenas um dia";
        }else{            
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
        }

        $query = "SELECT 		DATE_FORMAT(DATE_SUB(MIN(nullif(from_unixtime(created_utc,'%Y-%m-%d'),'31/12/1969')),INTERVAL 1 DAY),'%d-%M') AS DIA, 
                                    0 AS QTD_COMENTARIOS 
                    FROM 		".$_GET['link_id']."
                    
                    UNION ALL
                    
                    SELECT 		A.DIA, A.QTD_COMENTARIOS
                    FROM
                                (
                                    SELECT 		DATE_FORMAT(nullif(from_unixtime(created_utc,'%Y-%m-%d'),'31/12/1969'),'%d-%M') AS DIA, 
                                                    count(id) AS QTD_COMENTARIOS	
                                    FROM 		".$_GET['link_id']."
                                    GROUP BY 	nullif(from_unixtime(created_utc,'%Y-%m-%d'),'31/12/1969')
                                    ORDER BY 	created_utc
                                )A
        
                    UNION ALL
        
                    SELECT 		DATE_FORMAT(DATE_ADD(MIN(nullif(from_unixtime(created_utc,'%Y-%m-%d'),'31/12/1969')),INTERVAL 1 DAY),'%d-%M') AS DIA, 
                                    0 AS QTD_COMENTARIOS
                    FROM 		".$_GET['link_id']."";
        // echo "<pre>".$query_score."</pre>";							
        
        foreach($con->query($query) as $row) {
            $line_chart[] = [$row['DIA'],1*$row['QTD_COMENTARIOS']]; // NECESSÁRIO  MULTIPLICAR O VALOR POR 1 PARA PASSAR COMO O VALOR INTEGER POIS ESTAVA SENDO RECONHECIDO COMO STRING
        }
        $json = json_encode($line_chart);           
        echo "<h4 class='card-title'>".$age."</h4>"; ?>
        <!--Div that will hold the chart-->        
        <div id="chart_div" style="float:left; margin-left:35%; border:1px solid #eeeeee"></div>        
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
        Pode-se identificar 4 tipos de comportamento nas evolução das postagens na discussão:
            <ul>
                <li>
                    Discussões com comportamento explosivo, que duram apenas um dia.
                </li>
                <li>
                    Discussões que ficam mais acaloradas no primeiro dia e depois esfriam.
                </li>
                <li>
                    Discussões que tem quantidade de comentários constantes ao longo da vida.
                </li>
                <li>
                    Discussões que começam sem intensidade e ficam acaloradas após alguns dias, no final de sua vida.
                </li>
            </ul>
      </div>
	     <!--Div that will hold the pie chart-->
		 <div id="chart_div"></div>      
    </div>
  </div>
</div>

<script type="text/javascript">

    // Load the Visualization API and the corechart package.
    google.charts.load('current', {'packages':['corechart']});

    // Set a callback to run when the Google Visualization API is loaded.
    google.charts.setOnLoadCallback(drawChart);

    // Callback that creates and populates a data table,
    // instantiates the pie chart, passes in the data and
    // draws it.
    function drawChart() {

    // Create the data table.
    var data = new google.visualization.DataTable();
    data.addColumn('string', 'Topping');
    data.addColumn('number', 'Slices');
    data.addRows(<?php echo $json;?>);            

    // Set chart options
    var options = {'title':'Posts x dia',
                    'width':200,
                    'height':100,
                    legend: {position: 'none'}};

    // Instantiate and draw our chart, passing in some options.
    var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
    chart.draw(data, options);
    }

</script>