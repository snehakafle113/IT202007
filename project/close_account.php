<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php $db = getDB();
$users=[];
$id=get_user_id();
$stmt=$db->prepare("SELECT * from Accounts WHERE active = 'active' AND user_id = :id");
$r = $stmt->execute([":id"=>$id]);
if($r){
    $users=$stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<?php
if(isset($_POST["save"])){
    $user=get_user_id();
    $accountSrc=$_POST["accountSrc"];
    $stmt=$db->prepare("SELECT * from Accounts WHERE active = 'active' AND id like :q LIMIT 1");
    $r = $stmt->execute([":q"=>$accountSrc]);
    if($r){
        $results=$stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else{
        flash("There was a problem fetching the results. Please try again.");
    }

    foreach($results as $res){
        if($res["balance"]==0){
            $stmt=$db->prepare("UPDATE Accounts set active='inactive' WHERE active = 'active' AND id = :id");
            $r = $stmt->execute([":id"=>$accountSrc]);
            if($r){
                flash("Account successfully closed.");
                die(header("Location: close_account.php"));
            }
            else{
                flash("Error closing account.");
            }
        }
        else{
            flash("Your account still has a balance of $" . $res['balance'] . "! Please withdraw or transfer your balance and try again.");
        }
    }
}
?>

<h3>Close an Account</h3>
<form method="POST">
    <div class = "form-group">
    <label>Which Account Would You Like To Close?</label>
    <select class = "form-control" name="accountSrc">
        <?php foreach($users as $u):?>
            <option value = "<?= $u['id'];?>"><?=$u['account_number'];?></option>
        <?php endforeach; ?>
    </select>
    </div>
    <div class = "form-group">
        <input class = "btn btn-primary" type="submit" name="save" value="Close Account"/>
    </div>
</form>
<?php require(__DIR__ . "/partials/flash.php");

