
<?php require_once(__DIR__ . "/partials/nav.php"); ?>




<?php


if (!has_role("Admin")) {


    //this will redirect to login and kill the rest of this script (prevent it from executing)


    flash("You don't have permission to access this page");


    die(header("Location: login.php"));


}


?>


<?php


//we'll put this at the top so both php block have access to it


if (isset($_GET["id"])) {


    $id = $_GET["id"];


}


?>


<?php


//saving


if (isset($_POST["save"])) {


    //TODO add proper validation/checks


    $id = $_GET["id"];;


    if ($id % 2 == 1) {


        $id -= 1;


    }


    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM Transactions where id = :id");
    $result = $stmt->execute([":id" => $id]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    $query = null;
    $id2 = $id+1;
    $stmt2 = $db->prepare("SELECT * FROM Transactions WHERE id = :id");
    $result2 = $stmt->execute([":id" => $id2]);
    $r2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($r2 as $s){
        $r2TotExpected = $s["expected_total"];
        $r2Amount = $s["amount"];
        $actDest = $s["act_src_id"];
        $actSource = $s["act_dest_id"];
    }
    $amount = $_POST["amount"];
    $memo = $_POST["memo"];
    $totalExpected = $r["expected_total"] - $r["amount"] + $_POST["amount"];
    $totalExpected2 = $r2TotExpected - $r2Amount - $_POST["amount"];
    $user = get_user_id();
    $db = getDB();


    if (isset($id)) {


        $stmt = $db->prepare("UPDATE Transactions set amount=:amount, expected_total=:totalExpected, memo=:memo where id=:id");


        $r = $stmt->execute([


           ":amount" => $amount,


           ":totalExpected" => $totalExpected,


           ":memo" => $memo,


           ":id" => $id


       ]);
$r = $stmt->execute([
    ":amount" => -$amount,
    ":totalExpected" => $totalExpected2,
    ":memo" => $memo,
    ":id" => $id2
]);


       if ($r) {


           flash("Updated successfully with id: " . $id);


       }


       else {


           $e = $stmt->errorInfo();


           flash("Error updating: " . var_export($e, true));


       }


   }


   else {


       flash("ID isn't set, we need an ID in order to update");


   }


}


?>


<?php


//fetching


$result = [];


if (isset($id)) {


   $id = $_GET["id"];


   $db = getDB();


   $stmt = $db->prepare("SELECT * FROM Transactions where id = :id");


   $r = $stmt->execute([":id" => $id]);


   $result = $stmt->fetch(PDO::FETCH_ASSOC);


}


//get eggs for dropdown


$db = getDB();


$stmt = $db->prepare("SELECT id,action_type from Transactions LIMIT 10");


$r = $stmt->execute();


$users = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>


   <h3>Edit Transaction</h3>


   <form method="POST">


       <label>Amount</label>


       <input name="amount" value="<?php echo $result["amount"]; ?>"/>


       <label>Memo</label>


       <input name="memo" value="<?php echo $result["memo"]; ?>"/>
<!--
<label>Action Type</label>
<select value="<?php echo $result[action_type]; ?>" name="actType">
    <option value="Deposit">Deposit</option>
    <option value="Withdraw">Withdraw</option>
    <option value="Transfer">Transfer</option>
</select>
-->

       <input type="submit" name="save" value="Update"/>


   </form>





<?php require(__DIR__ . "/partials/flash.php");



