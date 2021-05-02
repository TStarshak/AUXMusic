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
    
            $sql = "SELECT user_id FROM users ORDER BY user_id DESC"; 

            $result = mysqli_query($conn, $sql);

            $nextUserID = intval(mysqli_fetch_assoc($result)["user_id"]) + 1;

            $sql = "INSERT INTO users (user_id, first_name, last_name, username, passcode, isDJ, rooms_room_id) VALUES ('$nextUserID', '$firstname', '$lastname', '$username', '$password', false, null)";

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

            $conn->close();
        } else if($_POST['functionname'] == "getAccount"){
            $username = clean_input($_POST['arguments'][0]);
            $password = clean_input($_POST['arguments'][1]);

            $sql = "SELECT passcode FROM users WHERE username='$username'";
            $result = mysqli_query($conn, $sql);

            if($password != "" && strcmp(mysqli_fetch_assoc($result)["passcode"], $password) == 0){
                echo "\nYou are now logged in.";
                $sql = "SELECT user_id FROM users WHERE username='$username'";
                $result = mysqli_query($conn, $sql);
                echo mysqli_fetch_assoc($result)["user_id"];
            } else {
                echo "\nThat username and password combination does not exist.";
            }

            $conn->close();
        } else if($_POST['functionname'] == "getFirstName"){
            $user_id = $_POST['arguments'][0];
            $sql = "SELECT first_name FROM users WHERE user_id='$user_id'";
            $result = mysqli_query($conn, $sql);
            echo mysqli_fetch_assoc($result)["first_name"];
        } else if($_POST['functionname'] == "getFullName"){
            $user_id = $_POST['arguments'][0];
            $sql = "SELECT first_name FROM users WHERE user_id='$user_id'";
            $result = mysqli_query($conn, $sql);
            echo mysqli_fetch_assoc($result)["first_name"] . " ";
            $user_id = $_POST['arguments'][0];
            $sql = "SELECT last_name FROM users WHERE user_id='$user_id'";
            $result = mysqli_query($conn, $sql);
            echo mysqli_fetch_assoc($result)["last_name"];
        } else if($_POST['functionname'] == "startSession"){
            $user_id = $_POST['arguments'][0];

            //Getting room ID to create new room
            $sql = "SELECT room_id FROM rooms ORDER BY room_id DESC";
            $result = mysqli_query($conn, $sql);
            $newRoomID = intval(mysqli_fetch_assoc($result)["room_id"]) + 1;

            //Creating the new room with the new room ID
            $sql = "INSERT INTO rooms (room_id, passcode) VALUES ('$newRoomID', '')";
            $result = mysqli_query($conn, $sql);

            //Placing user into new room
            $sql = "UPDATE users SET rooms_room_id='$newRoomID',isDJ=1 WHERE user_id='$user_id'";
            $result = mysqli_query($conn, $sql);
        }
    }

    function clean_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

?>