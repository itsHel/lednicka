<?php

    const server = "localhost";
    const username = "root";
    const pass = "";
    const name = "test3";

    if(isset($_GET["install"])){
        createDb();
    }

    switch($_POST["type"]){
        case "insert":
            insert();
            break;
        case "getdata":
            getdata();
            break;
        case "delete":
            deleteRow();
            break;
        case "edit":
            editRow();
            break;
    }

    exit();

    function editRow(){
        $id = $_POST["data"]["id"];
        $column = $_POST["data"]["column"];
        $value = $_POST["data"]["value"];

        if($column == "minExpire" || $column == "expire" || $column == "bought" || $column == "created"){
            if(!validateDate($value))
                exit("Wrong date");
        }

        $cmd = "UPDATE food SET ".$column." = '".$value."' WHERE id = '".$id."'";

        if($result = query($cmd)){
            echo $result;
            echo "<br>".$cmd;
        } else {
            echo "success";
        }
    }

    function getdata(){
        $cmd = "SELECT * FROM food ORDER BY IF(COALESCE(expire, minExpire) >= DATE(NOW()), COALESCE(expire, minExpire), PS)";

        $select = select($cmd);

        if(is_string($select)){
            echo $select;
            echo "<br>".$cmd;
        } else {
            echo json_encode($select);
        }
    }

    function deleteRow(){
        $cmd = "DELETE FROM food WHERE id = '".$_POST["id"]."'";

        if($result = query($cmd)){
            echo $result;
            echo "<br>".$cmd;
        } else {
            echo "success";
        }
    }

    function insert(){
        if(!isset($_POST["data"]["name"]) || !isset($_POST["data"]["minExpire"]) || !isset($_POST["data"]["bought"]))
            exit();

        $cmd = "INSERT INTO food ";
        $types = "(";
        $values = " VALUES ("; 

        foreach ($_POST["data"] as $key => $value){
            if($key == "minExpire" || $key == "expire" || $key == "bought" || $key == "created"){
                if(!validateDate($value))
                    exit();
            }

            $types .= $key.",";
            $values .= "'".$value."'".",";
        }

        $PSDate = getPS();

        $types .= "PS)";
        $values .= "'".$PSDate."')";

        $cmd .= $types.$values;

        if($result = query($cmd)){
            echo $result;
            echo "<br>".$cmd;
        } else {
            echo "success";
        }
    }

    function validateDate($date, $format = "Y-m-d"){
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    function getPS(){
        $startDateString = (isset($_POST["data"]["created"])) ? $_POST["data"]["created"] : $_POST["data"]["bought"];
        $endDateString = (isset($_POST["data"]["expire"])) ? $_POST["data"]["expire"] : $_POST["data"]["minExpire"];

        $startDate = DateTime::createFromFormat("Y-m-d H:i:s", $startDateString." 00:00:00");
        $endDate = DateTime::createFromFormat("Y-m-d H:i:s", $endDateString." 00:00:00");

        $PSTimestamp = ($endDate->getTimestamp() + $startDate->getTimestamp()) / 2;
        $PSDate = date("Y-m-d", $PSTimestamp);

        return $PSDate;
    }

    function query($cmd){
        $connection = new mysqli(server, username, pass, name);

        if(!$connection->query($cmd)){
            $return = $connection->error;
        } else {
            $return = false;
        }

        $connection->close();

        return $return;
    }

    function select($cmd){
        $connection = new mysqli(server, username, pass, name);

        if(!$result = $connection->query($cmd))
            return("Query failed: ".$connection->error);

        $select = [];
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $select[] = $row;
            }
        }

        $connection->close();

        return $select;
    }

    function createDb(){
        $connection = new mysqli(server, username, pass);

        if($connection->connectionect_error){
            die($connection->connectionect_error);
        }

        $cmd = "CREATE DATABASE IF NOT EXISTS ".name;
        if(!$connection->query($cmd))
            die($connection->error);

        $connection->close();

        $connection = new mysqli(server, username, pass, name);

        $cmd =
            "CREATE TABLE IF NOT EXISTS food(
                id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255),
                created DATE,
                bought DATE,
                minExpire DATE,
                expire DATE,
                PS DATE,
                picture blob
            )";

        if(!$connection->query($cmd))
            die($connection->error);
        
        $connection->close();
    }

    