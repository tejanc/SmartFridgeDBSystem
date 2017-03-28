<?php





//CONNECT TO DB

echo "GG this is a good day<br />\n";
//QUERY DATABASE TO SEE IF USER EXIST 
//USE PARAMETERS TO AVOID SQL INJECTION 

pg_query($dbconn,$query);





$query2 = "INSERT INTO MEALS(meal_id,chef_id,name,descr,cuisine) VALUES ('123456', '111111', 'lasagna', 'test', 'italian')";

if (pg_query($dbconn, $query2) === TRUE) {
    echo "New record created successfully";
} else {
}





 
?>
