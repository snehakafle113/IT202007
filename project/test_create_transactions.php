
<?php require_once(__DIR__ . "/partials/nav.php"); ?>




<?php


if (!has_role("Admin")) {


    //this will redirect to login and kill the rest of this script (prevent it from executing)


    flash("You don't have permission to access this page");


    die(header("Location: login.php"));


}


?>

<?php
$db = getDB();
$sql = "SELECT DISTINCT id, account_number from Accounts";
$stmt = $db->prepare($sql);
$stmt->execute();
$users=$stmt->fetchAll();
?>


<h3>Create a Transaction</h3>


<form method="POST">


    <label> Select Transaction Type </label>


    <select name="transType" id ="selection" onchange="transFunct()">


        <option value="Deposit">Deposit</option>
        <option value= "Withdraw">Withdraw</option>
        <option value="Transfer">Transfer</option>
    </select>


    <label>Select Account </label>


    <select name="source">
        <?php foreach($users as $user): ?>
            <option value="<?= $user['id']; ?>"><?= $user['account_number']; ?></option>
        <?php endforeach; ?>
    </select>


    <label>Transaction Amount</label>
    <input type="float" min="0.00" name="amount"/>
    <label>Memo</label>
    <input type="text" placeholder-"Optional" name="memo"/>

    <div id="transfer_chosen">
        <label>Transfer To</label>
        <select name="transTo">
            <?php foreach($users as $user): ?>
                <option value="<?= $user['id']; ?>"><?= $user['account_number']; ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <script>
        document.getElementById("transfer_chosen").style.display = "none";
        function transFunct() {
            var choice = document.getElementById("transType”).value;
            if (choice == "Deposit”){
                document.getElementById("transfer_chosen").style.display = "none";
            }
            if (choice == "Withdraw”){
                document.getElementById("transfer_chosen").style.display= "none”;
            }
        else{
                document.getElementById("transaction_chosen").style.display = "inline";
            }
        }
    </script>
    <input type="submit" name="save" value="Create Transaction"/>


</form>




<?php
function do_transaction($acc1, $acc2, $amount, $type, $memo){
    $db = getDB();
    $sql = "SELECT id, balance from Accounts WHERE id = :a1 or id = :a2";
    $stmt2 = $db->prepare($sql);
    $r2 = $stmt2->execute([":a1"=>$acc1, ":a2"=>$acc2]);
    if ($r2){
        $results = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }
    $acc1Total = null;
    $acc2Total= null;
    foreach($results as $r){
        if($acc1 == $r["id"])
            $acc1Total = $r["balance"];
        if($acc2 == $r["id"])
            $acc2Total = $r["balance"];
    }
    $query = "INSERT INTO `Transactions` (`act_src_id`, `act_dest_id`, `amount`, `action_type`, `expected_total`, `memo`) VALUES(:p1a1, :p1a2, :p1change, :type, :acc1Total, :memo), (:p2a1, :p2a2, :p2change, :type, :acc2Total, :memo)";
    $stmt = $db->prepare($query);
    $stmt->bindValue(":p1a1", $acc1);
    $stmt->bindValue(":p1a2", $acc2);
    $stmt->bindValue(":p1change", $amount);
    $stmt->bindValue(":type", $type);
    $stmt->bindValue(":acc1Total", $acc1Total+$amount);
    $stmt->bindValue(":memo", $memo);
//second half
    $stmt->bindValue(":p2a1", $acc2);
    $stmt->bindValue(":p2a2", $acc1);
    $stmt->bindValue(":p2change", ($amount*-1));
    $stmt->bindValue(":type", $type);
    $stmt->bindValue(":acc2Total", $acc2Total-$amount);
    $stmt->bindValue(":memo", $memo);
    $result = $stmt->execute();

    if ($result) {
        flash("Created successfully with id: " . $db->lastInsertId());
    }

    else {
        $e = $stmt->errorInfo();
        flash("Error creating: " . var_export($e, true));
    }

    $stmt = $db->prepare("UPDATE Accounts set balance = :balance where id=:id");
    $r = $stmt->execute([
        ":balance"=>($acc1Total+$amount), ":id"=>$acc1
    ]);
    $r = $stmt->execute([
        ":balance"=>($acc2Total-$amount), ":id"=>$acc2
    ]);

    return $result;
}

?>




<?php


if (isset($_POST["save"])) {


    //TODO add proper validation/checks


    $amount = (float)$_POST["amount"];


    $source  = $_POST["source"];


    $transTo = $_POST["transTo"];


    $transType = $_POST["transType"];
    $memo = $_POST["memo"];


    $user = get_user_id();
    $db = getDB();
    $sql = "SELECT DISTINCT id from Accounts where account_number = '000000000000'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $result=$stmt->fetch();
    $world = $result["id"];

    switch($transType)
    {
        case "Deposit":
            do_transaction($source, $world, ($amount * -1), $transType, $memo);
            break;
        case "Withdraw":
            do_transaction($world, $source, ($amount * -1), $transType, $memo);
            break;
        case "Transfer":
            do_transaction($world, $transTo, ($amount * -1), $transType, $memo);
            break;    
    }  

}

    ?>

    <?php require(__DIR__ . "/partials/flash.php");



