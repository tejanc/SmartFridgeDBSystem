<head>
<script language="JavaScript" type="text/JavaScript">
function makeRequestObject(){
   var xmlhttp=false; 
   try {
      xmlhttp = new ActiveXObject('Msxml2.XMLHTTP');
   } catch (e) {
      try {
         xmlhttp = new ActiveXObject('Microsoft.XMLHTTP'); 
      } catch (E) {
         xmlhttp = false;
      }
   }
   if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
      xmlhttp = new XMLHttpRequest(); 
   }
   return xmlhttp;
}

function showdata()
{
   var xmlhttp=makeRequestObject();
   var user=document.getElementById('user_name').value;
   var email=document.getElementById('email_id').value;
   var file = 'ajaxaccessdata.php?usernme='; 
   xmlhttp.open('GET', file + user+'&emailid='+email, true); 
   xmlhttp.onreadystatechange=function() {
      if (xmlhttp.readyState==4 && xmlhttp.status == 200) { 
         var content = xmlhttp.responseText; 
         if( content ){ 
            document.getElementById('info').innerHTML = content; 
         }
      }
   }
   xmlhttp.send(null) 
}
</script>
</head>
<body>
   Enter your Name: <input type="text" id="user_name"/> <br>
   Enter your email id : <input type="text" id="email_id" ><br>
   <input type="button" onclick="showdata()" value="Submit" ><br><br>
   <div id="info"></div>
</body>
</html>