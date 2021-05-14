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
        } else if($_POST['functionname'] == "getSessionOwner"){
            $user_id = $_POST['arguments'][0];
            $sql = "SELECT rooms_room_id FROM users WHERE user_id='$user_id'";
            $result = mysqli_query($conn, $sql);
            $roomID = mysqli_fetch_assoc($result)["rooms_room_id"];
            $sql = "SELECT first_name FROM users WHERE rooms_room_id='$roomID' AND isDJ=1";
            $result = mysqli_query($conn, $sql);
            echo mysqli_fetch_assoc($result)["first_name"];
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

            //Create a requests list
            $sql = "INSERT INTO requests (requests_id, rooms_room_id) VALUES ('$newRoomID', '$newRoomID');";
            $result = mysqli_query($conn, $sql);

            //Create a queue list
            $sql = "INSERT INTO queue (queue_id, rooms_room_id) VALUES ('$newRoomID', '$newRoomID');";
            $result = mysqli_query($conn, $sql);
        } else if($_POST['functionname'] == "joinSession"){
            $username = $_POST['arguments'][0];
            $user_id = $_POST['arguments'][1];
            $sql = "SELECT rooms_room_id FROM users WHERE username='$username'";
            $result = mysqli_query($conn, $sql);
            $roomID = mysqli_fetch_assoc($result)["rooms_room_id"];
            if($roomID == ""){
                echo "No room";
            } else {
                $sql = "UPDATE users SET rooms_room_id='$roomID',isDJ=0 WHERE user_id='$user_id'";
                $result = mysqli_query($conn, $sql);
                echo "Found room";
            }
        } else if($_POST['functionname'] == "inviteUser"){
            $username = $_POST['arguments'][0];
            $user_id = $_POST['arguments'][1];
            $sql = "UPDATE users SET invite_id='$user_id' WHERE username='$username'";
            $result = mysqli_query($conn, $sql);
        } else if($_POST['functionname'] == "checkForInvite"){
            $user_id = $_POST['arguments'][0];
            $sql = "SELECT invite_id FROM users WHERE user_id='$user_id'";
            $result = mysqli_query($conn, $sql);
            $user_id_inviter = mysqli_fetch_assoc($result)["invite_id"];
            if($user_id_inviter != ""){
                $sql = "SELECT username FROM users WHERE user_id='$user_id_inviter'";
                $result = mysqli_query($conn, $sql);
                echo "\nYou've been invited to a room by " . mysqli_fetch_assoc($result)["username"] . ". Would you like to join?";
                $sql = "UPDATE users SET invite_id=NULL WHERE user_id='$user_id'";
                $result = mysqli_query($conn, $sql);
            } else {
                echo "No invite";
            }
        } else if($_POST['functionname'] == "endSession"){
            $user_id = $_POST['arguments'][0];

            //Get room of user
            $sql = "SELECT rooms_room_id FROM users WHERE user_id='$user_id'";
            $result = mysqli_query($conn, $sql);
            $roomID = intval(mysqli_fetch_assoc($result)["rooms_room_id"]);

            //Making all users not a DJ and no longer in a room
            $sql = "UPDATE users SET rooms_room_id=NULL,isDJ=0 WHERE rooms_room_id='$roomID'";
            $result = mysqli_query($conn, $sql);

            //Deleting associated tables
            $sql = "DELETE FROM requested WHERE requests_rooms_room_id = '$roomID';";
            $result = mysqli_query($conn, $sql);

            $sql = "DELETE FROM in_queue WHERE queue_rooms_room_id = '$roomID';";
            $result = mysqli_query($conn, $sql);

            $sql = "DELETE FROM requests WHERE rooms_room_id = '$roomID';";
            $result = mysqli_query($conn, $sql);

            $sql = "DELETE FROM queue WHERE rooms_room_id = '$roomID';";
            $result = mysqli_query($conn, $sql);

            //Delete the room
            $sql = "DELETE FROM rooms WHERE room_id = '$roomID';";
            $result = mysqli_query($conn, $sql);
        } else if($_POST['functionname'] == "leaveSession"){
            $user_id = $_POST['arguments'][0];
            $sql = "UPDATE users SET rooms_room_id=NULL,isDJ=0 WHERE user_id='$user_id'";
            $result = mysqli_query($conn, $sql);
        } else if($_POST['functionname'] == "checkForEndSession"){
            $user_id = $_POST['arguments'][0];
            $sql = "SELECT rooms_room_id FROM users WHERE user_id='$user_id'";
            $result = mysqli_query($conn, $sql);
            $roomID = mysqli_fetch_assoc($result)["rooms_room_id"];
            if($roomID == ""){
                echo "No room";
            } else {
                echo "Found room";
            }
        } else if($_POST['functionname'] == "addSongToQueue"){
            $user_id = $_POST['arguments'][0];
            $song_id = $_POST['arguments'][1];
            $sql = "SELECT rooms_room_id FROM users WHERE user_id='$user_id'";
            $result = mysqli_query($conn, $sql);
            $roomID = mysqli_fetch_assoc($result)["rooms_room_id"];

            $sql = "SELECT song_id FROM song WHERE song_id='$song_id'";
            $result = mysqli_query($conn, $sql);
            if(mysqli_fetch_assoc($result)["song_id"] == ""){
                $sql = "INSERT INTO song (song_id) VALUES ('$song_id')";
                $result = mysqli_query($conn, $sql);
            }

            $sql = "SELECT DISTINCT play_order FROM in_queue WHERE queue_rooms_room_id='$roomID' ORDER BY play_order DESC";
            $result = mysqli_query($conn, $sql);
            $nextPlayOrder = mysqli_fetch_assoc($result)["play_order"];
            if($nextPlayOrder == ""){
                $nextPlayOrder = 1;
            } else {
                $nextPlayOrder = intval($nextPlayOrder) + 1;
            }

            $sql = "INSERT INTO in_queue (song_song_id, queue_queue_id, queue_rooms_room_id, play_order) VALUES ('$song_id', '$roomID', '$roomID', '$nextPlayOrder');";
            $result = mysqli_query($conn, $sql);

            $sql = "UPDATE users SET check_queue=1 WHERE rooms_room_id='$roomID'";
            $result = mysqli_query($conn, $sql);
        } else if($_POST['functionname'] == "checkQueueOnLoad"){
            $user_id = $_POST['arguments'][0];
            $sql = "SELECT rooms_room_id FROM users WHERE user_id='$user_id'";
            $result = mysqli_query($conn, $sql);
            $roomID = mysqli_fetch_assoc($result)["rooms_room_id"];

            $sql = "SELECT DISTINCT q.play_order, s.song_id FROM in_queue q, song s WHERE queue_rooms_room_id = '$roomID' AND q.song_song_id = s.song_id ORDER BY play_order ASC;";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result)["song_id"];
            do {
                echo "\n" . $row;
            } while($row = mysqli_fetch_assoc($result)["song_id"]);
        } else if($_POST['functionname'] == "checkQueue"){
            $user_id = $_POST['arguments'][0];
            $sql = "SELECT rooms_room_id FROM users WHERE user_id='$user_id'";
            $result = mysqli_query($conn, $sql);
            $roomID = mysqli_fetch_assoc($result)["rooms_room_id"];

            $sql = "SELECT check_queue FROM users WHERE user_id='$user_id'";
            $result = mysqli_query($conn, $sql);
            $checkQueue = mysqli_fetch_assoc($result)["check_queue"];

            if($checkQueue != 0){
                $sql = "SELECT DISTINCT q.play_order, s.song_id FROM in_queue q, song s WHERE queue_rooms_room_id = '$roomID' AND q.song_song_id = s.song_id ORDER BY play_order ASC;";
                $result = mysqli_query($conn, $sql);
                $row = mysqli_fetch_assoc($result)["song_id"];
                do {
                    echo "\n" . $row;
                } while($row = mysqli_fetch_assoc($result)["song_id"]);

                $sql = "UPDATE users SET check_queue=0 WHERE user_id='$user_id'";
                $result = mysqli_query($conn, $sql);
            } else {
                echo "No new songs";
            }
        } else if($_POST['functionname'] == "addSongToRequests"){
            $user_id = $_POST['arguments'][0];
            $song_id = $_POST['arguments'][1];
            $sql = "SELECT rooms_room_id FROM users WHERE user_id='$user_id'";
            $result = mysqli_query($conn, $sql);
            $roomID = mysqli_fetch_assoc($result)["rooms_room_id"];

            $sql = "SELECT song_id FROM song WHERE song_id='$song_id'";
            $result = mysqli_query($conn, $sql);
            if(mysqli_fetch_assoc($result)["song_id"] == ""){
                $sql = "INSERT INTO song (song_id) VALUES ('$song_id')";
                $result = mysqli_query($conn, $sql);
            }

            $sql = "SELECT request_number FROM requested WHERE song_song_id='$song_id' AND requests_requests_id='$roomID'";
            $result = mysqli_query($conn, $sql);
            $request_number = mysqli_fetch_assoc($result)["request_number"];
            if($request_number == ""){
                $sql = "INSERT INTO requested (song_song_id, requests_requests_id, requests_rooms_room_id, request_number) VALUES ('$song_id', '$roomID', '$roomID', 1)";
                $result = mysqli_query($conn, $sql);
            } else {
                $request_number = intval($request_number) + 1;
                $sql = "UPDATE requested SET request_number='$request_number' WHERE song_song_id='$song_id' AND requests_requests_id='$roomID' AND requests_rooms_room_id='$roomID'";
                $result = mysqli_query($conn, $sql);
            }
        } else if($_POST['functionname'] == "checkRequestsOnLoad"){
            $user_id = $_POST['arguments'][0];
            $sql = "SELECT rooms_room_id FROM users WHERE user_id='$user_id'";
            $result = mysqli_query($conn, $sql);
            $roomID = mysqli_fetch_assoc($result)["rooms_room_id"];

            $sql = "SELECT DISTINCT r.request_number, s.song_id FROM requested r, song s WHERE requests_rooms_room_id = '$roomID' AND r.song_song_id = s.song_id ORDER BY request_number DESC;";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result)["song_id"];
            do {
                echo "\n" . $row;
            } while($row = mysqli_fetch_assoc($result)["song_id"]);
        } else if($_POST['functionname'] == "acceptRequest"){
            $user_id = $_POST['arguments'][0];
            $song_id = $_POST['arguments'][1];
            $sql = "SELECT rooms_room_id FROM users WHERE user_id='$user_id'";
            $result = mysqli_query($conn, $sql);
            $roomID = mysqli_fetch_assoc($result)["rooms_room_id"];

            $sql = "SELECT DISTINCT play_order FROM in_queue WHERE queue_rooms_room_id='$roomID' ORDER BY play_order DESC";
            $result = mysqli_query($conn, $sql);
            $nextPlayOrder = mysqli_fetch_assoc($result)["play_order"];
            if($nextPlayOrder == ""){
                $nextPlayOrder = 1;
            } else {
                $nextPlayOrder = intval($nextPlayOrder) + 1;
            }

            $sql = "INSERT INTO in_queue (song_song_id, queue_queue_id, queue_rooms_room_id, play_order) VALUES ('$song_id', '$roomID', '$roomID', '$nextPlayOrder');";
            $result = mysqli_query($conn, $sql);

            $sql = "DELETE FROM requested WHERE song_song_id='$song_id' AND requests_requests_id='$roomID' AND requests_rooms_room_id='$roomID'";
            $result = mysqli_query($conn, $sql);
        } else if($_POST['functionname'] == "denyRequest"){
            $user_id = $_POST['arguments'][0];
            $song_id = $_POST['arguments'][1];
            $sql = "SELECT rooms_room_id FROM users WHERE user_id='$user_id'";
            $result = mysqli_query($conn, $sql);
            $roomID = mysqli_fetch_assoc($result)["rooms_room_id"];

            $sql = "DELETE FROM requested WHERE song_song_id='$song_id' AND requests_requests_id='$roomID' AND requests_rooms_room_id='$roomID'";
            $result = mysqli_query($conn, $sql);
        } else if($_POST['functionname'] == "denyAllRequests"){
            $user_id = $_POST['arguments'][0];
            $sql = "SELECT rooms_room_id FROM users WHERE user_id='$user_id'";
            $result = mysqli_query($conn, $sql);
            $roomID = mysqli_fetch_assoc($result)["rooms_room_id"];

            $sql = "DELETE FROM requested WHERE requests_requests_id='$roomID' AND requests_rooms_room_id='$roomID'";
            $result = mysqli_query($conn, $sql);
        } else if($_POST['functionname'] == "getFirstSong"){
            $user_id = $_POST['arguments'][0];
            $sql = "SELECT rooms_room_id FROM users WHERE user_id='$user_id'";
            $result = mysqli_query($conn, $sql);
            $roomID = mysqli_fetch_assoc($result)["rooms_room_id"];

            $sql = "SELECT DISTINCT s.song_id FROM in_queue q, song s WHERE queue_rooms_room_id = '$roomID' AND q.song_song_id = s.song_id ORDER BY play_order ASC;";
            $result = mysqli_query($conn, $sql);
            $nextSong = mysqli_fetch_assoc($result)["song_id"];
            echo $nextSong;
        } else if($_POST['functionname'] == "removeFirstSong"){
            $user_id = $_POST['arguments'][0];
            $sql = "SELECT rooms_room_id FROM users WHERE user_id='$user_id'";
            $result = mysqli_query($conn, $sql);
            $roomID = mysqli_fetch_assoc($result)["rooms_room_id"];

            $sql = "SELECT DISTINCT s.song_id FROM in_queue q, song s WHERE queue_rooms_room_id = '$roomID' AND q.song_song_id = s.song_id ORDER BY play_order ASC;";
            $result = mysqli_query($conn, $sql);
            $song_id = mysqli_fetch_assoc($result)["song_id"];

            $sql = "DELETE FROM in_queue q WHERE queue_rooms_room_id = '$roomID' AND q.song_song_id = '$song_id'";
            $result = mysqli_query($conn, $sql);

            $sql = "UPDATE users SET check_queue=1 WHERE user_id='$user_id'";
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