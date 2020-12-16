<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
    $db=getDB();
    $users = [];
    $id=get_user_id();
    $stmt=$db->prepare("SELECT * from Accounts WHERE user_id=:id");
    $r=$stmt->execute([":id"=>$id]);
    if($r){
        $users=$stmt->fetchAll(PDO::FETCH_ASSOC);
    }
?>
<div style="background: #7f94b2; font-size: 20px; padding: 10px; border: 1px solid lightgray; margin: 10px;">
    <form method="POST">
        <br>
        <h3>Take Out a Loan</h3>
        <br>
        <div class = "form-group">
	    <p>The APY for this loan is 5%.</p>
            <label><b>Loan Amount</b></label>
            <input class = "form-control" type="float" min="500.0" name="accountBal"/>
            <br>
        </div>
        <div class = "form-grouo">
            <label><b>Select an Account to Deposit to</b></label>
	 <select class = "form-control" name="accountSrc">
            <?php foreach($users as $user): ?>
                <?php if($user["user_id"]==$id): ?>
                    <option value="<?= $user['id']; ?>"><?= $user['account_number']; ?></option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>
        </div>
        <input class = "btn btn-primary" type="submit" name="save" value="Create"/>
    </form>
</div>
<?php
if(isset($_POST["save"])) {
    //TODO add proper validation/checks
    $accountNum = rand(000000000001, 999999999999);
    for ($i = strlen($accountNum); $i < 12; $i++) {
        $accountNum = ("0" . $accountNum);
    }
    $accountType = "Loan";
    $db = getDB();
    $user = get_user_id();
    $accountBal = $_POST["accountBal"];
    $accountBal = (-1)*$accountBal;
    $APY= 0.05;
    if ($accountBal >= 500) {
        do {
            $stmt = $db->prepare("INSERT INTO Accounts(account_number, account_type, user_id, balance, APY) VALUES(:accountNum, :accountType, :user, :accountBal, :APY)");
            $r = $stmt->execute([
                ":accountNum" => $accountNum,
                ":accountType" => $accountType,
                ":user" => $user,
                ":accountBal" => $accountBal,
                ":APY"=>$APY,
            ]);
            $accountNum = rand(000000000000, 999999999999);
            for ($j = strlen($accountNum); $j < 12; $j++) {
                $accountNum = ("0" . $accountNum);
            }

            $error = $stmt->errorInfo();
        } while ($error[0] == "23000");

        if ($r) {
            $lastId = $db->lastInsertId();
            flash("Savings account created successfully. Your account number is: " . $accountNum);
        } else {
            $error = $stmt->errorInfo();
            flash("Error creating: " . var_export($error, true));
        }

        $accountSrc = $_POST["accountSrc"];
        $query = null;
        $stmt2 = $db->prepare("SELECT id, account_number, user_id, account_type, opened_date, last_updated, balance from Accounts WHERE id like :q");
        $r2 = $stmt2->execute([":q" => "%$query%"]);
        if ($r2) {
            $results = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        }

        foreach($results as $r)
        {
            if($r["id"] == $accountSrc)
                $acc2Total = $r["balance"];
        }

        $query = "INSERT INTO `Transactions` (`act_src_id`, `act_dest_id`, `amount`, `action_type`, `expected_total`) 
	VALUES(:p1a1, :p1a2, :p1change, :type, :acc1Total), 
			(:p2a1, :p2a2, :p2change, :type, :acc2Total)";

        $stmt = $db->prepare($query);
        $stmt->bindValue(":p1a1", $lastId);
        $stmt->bindValue(":p1a2", $accountSrc);
        $stmt->bindValue(":p1change", $accountBal);
        $stmt->bindValue(":type", "Deposit");
        $stmt->bindValue(":acc1Total", $accountBal);
        //second half
        $stmt->bindValue(":p2a1", $accountSrc);
        $stmt->bindValue(":p2a2", $lastId);
        $stmt->bindValue(":p2change", $accountBal);
        $stmt->bindValue(":type", "Deposit");
        $stmt->bindValue(":acc2Total", $acc2Total+$accountBal);
        $result = $stmt->execute();
        if ($result) {
            flash("Loan successfully created.");
        }
        else {
            $e = $stmt->errorInfo();
            flash("Sorry, there was an error creating: " . var_export($e, true));
        }
        $stmt = $db->prepare("UPDATE Accounts SET balance = (SELECT SUM(amount) FROM Transactions WHERE Transactions.act_src_id = Accounts.id) where id = :id");
        $r = $stmt->execute();
        die(header("Location: list_accounts.php"));
    }
    else
    {
        flash('Loan must be at least $500. Please try again.');
    }


}
?>

<?php require(__DIR__ . "/partials/flash.php");
