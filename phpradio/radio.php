<form method="post" action="sample.php">
 select sex: 
 <input type="radio" name="radio" value="male">
 <input type="radio" name="radio" value="female">

 <input id="submit" name="submit" type="submit" value="Send" class="btn btn-primary">
 </form>

<?php

if (isset($_POST['radio'])){

    $Sex = $_POST['radio'];
 }
  ?>