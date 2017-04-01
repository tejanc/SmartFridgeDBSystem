<?php
session_start();

// Retrieve the users credentials for DB connection
$conn_string= "host=localhost port=5432 dbname=postgres user=postgres password=postgres";

// GET DB CONNECTION STRING
$dbconn = pg_connect($conn_string) or die ("Connection failed");
$query= "set search_path = 'public'";

if(isset($_GET['runFunction']) && function_exists($_GET['runFunction']))
	call_user_func($_GET['runFunction']);
else
	echo "Function not found or wrong input";

function createMeal() {
	echo "<br>";
    echo "<form target = 'meal_create_sent_frame' method='post' action='html/chef/chef.php?runFunction=addMeal'>";
	echo "<br>Chef ID: &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";
	echo "<input type = 'text' name = 'chef_id_str'><br>"; 
	echo "Meal Name: &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";
	echo "<input type = 'text' name = 'meal_name_str'><br>";
	echo "Meal Description: &nbsp";
	echo "<input type = 'text' name = 'meal_desc_str'><br>";
	echo "Cuisine: &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";
	echo "<input type = 'text' name = 'meal_cuis_str'><br>";
	
	$query1 = "SELECT * FROM INGREDIENTS";
	$result = pg_query($GLOBALS['dbconn'], $query1);
  
	// shows a table with all the ingredients for the chef to select from.
	if (!$result) {
		echo "An error occurred.\n";
		exit;
	} else {
		echo "<table style='width:100%'>";
		echo "<tr style='font-weight:bold'> <td>Ingredient Id</td>" . "<td>Name</td>" . "<td>Expiry Date</td>" . "<td>Price</td>" . "<td>Count</td>" . "<td>Category</td>" . "<td>Select</td>" . "</tr>";
		while ($row = pg_fetch_row($result)) {
		  echo "<tr><td>" . "$row[0]" . "</td><td>" . "$row[1]" . "</td><td>" . "$row[2]" . "</td><td>" . "$row[3]" . "</td><td>" . "$row[4]" . "</td><td>" . "$row[5]" . "</td>";
		  echo "<td>" . "<input type='checkbox' name='ingredient_checkbox[]' value='$row[0]'>" . "</td>";
		  echo "</tr>";
		}
		echo "</table>";
		echo "<br><iframe name='meal_create_sent_frame' height='200' width='1000' scrolling='yes' src='html/chef/create-meal-iframe-default.html'></iframe>";
		echo "<br><a class = 'btn btn-primary text' id='backbtn' onclick='back()''>Back</a> &nbsp;";
		echo "<input id='submit' name='submit' type='submit' value='Create Meal' class='btn btn-primary'><br><br>";
		echo "</div>";
		echo "</form>";
	}
}

function addMeal() {
	
	if (isset($_POST['chef_id_str']))
		$chef_id_str = $_POST['chef_id_str'];
	if (isset($_POST['meal_name_str']))
		$meal_name_str = $_POST['meal_name_str'];
	if (isset($_POST['meal_desc_str']))
		$meal_description_str = $_POST['meal_desc_str'];
	if (isset($_POST['meal_cuis_str']))
		$meal_cuisine_str = $_POST['meal_cuis_str'];

    // get array of all checkbox values 
	$selected_ingredients = $_POST['ingredient_checkbox'];
	
    if (empty($selected_ingredients)) { 
        echo "You didn't select anything.";
    } else {
       
        $meal_id_query = "SELECT Meal_id FROM MEALS ORDER BY Meal_id DESC LIMIT 1";
        $meal_id_query_res = pg_query($GLOBALS['dbconn'], $meal_id_query);

        if (!$meal_id_query_res) {
            echo "An error occurred.\n";
        } else {
            $meal_id_query_row = pg_fetch_row($meal_id_query_res);
            $max_meal_id = (int) $meal_id_query_row[0];
            $meal_id_str = $max_meal_id+1;
        }

        // Create MEAL
        $meal_create_query = "INSERT INTO MEALS(Meal_id, Chef_id, Name, Descr, Cuisine) VALUES (" . $meal_id_str ."," . $chef_id_str . ",'" . $meal_name_str . "','" . $meal_description_str . "','" . $meal_cuisine_str . "');";
        $meal_create_query_res = pg_query($GLOBALS['dbconn'], $meal_create_query);

        if (!$meal_create_query_res) {
            echo "An error occurred.\n";
        } else {
            // Add ingredients to MEAL_CONTAINS relation
            $N = count($selected_ingredients);

            for ($i = 0; $i < $N; $i++) {
                $add_ing_to_meal = "INSERT INTO MEAL_CONTAINS(Ing_id, Meal_id) VALUES($selected_ingredients[$i], $meal_id_str)";

                $add_ing_to_meal_result = pg_query($GLOBALS['dbconn'], $add_ing_to_meal);

                if (!$add_ing_to_meal_result) {
                    echo "An error occurred.\n";
                } 
            }     
            echo "Successfully created meal for: " . $meal_name_str . "!";
        }
        //getMeals();
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
    echo "<table style='width:100%'>";
    echo "<tr style='font-weight:bold'><td>MealID</td>" . "<td>Name</td>" . "<td>Description</td>" . "<td>Cuisine</td>" . "</tr>";
    while ($row = pg_fetch_row($result)) {
      echo "<tr><td>" . "$row[0]" . "</td><td>" . "$row[2]" . "</td><td>" . "$row[3]" . "</td><td>" . "$row[4]" . "</td>";
      echo "</tr>";
    }
    echo "</table>";
    echo "<br><iframe name='meal_order_sent_frame' height='35' width='1000' scrolling='no' src='html/user/order-meal-iframe-default.html'></iframe>";
    echo "<br><a class = 'btn btn-primary text' id='backbtn' onclick='back()''>Back</a> &nbsp;";
    echo "</div>";
    echo "</form>";
  }
}

