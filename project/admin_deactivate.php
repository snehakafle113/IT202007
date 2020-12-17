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
$stmt = $db->prepare("SELECT * from Users WHERE deactivated = 'false' AND (username != 'World')");
$r = $stmt->execute();
$users = [];
if ($r) {
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$users2 = [];
$stmt = $db->prepare("SELECT * from Users WHERE deactivated = 'true'");
$r = $stmt->execute();
if ($r) {
    $users2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (isset($_POST["save"])) {
    $deactivate = $_POST['deactivate'];
    $stmt = $db->prepare("UPDATE Users set deactivated = 'true' where id = :id");
    $r = $stmt->execute([":id" => $deactivate]);
    if ($r) {
        flash("Success: Selected User Was Deactivated!");
    }
    else {
        flash("Error deactivating this user. Please try again.");
    }
}

?>

    <form method="POST">
    <label>Select a User to Deactivate</label>
    <div class = "form-group">
    <select class = "form-control" name="deactivate">
        <?php foreach($users as $user): ?>
            <option value="<?= $user['id']; ?>"><?= $user['username']; ?></option>
        <?php endforeach; ?>
    </select>
    </div>
    <input class = "btn btn-primary" type="submit" name="save" value="Deactivate"/>


<?php require(__DIR__ . "/partials/flash.php");
