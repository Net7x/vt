<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
        <?php 
          include "condb.php";
          $cur_ip = $_SERVER['REMOTE_ADDR'];
          $cur_host = gethostbyaddr($cur_ip);
          $res = mysql_query("SELECT * FROM allowed_hosts WHERE host = '$cur_host'");
          if(mysql_num_rows($res) > 0) {
            echo "You can add users.";
            echo "<form action='makeuser.php' method='GET'>"; 
            echo "Name: ";
            echo "<input type='text' name='name' size=20>";
            echo "<br>Pass: ";
            echo "<input type='text' name='pass' size=20>";
            echo "<br>";
            echo "<input type='submit' value='add'>";
            echo "</form>";
            if(isset($_GET['name'])) {
              $given_name = $_GET['name'];   
              if($given_name != "") {
                $time = date("d.m.Y H:m:s");
                $saltedpass=$_GET['pass'].$time;
                $salted = md5($saltedpass);
                mysql_query("INSERT INTO users (name,pass,salt) VALUES ('$given_name','$salted','$time')");
                $res = mysql_query("SELECT * FROM users WHERE name = '$given_name'");
                $row = mysql_fetch_array($res);
                if ($row['pass'] == $salted) {
                    echo "Successfully added.";
                } else {
                    echo "Bad luck.";
                }
              }
            }
          } else {
            echo "You have no rights to add users.";    
          }
        ?>
    </body>
</html>
