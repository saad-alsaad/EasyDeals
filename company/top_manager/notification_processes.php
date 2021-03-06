<?php
include "db.php";
$output = array();
$top_managers = array();
$financial_managers = array();
$dealers = array();
$sending_flag = 0;

$top_managers = user_search($conn,"SELECT goods_manager.user_id,users.First_name,users.Last_name FROM goods_manager,users WHERE goods_manager.company_id = $_SESSION[com_id] AND users.ID = goods_manager.user_id");

$financial_managers = user_search($conn,"SELECT financial_manager.user_id,users.First_name,users.Last_name FROM financial_manager,users WHERE financial_manager.company_id = $_SESSION[com_id] AND users.ID = financial_manager.user_id");

$dealers = user_search($conn,"SELECT DISTINCT orders.dealer_id 'user_id',orders.Company_id,users.ID,users.First_name,users.Last_name FROM orders,users WHERE orders.Company_id = $_SESSION[com_id] AND users.ID = orders.dealer_id");

for($j=count($top_managers[0]),$k=0;$k < count($financial_managers[0]);$j++,$k++){
    $top_managers[0][$j] = $financial_managers[0][$k];
    $top_managers[1][$j] = $financial_managers[1][$k];
}

function user_search($conn,$query){
    $out = array();
    mysqli_query($conn, $query) or die('Error querying database 1');

    $result = mysqli_query($conn, $query);

    for ($i = 0;$row = mysqli_fetch_assoc($result);$i++) {
        $out[0][$i] = $row['user_id'];
        $out[1][$i] = $row['First_name']." ".$row['Last_name'];
    }
    return $out;
}

$query = "SELECT notification.sender_id, notification.receiver_id, notification.type, notification.sent_time, notification.Message, users.ID,users.First_name,users.Last_name FROM notification,users WHERE notification.receiver_id = '$_SESSION[id]' AND users.ID = notification.sender_id";

$output = not_search($conn,$query);

function not_search($conn,$query){
    $output = array();
    $i = 0;
    mysqli_query($conn, $query) or die('Error querying database.');
    $result = mysqli_query($conn, $query);

    while ($row = mysqli_fetch_array($result)) {
        $output[0][$i] = $row['sent_time'];
        $output[0][$i] = str_replace(' '," -- ",$output[0][$i]);
        $output[1][$i] = $row['Message'];
        $output[2][$i] = $row['First_name'].' '. $row['Last_name'];
        $output[3][$i] = $row['type'];
        $i++;
    }

    return $output;
}



if(isset($_POST['send'])){
    $sending_flag = 0;
    if($_POST['dealer'] == "" && $_POST['manager'] == ""){
        $_SESSION['not_flag'] = "1";
    }
    else{
        if($_POST['manager'] != ''){
            $q = "INSERT INTO notification (sender_id, receiver_id, Message, sent_time, type, status) VALUES ('$_SESSION[id]','$_POST[manager]','$_POST[message]',NOW(),0,0)";
            if(!mysqli_query($conn, $q)){
                $sending_flag = 1;
            }
        }
        if($_POST['dealer'] != ''){
            $q = "INSERT INTO notification (sender_id, receiver_id, Message, sent_time, type, status) VALUES ('$_SESSION[id]','$_POST[dealer]','$_POST[message]',NOW(),0,0)";
            if(!mysqli_query($conn, $q)){
                $sending_flag = 1;
            }
        }

        if($sending_flag == 1)
            $_SESSION['not_flag'] = "2";
        else
            $_SESSION['not_flag'] = "3";
    }

    header("Location: Notifications.php");
    exit();
}

if(isset($_POST['search'])){
    $search_word = "";
    $type = " AND notification.type = 0";
    if($_POST['search_word'] != ""){
        $search_word = " AND ((users.First_name LIKE '%$_POST[search_word]%') OR (users.Last_name LIKE '%$_POST[search_word]%') OR (CONCAT(users.First_name,' ',users.Last_name) LIKE '%$_POST[search_word]%'))";
    }

    if($_POST['type'] == "0"){
        $type = " AND notification.type = 0";
        $_SESSION['type_0'] = "selected";
    }
    elseif ($_POST['type'] == "1"){
        $type = " AND notification.type = 1";
        $search_word = "";
        $_SESSION['type_1'] = "selected";
    }
    elseif ($_POST['type'] == "3"){
        $type = " AND notification.type = 3";
        $search_word = "";
        $_SESSION['type_3'] = "selected";
    }

    $search_query = "SELECT notification.sender_id, notification.receiver_id, notification.type, notification.sent_time, notification.Message, users.ID,users.First_name,users.Last_name FROM notification,users WHERE notification.receiver_id = $_SESSION[id] AND users.ID = notification.sender_id".$type.$search_word;

    $output = not_search($conn,$search_query);
}

?>