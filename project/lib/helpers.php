<?php
session_start();//we can start our session here so we don't need to worry about it on other pages
require_once(__DIR__ . "/db.php");

function is_logged_in() {
    return isset($_SESSION["user"]);
}

function has_role($role) {
    if (is_logged_in() && isset($_SESSION["user"]["roles"])) {
        foreach ($_SESSION["user"]["roles"] as $r) {
            if ($r["name"] == $role) {
                return true;
            }
        }
    }
    return false;
}

function get_username() {
    if (is_logged_in() && isset($_SESSION["user"]["username"])) {
        return $_SESSION["user"]["username"];
    }
    return "";
}

function get_email() {
    if (is_logged_in() && isset($_SESSION["user"]["email"])) {
        return $_SESSION["user"]["email"];
    }
    return "";
}

function get_user_id() {
    if (is_logged_in() && isset($_SESSION["user"]["id"])) {
        return $_SESSION["user"]["id"];
    }
    return -1;
}

function safer_echo($var) {
    if (!isset($var)) {
        echo "";
        return;
    }
    echo htmlspecialchars($var, ENT_QUOTES, "UTF-8");
}

//for flash feature
function flash($msg) {
    if (isset($_SESSION['flash'])) {
        array_push($_SESSION['flash'], $msg);
    }
    else {
        $_SESSION['flash'] = array();
        array_push($_SESSION['flash'], $msg);
    }

}

function getMessages() {
    if (isset($_SESSION['flash'])) {
        $flashes = $_SESSION['flash'];
        $_SESSION['flash'] = array();
        return $flashes;
    }
    return array();
}

function do_transaction($acc1, $acc2, $amount, $memo, $type){
    $db = getDB();
    $query = null;
    $stmt2 = $db->prepare("SELECT SUM(amount) as balance FROM Transactions WHERE Transactions.act_src_id = :id");
    $r2 = $stmt2->execute([":id"=>$acc1]);
    $result = $stmt2->fetch(PDO::FETCH_ASSOC);
    $a1tot = (int)$result["balance"];
    $r2 = $stmt2->execute([
        ":id"=>$acc2
    ]);
    $result = $stmt2->fetch(PDO::FETCH_ASSOC);
    $a2tot = (int)$result["balance"];


//  if($a1tot+$amountChange >= 0)
    // {
    $query = "INSERT INTO `Transactions` (`act_src_id`, `act_dest_id`, `amount`, `action_type`, `expected_total`, `memo`) 
  	VALUES(:p1a1, :p1a2, :p1change, :type, :a1tot, :memo), 
  			(:p2a1, :p2a2, :p2change, :type, :a2tot, :memo)";

    $stmt = $db->prepare($query);
    $stmt->bindValue(":p1a1", $acc1);
    $stmt->bindValue(":p1a2", $acc2);
    $stmt->bindValue(":p1change", $amount);
    $stmt->bindValue(":type", $type);
    $stmt->bindValue(":a1tot", $a1tot+$amount);
    $stmt->bindValue(":memo", $memo);
    //flip data for other half of transaction
    $stmt->bindValue(":p2a1", $acc2);
    $stmt->bindValue(":p2a2", $acc1);
    $stmt->bindValue(":p2change", ($amount*-1));
    $stmt->bindValue(":type", $type);
    $stmt->bindValue(":a2tot", $a2tot-$amount);
    $stmt->bindValue(":memo", $memo);
    $result = $stmt->execute();
    if ($result) {
        flash("Created successfully with id: " . $db->lastInsertId());
    }
    else {
        $e = $stmt->errorInfo();
        flash("Error creating: " . var_export($e, true));
    }

    $stmt = $db->prepare("UPDATE Accounts SET balance = (SELECT SUM(amount) FROM Transactions WHERE Transactions.act_src_id = :id) where id = :id");
    $r = $stmt->execute([
        ":id"=>$acc1
    ]);
    $r = $stmt->execute([
        ":id"=>$acc2
    ]);

    return $result;
}

//end flash
?>
