<?php
    include "condb.php";
    mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'", $db);
    function show_form() {
        echo "<form action='adm.php' method='GET'>";
        echo "Login: ";
        echo "<input type='text' name='login' size=20>";
        echo "<br>Pass: ";
        echo "<input type='text' name='pass' size=20>";
        echo "<br>";
        echo "<input type='submit' value='Enter'>";
        echo "</form>";
    }
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title> Административная панель ВАМ ТОВАРЫ </title>
<link rel="stylesheet" type="text/css" href="main.css">
<script language="JavaScript">
  function ChAll () {
    document.caption.action.value = "chall";
    document.caption.submit();
  }
</script>
</head>

<body>

   <!-- Begin Wrapper -->
   <div id="wrapper">
   
         <!-- Begin Header -->
  <div id="header">
    <p id="ani">Панель управления</p>	 
  </div>
	 
        <?php
            echo "<div id='leftcolumn'>";
            if(isset($_GET['login']) && isset($_GET['pass'])) {
                $res = mysql_safe("SELECT * FROM users WHERE name = ?", array($_GET['login']));
                if (mysql_num_rows($res) > 0) {
                    $row = mysql_fetch_array($res);
                    $salted = md5($_GET['pass'].$row['salt']);
                    $res = mysql_safe("SELECT * FROM users WHERE (name = ?) AND (pass = ?)", 
                            array($_GET['login'], $salted));
                    if (mysql_num_rows($res) > 0) {
                        // authorized
                        //echo "You're in.";
                        if (isset($_GET['action'])) {
                            if ($_GET['action'] == 'change') {
                                if (isset($_GET['cat'])) {
                                    if (isset($_GET['chsub'])) { // subcat changing
                                        mysql_safe("UPDATE subcat
                                                    SET percent = ?,round = ? 
                                                    WHERE (id = ?) AND (id_cat = ?)",
                                                    array($_GET['percent'], $_GET['round'], $_GET['chsub'], $_GET['cat']));
                                    } else { // category changing
                                        mysql_safe("UPDATE category
                                                    SET percent = ?,round = ? 
                                                    WHERE id = ?",
                                                    array($_GET['percent'], $_GET['round'], $_GET['cat']));
                                    }   
                                }
                            }
                            if ($_GET['action'] == 'chall') {
                                if (isset($_GET['cat'])) {
                                    mysql_safe("UPDATE category
                                                    SET percent = ?,round = ? 
                                                    WHERE id = ?",
                                                    array($_GET['percent'], $_GET['round'], $_GET['cat']));
                                    mysql_safe("UPDATE subcat
                                                    SET percent = ?,round = ? 
                                                    WHERE id_cat = ?",
                                                    array($_GET['percent'], $_GET['round'], $_GET['cat']));
                                }
                            }
                        }
                        if (isset($_GET['cat'])) {
                            if (isset($_GET['sub'])) {
                                if (isset($_GET['item'])) { //one item with the picture and description
                                    
                                } else { // selected subcategory - items list
                                    $res = mysql_safe("SELECT * FROM category");
                                
                                    while ($row = mysql_fetch_array($res)) {
                                       echo "<a href='adm.php?login=".$_GET['login']."&pass=".$_GET['pass']."&cat=".$row['id']."' class='menuitem'>".$row['name']."</a><br>";
                                       if ($row['id'] == $_GET['cat']) {
                                           $cat_id = $row['id'];
                                           $cat_name = $row['name'];
                                           $cat_percent = $row['percent'];
                                           $cat_round = $row['round'];
                                           $res1 = mysql_safe("SELECT * FROM subcat WHERE id_cat = ?", array($row['id']));
                                           while ($row1 = mysql_fetch_array($res1)) {
                                               if ($row1['id'] == $_GET['sub']) {
                                                   $sub_id = $row1['id'];
                                                   $sub_name = $row1['name'];
                                                   $sub_percent = $row1['percent'];
                                                   $sub_round = $row1['round'];        
                                               }
                                               echo "<a href='adm.php?login=".$_GET['login']."&pass=".$_GET['pass']."&cat=".$row['id']."&sub=".$row1['id']."' class='submenu'>".$row1['name']."</a><br>";
                                           }
                                       }
                                    }      
                                    echo "</div>";   
                                    echo "<div id='rightcolumn'>";
                                    echo "<table width=100%><tr><td bgcolor='#ffffff'>";
                                    echo "<h3><a href='adm.php?login=".$_GET['login']."&pass=".$_GET['pass']."&cat=".$cat_id."'>".$cat_name."</a> > ".$sub_name."</h3>";
                                    echo "</td><td align='right'>";
                                    echo "<form action='adm.php' name='caption' method='GET'>";
                                    echo "%: <input type='number' name='percent' value='".$sub_percent."' size=5>";
                                    echo "</td><td align='right'>";
                                    echo "round: <input type='number' name='round' value='".$sub_round."' size=5>";
                                    echo "<input type='hidden' name='login' value='".$_GET['login']."'>";
                                    echo "<input type='hidden' name='pass' value='".$_GET['pass']."'>";
                                    echo "<input type='hidden' name='cat' value='".$cat_id."'>";
                                    echo "<input type='hidden' name='sub' value='".$sub_id."'>";
                                    echo "<input type='hidden' name='chsub' value='".$sub_id."'>";
                                    echo "<input type='hidden' name='action' value='change'>";
                                    echo "</td><td align='right'>";
                                    echo "<input type='submit' value=' change '>";
                                    echo "</form>";
                                    echo "</td></tr></table><br>";
                                    echo "<table class='price' width=100%>";
                                    echo "<th align='left'>Item</th><th>Buy Price</th><th>Sell Price</th><th>Profit</th>";
                                    $res1 = mysql_safe("SELECT * FROM items WHERE id_subcat = ?", array($sub_id));
                                    $counter = 0;
                                    while ($row1 = mysql_fetch_array($res1)) {
                                        if ($counter++ == 1) {
                                            echo "<tr bgcolor='#e8e8ff' class='price'>";
                                            $counter = 0;
                                        } else {
                                            echo "<tr bgcolor='#f2f2ff' class='price'>";
                                        }

                                        echo "<td>";
                                        echo $row1['name'];
                                        echo "</td>";
                                        echo "<td align='right' width='70px'>";
                                        echo number_format($row1['price'], 2, '.', ' ');;
                                        echo "</td>";
                                        echo "<td align='right' width='70px'><b>";
                                        $sell = $row1['price'] * (1 + $sub_percent/100);
                                        if ($sub_round > 0) {
                                            $sell = ceil($sell / $sub_round)*$sub_round;
                                        }
                                        echo number_format($sell, 2, '.', ' ');
                                        echo "</b></td>";
                                        echo "<td align='right' width='70px'>";
                                        echo number_format($sell-$row1['price'], 2, '.', ' ');;
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                    echo "</table>";
                                    echo "</div>";
                                }
                            } else { // selected category - subcategory list
                                $res = mysql_safe("SELECT * FROM category");
                                
                                while ($row = mysql_fetch_array($res)) {
                                   echo "<a href='adm.php?login=".$_GET['login']."&pass=".$_GET['pass']."&cat=".$row['id']."' class='menuitem'>".$row['name']."</a><br>";
                                   if ($row['id'] == $_GET['cat']) {
                                       $cat_id = $row['id'];
                                       $cat_name = $row['name'];
                                       $cat_percent = $row['percent'];
                                       $cat_round = $row['round'];
                                       $res1 = mysql_safe("SELECT * FROM subcat WHERE id_cat = ?", array($row['id']));
                                       while ($row1 = mysql_fetch_array($res1)) {
                                           echo "<a href='adm.php?login=".$_GET['login']."&pass=".$_GET['pass']."&cat=".$row['id']."&sub=".$row1['id']."' class='submenu'>".$row1['name']."</a><br>";
                                       }
                                   }
                                }      
                                mysql_data_seek($res1, 0);
                                echo "</div>";
                                echo "<div id='rightcolumn'>";
                                echo "<table width=100%><tr><td bgcolor='#ffffff'>";
                                echo "<h3>".$cat_name."</h3>";
                                echo "</td><td align='right'>";
                                echo "<form action='adm.php' name='caption' method='GET'>";
                                echo "%: <input type='number' name='percent' value='".$cat_percent."' size=5>";
                                echo "</td><td align='right'>";
                                echo "round: <input type='number' name='round' value='".$cat_round."' size=5>";
                                echo "<input type='hidden' name='login' value='".$_GET['login']."'>";
                                echo "<input type='hidden' name='pass' value='".$_GET['pass']."'>";
                                echo "<input type='hidden' name='cat' value='".$cat_id."'>";
                                echo "<input type='hidden' name='chsub' value='".$sub_id."'>";
                                echo "<input type='hidden' name='action' value='change'>";
                                echo "</td><td align='right'>";
                                echo "<input type='submit' value=' change '>";
                                echo "</td><td align='right'>";
                                echo "<input type='button' value=' change all ' onclick='javascript:ChAll();'>";
                                echo "</form>";
                                echo "</td></tr></table><br>";
                                echo "<table class='price' width=100%>";
                                echo "<th align='left'>Subcategory</th><th>%</th><th>round</th>";
                                $counter = 0;
                                while ($row1 = mysql_fetch_array($res1)) {
                                    if ($counter++ == 1) {
                                        echo "<tr bgcolor='#e8e8ff' class='price'>";
                                        $counter = 0;
                                    } else {
                                        echo "<tr bgcolor='#f2f2ff' class='price'>";
                                    }
                     
                                    echo "<td width=95%>";
                                    echo "<a href='adm.php?login=".$_GET['login']."&pass=".$_GET['pass']."&cat=".$cat_id."&sub=".$row1['id']."' class='menuitem'>".$row1['name']."</a>";
                                    echo "</td>";
                                    echo "<td>";
                                    echo "<form action='adm.php' method='GET'>";
                                    echo "<input type='hidden' name='login' value='".$_GET['login']."'>";
                                    echo "<input type='hidden' name='pass' value='".$_GET['pass']."'>";
                                    echo "<input type='hidden' name='cat' value='".$cat_id."'>";
                                    echo "<input type='hidden' name='chsub' value='".$row1['id']."'>";
                                    echo "<input type='hidden' name='action' value='change'>";
                                    echo "<input type='number' name='percent' value='".$row1['percent']."' size=5>";
                                    echo "</td>";
                                    echo "<td>";
                                    echo "<input type='number' name='round' value='".$row1['round']."' size=5>";
                                    echo "</td>";
                                    echo "<td>";
                                    echo "<input type='submit' value='change'>";
                                    echo "</form>";
                                    echo "</tr>";
                                }
                                echo "</table>";
                               
                                echo "</div>"; 
                            }
                        } else { // category list
                            $res = mysql_safe("SELECT * FROM category");
                            while ($row = mysql_fetch_array($res)) {
                                echo "<a href='adm.php?login=".$_GET['login']."&pass=".$_GET['pass']."&cat=".$row['id']."' class='menuitem'>".$row['name']."</a><br>";
                            }
                            echo "</div>";
                            echo "<div id='rightcolumn'>";
                            echo "Choose category for change";
                            echo "</div>";
                        }
                    } else {
                        show_form();
                        echo "</div>";
                        echo "<div id='rightcolumn'>";
                        echo "Incorrect login-pass pair, try again";
                        echo "</div>";
                    }
                } else {
                    show_form();
                    echo "</div>";
                    echo "<div id='rightcolumn'>";
                    echo "Incorrect login-pass pair, try again";
                    echo "</div>";
                }
            } else {
                show_form();
                echo "</div>";
                echo "<div id='rightcolumn'>";
                echo "Please, enter autorization data to the form in the left column.";
                echo "</div>";
            }
        ?>
   </div>
</body></html>

