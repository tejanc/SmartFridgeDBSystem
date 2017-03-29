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
  echo "<form target = 'meal_order_sent_frame' method='post' action='html/user/user.php?runFunction=orderMeal'>";
  echo "<table style='width:100%'>";
  echo "<tr style='font-weight:bold'><td>MealID</td>" . "<td>Name</td>" . "<td>Description</td>" . "<td>Cuisine</td>" . "<td>Select</td>" . "</tr>";
  while ($row = pg_fetch_row($result)) {
    echo "<tr><td>" . "$row[0]" . "</td><td>" . "$row[2]" . "</td><td>" . "$row[3]" . "</td><td>" . "$row[4]" . "</td>";
    echo "<td>" . "<input type='radio' name='meal_radio' value='$row[0],$row[1],$row[2]'>" . "</td>";
    echo "</tr>";
  }
  echo "</table>";
  echo "<br><iframe name='meal_order_sent_frame' height='35' width='1000' scrolling='no' src='html/user/order-meal-iframe-default.html'></iframe>";
  echo "<br><a class = 'btn btn-primary text' id='backbtn' onclick='back()''>Back</a> &nbsp;";
  echo "<input id='submit' name='submit' type='submit' value='Order Meal' class='btn btn-primary'><br><br>";
  echo "</div>";
  echo "</form>";
}

function orderMeal() {
  if (isset($_POST['meal_radio'])) { 

    // Get the values from the selected radio button
    $selected_meal = $_POST['meal_radio'];

    // Split up the above to get each individual value in an array
    $meal_order_fields = explode(',',$selected_meal);

    // Make strings for SQL query 
    $meal_id_str = (string) $meal_order_fields[0];
    $chef_id_str = (string) $meal_order_fields[1];
    $meal_name_str = (string) $meal_order_fields[2];

    // Query to order to CHEF_ORDER table
    $meal_order_query = "INSERT INTO CHEF_ORDER(meal_id, chef_id, approved) VALUES(" . $meal_id_str . "," . $chef_id_str . ", false);";

    // Make the query
    $result = pg_query($GLOBALS['dbconn'], $meal_order_query);
    if (!$result) {
      echo "An error occurred.\n";
      exit;
    } else {
      echo "<div class='div-center'>";
      echo "Order successfully placed for: " . $meal_name_str . "!";
      echo "</div>";
    }
  } else {
    echo "<div class='div-center'>";
    echo "Please select a meal to order first!";
    echo "</div>";
  }
}
?>

<style>
  .div-center {
      text-align:center;
  }
</style>
