  <!-- Start your project here-->
  <div class="container" style="text-align: center; max-width:500px;">
	<img src="img/logo-ppgi.png" >
	<br><br>
	<form id="app-login" action="index.php" method="get">
	<input type="hidden" name="page" value="prototipo">
	<input type="hidden" name="link_id" value="<?php echo $_GET['link_id']?>">
	<input type="hidden" name="type" value="<?php echo $type;?>">
    <div class="flex-justfy flex-column">
		<p>
			<!-- Olá, este é um experimento para uma pesquisa de dissertação de mestrado do Núcleo de Estudos em Computação Humana e Inteligência Coletiva do programa de Pós Graduação em Informática da Universidade Federal do Estado do Rio de Janeiro.  -->
			Hello, this is an experiment for a master degree research at the Center for Studies in Human Computing and Collective Intelligence of the Postgraduate Program in Informatics at the Federal University of the State of Rio de Janeiro.
		</p>
		<p>
			<h5>
				<!-- Objetivo do estudo -->
				Objective of the study
			</h5>
			<p>
				<!-- O objetivo desse experimento é investigar se é possível aumentar a compreensão do usuário sobre uma discussão em andamento em um fórum de discussões. -->
				The purpose of this experiment is to investigate whether it is possible to increase the user's understanding of an ongoing discussion in a discussion forum.
			</p>
			<p>
				<!-- Nosso estudo de caso é o site <i>Reddit</i>, a plataforma de fóruns de discussão mais popularmente utilizada no mundo. -->
				Our case study is the <i> Reddit </i> website, a popular discussion foruns platform.
			</p>
		</p>
		<p>
			<h5>
				<!-- Detalhamento das etapas -->
				Details of the steps
			</h5>
			<p>
				<!-- Ao clicar no botão "Prosseguir" o sistema exibirá a página do experimento. -->
				By clicking on the "Proceed" button, you will see the experiment page with a Reddit topic discussion.
			</p>
			<p>
				<!-- No final da página haverá um botão que te direcionará para um questionário com perguntas para você responder a respeito da experiência vivenciada. -->
				At the bottom of the page there will be a button that will take you to a questionnaire with questions for you to answer about the experience.
			</p>
			<p>
				<!-- Todo o processo demorará entre 10 a 20 minutos, dependendo de seu aprofundamento na discussão. -->
				The whole process will take between 10 and 20 minutes, depending on your deepening in the discussion.
			</p>
		</p>
		<p>
			<h5>
				<!-- Dados coletados e Confidencialidade -->
				Collected data and Confidentiality
			</h5>
			<p>
				<!-- Os dados coletados serão utilizados para fins de pesquisa acadêmica e divulgação científica sem fins comerciais. -->
				The collected data will be used for academic research and scientific dissemination purposes without commercial purposes.
			</p>
			<p>
				<!-- Todas as informações obtidas por meio desta pesquisa serão confidenciais e o sigilo de sua participação é assegurado.  -->
				All information obtained through this survey will be confidential and the confidentiality of your participation is ensured.
			</p>
		</p>
		<p>
			<h5><!-- Dúvidas -->Doubts</h5>
			<p>
				<!-- Esta pesquisa está sendo realizada pela aluna de mestrado Lívia Gouvêa sob orientação da professora Ana Cristina Bicharra Garcia. -->
				This research is being carried out by master student Lívia Gouvêa under the guidance of professor Ana Cristina Bicharra Garcia.
			</p>
			<p>
				<!-- Em caso de dúvidas voce pode entrar em contato pelo email <b>livia.faria@uniriotec.br</b> -->
				In case of doubts you can contact us by email <b> livia.faria@uniriotec.br </b>
			</p>
		</p>
		<input type="checkbox" name="accept" value="accept" required><label for="vehicle2">&nbsp;
			<!-- Concordo em participar da pesquisa -->
			I agree to participate in the survey
		</label>		
	</div>
	<input name="login" type="submit" value="Proceed" class="btn btn-primary">
  </div>
  <!-- /Start your project here-->
