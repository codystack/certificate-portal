<?php

session_start();

//Connect database
include "./config/db.php";



//Login script
if (isset($_POST['login_btn'])) {

    $password = $conn->real_escape_string($_POST['password']);
    $username = $conn->real_escape_string($_POST['username']);
    $status = $conn->real_escape_string($_POST['status']);
    $verified = $conn->real_escape_string($_POST['verified']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $role = $conn->real_escape_string($_POST['role']);

    $password = sha1($password);
    $query = "SELECT * FROM admin WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_array($result)) {
        $firstName = $row['firstName'];
        $lastName = $row['lastName'];
        $email = $row['email'];
        $id = $row['id'];
        $status = $row['status'];
        $picture = $row['picture'];
        $phone = $row['phone'];
        $username = $row['username'];
        $role = $row['role'];
    }if (mysqli_num_rows($result) == 1) {
        $_SESSION['firstName'] = $firstName;
        $_SESSION['lastName'] = $lastName;
        $_SESSION['picture'] = $picture;
        $_SESSION['email'] = $email;
        $_SESSION['phone'] = $phone;
        $_SESSION['id'] = $id;
        $_SESSION['role'] = $role;
        $_SESSION['username'] = $username;
        if ($status == 1){
            $_SESSION['success_message'] = "Login Successfull";
            header('location: overview');
        }
        if ($status == 0){
            $_SESSION['error_message'] = "Account Deactivated";
            echo "<meta http-equiv='refresh' content='10; URL=./'>";
        }
    }else {
        $_SESSION['error_message'] = "Incorrect Login Details";
        echo "<meta http-equiv='refresh' content='5; URL=./'>";
    }
}