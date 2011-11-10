<?php
  include "condb.php";
  mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'", $db);
  
  $cur_ip = $_SERVER['REMOTE_ADDR'];
  $host = gethostbyaddr($cur_ip); 
  
  if (isset($_COOKIE['uid'])) {
    $user = $_COOKIE['uid'];
    if (md5($host) != $user) {
      //setcookie('uid', md5($host), time()+31556926);
      $user = $_COOKIE['uid'];
    }
  } else {
    $user = md5($host);
    setcookie('uid', md5($host), time()+31556926);
  }
  
  function get_cat($subcat) {
    $res1 = mysql_safe("SELECT id_cat FROM subcat WHERE id=?", array($subcat));
    if(mysql_num_rows($res1) > 0) {
      $row1 = mysql_fetch_array($res1);
      return $row1['id_cat'];
    } else {
      return 0;
    }
  }

  
  // this block adds item to user's cart
  if (isset($_GET['action'])) {
    if ($_GET['action'] == 'add') {
      if (isset($_GET['cat'])&&isset($_GET['sub'])&&isset($_GET['item'])) {
        $res = mysql_safe("SELECT * FROM items WHERE (id = ?) AND (id_subcat = ?)",
                          array($_GET['item'],$_GET['sub']));
        if (mysql_num_rows($res) > 0) {
          $row = mysql_fetch_array($res);
          $res2 = mysql_safe("SELECT percent, round FROM subcat WHERE id = ?", array($_GET['sub']));
          $row2 = mysql_fetch_array($res2);
          $sell = 0;
          if ($row2['percent'] > 0) {
              $sell = $row['price'] * (1 + $row2['percent']/100);
          }
          if ($row2['round'] > 0) {
              $sell = ceil($sell / $row2['round'])*$row2['round'];
          }
          if ($sell > 0) {
            mysql_safe("INSERT INTO cart (user, cat, sub, tov, name, kolvo, price, sum)
                        VALUES (?,?,?,?,?,?,?,?)",
                        array($user, $_GET['cat'], $_GET['sub'], $_GET['item'], $row['name'],
                        1, $sell, $sell));  
          }
        }
      }
    }
  }
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Вам Товары</title>
<link rel="stylesheet" type="text/css" href="main.css">
</head>

<body>

   <!-- Begin Wrapper -->
   <div id="wrapper">
   
         <!-- Begin Header -->
  <div id="header">
    <img src='ani.gif' id="ani">		 
  </div>
	 
  <div id="navigation">
		 
    <a href="index.php"> Каталог </a>
    <a href="cart.php"> Заказы </a>
			   
  </div>

  <div id="cart">
    <?php
    $res = mysql_safe("SELECT user, SUM(kolvo) as k, SUM(sum) as s FROM cart WHERE user = ? GROUP BY user", array($user));
    $row = mysql_fetch_array($res);
    if ($row['k'] == 0) {
      echo "Ваша корзина пуста.";
    } else {
      echo "<a href='cart.php'>";
      echo "В корзине ".$row['k']." ".right_case($row['k'],"товар", "товара", "товаров")
          ." на сумму ".number_format($row['s'], 2, '.', ' ')." руб.";
      echo "</a>";
      
    }
    ?>
		   
  </div>
         
	<div id="leftcolumn">
<?php
  if (isset($_GET['cat'])) { //need to advance selected category
    //firstly lets check the existance of given category
    $cat_id = $_GET['cat'];
    $res = mysql_safe("SELECT * FROM category WHERE id = ?", array($cat_id));
    if(mysql_num_rows($res) > 0) { // Yes! We have found the same category!
      $res = mysql_safe("SELECT * FROM category");
      while($row = mysql_fetch_array($res)) {
        echo "&bull; <a href='index.php?cat=".$row['id']."' class='menuitem'>".$row['name']."</a><br>";
        if($row['id'] == $cat_id) { // advance this category
          $res1 = mysql_safe("SELECT * FROM subcat WHERE id_cat = ?", array($cat_id));
          while($row1 = mysql_fetch_array($res1)) {
            echo "<a href='index.php?cat=".$cat_id."&sub=".$row1['id']."' class='submenu'>".$row1['name']."</a><br>";
          }
        }
      }
    } else { //No :( User tried to cheat or something goes wrong 
      // simply show him categories list without any subcategories
      $res = mysql_safe("SELECT * FROM category");
      while($row = mysql_fetch_array($res)) {
        echo "&bull; <a href='index.php?cat=".$row['id']."' class='menuitem'>".$row['name']."</a><br>";
      }
    }
  } else { // only categories list
    $res = mysql_safe("SELECT * FROM category");
    while($row = mysql_fetch_array($res)) {
      echo "&bull; <a href='index.php?cat=".$row['id']."' class='menuitem'>".$row['name']."</a><br>";
      
    }
  }
