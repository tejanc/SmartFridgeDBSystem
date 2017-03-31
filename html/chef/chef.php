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
	
	$query1 = "SELECT * FROM INGREDIENTS";
	$result = pg_query($GLOBALS['dbconn'], $query1);

  	// A form to get input about the new meal from the chef
	echo "<form>";
	echo "Meal Name:";
	echo "<input type = 'text' name = 'meal_name_str'><br>";
	echo "Meal Description:";
	echo "<input type = 'text' name = 'meal_desc_str'><br>";
	echo "Cuisine:";
	echo "<input type = 'text' name = 'meal_cuis_str'><br>";
	echo "</form>";
  
	// shows a table with all the ingredients for the chef to select from.
	if (!$result) {
		echo "An error occurred.\n";
		exit;
	} else {
		echo "<br>";
		echo "<form target = 'meal_create_sent_frame' method='post' action='html/chef/chef.php?runFunction=addMeal'>";
		echo "<table style='width:100%'>";
		echo "<tr style='font-weight:bold'> <td>Ingredient Id</td>" . "<td>Name</td>" . "<td>Expiry Date</td>" . "<td>Price</td>" . "<td>Count</td>" . "<td>Category</td>" . "<td>Select</td>" . "</tr>";
		while ($row = pg_fetch_row($result)) {
		  echo "<tr><td>" . "$row[0]" . "</td><td>" . "$row[1]" . "</td><td>" . "$row[2]" . "</td><td>" . "$row[3]" . "</td><td>" . "$row[4]" . "</td><td>" . "$row[5]" . "</td>";
		  echo "<td>" . "<input type='checkbox' name='ingredient_checkbox[]' value='$row[0],$row[1],$row[2],$row[3],$row[4],$row[5]'>" . "</td>";
		  echo "</tr>";
		}
		echo "</table>";
		echo "<br><iframe name='meal_create_sent_frame' height='35' width='1000' scrolling='yes' src='html/chef/create-meal-iframe-default.html'></iframe>";
		echo "<br><a class = 'btn btn-primary text' id='backbtn' onclick='back()''>Back</a> &nbsp;";
		echo "<a class = 'btn btn-primary text' id='create_meal_btn' onclick='addMeal()''>Create Meal</a> &nbsp;";
		echo "</div>";
		echo "</form>";
	}
}

function addMeal() {
	
	$selected_ingredients[] = $_POST['ingredient_checkbox[]'];
	
	// split up the selected fields to get each individual values in an array.
	$meal_create_fields = explode(',',$selected_ingredients);
	
	// Make strings for SQL query
	$meal_id_str = (string) $meal_create_fields[0];
	$chef_id_str = (string) $meal_create_fields[1];
	$meal_name_str = (string) $meal_create_fields[2];
	$meal_description_str = (string) $meal_create_fields[3];
	$meal_cuisine_str =(string) $meal_create_fields[4];
	
	// Query to the MEAL table
	$meal_create_query = "INSERT INTO MEALS(Meal_id, Chef_id, Name, Descr, Cuisine) VALUES (" . $meal_id_str ."," . $chef_id_str . "," . $meal_name_str . "," . $meal_description_str . "," . $meal_cuisine_str . ", false); ";
	
	if(empty('ingredient_checkbox[]')) {
		echo("You didn't select anything.");
		return;
	} else {
		echo "Successfully created meal for: " . $meal_name_str . "!";
	}
	
	
	
}

function placeOrder() {
	
}

function reports() {
	
}

?>

<style>
  .div-center {
      text-align:center;
  }
</style>
