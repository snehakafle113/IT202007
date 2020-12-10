<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}
$type = array("Deposit", "Withdraw", "Transfer");
?>

<h3>Filter Results</h3>
    <form method="POST">
        <label>Filter by Type</label>
        <select name = "trans">
            <?php foreach($type as $trans): ?>
                <option value="<?=$trans; ?>"><?=$trans;?></option>
            <?php endforeach; ?>
        </select>

        <label>Filter by Date </label>
        <input type = "text" placeholder="First Date (YYYY-MM-DD)" name = "firstDate">
        <input type = "text" placeholder ="Second Date (YYYY-MM-DD)" name = "secondDate">
        <input type = "submit" name = "search" value = "Search">

    </form>

<?php
if (!is_logged_in()){
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}

$query = "";
$results = [];
if (isset($id)) {
    $db = getDB();
    $userId = get_user_id();
    $page = 1;
    $per_page = 10;
    if (isset($_GET["page"])) {
        try {
            $page = (int)$_GET["page"];
        } catch (Exception $e) {
        }
    }

    $stmt = $db->prepare("SELECT count(*) as total from Transactions WHERE act_src_id like :q ORDER BY id DESC LIMIT 10");
    $r = $stmt->execute([":q" => "%$id%"]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total = 0;
    if ($result) {
        $total = (int)$result["total"];
    }
    $total_pages = ceil($total / $per_page);
    $offset = ($page - 1) * ($per_page);

    $stmt = $db->prepare("SELECT * from Transactions WHERE act_src_id like :q ORDER BY id DESC LIMIT :offset, :count");
    //need to use bindValue to tell PDO to create these as ints
    //otherwise it fails when being converted to strings (the default behavior)
    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
    $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
    $stmt->bindValue(":q", $id);
    $stmt->execute();
    $e = $stmt->errorInfo();
    if ($e[0] != "0000") {
        flash(var_export($e, true), "alert");
    }


    if (isset($_POST["search"]) || isset($_SESSION['filtered'])) {
        if (isset($_POST["trans"])) {
            $_SESSION["transAct"] = $_POST["trans"];
            $actionType = $_SESSION["transAct"];
        } else {
            $actionType = $_SESSION["transAct"];
        }

        $firstDate = "0000-01-01";
        $secondDate = "9999-12-31";

        if (isset($_POST["firstDate"]) || isset($_POST["secondDate"])) {
            if ($_POST["firstDate"] != "" && $_POST["secondDate"] != "") {
                $_SESSION["firstTrans"] = $_POST["firstDate"];
                $_SESSION["secondTrans"] = $_POST["secondDate"];
                $firstDate = $_SESSION["firstTrans"];
                $secondDate = $_SESSION["secondTrans"];
            } elseif (($_POST["firstDate"] != "" && $_POST["secondDate"] == "") || ($_POST["secondDate"] != "" && $_POST["firstDate"] == "")) {
                echo "Please enter valid dates in both fields";
                $_SESSION["firstTrans"] == '0000-01-01';
                $_SESSION["secondTrans"] == '9999-12-31';
                $firstDate = $_SESSION["firstTrans"];
                $secondDate = $_SESSION["secondTrans"];
            }
        } elseif (isset($_SESSION["filtered"])) {
            if ($_SESSION["filtered"]) {
                $firstDate = $_SESSION["firstTrans"];
                $secondDate = $_SESSION["secondTrans"];
            }
        } else {
            $_SESSION["firstTrans"] = '0000-01-01';
            $_SESSION["secondTrans"] = '9999-12-31';
            $firstDate = $_SESSION["firstTrans"];
            $secondDate = $_SESSION["secondTrans"];
        }

        $_SESSION['filtered'] = true;

        if ($actionType != "") {
            $stmt = $db->prepare("SELECT count(*) as total from Transactions WHERE (action_type like :a) AND (act_src_id like :q) AND (created BETWEEN :s and :e) ORDER BY id DESC LIMIT 10");
            $r = $stmt->execute([":q" => "%$id%", ":a" => $actionType, ":s" => $firstDate, ":e" => $secondDate]);
            $results = $stmt->fetch(PDO::FETCH_ASSOC);

            $total = 0;
            if ($results) {
                $total = (int)$results["total"];
            }

            $total_pages = ceil($total / $per_page);
            $offset = ($page - 1) * ($per_page);

            $stmt = $db->prepare("SELECT * from Transactions WHERE (action_type like :a) AND (act_src_id like :q) AND (created BETWEEN :s AND :e) ORDER BY id DESC LIMIT :offset, :count");
            $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
            $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
            $stmt->bindValue(":a", $actionType, PDO::PARAM_INT);
            $stmt->bindValue("s", $firstDate, PDO::PARAM_INT);
            $stmt->bindValue(":e", $secondDate, PDO::PARAM_INT);
            $stmt->bindValue(":q", $id);
        } else {
            $stmt = $db->prepare("SELECT count(*) as total from Transactions WHERE (act_src_id like :q) AND (created BETWEEN :s AND :e) ORDER BY id DESC LIMIT 10");
            $r = $stmt->execute([":q" => "%$id%", ":s" => $firstDate, ":e" => $secondDate]);
            $results = $stmt->fetch(PDO::FETCH_ASSOC);
            $total = 0;
            if ($results) {
                $total = (int)$results["total"];
            }
            $total_pages = ceil($total / $per_page);
            $offset = ($page - 1) * ($per_page);

            $stmt = $db->prepare("SELECT * from Transactions WHERE (act_src_id like :q) AND (created BETWEEN :s AND :e) ORDER BY id DESC LIMIT :offset, :count");
            $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
            $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
            $stmt->bindValue(":s", $firstDate);
            $stmt->bindValue(":e", $secondDate);
            $stmt->bindValue(":q", "$id");
        }
    }

    if (isset($_POST["reset"])) {
        unset($_POST["search"]);
        unset($_SESSION["filtered"]);
        die(header("Location: transaction_history.php?id=$id&page=1"));
    }

    $r = $stmt->execute();
    if ($r) {
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $stmt2 = $db->prepare("SELECT id, account_number, account_type from Accounts WHERE user_id = :userId");
    $r2 = $stmt2->execute([":userId" => $userId]);
    if ($r2) {
        $results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<h3>Transaction History</h3>
<div class="results">
    <label>Filtering Results By: 
    <?php
        if(isset($_SESSION['filtered'])):
            if($_SESSION['filtered']):
                if($actionType !=""):
                    echo $actionType;
                else:
                    echo "All Transactions";
                endif;
            endif;
        else:
            echo "All Transactions";
        endif;
    ?>

    Dates:

    <?php
    if(isset($_SESSION['filtered'])):
        if($_SESSION['filtered']):
            echo $firstDate . " and " . $secondDate;
        endif;
    else:
        echo "0000-01-01 and 9999-12-31";
    endif;
    ?>
    </label>
    
    <?php if (count($results) > 0): ?>
        <div class="list-group">
            <?php foreach ($results as $result): ?>
                <div class="list-group-item">
                    <?php foreach ($results2 as $result2): ?>
                        <?php if ($result2["id"] == $result["act_src_id"]): ?>
                            <div>
                                <div>Account Number:</div>
                                <div><?php safer_echo($result2["account_number"]); ?></div>
                            </div>
                            <div>
                                <div>Account Type:</div>
                                <div><?php safer_echo($result2["account_type"]); ?></div>
                            </div>
                            <div>
                                <div>Balance:</div>
                                <div><?php safer_echo($r["expected_total"]); ?></div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <br>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No results</p>
    <?php endif; ?>
</div>

<form method="POST">
    <nav aria-label="Transaction History">
        <ul class = "pagination justify-content-center">
            <li class = "page-item <?php echo ($page-1) < 1? "disabled":"";?>">
        <a class="page-link" href="?id=<?php echo $id;?>&page=<?php echo $page-1;?>" tabindex="-1">Previous</a>
            </li>
            <?php for ($i = 0; $i<$total_pages; $i++):?>
            <li class = "page-item <?php echo($page-1)==$i?"active":"";?>"><a class = "page-link" href="?id=<?php echo $id; ?> &page=<?php echo ($i+1);?>"><?php echo ($i+1);?></a></li>
        <?php endfor;?>
            <li class="page-item" <?php echo ($page+1)> $total_pages?"disabled":"";?>">
            <a class="page-link" href="?id=<?php echo $id; ?> &page=<?php echo $page+1?>">Next</a>
            </li>
        </ul>
    </nav>

</form>
