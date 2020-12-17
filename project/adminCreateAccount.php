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
    $stmt = $db->prepare("SELECT * from Users");
    $r = $stmt->execute();
    $results = [];
    if ($r) {
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    ?>
<div style="background: #7f94b2; font-size: 20px; padding: 10px; border: 1px solid lightgray; margin: 10px;">
    <h3>Create an Account</h3>
    <label> Select User to Make Account For </label>
    <div class = "form-group">
    <select class = "form-control" name="dest">
    	<?php foreach ($results as $res): ?>
        	<option value="<?= $res["id"]; ?>"><?= $res["id"]; ?></option>
    	<?php endforeach;?>
    </select>
    </div>
    <div class = "form-group">
    <input class = "form-control" type="text" placeholder="Account Type" name="accountType"/>
    </div>
    <div class = "form-group">
    <input class = "form-control" type="float" placeholder="Balance" min="0.00" name="balance"/>
    </div>
    <input class = "btn btn-primary" type="submit" name="save" value="Create"/>
    </form>
</div>

<?php
if(isset($_POST["save"])){
    //TODO add proper validation/checks
    $accountNum = rand(000000000001, 999999999999);
    for ($i = strlen($accountNum); $i < 12; $i++) {
        $accountNum = ("0" . $accountNum);
    }
    $accountType = $_POST["accountType"];
    $user = $_POST["dest"];
    $balance = $_POST["balance"];
    $db = getDB();
    $APY= 0.05;
    if ($balance >= 5) {
        do {
            $accountNum = rand(000000000000, 999999999999);
            for ($j = strlen($accountNum); $j < 12; $j++) {
                $accountNum = ("0" . $accountNum);
            }

            if($accountType=='Checking') {
                $stmt = $db->prepare("INSERT INTO Accounts(account_number, account_type, user_id, balance) VALUES(:accountNum, :accountType, :user, :balance)");
                $r = $stmt->execute([
                    ":accountNum" => $accountNum,
                    ":accountType" => $accountType,
                    ":user" => $user,
                    ":balance" => $balance
                ]);
            }
            else{
                $stmt = $db->prepare("INSERT INTO Accounts(account_number, account_type, user_id, balance, APY) VALUES(:accountNum, :accountType, :user, :balance, :APY)");
                $r = $stmt->execute([
                    ":accountNum" => $accountNum,
                    ":accountType" => $accountType,
                    ":user" => $user,
                    ":APY"=>$APY,
                    ":balance" => $balance
                ]);
            }

            $error = $stmt->errorInfo();
        } while ($error[0] == "23000");

        if($accountType=='Savings'){
            $months = 1;
            $lastId = $db->lastInsertId();
            $stmt = $db->prepare("UPDATE Accounts set nextAPY = TIMESTAMPADD(MONTH, :months, opened_date) WHERE id = :id");
            $r = $stmt->execute([":id"=>$lastId, ":months"=>$months]);
        }
        if ($r) {
            $lastId = $db->lastInsertId();
            flash("Checking account created successfully: " . $accountNum . "for user #" . $user);
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
        $stmt->bindValue(":acc1Total", $acc1Total-$balance);
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
    }
    else
    {
        flash('Balance must be at least $5. Please try again.');
    }
}
?>
    </div>
<?php require(__DIR__ . "/partials/flash.php");
