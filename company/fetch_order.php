<?php
include "../db.php";
session_start();

if(!empty($_REQUEST['q']) && !preg_match('/^[ ]{1,20}$/',$_REQUEST['q'])) {

    $word = $_REQUEST['q'];
    $query1 = "SELECT orders.dealer_id,orders.order_id,users.First_name,users.Last_name FROM orders,users WHERE orders.Company_id = '$_SESSION[com_id]' AND users.ID = orders.dealer_id AND ( (users.First_name LIKE '%$word%' OR users.Last_name LIKE '%$word%') OR (users.First_name + ' ' + users.Last_name) = '$word') GROUP BY orders.dealer_id";
    $result = mysqli_query($conn, $query1);

    while($row = mysqli_fetch_assoc($result)){

        $name = $row['First_name']." ".$row['Last_name'];
        echo '<option onclick="add_word(this.value)">'.$name.'</option>';
    }
}