<?php

    // Create connection
    $SQLhostname = "auxdb.cuhkzzecjb81.us-east-2.rds.amazonaws.com";
    $SQLusername = "admin";
    $SQLpassword = "Cltee9001!";
    $SQLname = "aux";
    $SQLport = "3306!";
    $conn = mysqli_connect($SQLhostname, $SQLusername, $SQLpassword, $SQLname, $SQLport);

    // Check connection
    if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
    } else {
        echo "Connected successfully";
    }

    if(isset($_POST['functionname'])){
        if($_POST['functionname'] == "createAccount"){
            $firstname = clean_input($_POST['arguments'][0]);
            $lastname = clean_input($_POST['arguments'][1]);
            $email = clean_input($_POST['arguments'][2]);
            $username = clean_input($_POST['arguments'][3]);
            $password = clean_input($_POST['arguments'][4]);
    
            $sql = "INSERT INTO users (first_name, last_name, username, passcode) VALUES ('$firstname', '$lastname', '$username', '$password')";

            if (mysqli_query($conn, $sql)) {
                echo "\nUser data uploaded successfully";
            } else {
                echo "\nError: " . $sql . " - " . mysqli_error($conn);
            }

            $conn->close();
        } else if($_POST['functionname'] == "seeUsers"){
            $sql = "SELECT * FROM users";
            $result = mysqli_query($conn, $sql);
            
            echo "\n\nCurrent Users:\nuser_id - first_name - last_name - username - passcode\n";
            while($row = mysqli_fetch_assoc($result)) {
                echo $row["user_id"] . " - " . $row["first_name"] . " - " . $row["last_name"] . " - " . $row["username"] . " - " . $row["passcode"] . "\n";
            }
        }
    }

    function clean_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

?>