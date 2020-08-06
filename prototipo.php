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
	<p class="lead">
		Abaixo temos, no primeiro quadro, o link para a discussão. No quadro azul, um breve resumo do conteúdo da discussão. E nos quadros amarelos, informações sobre a discussão.
		Explore o que achar necessário para compreender o debate. Em seguida, responda ao questionário.
	</p>
	<?php }else{?>
	<p class="lead">
		Nesta página temos o link para a discussão seguido de um breve resumo dela.
		<br>
		Explore o que achar necessário para compreender o debate. Em seguida, responda ao questionário.
	</p>
	<?php }?>

	<div class="row">
		<div class="col-sm">
			<div class="card">
  				<div class="card-header">
					<blockquote class="reddit-card" data-card-created="1556072778" >
						<a href="https://www.reddit.com/comments/<?php echo $_GET['link_id']?>/"></a>
					</blockquote>
					<!-- <a href="https://www.reddit.com/comments/<?php // echo $_GET['link_id']?>/" target="_blank">Ir para a discussão</a>	 -->
  				</div>
			</div>
		</div>
	</div>
	<br>
	<div class="row">
		<div class="col-sm">
			<?php include_once "summarization.php";?>
		</div>
	</div>
	<br>
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
	<br>
	<div class="row">
		<div class="col-sm">
			<?php if($_GET['type'] ==  "indicadores"){?>
				<a class="btn btn-lg"  href="https://docs.google.com/forms/d/e/1FAIpQLSfHHWLF_IYy6R2EMvE-hyPNuZWmA9ePcVq3g8yjGloT2VSUaw/viewform?usp=sf_link" target="_blank">Responder ao questionário <i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
			<?php }else{?>
				<a class="btn btn-lg"  href="https://docs.google.com/forms/d/e/1FAIpQLSeoBfXlQRSSxgl0dOndt50lZIplapK_KQQ4qqMX7bzN3SqF5Q/viewform?usp=sf_link" target="_blank">Responder ao questionário <i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
			<?php }?>
		</div>
	</div>
	<br>
</div>
						
<?php include_once "footer.php";?>		
