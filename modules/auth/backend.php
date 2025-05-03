<?php
$conn = new mysqli("localhost", "clientzone_user", "S@utech2024!", "clientzone");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
if (isset($_POST['register_user'])) {
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "INSERT INTO `registers` (`name`, `surname`, `address`, `email`, `username`, `password`) 
    VALUES ('$name', '$surname', '$address', '$email', '$username', '$password')";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        header('location:register.php');
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
//update
if (isset($_POST['update_user'])) {
    $id = intval($_POST['update_user_id']);
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    $update_sql = "UPDATE `registers` SET 
        name = '$name',
        surname = '$surname',
        address = '$address',
        email = '$email',
        username = '$username',
        password = '$password'
        WHERE id = $id";

    $update_result = mysqli_query($conn, $update_sql);

    if ($update_result) {
        header("Location: register.php?updated=1");
        exit;
    } else {
        echo "Update failed: " . mysqli_error($conn);
    }
}
//Delete
if(isset($_GET['delete_user'])){
    $id = $_GET['delete_user'];
    $sql = "Delete From `registers` WHERE `id` = '$id';";
    $result = mysqli_query($conn,$sql);
    if($result){
        header('location:register.php');
    }else{
        echo "Error";
    }
}


?>


