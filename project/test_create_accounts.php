<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>

<form method="POST">
	<label>Account Number</label>
	<input type="text" min="12" name="accountNum"/>
	<label>Account Type</label>
	<input type = "text" name="accountType"/>
	<label>Balance</label>
	<input type="float" min="0.0" name="accountBal"/>
	<input type="submit" name="save" value="Create"/>
</form>

<?php
if(isset($_POST["save"])){
	//TODO add proper validation/checks
	$accountNum = $_POST["accountNum"];
	$accountType = $_POST["accountType"];
	$accountBal = $_POST["accountBal"];
	$user = get_user_id();
	$db = getDB();
	$stmt = $db->prepare("INSERT INTO Accounts(account_number, account_type, user_id, balance) VALUES(:accountNum, :accountType, :user, :accountBal)");
	$r = $stmt->execute([
		":accountNum"=>$accountNum,
		":accountType"=>$accountType,
		":user"=>$user,
                ":accountBal"=>$accountBal
	]);

	if($r){
		flash("Created successfully with id: " . $db->lastInsertId());
	}
	else{
		$e = $stmt->errorInfo();
		flash("Error creating: " . var_export($e, true));
	}
}
?>
<?php require(__DIR__ . "/partials/flash.php");
