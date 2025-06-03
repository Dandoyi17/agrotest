
<?php

include 'config.php';
if(isset($_POST['submit'])){
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $pass = mysqli_real_escape_string($con, $_POST['password']);
    $cpass = mysqli_real_escape_string($con, $_POST['cpassword']);
    $image = $_FILES['image']['name'];
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = 'uploaded_img/'.$image;


    $select = mysqli_query($con, "SELECT * FROM `danny` WHERE email = '$email' AND password = '$pass'") 
    or die('query failed');


    if(mysqli_num_rows($select) > 0){
        $massage[] = 'user already exist';
    }else {
        if($pass != $cpass){
            $massage[] = 'comfirm password not matched!';
        }elseif($image_size > 2000000){
            $massage[] = 'image size too large';

        }else{
            $insert = mysqli_query($con, "INSERT INTO `danny` (name, email, password, image) VALUE('$name','$email','$pass','$image')")
            or die('query failed');


            if($insert){
                move_uploaded_file($image_tmp_name, $image_folder);
                $massage[] = 'registered successful';
                header('location:login.php');
                
                
            }else{
                $massage[] = 'registration failed';
            }

        }
    }

}


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>register</title>


<link rel="stylesheet" href="style.css">


</head>
<body>

<div class="form-container">
    

<form action="" method="post" enctype="multipart/form-data">
<h3>Register now</h3>
<?php
if(isset($massage)){
    foreach($massage as $massage){
        echo '<div class="massage">'.$massage.'</div>';
    } 
}


?>
    <input type="text" name="name" placeholder="enter username" class="box" required>
    <input type="email" name="email" placeholder="enter email" class="box" required>
    <input type="password" name="password" placeholder="enter password" class="box" required>
    <input type="password" name="cpassword" placeholder="confirm password" class="box" required>
    <input type="file" name="image" class="box" accept="image/jpg, image/jpeg, image/png">
    <input type="submit" name="submit" value="register now" class="btn">
    <p>already a member?<a href="login.php">login now</a></p>






</form>

</div>
    
</body>
</html>