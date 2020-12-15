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
<div style="background: #7f94b2; font-size: 20px; padding: 10px; border: 1px solid lightgray; margin: 10px;">
    <h3>Transfer With Other Users</h3>
    <form method="POST">
        <div class = "form-group">
            <label><b>Transfer From</b></label>
            <select class = "form-control" name="AccountSrc">
                <?php foreach($users as $user): ?>
                    <option value="<?= $user['id']; ?>"><?= $user['account_number']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class = "form-group">
            <label><b>Transfer To</b></label>
            <br>
            <label>Enter the last 4 digits of the receiving account: </label>
            <input class = "form-control" name = "AccountDest" maxlength = "4">
            </div>
        <div class = "form-group">
            <label>Enter the last name of the receiving user:</label>
            <input class = "form-control" type = "text" name = "lastName">
        </div>
        <div class = "form-group">
            <label><b>Transfer Amount</b></label>
            <input class = "form-control" type="float" min="0.00" name="amount"/>
        </div>
        <div class = "form-group">
            <label><b>Memo</b></label>
            <input class = "form-control" type="text" placeholder-"Optional" name="memo"/>
        </div>
        <input class = "btn btn-primary" type="submit" name="save" value="Transfer"/>


    </form>
</div>
<?php


if (isset($_POST["save"])) {


    //TODO add proper validation/checks


    $query = "";

    $amount = (float)$_POST["amount"];


    $AccountSrc = $_POST["AccountSrc"];
    
    $AccountDest = $_POST["AccountDest"];

    $lastName = $_POST["lastName"];

    $memo = $_POST["memo"];

    $user = get_user_id();

    $isValid = false;
    $stmt = $db->prepare("SELECT * from Users WHERE id like :q");
    $r = $stmt->execute([":q" => "%$query%"]);
    if ($r) {
        $results = $stmt->FetchAll(PDO::FETCH_ASSOC);
    }

    foreach ($results as $result) {
        if ($result["last_name"] == $lastName) {
            $current_user_id = $result["id"];
            $stmt2 = $db->prepare("SELECT * from Accounts WHERE user_id like :q");
            $r2 = $stmt2->execute([":q" => "%$current_user_id%"]);
            if ($r) {
                $results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            }
            foreach ($results2 as $other) {
                if (substr($other["account_number"], 8, 12) == $AccountDest) {
                    $isValid = true;
                    $AccountDest = $other["id"];
                    break;
                }
            }
            if (strlen($AccountDest > 4)) {
                break;
            }
        }
}
        if ($isValid) {
            if ($amount >= 1) {
                if ($AccountSrc != $AccountDest) {
                    do_transaction($AccountSrc, $AccountDest, ($amount * -1), $memo, "Ext-Transfer");
                }
            }
            else {
                if ($amount < 1) {
                    flash("Error: Please enter a positive value.");
                }
                if ($AccountSrc == $AccountDest) {
                    flash("Error: Can't transfer to the same account. Please enter another account.");
                }
            }
        }
        else {
            flash("Error: Account not found. Please try again.");
        }
}

?>

<?php require(__DIR__ . "/partials/flash.php");
