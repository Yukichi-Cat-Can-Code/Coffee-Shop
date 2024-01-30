<?php
    session_start();
    define("APPURl", "http://localhost/coffee-Shop");
    require "../config/config.php";

    $user_id = $_SESSION['user_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $phone_number = $_POST['phone_number'];
    $message = $_POST['message'];


    if(isset($_POST['submit'])){

        if(empty($first_name) || empty($last_name) || empty($date) || empty($time) || empty($phone_number)){

            echo "<script> alert('one or more infield are empty') </script>";

        }else{

            if($date > date("m/d/Y")){

                $insert = $conn->prepare("INSERT INTO bookings(user_id, first_name, last_name, date, time, phone_number, message, status)
                VALUES(:user_id, :first_name, :last_name, :date, :time, :phone_number, :message, :status)");
    
                $insert->execute([
                    ":user_id" => $user_id,
                    ":first_name" => $first_name,
                    ":last_name" => $last_name,
                    ":date" => $date,
                    ":time" => $time,
                    ":phone_number" => $phone_number,
                    ":message" => $message,
                    ":status" => "pending",
                ]);
    
                header("location: http://localhost/coffee-Shop");
                exit();

            }else{

                echo "<script> alert('you need to choose a valid date !!') </script>";

            }

            header("location: ".APPURl."");

        }
    }
?>