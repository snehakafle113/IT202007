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
$stmt = $db->prepare("SELECT * from Accounts WHERE active = 'active' AND frozen = 'false' AND (account_number != '000000000000')");
$r = $stmt->execute();
$users = [];
if ($r) {
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$stmt = $db->prepare("SELECT * from Accounts WHERE active = 'active' AND frozen = 'true'");
$r = $stmt->execute();
$users2 = [];
if ($r) {
    $users2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (isset($_POST["freezeAcc"])) {
    $frozen = $_POST['accountFreeze'];
    $stmt = $db->prepare("UPDATE Accounts set frozen = 'true' where active = 'active' and id = :id");
    $r = $stmt->execute([":id" => $frozen]);
    if ($r) {
        flash("Account was frozen");
        die(header("Location: freeze_account_admin.php"));
    }
    else {
        flash("Error updating account");
    }
}

if (isset($_POST["unfreezeAcc"])) {
    $not_frozen = $_POST['accountUnfreeze'];
    $stmt = $db->prepare("UPDATE Accounts set frozen = 'false' where active = 'active' and id = :id");
    $r = $stmt->execute([":id" => $not_frozen]);
    if ($r) {
        flash("Account was unfrozen");
        die(header("Location: adminFreeze.php"));
    }
    else {
        flash("Error updating account");
    }
}
?>
<div style="background: #7f94b2; font-size: 20px; padding: 10px; border: 1px solid lightgray; margin: 10px;">
    <form method="POST">
    <div class = "form-group">
    <label>Which Account Would You Like to Freeze?</label>
    <br>
    <select class = "form-control" name="accountFreeze">
        <?php foreach($users as $u): ?>
            <option value="<?= $u['id']; ?>"><?= $u['account_number']; ?></option>
        <?php endforeach; ?>
    </select>
    </div>
    <input class = "btn btn-primary" type="submit" name="freezeAcc" value="Freeze"/>
    <br>
    <div class = "form-group">
    <label>Which Account Would You Like to Unfreeze?</label>
    <select class = "form-control" name="accountUnfreeze">
        <?php foreach($users2 as $u2): ?>
            <option value="<?= $u2['id']; ?>"><?= $u2['account_number']; ?></option>
        <?php endforeach; ?>
    </select>
    </div>
    <input class = "btn btn-primary" type="submit" name="unfreezeAcc" value="Unfreeze"/>
    </form>
</div>
<?php require(__DIR__ . "/partials/flash.php");
