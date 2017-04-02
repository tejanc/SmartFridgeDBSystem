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

function showDepletedIngredients() {
  $no_ing_query = "SELECT * FROM INGREDIENTS";
    $no_ing_query_res = pg_query($GLOBALS['dbconn'], $no_ing_query);
    
    echo "<br><h1>Place an order</h1></br>";
    echo "<form target = 'place_order_sent_frame' method='post' action='html/admin/admin.php?runFunction=placeOrder'>";
    if (!$no_ing_query_res) {
      echo "An error occurred.\n";
      exit;
    } else {
      echo "<br>";
      echo "<table style='width:100%'>";
      echo "<tr style='font-weight:bold'> <td>Ingredient Id</td>" . "<td>Name</td>" . "<td>Expiry Date</td>" . "<td>Price</td>" . "<td>Count</td>" . "<td>Category</td>" . "<td>Select</td>" . "</tr>";
      while ($row = pg_fetch_row($no_ing_query_res)) {
        echo "<tr><td>" . "$row[0]" . "</td><td>" . "$row[1]" . "</td><td>" . "$row[2]" . "</td><td>" . "$row[3]" . "</td><td>" . "$row[4]" . "</td><td>" . "$row[5]" . "</td>";
        echo "<td>" . "<input type='text' name='quantity_requested[]'>" . "</td>";
        echo "</tr>";
      }
      echo "</table>";
      echo "<br><iframe name='place_order_sent_frame' height='50' width='1000' scrolling='no' src='html/chef/place-order-iframe-default.html'></iframe>";
      echo "<br><input id='submit' name='submit' type='submit' value='Place Order' class='btn btn-primary'><br><br>";
      echo "</div>";
      echo "</form>";
    }
}  

function placeOrder() {
  if (isset($_POST['quantity_requested']))
    $selected_quantities = $_POST['quantity_requested'];
  
  if (empty($selected_quantities)) { 
        echo "You didn't select anything.";
    } else {
    // Create a FRIDGE_ORDER
    $N = count($selected_quantities);
    
    // Get list of depleted ingredients again
    // $N = # of rows
    $no_ing_query = "SELECT * FROM INGREDIENTS;";
    //$no_ing_query = "SELECT * FROM INGREDIENTS WHERE Count = '0'";
    $no_ing_query_res = pg_query($GLOBALS['dbconn'], $no_ing_query);

    for ($i = 0; $i < $N; $i++) {     
      // Fetch current depleted ingredient row
      $row = pg_fetch_row($no_ing_query_res);

      if (is_numeric($selected_quantities[$i]) && $selected_quantities[$i] > 0) {
        $order_ing_query = "UPDATE INGREDIENTS SET COUNT = COUNT + $selected_quantities[$i] WHERE ING_ID = '$row[0]'";
        $order_ing_query_res = pg_query($GLOBALS['dbconn'], $order_ing_query);
          if (!$order_ing_query_res) {
            echo "An error occurred.\n";
          }
        echo "Successfully ordered $selected_quantities[$i] of ingredient $row[1]";
      }  
    }
  }
}

// Displays all fridge_orders that haven't been approved for approval
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
    echo "<tr style='font-weight:bold'><td>order_id</td>" . "<td>count</td>" . "<td>ing_id</td>" . "<td>admin_id</td>" . "<td>approved</td>" . "<td>Accept?</td></tr>";
    while ($row = pg_fetch_row($result)) {
      echo "<tr><td>" . "$row[0]" . "</td><td>" . "$row[1]" . "</td><td>" . "$row[2]" . "</td><td>" . "$row[3]" . "</td><td>"."$row[4]"."</td>";
      echo "<td>" . "<input type='checkbox' name='order_checkbox[]' value='$row[0],$row[1],$row[2]'>" . "</td>";
      echo "</tr>";
    }
    echo "</table>";
    echo "<br><iframe name='order_approved_frame' height='200' width='1000' scrolling='yes' src='html/admin/order-approved-iframe-default.html'></iframe><br>";
    echo "<input id='submit' name='submit' type='submit' value='Approve Orders' class='btn btn-primary'><br><br>";
    echo "</form>";
  }
}

// Approves orders that were checked and orders the ingredients
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

// Displays expense report
function Expense(){
	$meal_expense_query = "SELECT meal_id, SUM(price) FROM (SELECT DISTINCT meal_id, M.ing_id, price FROM MEAL_CONTAINS M, INGREDIENTS I WHERE (M.ing_id = I.ing_id) GROUP BY meal_id, M.ing_id, price ORDER BY price DESC) AS DERIVED_TABLE GROUP BY DERIVED_TABLE.meal_id;";
  $meal_expense_query_res = pg_query($GLOBALS['dbconn'], $meal_expense_query);

  if (!$meal_expense_query_res) {
    echo "An error occurred.\n";
    exit;
  } 

  $cnt = 1;
  echo "<table style='width:100%'>";
  echo "<tr style='font-weight:bold'><td>Rank</td> <td>meal_id</td> <td>Total Ingredient Cost ($)</td></tr>";
  while ($row = pg_fetch_row($meal_expense_query_res)) {
    echo "<tr><td>$cnt</td>" . "<td>" . "$row[0]" . "</td><td>" . "$row[1]" . "</td></tr>";
    $cnt++;
  }
  echo "</table>";
}

// Displays top three ingredients report
function TopThree(){
  $top_three_ing_query = "SELECT ing_id, COUNT(ing_id) FROM MEAL_CONTAINS GROUP BY ing_id ORDER BY COUNT(ing_id) DESC;";
  $top_three_ing_query_res = pg_query($GLOBALS['dbconn'], $top_three_ing_query);
  
  if (!$top_three_ing_query_res) {
    echo "An error occurred.\n";
    exit;
  } 
  
  $cnt = 1;
  echo "<table style='width:100%'>";
  echo "<tr style='font-weight:bold'><td>Rank</td> <td>ing_id</td> <td># times used in a meal</td></tr>";
  while (($row = pg_fetch_row($top_three_ing_query_res)) && $cnt <= 3) {
    echo "<tr><td>$cnt</td>" . "<td>" . "$row[0]" . "</td><td>" . "$row[1]" . "</td></tr>";
    $cnt++;
  }
  echo "</table>";
}
  

// Displays frequently requested meals report
function FreqRequested(){
  $top_meals_query = "SELECT meal_id, COUNT(meal_id) FROM CHEF_ORDER GROUP BY meal_id ORDER BY COUNT(meal_id) DESC";
  $top_meals_query_res = pg_query($GLOBALS['dbconn'], $top_meals_query);

  if (!$top_meals_query_res) {
    echo "An error occurred.\n";
    exit;
  } 

  $cnt = 1;
  echo "<table style='width:100%'>";
  echo "<tr style='font-weight:bold'><td>Rank</td> <td>meal_id</td> <td># ordered</td></tr>";
  while ($row = pg_fetch_row($top_meals_query_res)) {
    echo "<tr><td>$cnt</td>" . "<td>" . "$row[0]" . "</td><td>" . "$row[1]" . "</td></tr>";
    $cnt++;
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