<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
$db = getDB();
$id = get_user_id();
$users = [];
$stmt = $db->prepare("SELECT * FROM Accounts WHERE active='active' AND user_id = :id");
$r = $stmt->execute([":id" => "$id"]);
if ($r) {
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<div style="background: #7f94b2; font-size: 20px; padding: 10px; border: 1px solid lightgray; margin: 10px;">
    <h3>Transfer Funds</h3>
    <form method="POST">
	<div class = "form-group">
        <label>Transfer From</label>
        <select class = "form-control" name="AccountSrc">
            <?php foreach($users as $user): ?>
                <option value="<?= $user['id']; ?>"><?= $user['account_number']; ?></option>
            <?php endforeach; ?>
        </select>
	</div>
	<div class = "form-group">
        <label>Transfer To</label>
        <select class = "form-control" name="AccountDest">
                <?php foreach($users as $user): ?>
                    <option value="<?= $user['id']; ?>"><?= $user['account_number']; ?></option>
                <?php endforeach; ?>
        </select>
	</div>
	<div class = "form-group">
        <label>Transaction Amount</label>
        <input class = "form-control" type="float" min="0.00" name="amount"/>
	</div>
	<div class = "form-group">
        <label>Memo</label>
        <input class = "form-control" type="text" placeholder-"Optional" name="memo"/>
	</div>
        <input class = "btn btn-primary" type="submit" name="save" value="Transfer"/>


    </form>
</div>
<?php


if (isset($_POST["save"])) {


    //TODO add proper validation/checks


    $amount = (float)$_POST["amount"];
    
	
    $AccountSrc = $_POST["AccountSrc"];


    $AccountDest = $_POST["AccountDest"];

    $memo = $_POST["memo"];

    $user = get_user_id();
    $db = getDB();
    $stmt = $db->prepare("SELECT balance FROM Accounts WHERE id = :id");
    $r = $stmt->execute([":id"=>$AccountSrc]);
    $results = $stmt->fetch(PDO::FETCH_ASSOC);
    $srcTotal = $results["balance"];

    if ($amount >= 1) {
        if ($amount < $srcTotal) {
            do_transaction($AccountSrc, $AccountDest, ($amount * -1), $memo, "Transfer");
        }
        elseif ($amount > $srcTotal){
            flash("Error: Insufficient Funds in source account.");
        }
    }
 	elseif ($acount_type=="Loan"){
		flash("Error: Cannot transfer from a loan.");
	}
        else {
            flash("Please enter a positive value.");
    }
}

?>

<?php require(__DIR__ . "/partials/flash.php");
