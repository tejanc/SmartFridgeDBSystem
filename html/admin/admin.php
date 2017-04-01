<?php
session_start();
// Check if the login button was clicked and the login value is in the POST array object

//retrieve the users student number and password from the login form
$conn_string= "host=localhost port=5432  dbname=smartfridgedb user=postgres password=csi2132";

//GET DB CONNECTION STRING 
$dbconn = pg_connect($conn_string) or die ("Connection failed");
$query= "set search_path = 'public'";
if(isset($_GET['runFunction']) && function_exists($_GET['runFunction']))
  call_user_func($_GET['runFunction']);
else
  echo "Function not found or wrong input"; 


function getFridgeRequest()
{
  $query1 = "SELECT * FROM fridge_order where approved='f'";

  $result = pg_query($GLOBALS['dbconn'], $query1);
  if (!$result) {
    echo "An error occurred.\n";
    exit;
  } else {
    echo "<br>";
    echo "<form target = 'order_approved_frame' method='post' action='html/admin/admin.php?runFunction=approveOrders'>";
    echo "<table style='width:100%'>";
    echo "<tr style='font-weight:bold'><td>order_id</td>" . "<td>count</td>" . "<td>ing_id</td>" . "<td>chef_id</td>" . "<td>admin_id</td>" . "<td>approved</td>" . "<td>Accept?</td></tr>";
    while ($row = pg_fetch_row($result)) {
      echo "<tr><td>" . "$row[0]" . "</td><td>" . "$row[1]" . "</td><td>" . "$row[2]" . "</td><td>" . "$row[3]" . "</td><td>"."$row[4]"."</td><td>" . "$row[5]" . "</td>";
      echo "<td>" . "<input type='checkbox' name='order_checkbox[]' value='$row[0],$row[1],$row[2]'>" . "</td>";
      echo "</tr>";
    }
    echo "</table>";
    echo "<br><iframe name='order_approved_frame' height='200' width='1000' scrolling='yes' src='html/admin/order-approved-iframe-default.html'></iframe><br>";
    echo "<input id='submit' name='submit' type='submit' value='Approve Orders' class='btn btn-primary'><br><br>";
    echo "</form>";
  }
	
}

function approveOrders() {
    if (!isset($_POST['order_checkbox'])) {
        echo "You must select an order first!";
    } else {
        $selected_orders = $_POST['order_checkbox'];

        if (empty($selected_orders)) { 
            echo "You didn't select anything.";
        } else {
            $N = count($selected_orders);

            for ($i = 0; $i < $N; $i++) {
                // Get relevant attributes from checked boxes
                $selected_orders_fields = explode(',',$selected_orders[$i]);
                $selected_order_id = $selected_orders_fields[0];
                $selected_order_count = (int) $selected_orders_fields[1];
                $selected_order_ing_id = $selected_orders_fields[2];

                // Approve order 
                $order_query = "UPDATE FRIDGE_ORDER SET APPROVED = TRUE WHERE ORDER_ID = $selected_order_id;";
                $order_query_result = pg_query($GLOBALS['dbconn'], $order_query);
                if (!$order_query_result) {
                    echo "An error occurred.\n";
                }

                // Update ingredient count
                $update_ing_query = "UPDATE INGREDIENTS SET COUNT = COUNT + $selected_order_count WHERE ING_ID = '$selected_order_ing_id'";
                $update_ing_query_res = pg_query($GLOBALS['dbconn'], $update_ing_query);
                if (!$update_ing_query_res) {
                    echo "An error occurred.\n";
                }
                
                echo "Order $selected_order_id successfully approved!<br>";
            }
        }
    }
}

