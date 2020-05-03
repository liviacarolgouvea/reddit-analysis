<?php
$query_created_utc = "	SELECT 		DATE_FORMAT(nullif(from_unixtime(created_utc,'%Y-%m-%d'),'31/12/1969'),'%d/%b/%Y') AS data_criacao, 
												count(id) AS count
								FROM 			".$_GET['link_id']."
								WHERE 		link_id = 't3_".$_GET['link_id']."'
								GROUP BY 	nullif(from_unixtime(created_utc,'%Y-%m-%d'),'31/12/1969')
								ORDER BY 	created_utc";
$result_created_utc = mysqli_query($con,$query_created_utc);

$created_utc_array = array();
$count_created_utc_array = array();

while($row_created_utc = mysqli_fetch_assoc($result_created_utc)) {
$created_utc_array[] = $row_created_utc['data_criacao'];
$count_created_utc_array[] = (int)$row_created_utc['count'];		
}

// $query_last_3_days =	"	SELECT 	nullif(from_unixtime(created_utc,'%Y-%m-%d'),'31/12/1969') AS data_criacao, count(id)
// 								FROM 		".$_GET['link_id']."
// 								WHERE 	link_id = 't3_".$_GET['link_id']."'
// 								AND 		nullif(from_unixtime(created_utc,'%Y-%m-%d'),'31/12/1969') BETWEEN DATE_ADD(date(now()), INTERVAL -3 DAY) AND date(now())
// 								GROUP BY nullif(from_unixtime(created_utc,'%Y-%m-%d'),'31/12/1969')";
// $result_last_3_days = mysqli_query($con,$query_last_3_days);

// $num_rows = mysqli_num_rows($result_last_3_days);
// echo "O primeiro post foi em ". $row_caracteristica_conversa['INICIO'] ." e o último em ". $row_caracteristica_conversa['FIM'].". ";
// if ($num_rows > 0) {
// 	echo "A discussão inda está em curso";
// }else{
// 	echo "A discussão acabou.";
// }
?>


<!-- Button trigger modal -->
<button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#dinamica_temporal" style="padding: 5px">
	<span class="fas fa-chart-bar" data-toggle="tooltip" title="Visualizar dinâmica temporal" style="font-size: 14px"></span>
</button>

<!-- Modal -->
<div class="modal fade" id="dinamica_temporal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  	<div class="modal-dialog" role="document" style="max-width:100%">
    <div class="modal-content" style="width: 80%; margin: 0 auto">
      <div class="modal-header">
      	Post frequency decay
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <canvas id="lineChart"></canvas>
      </div>
    </div>
  </div>
</div>				
