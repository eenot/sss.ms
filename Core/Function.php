<?php
// 通用函数库
function p($val){
    echo '<pre>';
    return var_dump($val);
}

function clean($string,$level='1',$chars=FALSE,$leave=""){        
    if(is_array($string)) return array_map("clean",$string);

    $string=preg_replace('/<script[^>]*>([\s\S]*?)<\/script[^>]*>/i', '', $string);      
    switch ($level) {
      case '3':
        $search = array('@<script[^>]*?>.*?</script>@si',
                       '@<[\/\!]*?[^<>]*?>@si',
                       '@<style[^>]*?>.*?</style>@siU',
                       '@<![\s\S]*?--[ \t\n\r]*>@'
        ); 
        $string = preg_replace($search, '', $string);           
        $string=strip_tags($string,$leave);      
        if($chars) {
          if(phpversion() >= 5.4){
            $string=htmlspecialchars($string, ENT_QUOTES | ENT_HTML5,"UTF-8");  
          }else{
            $string=htmlspecialchars($string, ENT_QUOTES,"UTF-8");  
          }
        }
        break;
      case '2':
        $string=strip_tags($string,'<b><i><s><u><strong><span><p>');
        break;
      case '1':
        $string=strip_tags($string,'<b><i><s><u><strong><a><pre><code><p><div><span>');
        break;
    }   
    $string=str_replace('href=','rel="nofollow" href=', $string);   
    return $string; 
}


function get_header(){        
    return include(TEMPLATE."/header.php");
}
function get_footer(){        
    return include(TEMPLATE."/footer.php");
}
?>