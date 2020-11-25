<?php require_once(__DIR__ . "/partials/nav.php"); ?>


<?php
$db = getDB();
$id = get_user_id();
$users=[];
$stmt = $db->prepare("SELECT * from Accounts WHERE user_id = :id");
$r = $stmt->execute([":id"=>"$id"]);

if($r){
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>

    <h3>Create a Withdrawal</h3>

    <form method="POST">
        <label>Select Account </label>
        <select name="dest">
            <?php foreach($users as $user): ?>
                <?php if($user["user_id"]==$id): ?>
                <option value="<?= $user['id']; ?>"><?= $user['account_number']; ?></option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>


        <label>Transaction Amount</label>
        <input type="float" min="0.00" name="amount"/>
        <label>Memo</label>
        <input type="text" placeholder-"Optional" name="memo"/>


        <input type="submit" name="save" value="Withdraw"/>
    </form>




<?php
function withdraw($acc1, $acc2, $amount, $memo){
    $db = getDB();
    $query = null;
    $stmt2 = $db->prepare("SELECT id, account_number, user_id, account_type, opened_date, last_updated, balance from Accounts WHERE id like :q");
    $r2 = $stmt2->execute([":q" => "%$query%"]);
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


    if($acc1Total+$amount >= 0){
        $query = "INSERT INTO `Transactions` (`act_src_id`, `act_dest_id`, `amount`, `action_type`, `expected_total`, `memo`) VALUES(:p1a1, :p1a2, :p1change, :type, :acc1Total, :memo), (:p2a1, :p2a2, :p2change, :type, :acc2Total, :memo)";
        $stmt = $db->prepare($query);
        $stmt->bindValue(":p1a1", $acc1);
        $stmt->bindValue(":p1a2", $acc2);
        $stmt->bindValue(":p1change", $amount);
        $stmt->bindValue(":type", "Withdraw");
        $stmt->bindValue(":acc1Total", $acc1Total + $amount);
        $stmt->bindValue(":memo", $memo);
//second half
        $stmt->bindValue(":p2a1", $acc2);
        $stmt->bindValue(":p2a2", $acc1);
        $stmt->bindValue(":p2change", ($amount * -1));
        $stmt->bindValue(":type", "Withdraw");
        $stmt->bindValue(":acc2Total", $acc2Total - $amount);
        $stmt->bindValue(":memo", $memo);
        $result = $stmt->execute();
    if ($result) {
        flash("Created successfully with id: " . $db->lastInsertId());
    }

    else {
        $e = $stmt->errorInfo();
        flash("Error creating: " . var_export($e, true));
    }

    $stmt = $db->prepare("UPDATE Accounts SET balance = (SELECT ifnull(SUM(amount, 0)) FROM Transactions WHERE Transactions.act_src_id = :id WHERE id=:id");
        
    $r = $stmt->execute([":id"=>$acc1]);
    $r = $stmt->execute([":id"=>$acc2]);

   return $result;
    }
    else{
        flash("Cannot withdraw: insufficient funds");
    }
}

?>




<?php


if (isset($_POST["save"])) {
    //TODO add proper validation/checks
    $amount = (float)$_POST["amount"];
    $dest  = $_POST["dest"];
    $memo = $_POST["memo"];
    $user = get_user_id();
    $db = getDB();
    $stmt = $db->prepare("SELECT DISTINCT id from Accounts where account_number = '000000000000'");
    $stmt->execute();
    $result=$stmt->fetch();
    $world = $result["id"];
    if($amount>0){
        withdraw($dest, $world, ($amount * -1), $memo);
    }
    else{
        flash("Withdrawal must be a positive value");
    }
}

?>

<?php require(__DIR__ . "/partials/flash.php");

