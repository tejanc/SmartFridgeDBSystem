<?php

session_start();
// Check if the login button was clicked and the login value is in the POST array object

//retrieve the users student number and password from the login form
//GET DB CONNECTION STRING 

$conn_string= "host=web0.site.uottawa.ca port=15432 dbname=xxxxxxx user=xxxxxx password=xxxxxxx";

//CONNECT TO DB

$dbconn = pg_connect($conn_string) or die ("Connection failed");
echo "GG this is a good day";
//QUERY DATABASE TO SEE IF USER EXIST 
//USE PARAMETERS TO AVOID SQL INJECTION 
$query= "set search_path = 'DBProject'";
$query1 = "SELECT * FROM food";
//$query = 'SELECT * FROM "DBProject.food";';

$result = pg_query($dbconn, $query1);
if (!$result) {
  echo "An error occurred.\n";
  exit;
}

while ($row = pg_fetch_row($result)) {
  echo "FoodId: $row[0]  Name: $row[1]";
  echo "<br />\n";
}
 
?>