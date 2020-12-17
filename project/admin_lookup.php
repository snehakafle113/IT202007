<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>

<?php
    $db = getDB();
    $stmt = $db->prepare("SELECT * from Users");
    $r = $stmt->execute();
    $results=[];
    if($r)
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $F_L_name = null;

        if(isset($_POST["lookup"])){
            $F_L_name = $_POST['name'];

        }

        $accountNum = null;

        if(isset($_POST["lookupUser"])){
            $accountNum = $_POST['accountNum'];
        }

    $stmt2 = $db->prepare("SELECT * from Accounts where account_number = :q");
    $r2 = $stmt2->execute([":q" => $accountNum]);
    $results2=[];
    if($r2) {
        $results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }

    ?>
<div style="background: #7f94b2; font-size: 20px; padding: 10px; border: 1px solid lightgray; margin: 10px;">
        <label>Search for Users</label>
        <div class="results">
            <?php if (count($results) > 0): ?>
                <div class="list-group">
                    <?php foreach ($results as $r): ?>
                        <?php if($F_L_name == ''): ?>
                            <div class="list-group-item">
                                <div>
                                    <div>User Info:</div>
                                    <a type="button" href="profile.php?id=<?php safer_echo($r['id']); ?>"><?php safer_echo($r['first_name'] . " " . $r['last_name'] . " (" . $r['username'] . ")"); ?></a>
                                </div>
                                <br>
                            </div>
                        <?php elseif($r['first_name'] == $F_L_name || $r['last_name'] == $F_L_name): ?>
                            <div class="list-group-item">
                                <div>
                                    <div>User Info:</div>
                                    <a type="button" href="profile.php?id=<?php safer_echo($r['id']); ?>"><?php safer_echo($r['first_name'] . " " . $r['last_name'] . " (" . $r['username'] . ")"); ?></a>
                                </div>
                                <br>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No results</p>
            <?php endif; ?>
        </div>
        <br>
	<div style="background: #7f94b2; font-size: 20px; padding: 10px; border: 1px solid lightgray; margin: 10px;">
        <label>Search for Account</label>
        <div class="results">
            <?php if (count($results2) > 0): ?>
                <div class="list-group">
                    <?php foreach ($results2 as $res2): ?>
                        <div class="list-group-item">
                            <div>
                                <div>Account Number: <?php safer_echo($res2['accountNum']); ?> </div>
                                <a type="button" href="profile.php?id=<?php safer_echo($r['id']); ?>"><?php safer_echo($res2['id']); ?>">Transaction History</a>
                            </div>
                            <br>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No results</p>
            <?php endif; ?>
        </div>
    </div>

        <form method="POST">
	    <div class = "form-group">
            <label>Search Users</label>
            <br>
            <input class = "form-control" type="text" placeholder="Enter First or Last Name" name="name"/>
            </div>
	    <input type="submit" name="lookup" value="Lookup"/>
            <br>
	    <div class = "form-group">
            <label>Search Accounts</label>
            <input class = "form-control" type="int" maxlength="12" placeholder="Account Number" name="accountNum"/>
            <input type="submit" name="lookupUser" value="Lookup"/>
            </div>
	</form>

</div>

<?php require(__DIR__ . "/partials/flash.php");
