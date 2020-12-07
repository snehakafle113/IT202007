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

	<br> 
        <label>Transaction Amount</label>
        <input type="float" min="0.00" name="amount"/>
	<br>    
        <label>Memo</label>
        <input type="text" placeholder-"Optional" name="memo"/>

	<br>
        <input type="submit" name="save" value="Withdraw"/>
    </form>

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
    $results=$stmt->fetch();
    $world = $results["id"];

    $stmt2 = $db->prepare("SELECT balance FROM Accounts WHERE id = :id");
    $r2 = $stmt2->execute([":id"=>$dest]);
    $results = $stmt2->fetch(PDO::FETCH_ASSOC);
    $acc1Total = $results["balance"];

    if ($amount >= 1) {
        if ($amount < $acc1Total) {
            do_transaction($dest, $world, ($amount * -1), "Withdraw", $memo);
        }
        elseif ($amount > $acc1Total){
            flash("Error: Insufficient Funds.");
        }
    }
    else {
        flash("Please enter a positive value.");
    }
}
?>

<?php require(__DIR__ . "/partials/flash.php");
