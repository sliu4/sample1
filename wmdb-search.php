  <!-- Sheree Liu
       Assignment 4
       Fall 2016
       wmdb-search.php -->

  <html>
  <head>
  <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css">
  <link href="https://fonts.googleapis.com/css?family=Yanone+Kaffeesatz:300" rel="stylesheet">
  <style>
    h1 {
      color: #ffffff;
      font-family: 'Montserrat';
      font-size: 450%;
      text-align: center;
      margin: 50px 10px 50px 10px;
    }

    h2 {
      color: lightblue;
    }

    body {
      color: #ffffff;
      font-family: 'Yanone Kaffeesatz';
      background: url("http://wallpapercave.com/wp/WIzDZz0.jpg");
      background-size: cover;
      background-repeat: no-repeat;
      text-align: center;
    }

    a:link {
      color: #ffffff;
    }

    a:visited {
      color: #4baac8;
    }

    form {
      display: inline-block;
      text-align: center;
      width: 100%;
      margin: 0 auto;
    }
    
    p,a {
      font-size: 110%;
    }

    h3 {
      font-size:120%;
    }


  </style>
  <title>WMDB Search Results</title>
  </head>
  <body>
  <p>
                  <h1>Search the WMDB</h1>

                  <form method="get" action="wmdb-search.php">
                      <select name="tables">
                          <option>both
                          <option>titles
                          <option>names
                      </select>
                      <br>
                      <input type="text" name="sought"> <br>
                      <input type="submit" value="GO!">
                  </form>

	  <h2>Search Results</h2>

  <?php
  require_once("/home/cs304/public_html/php/DB-functions.php"); //contains helper functions for connecting to the database
  require_once("sliu4-dsn.inc");

  $dbh = db_connect($sliu4_dsn);
  $self_url = $_SERVER['PHP_SELF'];
  $imdb_url = "http://www.imdb.com/";

  if (isset($_REQUEST['nm'])) {
     //display the info of the one name if it is valid
     display_nm($_REQUEST['nm'],$dbh,$imdb_url,$self_url);
  } else if (isset($_REQUEST['tt'])) {
     //display the info of the one title if it is valid
     display_tt($_REQUEST['tt'],$dbh,$imdb_url,$self_url);
  } else {
  if (isset($_REQUEST['tables'])) {
    $dropdown = $_REQUEST['tables'];

    if (isset($_REQUEST['sought'])) {
      $result_link = $_REQUEST['sought'];

      if ($result_link != "") {
        if ($dropdown == 'both') {

          //for the person query
          person_query($result_link,$dbh,$imdb_url,$self_url);
          //for the movie query
          movie_query($result_link,$dbh,$imdb_url,$self_url);

        } else if ($dropdown == 'titles') {
          movie_query($result_link,$dbh,$imdb_url,$self_url);

        } else {
          person_query($result_link,$dbh,$imdb_url,$self_url);
        }
      }
    }
  }
}

  function person_query($result_link,$dbh,$imdb_url,$self_url) {
    //queries person table with information given through form and displays results
    $sql_person = "SELECT nm,name,birthdate FROM person WHERE name LIKE CONCAT('%',?,'%')";
    $result_set = prepared_query($dbh,$sql_person,array($result_link));
    $person_count = count($result_set->fetchAll());
    echo "<h3>$person_count Names Matched</h3>";
      if ($person_count == 0) {
        echo "<p>Sorry, no names match $result_link</p>";
      } else if ($person_count == 1) {
        $result_set = prepared_query($dbh,$sql_person,array($result_link));
        $row = $result_set->fetchRow(MDB2_FETCHMODE_ASSOC);
        $nm = $row['nm'];
        display_nm($nm,$dbh,$imdb_url,$self_url);
      } else {
        $result_set = prepared_query($dbh,$sql_person,array($result_link));
        while ($row = $result_set->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $nm = $row['nm'];
            $name = $row['name'];
            $birthdate = $row['birthdate'];
            echo "<a href=\"$self_url?nm=$nm\">$name ($birthdate)</a><br>";
          }
      }
  }

  function movie_query($result_link,$dbh,$imdb_url,$self_url) {
    //queries movie table with information given through form and displays results
    $sql_movie = "SELECT tt,title,`release` FROM movie WHERE title LIKE CONCAT('%',?,'%')";
    $result_set = prepared_query($dbh,$sql_movie,array($result_link));
    $movie_count = count($result_set->fetchAll());
    echo "<h3>$movie_count Movies Matched</h3>";
      if ($movie_count == 0) {
        echo "<p>Sorry, no movies match $result_link</p>";
      } else if ($movie_count == 1) {
        $result_set = prepared_query($dbh,$sql_movie,array($result_link));
        $row = $result_set->fetchRow(MDB2_FETCHMODE_ASSOC);
        $tt = $row['tt'];
        display_tt($tt,$dbh,$imdb_url,$self_url);
      } else {
         $result_set = prepared_query($dbh,$sql_movie,array($result_link));
         while($row = $result_set->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $tt = $row['tt'];
            $title = $row['title'];
            $release = $row['release'];
            echo "<a href=\"$self_url?tt=$tt\">$title ($release)</a><br>";
          }
      }
  }

  function display_nm($nm,$dbh,$imdb_url,$self_url) {
      // displays additional information of a given nm
      $sql_person = "SELECT name,birthdate from person where nm=?";
      $result_person = prepared_query($dbh,$sql_person,array($nm));
      $row_person = $result_person->fetchRow(MDB2_FETCHMODE_ASSOC);

          echo "<h3>". $row_person['name'] ."</h3>";
          echo "<p>born on " . $row_person['birthdate'] ."</p>";
          echo "<p>Filmography:</p>";
          $sql_movies = "SELECT title,`release`,movie.tt FROM person,credit,movie WHERE person.nm=credit.nm and person.nm=? and credit.tt=movie.tt";
          $result_movies = prepared_query($dbh,$sql_movies,array($nm));
          while($row_movies = $result_movies->fetchRow(MDB2_FETCHMODE_ASSOC)) {
              $tt = $row_movies['tt'];
              $title = $row_movies['title'];
              $release = $row_movies['release'];
              echo "<p><a href=\"$self_url?tt=$tt\">$title ($release)</a><br></p>";
          }
          echo "<p>Here's the real <a href=\"$imdb_url" . "name/nm$nm\">IMDb entry for " . $row_person['name'] ."</a></p>";
   }


  function display_tt($tt,$dbh,$imdb_url,$self_url) {
      // displays additional information of a given nm
      $sql_movie = "SELECT title,`release`,director FROM movie WHERE tt=?";
      $result_movie = prepared_query($dbh,$sql_movie,array($tt));
      $row_movie = $result_movie->fetchRow(MDB2_FETCHMODE_ASSOC);
          echo "<h3>". $row_movie['title'] ." (" . ($row_movie['release']) . ")" ."</h3>";
          if ($row_movie['director'] != NULL) {
              $sql_director = "SELECT name FROM person WHERE nm=?";
              $result_director = prepared_query($dbh,$sql_director,array($row_movie['director']));
              $row_director = $result_director->fetchRow(MDB2_FETCHMODE_ASSOC);
              echo "<p>directed by <a href=\"$imdb_url" . "name/nm" . $row_movie['director'] . "\">" . $row_director['name'] ."</a></p>";
          } else {
              echo "<p>director unknown</p>";
          }
          echo "<p>Cast:</p>";
          $sql_people = "SELECT person.nm,name FROM credit,person WHERE tt=? AND credit.nm=person.nm";
          $result_people = prepared_query($dbh,$sql_people,array($tt));
          while($row_people = $result_people->fetchRow(MDB2_FETCHMODE_ASSOC)) {
              $nm = $row_people['nm'];
              $name = $row_people['name'];
              echo "<p><a href=\"$self_url?nm=$nm\">$name</a><br></p>";
          }
          echo "<p>Here's the real <a href=\"$imdb_url" . "title/tt$tt\">IMDb entry for " . $row_movie['title'] ."</a></p>";
  }
  ?>

  </body>
  </html>
