<?php require_once(__DIR__ . "/partials/nav.php"); ?>
        <?php
        $db = getDB();
        $id = get_user_id();
        $users = [];
        $stmt = $db->prepare("SELECT * FROM Accounts WHERE (account_type != 'Loan') AND active = 'active' AND frozen = 'false' AND user_id = :id");
        $r = $stmt->execute([":id" => "$id"]);
        if ($r) {
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        ?>
<div style="background: #7f94b2; font-size: 20px; padding: 10px; border: 1px solid lightgray; margin: 10px;">
        <h3>Make a Deposit</h3>
	 <form method="POST">
	    <div class = "form-group">
            <label>Select Account</label>
            <br>
            <select class = "form-control" name="source">
                <?php foreach($users as $user): ?>
                    <option value="<?= $user["id"]; ?>"><?= $user["account_number"]; ?></option>
                <?php endforeach; ?>
            </select>
            <br>
	    </div>
	    <div class = "form-group">
            <label>Enter Amount</label>
            <br>
            <input class = "form-control" type="float" min="0.00" name="amount"/>
            <br>
	    </div>
	    <div class = "form-group">
            <label>Memo</label>
            <br>
            <input class = "form-control" type="text" placeholder="Optional" name="memo"/>
            <br>
	    </div>
            <input class = "btn btn-primary" type="submit" name="save" value="Deposit"/>
        </form>
</div>
        <?php
        if (isset($_POST["save"])) {
            $amount = (float)$_POST["amount"];
            $source = $_POST["source"];
            $memo = $_POST["memo"];
            $user = get_user_id();
            $db = getDB();
            $stmt = $db->prepare("SELECT DISTINCT id from Accounts where account_number = '000000000000'");
            $stmt->execute();
            $result=$stmt->fetch();
            $world = $result["id"];
            if ($amount > 0) {
                do_transaction($world, $source, ($amount * -1), $memo, "Deposit");
            }
            else {
                flash("Error: Amount must be positive.");
            }
        }
        ?>
<?php require(__DIR__ . "/partials/flash.php");