function Expense(){
	$query1 = "select* from expenses";
	$result = pg_query($GLOBALS['dbconn'], $query1);
 if (!$result) {
    echo "An error occurred.\n";
    exit;
  } else {
    echo "<br>";
    echo "<form target = 'meal_order_sent_frame' method='post' action='html/user/user.php?runFunction=orderMeal'>";
    echo "<table style='width:100%'>";
    echo "<tr style='font-weight:bold'><td>report_id</td>" . "<td>admin_id</td>" . "<td>ing_id</td>" . "<td>ingpricemoney</td></tr>";
    while ($row = pg_fetch_row($result)) {
      echo "<tr><td>" . "$row[0]" . "</td><td>" . "$row[1]" . "</td><td>" . "$row[2]" . "</td><td>" . "$row[3]" . "</td>";
      echo "</tr>";
    }
    echo "</table>";
    echo "</form>";
  }
}
function TopThree(){
	$query1 = "select* from top_three_ings";
	$result = pg_query($GLOBALS['dbconn'], $query1);
  if (!$result) {
    echo "An error occurred.\n";
    exit;
  } else {
    echo "<br>";
    echo "<form target = 'meal_order_sent_frame' method='post' action='html/user/user.php?runFunction=orderMeal'>";
    echo "<table style='width:100%'>";
    echo "<tr style='font-weight:bold'><td>report_id</td>" . "<td>admin_id</td>" . "<td>ing_id</td>" . "<td>rank</td></tr>";
    while ($row = pg_fetch_row($result)) {
      echo "<tr><td>" . "$row[0]" . "</td><td>" . "$row[1]" . "</td><td>" . "$row[2]" . "</td><td>" . "$row[3]" . "</td>";
      echo "</tr>";
    }
    echo "</table>";
    echo "</form>";
  }
}
function FreqRequested(){
	$query1= "select* from freq_req_meals";
	$result = pg_query($GLOBALS['dbconn'], $query1);
  if (!$result) {
    echo "An error occurred.\n";
    exit;
  } else {
    echo "<br>";
    echo "<form target = 'meal_order_sent_frame' method='post' action='html/user/user.php?runFunction=orderMeal'>";
    echo "<table style='width:100%'>";
    echo "<tr style='font-weight:bold'><td>report_id</td>" . "<td>admin_id</td>" . "<td>ing_id</td>" . "<td>count </td></tr>";
    while ($row = pg_fetch_row($result)) {
      echo "<tr><td>" . "$row[0]" . "</td><td>" . "$row[1]" . "</td><td>" . "$row[2]" . "</td><td>" . "$row[3]" . "</td>";
      echo "</tr>";
    }
    echo "</table>";
    echo "</form>";
  }
}
// Echoes form that allows user to select an ingredient by name
function getFoodByName() {
  echo "<br><form target = 'food_order_sent_frame' method='post' action='html/user/user.php?runFunction=searchFoodByName'>"; 
  echo "Ingredient Name: ";
  echo "<input type='text' name='food_input'>&nbsp;";
  echo "Quantity: ";
  echo "<input type='text' name='food_quantity'>&nbsp;<br>";
  echo "<br><iframe name='food_order_sent_frame' height='35' width='1000' scrolling='no' src='html/user/order-food-name-iframe-default.html'></iframe><br>";
  echo "<br><a class = 'btn btn-primary text' id='backbtn' onclick='back()''>Back</a> &nbsp;";
  echo "<input id='submit' name='submit' type='submit' value='Order Ingredient' class='btn btn-primary'><br><br>";
  echo "</form>";
}

// Makes a query to the DB to find an ingredient with the user-input name
// If food found and is available, places an order
function searchFoodByName() {
  $user_input_food = $_POST['food_input'];
  $user_input_quantity = $_POST['food_quantity'];

  $query1 = "SELECT * FROM INGREDIENTS WHERE Name = '" . $user_input_food . "';";

  $result = pg_query($GLOBALS['dbconn'], $query1);
  if (!$result) {
    echo "An error occurred.\n";
    exit;
  }
  
  $search_result = pg_fetch_row($result);

  if (!$search_result) {
    echo "<center>";
    echo "Error: food not found.";
    echo "</center>";
    exit;
  } else {
    $food_id = $search_result[0];
    $food_name = $search_result[1];
    $food_count = $search_result[4];

    if (!is_numeric($user_input_quantity)) {
      echo "<div class='div-center'>";
      echo "Error: please enter a quantity.";
      echo "</div>";
    } else if ($user_input_quantity > $food_count) {
      echo "<div class='div-center'>";
      echo "Error: Only $food_count left of ingredient $food_name!";
      echo "</div>";
    } else {
      $new_food_count = $food_count - $user_input_quantity;
      placeFoodOrder($food_id, $food_name, $new_food_count);
    }
  }    
}

