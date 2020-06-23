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
			FROM 	".$_POST['link_id']."
			WHERE 	link_id = 't3_".$_POST['link_id']."'";
$stmt=$con->prepare($query);
$stmt->execute();
$row_caracteristica_conversa = $stmt->fetchAll();
?>

<table style="width:90%; position: relative; min-width: 200px; margin: 5px auto; background: white; border:1px solid #cccccc">
	<tr>			
	<?php if(isset($_POST['indicadores'])){?>
		<td style="width:45%; padding: 10px;">
			<div>
				<?php include_once "analysis_of_interactions.php";?>															
				<br>
					<?php include_once "signatures_of_controversies.php";?>
				<br>
					<?php include_once "age_dynamics.php";?>
				
					<?php //include_once "mayfly_buzz_behavior.php";?>
				<br>
					<?php include_once "monopolistic_behavior.php";?>
				<br>
					<?php include_once "concentracao_votos.php";?>
			</div>														
		</td> 
	<?php }?>
		<td style="width:45%; padding: 10px;vertical-align: top">					
			<?php include_once "summarization.php";?>
			<br>
			<div>
				<blockquote class="reddit-card" data-card-created="1556072778" >
					<a href="https://www.reddit.com/r/Coronavirus/comments/<?php echo $_POST['link_id']?>/"></a>
				</blockquote>
				<a style="float: right;"  href="https://www.reddit.com/r/Coronavirus/comments/<?php echo $_POST['link_id']?>/" target="_blank">Ir para a discussão</a>						
			</div>								
						
		</td>		
	</tr>
	<tr>
		<td colspan="2">
			<a class="btn btn-primary btn-lg btn-block"  href="" target="_blank" style="float: right;">Responder ao questionário <i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
		</td>
	</tr>
</table>
						
<?php include_once "footer.php";?>		
