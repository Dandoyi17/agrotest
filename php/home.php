<?php
include 'config.php';
session_start();
$user_id = $_SESSION['user_id'];


if(!isset($user_id)){
    header('location:login.php');
};

if(isset($_GET['logout'])){
    unset($user_id);
    session_destroy();
    header('location:login.php');
}



?>






<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>

    
<link rel="stylesheet" href="style.css">

</head>
<body>
    <div class="container">

<div class="profile">
    <?php
    $select = mysqli_query($con, "SELECT * FROM `danny` WHERE id = 'danny_id'") or die('query failed');
    if(mysqli_num_rows($select) > 0){
        $fetch = mysqli_fetch_assoc($select);
    }

    if($fetch['image'] ==''){
        echo '<img src="images/default-avatar.png">';
    }else{
        echo '<img src="uploaded_img/'.$fetch['image'].'">';
    }
    ?>

    <h3><?php echo $fetch['name']; ?></h3>
    <a href="update_profile.php" class="btn">update profile</a>
    <a href="home.php?logout=<?php echo $user_id; ?>" class="delete-btn">logout</a>
    <p>new <a href="register.php">Register</a> or <a href="login.php">login</a></p>

</div>

    </div>
</body>
</html>