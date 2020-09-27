<div class="card">
        <!-- <b>Dinâmica temporal:</b> -->
        <!-- <h4 class="card-title">Evolução temporal dos comentários</h4> -->
        <?php
        if(!empty($row_caracteristica_conversa) && $row_caracteristica_conversa[0]['DURACAO'] == 0){
            $age = "A discussão teve um comportamento explosivo durando apenas um dia (fogo de palha) ";
        }else{
            $query = " SELECT 		CASE    WHEN data_criacao = DATA_INICIAL THEN 'DATA_INICIAL'
                                            WHEN data_criacao = SEGUNDO_DIA THEN 'SEGUNDO_DIA'
                                            WHEN data_criacao = DATA_FINAL THEN 'DATA_FINAL'
                                            ELSE 'INTERMEDIARIO'
                                    END LOCALIZACAO,
                                    COMENTARIOS_DIA,
                                    TOTAL_COMENTARIOS,
                                    (COMENTARIOS_DIA / TOTAL_COMENTARIOS) PORCENTAGEM
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
                                    SELECT 		MIN(DATE_FORMAT(nullif(from_unixtime(created_utc,'%Y-%m-%d'),'31/12/1969'),'%Y-%m-%d')) DATA_INICIAL,
                                                MAX(DATE_FORMAT(nullif(from_unixtime(created_utc,'%Y-%m-%d'),'31/12/1969'),'%Y-%m-%d')) DATA_FINAL,
                                                DATE_ADD(MIN(DATE_FORMAT(nullif(from_unixtime(created_utc,'%Y-%m-%d'),'31/12/1969'),'%Y-%m-%d')),INTERVAL 1 DAY) SEGUNDO_DIA
                                    FROM 		".$_GET['link_id']."
                                )C
                        ON 1 = 1
                        ORDER BY LOCALIZACAO";
            //echo "<pre>".$query."</pre>";
            foreach($con->query($query) as $row) {
                if ($row['LOCALIZACAO'] == 'DATA_FINAL' && $row['PORCENTAGEM'] >= 0.75) {
                    $age = "Esta discussão teve mais postagens no final (explosão final)";
                }elseif($row['LOCALIZACAO'] == 'DATA_INICIAL' && $row['PORCENTAGEM'] >= 0.75) {
                    $age = "Esta discussão teve mais postagens no início (explosão inicial).";
                }elseif($row['LOCALIZACAO'] == 'SEGUNDO_DIA' && $row['PORCENTAGEM'] <= 0.30) {
                    //$age = "Esta discussão começou quente mas esfriou no segundo dia";
                    $age = "Discussion was hotter in the first day";
                }else{

                    $age = "";
                }
            }
        }

        $query = "SELECT 		A.DIA, A.QTD_COMENTARIOS
                    FROM
                                (
                                    SELECT 		DATE_FORMAT(nullif(from_unixtime(created_utc,'%Y-%m-%d'),'31/12/1969'),'%d/%m') AS DIA,
                                                    count(id) AS QTD_COMENTARIOS
                                    FROM 		".$_GET['link_id']."
                                    GROUP BY 	nullif(from_unixtime(created_utc,'%Y-%m-%d'),'31/12/1969')
                                    ORDER BY 	created_utc
                                )A";
        //echo "<pre>".$query."</pre>";

        foreach($con->query($query) as $row) {
            $line_chart[] = [$row['DIA'],1*$row['QTD_COMENTARIOS']]; // NECESSÁRIO  MULTIPLICAR O VALOR POR 1 PARA PASSAR COMO O VALOR INTEGER POIS ESTAVA SENDO RECONHECIDO COMO STRING
        }
        $json = json_encode($line_chart);
        echo "<div class='card-header indicators' style='padding-bottom: 0px;'>";
        echo "<i class='fa fa-question-circle-o' aria-hidden='true' data-toggle='modal' data-target='#modalAgeDynamics'></i>";
        echo $age;
        echo "</div>";
        ?>
        <!--Div that will hold the chart-->
        <div id="chart_div" style="border:1px solid #eeeeee"></div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalAgeDynamics" tabindex="-1" role="dialog" aria-labelledby="modalAgeDynamicsLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalAgeDynamicsLabel">Time dynamics</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Pode-se identificar 4 tipos de comportamento na evolução das postagens na discussão: -->
        Discussions use to have 4 behavior types in the evolution of posts:
            <ul>
                <li>
                    <!-- Discussões com comportamento explosivo, que duram apenas um dia. -->
                    Explosive behavior discussions, which last only one day.
                </li>
                <li>
                    <!-- Discussões que ficam mais acaloradas no primeiro dia e depois esfriam. -->
                    Discussions that get hotter on the first day and then cool down.
                </li>
                <li>
                    <!-- Discussões que tem quantidade de comentários constantes ao longo da vida. -->
                    Discussions that have a constant amount of comments throughout life.
                </li>
                <li>
                    <!-- Discussões que começam sem intensidade e ficam acaloradas após alguns dias, no final de sua vida. -->
                    Discussions that begin without intensity and become heated after a few days, at the end of your life.
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
    var options = {'title':'Posts x day',
                    'width':200,
                    'height':155,
                    'backgroundColor': '#fff',
                    'colors': ['#000'],

                    legend: {position: 'none'},
                    hAxis: {
                            slantedText:true,
                            //slantedTextAngle:80
                            },
                    vAxis: {
                            gridlines: { count: 3, color: '#000' }
                            },

                    };

    // Instantiate and draw our chart, passing in some options.
    var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
    chart.draw(data, options);
    }

</script>