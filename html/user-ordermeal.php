<?php 
    // Used to run the functions below from external html pages
    if(isset($_GET['runFunction']) && function_exists($_GET['runFunction']))
        call_user_func($_GET['runFunction']);
    else
        echo "Function not found or wrong input";

    function orderMeal() {
        echo "test";
    }
?>



<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
  <script language="JavaScript" type="text/JavaScript"></script>
</head>
    <div class="panel panel-default padding" id="info">
    </div>
    <div id = "orderinfo"></div></br>
    <div id="btn-div">
        <a class = 'btn btn-primary text' id="backbtn" onclick='back()''>Back</a>
        <a class = "btn btn-primary text" id ="ordermealbtn" onclick="orderMeal()">Order Meal</a> 
    </div>
