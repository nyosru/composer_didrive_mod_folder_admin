<?php

// только я могу 
if (isset($_SESSION['now_user_di']['soc_web_id']) && $_SESSION['now_user_di']['soc_web_id'] == 5903492) {


    /**
      определение функций для TWIG
     */
//creatSecret
// $function = new Twig_SimpleFunction('creatSecret', function ( string $text ) {
//    return \Nyos\Nyos::creatSecret($text);
// });
// $twig->addFunction($function);

    $function = new Twig_SimpleFunction('getListFolder', function ( ) {
        return \Nyos\mod\FolderAdmin::getList();
    });
    $twig->addFunction($function);
    
}