<?php
  $db = mysql_connect("xxx","xxx","xxx");
  mysql_select_db ("xxx") or die ("Нет подключения к базе данных");
  
   function mysql_safe($query,$params=false) { 
    if ($params) { 
        foreach ($params as &$v) { $v = mysql_real_escape_string($v); }    # Escaping parameters 
        # str_replace - replacing ? -> %s. %s is ugly in raw sql query 
        # vsprintf - replacing all %s to parameters 
        $sql_query = vsprintf( str_replace("?","'%s'",$query), $params );    
        $sql_query = mysql_query($sql_query);    # Perfoming escaped query 
    } else { 
        $sql_query = mysql_query($query);    # If no params... 
    } 

    return ($sql_query); 
  }
?>
