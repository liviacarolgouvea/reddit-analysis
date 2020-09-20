<?php 

include_once "header.php";
		
include_once "con.php";

$query = "	SELECT 	count(distinct id) AS TOTAL_POSTS,
					count(distinct author) AS TOTAL_AUTORES,
					DATE_FORMAT(MIN(nullif(from_unixtime(created_utc,'%Y-%m-%d'),'31/12/1969')),'%d/%b/%Y') AS INICIO,
					DATE_FORMAT(MAX(nullif(from_unixtime(created_utc,'%Y-%m-%d'),'31/12/1969')),'%d/%b/%Y') AS FIM,
					ROUND(AVG(CHAR_LENGTH(body))) TAMANHO_MEDIO_POSTS,
					DATEDIFF(date(now()),MAX(nullif(from_unixtime(created_utc,'%Y-%m-%d'),'31/12/1969'))) DIAS_ULTIMO_POST,
					DATEDIFF(MAX(nullif(from_unixtime(created_utc,'%Y-%m-%d'),'31/12/1969')),MIN(nullif(from_unixtime(created_utc,'%Y-%m-%d'),'31/12/1969'))) DURACAO
			FROM 	".$_GET['link_id']." ";
$stmt=$con->prepare($query);
$stmt->execute();
$row_caracteristica_conversa = $stmt->fetchAll();
?>

<div class="container">
	<?php if($_GET['type'] ==  "indicadores"){?>
<!-- 	<p class="lead">
		Abaixo temos, no primeiro quadro, o link para a discussão. No quadro azul, um breve resumo do conteúdo da discussão. E nos quadros amarelos, informações sobre a discussão.
		Explore o que achar necessário para compreender o debate. Em seguida, responda ao questionário.
	</p> -->
	<p class="lead">
		Below, there is the title of a topic discussion witch is a link to the discussion. And, in the yellow boxes, there are some information about this discussion.
		Explore what you think is necessary to understand the debate. Then, answer the questionnaire.
	</p>
	<?php }else{?>
<!-- 	<p class="lead">
		Nesta página temos o link para a discussão seguido de um breve resumo dela.
		<br>
		Explore o que achar necessário para compreender o debate. Em seguida, responda ao questionário.
	</p> -->
	<p class="lead">
		Below, there is the title of the discussion topic. Click to go to the discussion, browse through it and then answer the questionnaire
	</p>
	<?php }?>

	<div class="row">
		<div class="col-sm">
			<div class="card">
  				<div class="card-header">
					  <!-- <b>					<a href="https://www.reddit.com/r/politics/comments/<?php echo $_GET['link_id']?>" target="blank">Go to discussion</a></b> -->
					<blockquote class="reddit-card" data-card-created="1597519765" data-card-preview="0">
						<a href="https://www.reddit.com/r/politics/comments/<?php echo $_GET['link_id']?>/"></a>
					</blockquote>
					<script async src="//embed.redditmedia.com/widgets/platform.js" charset="UTF-8"></script>

					<div class="row">
						<?php if($_GET['type'] ==  "indicadores"){?>
							<div class="col-sm">
								<?php include_once "analysis_of_interactions.php";?>
							</div>
							<div class="col-sm">
								<?php include_once "concentracao_votos.php";?>
							</div>
							<div class="col-sm">
								<?php include_once "monopolistic_behavior.php";?>
							</div>
							<div class="col-sm">
								<?php include_once "signatures_of_controversies.php";?>
							</div>
							<div class="col-sm">
								<?php include_once "age_dynamics.php";?>
								<?php //include_once "mayfly_buzz_behavior.php";?>
							</div>
						<?php }?>
					</div>
  				</div>
			</div>
		</div>
	</div>
<!-- 	<br>
	<div class="row">
		<div class="col-sm">
			<?php //include_once "summarization.php";?>
		</div>
	</div> -->
	<br>

	<br>
	<div class="row">
		<div class="col-sm">
			<?php if($_GET['type'] ==  "indicadores"){?>
				<!-- <a class="btn btn-lg"  href="https://docs.google.com/forms/d/e/1FAIpQLSfHHWLF_IYy6R2EMvE-hyPNuZWmA9ePcVq3g8yjGloT2VSUaw/viewform?usp=sf_link" target="_blank">Answer the questionnaire <i class="fa fa-pencil-square-o" aria-hidden="true"></i></a> -->
				<a class="btn btn-lg"  href="https://docs.google.com/forms/d/e/1FAIpQLSdCmFNqE3RI3NXepQ9Vh4rjUDqiku53I9JPxlkdbABv-j5VpA/viewform?usp=sf_link" target="_blank">Answer the questionnaire <i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
			<?php }else{?>
				<!-- <a class="btn btn-lg"  href="https://docs.google.com/forms/d/e/1FAIpQLSeoBfXlQRSSxgl0dOndt50lZIplapK_KQQ4qqMX7bzN3SqF5Q/viewform?usp=sf_link" target="_blank">Answer the questionnaire <i class="fa fa-pencil-square-o" aria-hidden="true"></i></a> -->
				<a class="btn btn-lg"  href="https://docs.google.com/forms/d/e/1FAIpQLSfR_I_NW7ltCQ681hXA6nWFZ-8olKpyW8UBY8kQZw9-M7SChQ/viewform?usp=sf_link" target="_blank">Answer the questionnaire <i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
			<?php }?>
		</div>
	</div>
	<br>
</div>
						
<?php include_once "footer.php";?>		
