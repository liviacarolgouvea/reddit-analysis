<!-- iumbm7
https://www.reddit.com/r/politics/comments/iumbm7/fbi_director_wray_says_russia_is_actively/ -->
<?php

//http://localhost?link_id=ger4qu


include_once "header.php";

include_once "con.php";


$chrome = strpos($_SERVER["HTTP_USER_AGENT"], 'Chrome') ?  true : false;


if(!$chrome){

    echo "<h2 style='text-align:center;margin-top:10%'>Please, open this experiment in a Chrome browser<h2>";

}elseif($_GET['screenwidth']){

    echo "<h2 style='text-align:center;margin-top:10%'>Please, open this experiment on a larger screen<h2>";

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
        echo "<b style='color:transparent; float:left'>".$type."</b>";
        include_once "description.php";


    }
}

include_once "footer.php";

 ?>