?>
		 </div>
		 <!-- End Left Column -->
		 
		 <!-- Begin Right Column -->
		 <div id="rightcolumn">
<?php
  if (isset($_GET['cat'])) {
    if (isset($_GET['sub'])) {
      if (isset($_GET['item'])) { // need to show item with description
        $item_id = $_GET['item'];
        $res = mysql_safe("SELECT id, id_subcat, name, price FROM items WHERE id=?", array($item_id));
        if(mysql_num_rows($res) > 0) { // everything is Ok, item exists
          $row = mysql_fetch_array($res);
          echo "<p id='itemtitle'>".$row['name']."</p>";
          echo "<table width = 100%><tr>";
          $cur_cat = get_cat($row['id_subcat']);
          $cur_sub = $row['id_subcat'];
          $cur_item = $row['id'];
          $cur_name = $row['name'];
          mysql_query("INSERT INTO hits (`subcat`,`item`,`host`,`ip`) VALUES('$cur_sub','$cur_item','$host','$cur_ip')");  
          $res1 = mysql_safe("SELECT id FROM items WHERE (id_subcat=?) AND (name=(SELECT MAX(name) FROM items WHERE (name<?) AND (id_subcat=?)))", 
                            array($cur_sub, $cur_name, $cur_sub));
          if(mysql_num_rows($res1) > 0) {
            $row1 = mysql_fetch_array($res1);
            echo "<td align='left' width=30%><a href='index.php?cat=".$cur_cat."&sub=".$cur_sub."&item=".$row1['id']."' class='pointer'>&lt;&lt;&lt; предыдущий товар</a></td>";
          } else {
            echo "<td align='left' width=30%> &nbsp; </td>";
          };
          $res2 = mysql_safe("SELECT percent, round FROM subcat WHERE id = ?", array($cur_sub));
          $row2 = mysql_fetch_array($res2);
          $sell = 0;
          if ($row2['percent'] > 0) {
              $sell = $row['price'] * (1 + $row2['percent']/100);
          }
          if ($row2['round'] > 0) {
              $sell = ceil($sell / $row2['round'])*$row2['round'];
          }
          if ($sell > 0) {
              echo "<td align='center' id='cena'>".number_format($sell, 2, '.', ' ')." руб. ";
              echo "<form action='index.php' method='GET'>";
              echo "<input type='hidden' name='cat' value='".$cur_cat."'>";
              echo "<input type='hidden' name='sub' value='".$cur_sub."'>";
              echo "<input type='hidden' name='item' value='".$cur_item."'>";
              echo "<input type='hidden' name='action' value='add'>";
              echo "<input type='submit' value=' В корзину '>";
              echo "</form>";
              echo "</td>";
          } else {
              echo "<td align='center' id='cena'>"."-.--"." руб. "."</td>";
          }
          //echo "<td align='center' id='cena'>".$row['price']." руб. "."</td>";
          $res1 = mysql_safe("SELECT id FROM items WHERE (id_subcat=?) AND (name=(SELECT MIN(name) FROM items WHERE (name>?) AND (id_subcat=?)))", 
                            array($cur_sub, $cur_name, $cur_sub,));
          if(mysql_num_rows($res1) > 0) {
            $row1 = mysql_fetch_array($res1);
            echo "<td align='right' width=30%><a href='index.php?cat=".$cur_cat."&sub=".$cur_sub."&item=".$row1['id']."' class='pointer'>следующий товар&gt;&gt;&gt;</a></td>";
          } else {
            echo "<td align='right' id='pointer' width=30%> &nbsp; </td>";
          };
          echo "</tr></table>";
          echo "<table width=100% id='photo'><tr><td align='center'>";
          if(file_exists("pics/".$item_id.".jpg")) {
            echo "<p align='center'><br><img src='pics/".$item_id.".jpg'><br><br></p>";
          } else {
            echo "<br><br><br> нет картинки <br><br><br><br>";
          };
          echo "</td></tr></table>";
          $res1 = mysql_safe("SELECT description FROM descr WHERE id=?", array($item_id));
          if(mysql_num_rows($res1) > 0) {
            $row1 = mysql_fetch_array($res1);
            echo "<br>".$row1['description'];
          }
        } else { // no such item in database, sorry
          echo "No such item in our database";
        }
      } else { // list of items in subcategory
        $sub_id = $_GET['sub'];
        $res = mysql_safe("SELECT id, id_subcat, name, price FROM items WHERE id_subcat=?", array($sub_id));
        if(mysql_num_rows($res) > 0) {
          $res2 = mysql_safe("SELECT name FROM subcat WHERE id = ?", array($sub_id));
          $row2 = mysql_fetch_array($res2);
          echo "<h3>".$row2['name']."</h3>";
          echo "<form method='GET' action='add.php'><table width=100% class='price'>";
          echo "<tr bgcolor='#ccccff' height=25 valign='middle'><th>Наименование</th><th width = 70>Цена</th><th width=70>Заказ</th></tr>";
          $counter = 0;
          while($row = mysql_fetch_array($res)) {
            $res1 = mysql_safe("SELECT id_cat, percent, round FROM subcat WHERE id=?", array($sub_id));
            if(mysql_num_rows($res1) > 0) {
              $row1 = mysql_fetch_array($res1);
              $cat_id = $row1['id_cat'];
              if($counter++ == 1) {
                echo "<tr bgcolor='#e8e8ff' class='price'><td>";
                $counter = 0;
              } else {
                echo "<tr bgcolor='#f2f2ff' class='price'><td>";
              }
              echo "<a href='index.php?cat=".$cat_id."&sub=".$row['id_subcat']."&item=".$row['id']."' class='item'>".$row['name']."</a>";
              echo "</td><td align='right' class='price'>";
              $sell = 0;
              if ($row1['percent'] > 0) {
                  $sell = $row['price'] * (1 + $row1['percent']/100);
              }
              if ($row1['round'] > 0) {
                  $sell = ceil($sell / $row1['round'])*$row1['round'];
              }
              if ($sell > 0) {
                  echo number_format($sell, 2, '.', ' ');
              } else {
                  echo "-.--";
              }
              //echo number_format($row['price'], 2, '.', ' ');
              echo "</td><td align='center'>";
              echo "<input type='text' name='qty[]' maxlength = 5 size = 3>";
              echo "<input type='hidden' name='num[]' value=".$row['id'].">";
              echo "</td></tr>";
            }
          };
          echo "</table></form>";
        } else {
          echo("Wrong subcategory have been chosen - try another one <a href='index.php?cat=".$_GET['cat']."'>here</a>");
        }
      }
    } else { //only category has been specified - need to show smth
      $res = mysql_safe("SELECT category.id, category.name
                         FROM category
                         WHERE id = ?", array($_GET['cat']));
      if (mysql_num_rows($res)>0) {
        $row = mysql_fetch_array($res);
        echo "<span id='groupname'>".$row['name']."</span><br>";
        $res1 = mysql_safe("SELECT id, id_cat, name
                         FROM subcat
                         WHERE id_cat = ?", array($row['id']));
        echo "<table width=100% cellspacing=10px class='nowrap'>";
        $col = 5;
        while($row_sub = mysql_fetch_array($res1)) {
          if($col > 1) {
            if($col < 5) {
              echo "</td></tr>";
            }
            $col = 0;
            echo "<tr valign='top'><td width=300px>";
          } else {
            echo "</td><td width=300px>";
          }
          echo "<a href='index.php?cat=".$row_sub['id_cat']."&sub=".$row_sub['id']."' class='menuitem'>".$row_sub['name']."</a><br>";
          $res2 = mysql_safe("SELECT id, id_subcat, name
                              FROM items
                              WHERE (id_subcat = ?)", array($row_sub['id']));
          $counter = 0;
          $overall = mysql_num_rows($res2);
          while(($row_item = mysql_fetch_array($res2)) AND ($counter++ < 3)){
            echo "<a href='index.php?cat=".$row['id']
                                  ."&sub=".$row_sub['id']
                                  ."&item=".$row_item['id']."' class='submenu_nomargin'>"
                                  .$row_item['name']."</a><br>";
                                  //.mb_substr($row_item['name'], 0, 50,'utf-8')."</a><br>";
          }
          if($overall>3){
            echo "<p align='right'><a href='index.php?cat=".$row['id']
                                  ."&sub=".$row_sub['id']."' class='submenu'>"
                                  .right_case($overall, "весь ", "все ", "все ").$overall
                                  .right_case($overall, " товар", " товара", " товаров")
                                  ." группы >></a></p>";
          }
          $col++;
        }
        echo "</table>";
      }
    }
  } else {
    $res = mysql_safe("SELECT category.id, category.name, subcat.id as sid, subcat.name as sname FROM category, subcat WHERE category.id=subcat.id_cat ORDER BY category.name, subcat.name");
    echo "<table><tr valign='top'><td>";
    $cur_cat = "";
    $num = mysql_num_rows($res);
    $k = 0;
    while($row = mysql_fetch_array($res)) {
      if($k++ > $num/3) {
       echo "</td><td>";
       $k = 0; 
      }
      if($cur_cat != $row['id']) {
        echo "<a href='index.php?cat=".$row['id']."' class='menuitem'>".$row['name']."</a><br>";
        $cur_cat = $row['id'];
      }
      echo "<a href='index.php?cat=".$row['id']."&sub=".$row['sid']."' class='submenu'>".$row['sname']."<br>";
    }
    echo "</td></tr></table>";
  }

?>
		 <!-- End Right Column -->
		 
   </div>
   <!-- End Wrapper -->
   

   </div>
</body></html>