<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
	<label>Account Number:</label>
	<br>
	<label>Account Type: Checking</label>
	</br>
<form method="POST">
	<label>Balance</label>
	<input type="float" min="5.0" name="accountBal"/>
	<input type="submit" name="save" value="Create"/>
</form>

<?php
if(isset($_POST["save"])){
	//TODO add proper validation/checks
	for($i = 0; $i < 12; $i++) {
        $accountNum .= mt_rand(0, 9);
        }
	$accountType = "Checking";
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
