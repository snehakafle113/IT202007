<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
$db = getDB();
$id = get_user_id();
$users = [];
$stmt = $db->prepare("SELECT * FROM Accounts WHERE user_id = :id");
$r = $stmt->execute([":id" => "$id"]);
if ($r) {
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

    <h3>Transfer Funds</h3>
    <form method="POST">

        <label>Transfer From</label>
        <select name="AccountSrc">
            <?php foreach($users as $user): ?>
                <option value="<?= $user['id']; ?>"><?= $user['account_number']; ?></option>
            <?php endforeach; ?>
        </select>

        <label>Transfer To</label>
        <select name="AccountDest">
                <?php foreach($users as $user): ?>
                    <option value="<?= $user['id']; ?>"><?= $user['account_number']; ?></option>
                <?php endforeach; ?>
        </select>

        <label>Transaction Amount</label>
        <input type="float" min="0.00" name="amount"/>

        <label>Memo</label>
        <input type="text" placeholder-"Optional" name="memo"/>

        <input type="submit" name="save" value="Transfer"/>


    </form>

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
    else {
        flash("Please enter a positive value.");
    }
}

?>

<?php require(__DIR__ . "/partials/flash.php");
