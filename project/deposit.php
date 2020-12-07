<?php require_once(__DIR__ . "/partials/nav.php"); ?>
    <div class="shiftRight">
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
        <h3>Make a Deposit</h3>
        <form method="POST">
            <label>Select Account</label>
            <br>
            <select name="source">
                <?php foreach($users as $user): ?>
                    <option value="<?= $user["id"]; ?>"><?= $user["account_number"]; ?></option>
                <?php endforeach; ?>
            </select>
            <br>
            <label>Enter Amount</label>
            <br>
            <input type="float" min="0.00" name="amount"/>
            <br>
            <label>Memo</label>
            <br>
            <input type="text" placeholder="Optional" name="memo"/>
            <br>
            <input type="submit" name="save" value="Deposit"/>
        </form>

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
                do_transaction($world, $source, ($amount * -1), "Deposit", $memo);
            }
            else {
                flash("Error: Amount must be positive.");
            }
        }
        ?>
    </div>
<?php require(__DIR__ . "/partials/flash.php");
