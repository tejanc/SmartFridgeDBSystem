<?php

session_start();
// Check if the login button was clicked and the login value is in the POST array object

//retrieve the users student number and password from the login form
//GET DB CONNECTION STRING 

$conn_string= "host=localhost port=5432 dbname=smartfridgedb user=postgres password=csi2132";

$dbconn = pg_connect($conn_string) or die ("Connection failed");
$query= "set search_path = 'smartfridge'";

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
  echo "<form method='post' action='html/user.php?runFunction=orderMeal'>";
  echo "<table style='width:100%'>";
  echo "<tr style='font-weight:bold'><td>MealID</td>" . "<td>Name</td>" . "<td>Description</td>" . "<td>Cuisine</td>" . "<td>Select</td>" . "</tr>";
  while ($row = pg_fetch_row($result)) {
    echo "<tr><td>" . "$row[0]" . "</td><td>" . "$row[2]" . "</td><td>" . "$row[3]" . "</td><td>" . "$row[4]" . "</td>";
    echo "<td>" . "<input type='radio' name='meal_radio' value='$row[0]'>" . "</td>";
    echo "</tr>";
  }
  echo "</table>";
  echo "<input id='submit' name='submit' type='submit' value='Send' class='btn btn-primary' onclick='ordTest()'>";
  echo "</form>";
}

function orderMeal() {
  if (isset($_POST['meal_radio'])) { 
    $selected_meal = $_POST['meal_radio'];
    echo "Selected: " . $selected_meal;
  }
}
?>

