<?php

session_start();

$conn_string= "host=localhost port=5432 dbname=postgres user=postgres password=postgres";

$dbconn = pg_connect($conn_string) or die ("Connection failed");
$query= "set search_path = 'public'";

$runFunction= 

if(isset($_GET['runFunction']) && function_exists($_GET['runFunction']))
call_user_func($_GET['runFunction']);
else
echo "Function not found or wrong input";

function getMeals() {
  $query1 = "SELECT * FROM MEALS";

  $result = pg_query($GLOBALS['dbconn'], $query1);
  if (!$result) {
    echo "An error occurred.\n";
    exit;
  }

  while ($row = pg_fetch_row($result)) {
    echo "FoodId: $row[0]  Name: $row[2]";
    echo "<br />\n";
  }
}

?>

