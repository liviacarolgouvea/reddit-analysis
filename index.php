
<?php 

//http://localhost?link_id=ger4qu


include_once "header.php";
		
include_once "con.php";


$chrome = strpos($_SERVER["HTTP_USER_AGENT"], 'Chrome') ?  true : false;


if(!$chrome){

    echo "<h2 style='text-align:center;margin-top:10%'>Por favor, abra este experimento em um navegador Chrome<h2>";

}elseif($_GET['screenwidth']){

    echo "<h2 style='text-align:center;margin-top:10%'>Por favor, abra este experimento em uma tela maior<h2>";

}else{

    if($_GET['page']){

        include_once $_GET['page'].".php";

    }else{

        $random = rand(1,100);

        if(($random % 2) == 0){
            $type = "indicadores";
        }else{
            $type = "resumo";
        }

        if($_GET['type']){
            $type = $_GET['type'];
        }

        include_once "description.php";
        echo "<b style='color:transparent'>".$type."</b>";

    }
}

include_once "footer.php";
 
 ?>		
