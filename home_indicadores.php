  <!-- Start your project here-->
  <div class="container" style="text-align: center; max-width:500px">
	<img src="img/logo-ppgi.png" >  	
	<br><br>
    <div class="flex-justfy flex-column">				  		
		<p>
			Olá, este é um experimento para uma pesquisa de dissertação de mestrado do Núcleo de Estudos em Computação Humana e Inteligência Coletiva do programa de Pós Graduação em Informática da Universidade Federal do Estado do Rio de Janeiro. Caso concorde em participar, leia abaixo os detalhes do experimento.
		</p>
		<p>
			<h5>Objetivo do estudo</h5>			
			<p>
				O objetivo desse experimento é investigar se é possível aumentar a compreensão do usuário sobre uma discussão em andamento em um fórum de discussões a partir da apresentação simultânea de indicadores da dinâmica dessa discussão. Nosso estudo de caso é o site Reddit, a plataforma de fóruns de discussão mais popularmente utilizada no mundo.
			</p>
		</p>  
		<p>
			<h5>Detalhamento das etapas</h5>	
			<p>
				Ao clicar no botão "Prosseguir" o sistema exibirá uma página, onde do lado esquerdo, exibirá algumas caixas com indicadores e do lado direito, um resumo seguido de um tópico de discussão do Reddit. Os indicadores e resumo se referem a este tópico de discussão.
			</p>
			<p>
				No final da página haverá um botão que te direcionará para para um questionário com perguntas para você responder a respeito da experiência vivenciada. 
			</p>
			<p>
				Todo o processo demorará entre 10 a 20 minutos, dependendo de seu aprofundamento na discussão.
			</p>
		</p>
		<p>
			<h5>Dados coletados e Confidencialidade</h5>
			<p>
				Os dados coletados serão utilizados para fins de pesquisa acadêmica e divulgação científica sem fins comerciais.
			</p>
			<p>
				Todas as informações obtidas por meio desta pesquisa serão confidenciais e o sigilo de sua participação é assegurado. 
			</p>
		</p>
		<p>
			<h5>Dúvidas</h5>
			<p>
				Esta pesquisa está sendo realizada pela aluna de mestrado Lívia Gouvêa sob orientação da professora Ana Cristina Bicharra Garcia.
			</p>
			<p>
				Em caso de dúvidas voce pode entrar em contato pelo email <b>livia.faria@uniriotec.br</b>
			</p>
		</p>			  			  
	</div>
	<form action="index.php" method="post">
			<input type="hidden" name="link_id" value="<?php echo $_GET['link_id']?>">	
			<input type="hidden" name="indicadores" value="sim">			  
		<input type="submit" class="btn btn-info btn-lg	 animated fadeIn" value="Prosseguir">
	</form>	
  </div>
  <!-- /Start your project here-->
