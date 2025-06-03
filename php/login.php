
<?php

include 'config.php';
session_start();


if(isset($_POST['submit'])){
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $pass = mysqli_real_escape_string($con, $_POST['password']);


    $select = mysqli_query($con, "SELECT * FROM `danny` WHERE email = '$email' AND password = '$pass'") 
    or die('query failed');


    if(mysqli_num_rows($select) > 0){
        $row = mysqli_fetch_assoc($select);
        $_SESSION['user_id'] = $row['id'];
        header('location:home.php');


        
        }else{
            
        $massage[] = 'incorrect username or password';
        }

}


?>









<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login</title>


<link rel="stylesheet" href="style.css">





</head>
<body>

<div class="form-container">
    

<form action="" method="post" enctype="multipart/form-data">
<h3>Login Now</h3>
<?php
if(isset($massage)){
    foreach($massage as $massage){
        echo '<div class="massage">'.$massage.'</div>';
    } 
}


?>
    <input type="email" name="email" placeholder="enter email" class="box" required>
    <input type="password" name="password" placeholder="enter password" class="box" required>
    <input type="submit" name="submit" value="login now" class="btn">
    <p>Not a member?<a href="register.php">Register now</a></p>






</form>

</div>
    
</body>
</html>