/*
	A chef can request an ingredient to be orderdered.
		1. CHEF 		---- < REQUESTS_ING > ---- FRIDGE_ORDER
		2. FRIDGE_ORDER ---- < ORDER_ING 	> ---- INGREDIENTS
		3. INGREDIENTS  ---- < EXP_REPORT 	> ---- EXPENSES
		4. EXPENSES		---- < VIEWS		> ---- ADMIN
		5. ADMIN		---- < APPROVES		> ---- FRIDGE_ORDER
*/
function placeOrder() {
	


	if (isset($_POST['quantity_requested']))
		$selected_quantities = $_POST['quantity_requested'];
	
	// sets the chef_id already set in the Database
	$chef_id_query = "SELECT user_id FROM USERS WHERE cflag = '1'";
    $chef_id_query_res = pg_query($GLOBALS['dbconn'], $chef_id_query);
	if (!$chef_id_query_res) {
		echo "An error occurred.\n";
	} else {
		$chef_id_query_row = pg_fetch_row($chef_id_query_res);
		$chef_id_str = (int) $chef_id_query_row[0];
	}
	
	// sets the admin_id already set in the Database
	$admin_id_query = "SELECT user_id FROM USERS WHERE cflag = '1'";
    $admin_id_query_res = pg_query($GLOBALS['dbconn'], $admin_id_query);
	if (!$admin_id_query_res) {
		echo "An error occurred.\n";
	} else {
		$admin_id_query_row = pg_fetch_row($admin_id_query_res);
		$admin_id_str = (int) $admin_id_query_row[0];
	}
	
	if (empty($selected_quantities)) { 
        echo "You didn't select anything.";
    } else {
		
		// Create a FRIDGE_ORDER
		$N = count($selected_quantities);
		
		// Get list of depleted ingredients again
		// $N = # of rows
		$no_ing_query = "SELECT * FROM INGREDIENTS WHERE Count = '0'";
		$no_ing_query_res = pg_query($GLOBALS['dbconn'], $no_ing_query);


		for ($i = 0; $i < $N; $i++) {			
			// Fetch current depleted ingredient row
			$row = pg_fetch_row($no_ing_query_res);

			if (is_numeric($selected_quantities[$i]) && $selected_quantities[$i] > 0) {
			$ing_order_query = "INSERT INTO FRIDGE_ORDER(Ing_id, Count, Chef_id, Admin_id, Approved) VALUES (" . $row[0] . "," . $selected_quantities[$i] . "," . $chef_id_str . "," . $admin_id_str . ", false);";
			
				$ing_order_query_res = pg_query($GLOBALS['dbconn'], $ing_order_query);
			
				if (!$ing_order_query_res) {
                    echo "An error occurred.\n";
				}    
			}  
		}
		echo "Successfully created fridge order!";
	}

	//showFridgeOrder();
}

function showFridgeOrder() {
	$fridge_order_query = "SELECT * FROM FRIDGE_ORDER";
	$fridge_order_query_res = pg_query($GLOBALS['dbconn'],$fridge_order_query);
	if (!$fridge_order_query_res) {
		echo "An error occurred.\n";
		exit;
	} else {
		echo "<br>";
		echo "<table style='width:100%'>";
		echo "<tr style='font-weight:bold'><td>Order ID</td>" . "<td>Count</td>" . "<td>Ing ID</td>" . "<td>Chef ID</td>" . "</tr>";
		while ($row = pg_fetch_row($fridge_order_query_res)) {
		  echo "<tr><td>" . "$row[0]" . "</td><td>" . "$row[1]" . "</td><td>" . "$row[2]" . "</td><td>" . "$row[3]" . "</td>";
		  echo "</tr>";
		}
		echo "</table>";
		echo "<br><iframe name='meal_order_sent_frame' height='35' width='1000' scrolling='yes' src='html/user/place-order-iframe-default.html'></iframe>";
	}
}

/*
 Shows the chef a list of all ingredients that are depleted.
 Then it propts the chef with a form where he can specify
 the ingredient that they wish to order.
 */
function showDepletedIngredients() {
	
	$no_ing_query = "SELECT * FROM INGREDIENTS WHERE Count = '0'";
	$no_ing_query_res = pg_query($GLOBALS['dbconn'], $no_ing_query);
	
	echo "<br><h1>Place an order</h1></br>";
	echo "<form target = 'place_order_sent_frame' method='post' action='html/chef/chef.php?runFunction=placeOrder'>";
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
		echo "<br><a class = 'btn btn-primary text' id='backbtn' onclick='back()''>Back</a> &nbsp;";
		echo "<input id='submit' name='submit' type='submit' value='Place Order' class='btn btn-primary'><br><br>";
		echo "</div>";
		echo "</form>";
	}
}

function reports() {
	
}

?>

<style>
  .div-center {
      text-align:center;
  }
</style>
