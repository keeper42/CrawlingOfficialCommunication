<?php
/*
 * User: LJF
 * Date: 2017/3/25
 */
    header("Content-type: text/html; charset=gb2312");
    // Refresh webpage.
    header("refresh:600; url=remind.php");

    // Local database.
    $hostname = 'localhost';
    $username = 'root';
    $password = '654321';
    $database = 'lizhi';
    $link = mysqli_connect($hostname, $username, $password, $database);
    if (!$link) {
        die('Connect Error (' . $link->connect_errno . ')'
            . $link->connect_error);
    }
    mysqli_query($link, "set names 'gb2312'");

    // Use curl to get the landing webpage.
    $url = "http://www.szu.edu.cn/board/";
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $content = curl_exec($curl);
    curl_close($curl);

    // Use regular expressions to parse webpage.
    preg_match_all('/<td align="center">\d+<\/td>(.*)<td align="center" style="font-size: 9pt">.*<\/td>.*<\/tr>/iUs', $content, $table);
    foreach ($table[0] as $table) {
        preg_match_all('/<td align="center" style=".*">(.*)<\/td>/', $table, $date);
  		$time = date('Y-n-j');
  		if($time == $date[1][2]){
  			preg_match_all('/<a href="\?infotype=.*">(.*)<\/a>/', $table, $category);
  			preg_match_all('/<a href=# onclick=".*">(.*)<\/a>/', $table, $department);
  			preg_match_all('/<a.*>(.*)<\/a>/', $table, $title);
  			$titletxt = strip_tags($title[0][2]);
  			preg_match_all('/<a target=_blank href="(.*)".*<\/a>/', $table, $detail);
  			$vurl = "$url"."{$detail[1][0]}";

 			// Check whether the form has this title.
 			echo $titletxt;
  			$search = "SELECT * FROM `remind` ";
  			$result = mysqli_query($link, $search);
  			$rownum = mysqli_num_rows($result);
  			$flag = 0;
  			for($i = 0; $i < $rownum; $i++){
  				$row = mysqli_fetch_assoc($result);
  				if($row['title'] == $titletxt){
  					$flag = 1;
  					break;
  				}
  			}

  			// If the query is successful then insert the information into the form.
  			if($flag) {
  			    echo 'search success </br>';
  			}else{ 	
  				$query  = "INSERT IGNORE INTO `remind` (`category`,`department`,`title`,`date`,`url`) VALUES ('".$category[1][0]."','".$department[1][0]."','".$titletxt."','{$date[1][2]}','".$vurl."')";
  			    if(mysqli_query($link, $query)) {
  			        echo 'insert success';
  			    }else{
  			        echo mysqli_error($link);
  			    }
  			    
  			}
  			echo '</br>'; 	

  			// Set a reminder
  		
  		}	

    }

    mysqli_close($link);
    
?>