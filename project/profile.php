<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//Note: we have this up here, so our update happens before our get/fetch
//that way we'll fetch the updated data and have it correctly reflect on the form below
//As an exercise swap these two and see how things change
if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}
$id=get_user_id();
if(isset($_GET["id"])){
    $id = $_GET["id"];
}

if(isset($id)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * from Users WHERE id = :id LIMIT 1");
    $stmt->execute([":id" => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $vis = $result['privacy'];

//save data if we submitted the form
    if (isset($_POST["saved"])) {
        $isValid = true;
        //check if our email changed
        $newEmail = get_email();
        if (get_email() != $_POST["email"]) {
            //TODO we'll need to check if the email is available
            $email = $_POST["email"];
            $stmt = $db->prepare("SELECT COUNT(1) as InUse from Users where id = :id");
            $stmt->execute([":id" => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $inUse = 1;//default it to a failure scenario
            if ($result && isset($result["InUse"])) {
                try {
                    $inUse = intval($result["InUse"]);
                } catch (Exception $e) {

                }
            }
            if ($inUse > 0) {
                flash("Email already in use");
                //for now we can just stop the rest of the update
                $isValid = false;
            } else {
                $newEmail = $email;
            }
        }
        $newUsername = get_username();
        if (get_username() != $_POST["username"]) {
            $username = $_POST["username"];
            $stmt = $db->prepare("SELECT COUNT(1) as InUse from Users where username = :username");
            $stmt->execute([":username" => $username]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $inUse = 1;//default it to a failure scenario
            if ($result && isset($result["InUse"])) {
                try {
                    $inUse = intval($result["InUse"]);
                } catch (Exception $e) {

                }
            }
            if ($inUse > 0) {
                flash("Username already in use");
                //for now we can just stop the rest of the update
                $isValid = false;
            } else if (strlen($username >= 5)) {
                $newUsername = $username;
            } else {
                flash("Username must be at least 5 characters long.");
                $isValid = false;
            }
        }

        $newFirstName = get_first();
        if ((get_first() != $_POST["firstName"])) {
            $newFirstName = $_POST["firstName"];
        }

        $newLastName = get_last();
        if ((get_last() != $_POST["lastName"])) {
            $newLastName = $_POST["lastName"];
        }

        $privacy = $_SESSION["user"]["privacy"];

        if (($privacy != $_POST["privacySetting"])) {
            $privacy = $_POST["privacySetting"];
        }

        if ($isValid) {
            $stmt = $db->prepare("UPDATE Users set email = :email, username= :username, first_name = :firstName, last_name = :lastName, privacy = :privacy where id = :id");
            $r = $stmt->execute([":email" => $newEmail, ":username" => $newUsername, ":firstName" => $newFirstName, ":lastName" => $newLastName, ":privacy" => $privacy, ":id" => get_user_id()]);
            if ($r) {
                flash("Success! Profile Updated.");
            } else {
                flash("Error updating profile. Please Try Again.");
            }
            //password is optional, so check if it's even set
            //if so, then check if it's a valid reset request
            if (!empty($_POST["password"]) && !empty($_POST["confirm"]) && !empty($_POST["current"])) {
                $current = $_POST["current"];
                $stmt = $db->prepare("SELECT password from Users WHERE id = :userid");
                $stmt->execute([":userid" => get_user_id()]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($result && isset($result["password"])) {
                    $newHash = $result["password"];
                    if (password_verify($current, $newHash)) {
                        if ($_POST["password"] == $_POST["confirm"]) {
                            if (strlen($_POST["password"]) >= 5) {

                                $password = $_POST["password"];
                                $hash = password_hash($password, PASSWORD_BCRYPT);
                                $stmt = $db->prepare("UPDATE Users set password = :password where id = :id");
                                $r = $stmt->execute([":id" => get_user_id(), ":password" => $hash]);
                                if ($r) {
                                    flash("Reset Password");
                                } else {
                                    flash("Error resetting password");
                                }
                            } else if (strlen($_POST["password"]) < 5) {
                                flash("Password must be at least 5 letters");
                            } else {
                                flash("Passwords do not match");
                            }
                        }
                    }
                } else {
                    flash("Please input the correct current password.");
                }
            }
//fetch/select fresh data in case anything changed
            $stmt = $db->prepare("SELECT email, username, first_name, last_name, privacy from Users WHERE id = :id LIMIT 1");
            $stmt->execute([":id" => get_user_id()]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $email = $result["email"];
                $username = $result["username"];
                $firstName = $result["first_name"];
                $lastName = $result["last_name"];
                $privacy = $result["privacy"];
                //let's update our session too
                $_SESSION["user"]["email"] = $email;
                $_SESSION["user"]["username"] = $username;
                $_SESSION["user"]["first_name"] = $firstName;
                $_SESSION["user"]["last_name"] = $lastName;
                $_SESSION["user"]["privacy"] = $privacy;
            }
        } else {
            //else for $isValid, though don't need to put anything here since the specific failure will output the message
        }
    }
}
?>

    <form method="POST">
        <?php if(($vis == 'Public') || ($id == get_user_id())): ?>
        <div class = "form-group">
            <label for="username">Username</label>
            <input class = "form-control" type="text" maxlength="60" name="username" value="<?php safer_echo(get_username()); ?>"/>
        </div>
        <div class = "form-group">
            <label for="firstName">First Name</label>
            <input class = "form-control" type="text" name="firstName" value="<?php safer_echo(get_first()); ?>"/>
        </div>
        <div class = "form-group">
            <label for="lastName">Last Name</label>
            <input class = "form-control" type="text" name="lastName" value="<?php safer_echo(get_last()); ?>"/>
            <!-- DO NOT PRELOAD PASSWORD-->
        </div>
        <?php endif;?>
        <?php if($id==get_user_id()):?>
        <div class = "form-group">
            <label for="email">Email</label>
            <input class = "form-control" type="email" name="email" value="<?php safer_echo(get_email()); ?>"/>
        </div>
        <div class = "form-group">
            <label for="pw">Password</label>
            <input class = "form-control" type="password" name="password"/>
        </div>
        <div class = "form-group">
            <label for="cpw">Confirm Password</label>
            <input class = "form-control" type="password" name="confirm"/>
        </div>
        <div class = "form-group">
        <select class = "form-control" name = "privacySetting">
	<label for="privacySetting">Privacy Setting</label>
            <option value = "Public">Public</option>
            <option value="Private">Private</option>
        </select>
        </div>
        <input class = "btn btn-primary" type="submit" name="saved" value="Save Profile"/>
    <?php endif;?>
    </form>
<?php require(__DIR__ . "/partials/flash.php");
