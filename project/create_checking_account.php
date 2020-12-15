<?php require_once(__DIR__ . "/partials/nav.php"); ?>

    <form method="POST">
        <br>
        <label>Create a Checking Account</label>
        <br></br>
        <div class = "form-group">
            <label>Balance</label>
            <input class = "form-control" type="float" min="5.0" name="accountBal"/>
            <br>
        </div>
        <input class = "btn btn-primary" type="submit" name="save" value="Create"/>
    </form>

<?php
if(isset($_POST["save"])) {
    //TODO add proper validation/checks
    $accountNum = rand(000000000001, 999999999999);
    for ($i = strlen($accountNum); $i < 12; $i++) {
        $accountNum = ("0" . $accountNum);
    }
    $accountType = "Checking";
    $user = get_user_id();
    $db = getDB();
    $accountBal = $_POST["accountBal"];
    if ($accountBal >= 5) {
        do {
            $accountNum = rand(000000000000, 999999999999);
            for ($j = strlen($accountNum); $j < 12; $j++) {
                $accountNum = ("0" . $accountNum);
            }

            $stmt = $db->prepare("INSERT INTO Accounts(account_number, account_type, user_id, balance) VALUES(:accountNum, :accountType, :user, :accountBal)");
            $r = $stmt->execute([
                ":accountNum" => $accountNum,
                ":accountType" => $accountType,
                ":user" => $user,
                ":accountBal" => 0
            ]);

            $error = $stmt->errorInfo();
        } while ($error[0] == "23000");

        if ($r) {
            $lastId = $db->lastInsertId();
            flash("Checking account created successfully: " . $accountNum);
        } else {
            $error = $stmt->errorInfo();
            flash("Error creating: " . var_export($error, true));
        }

        $query = null;
        $stmt2 = $db->prepare("SELECT id, account_number, user_id, account_type, opened_date, last_updated, balance from Accounts WHERE id like :q");
        $r2 = $stmt2->execute([":q" => "%$query%"]);
        if ($r2) {
            $results = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        }
        $acc1Total = null;
        foreach($results as $r)
        {
            if($r["id"] == 0)
                $acc1Total = $r["balance"];
        }

        $query = "INSERT INTO `Transactions` (`act_src_id`, `act_dest_id`, `amount`, `action_type`, `expected_total`) 
	VALUES(:p1a1, :p1a2, :p1change, :type, :acc1Total), 
			(:p2a1, :p2a2, :p2change, :type, :acc2Total)";

        $stmt = $db->prepare($query);
        $stmt->bindValue(":p1a1", 0);
        $stmt->bindValue(":p1a2", $lastId);
        $stmt->bindValue(":p1change", ($balance*-1));
        $stmt->bindValue(":type", "Deposit");
        $stmt->bindValue(":acc1Total", $acc1Total-$accountBal);
        //second half
        $stmt->bindValue(":p2a1", $lastId);
        $stmt->bindValue(":p2a2", 0);
        $stmt->bindValue(":p2change", $balance);
        $stmt->bindValue(":type", "Deposit");
        $stmt->bindValue(":acc2Total", $balance);
        $result = $stmt->execute();
        if ($result) {
            flash("Your transaction was created successfully with id: " . $db->lastInsertId());
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
        flash('Balance must be at least $5. Please try again.');
    }


}
?>

<?php require(__DIR__ . "/partials/flash.php");