// Echoes a form that allows the user to search for ingredient by category
function getFoodByCategory() {
  echo "<br><form name='fc' target = 'food_category_results' method='post' action='html/user/user.php?runFunction=searchFoodByCategory'>";
  echo "Category: ";
  echo "<input type='text' name='food_input'><br>";
  echo "<br><iframe name='food_category_results' height='400' scrolling='yes' style='border:none; border-radius: 0px;'></iframe><br>";
  echo "<br><a class = 'btn btn-primary text' id='backbtn' onclick='back()''>Back</a> &nbsp;";
  echo "<input id='submit' name='submit' type='submit' value='Search' class='btn btn-primary'><br><br>";
  echo "</form>";

}

// Makes a query to the DB for all foods with user-input category
// and displays in a table
function searchFoodByCategory() {
  $user_input_category = $_POST['food_input'];
  $query1 = "SELECT * FROM INGREDIENTS WHERE CATEGORY = '" . "$user_input_category" . "';";
  $result = pg_query($GLOBALS['dbconn'], $query1);
  if (!$result) {
    echo "Error: category does not exist.\n";
    exit;
  } else {
    echo "<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css' integrity='sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u' crossorigin='anonymous'>";
      echo "<br><iframe name='food_category_order' height='35' width='910' scrolling='yes' style='border:none; border-radius:0px;margin:0 auto;'></iframe><br>";
    echo "<style> table, th, td { border: 1px solid black; border-collapse: collapse; } </style>";
    echo "<form name = 'food_by_cat' target = 'food_category_results' method='post' action='user.php?runFunction=orderFoodByCategory'>";
    echo "<table style='width:100%'>";
    echo "<tr style='font-weight:bold'><td>Name</td>" . "<td>Expiry</td>" . "<td>Price ($)</td>" . "<td>Count</td>" . "<td>Category</td>" . "<td>Select</td>" . "</tr>";
    while ($row = pg_fetch_row($result)) {
      echo "<tr><td>" . "$row[1]" . "</td><td>" . "$row[2]" . "</td><td>" . "$row[3]" . "</td><td>" . "$row[4]" . "</td><td>" . "$row[5]" . "</td>";
      echo "<td>" . "<input type='radio' name='food_radio' value='$row[0],$row[1],$row[4]'>" . "</td>";
      echo "</tr>";
    }
    echo "</table>";
    echo "<br>Quantity: ";
    echo "<input type='text' name='food_quantity'><br>";
    echo "<div class='div-center'>";
    echo "<input id='submit' name='submit' type='submit' value='Order Ingredient' class='btn btn-primary'><br>";
    echo "</div>";
    echo "</div>";
    echo "</form>";
  }
}

// Places an order for the ingredient selected by the user
function orderFoodByCategory() {  
  echo "<div class='div-center' style='font-weight: bold;'>";
  $user_input_quantity = $_POST['food_quantity'];

  if (!is_numeric($user_input_quantity)) {
    echo "Error: please enter a quantity.";
  } else if (isset($_POST['food_radio'])) { 
    // Get the values from the selected radio button
    $selected_food = $_POST['food_radio'];

    // Split up the above to get each individual value in an array
    $food_order_fields = explode(',',$selected_food);

    // Make strings for SQL query 
    $food_id = $food_order_fields[0];
    $food_name = $food_order_fields[1];
    $food_count = $food_order_fields[2];

    if ($user_input_quantity > $food_count || $food_count == 0) {
      echo "Error: Only $food_count left of ingredient $food_name!";
    } else {
      $new_food_count = $food_count - $user_input_quantity;
      placeFoodOrder($food_id, $food_name, $new_food_count);
    }
  } else {
    echo "Please select a meal to order first!";
  }
  echo "</div>";
}

// Places an ingredient order by sending an UPDATE query to the db
function placeFoodOrder($food_id, $food_name, $new_food_count) {
  $query1 = "UPDATE INGREDIENTS SET COUNT = $new_food_count WHERE ING_ID = '" . "$food_id" . "';";

  $result = pg_query($GLOBALS['dbconn'], $query1);
  if (!$result) {
    echo "An error occurred.\n";
    exit;
  } else {
    echo "Successfully ordered $food_name. There is $new_food_count of this ingredient left.";
  }
}

// Displays the list of available meals in a table
function getMeals() {
  $query1 = "SELECT * FROM MEALS";

  $result = pg_query($GLOBALS['dbconn'], $query1);
  if (!$result) {
    echo "An error occurred.\n";
    exit;
  } else {
    echo "<br>";
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
}

// Places an order for a meal
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