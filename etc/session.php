<?php 

 if(session_id() == '')
 {
      session_start();
 }
 
//THIS is a security key to prevent unauthorised access of code
$_SESSION['auth']="kMuGLmlIzCWmkNbtksAh";
//session_destroy();
 ?>