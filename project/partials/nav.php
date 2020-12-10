<link rel="stylesheet" href="static/css/styles.css">

<?php
//we'll be including this on most/all pages so it's a good place to include anything else we want on those pages
require_once(__DIR__ . "/../lib/helpers.php");
?>
<!-- CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">

<!-- jQuery and JS bundle w/ Popper.js -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>

<nav class="navbar navbar-expand-lg navbar-light" style="background-color: #bfcad9;">
<ul class="navbar-nav mr-auto">
    <a class="navbar-brand">SK Bank</a>
    <li class = "nav-item"><a class = "nav-link" href="home.php">Home</a></li>
    <?php if (!is_logged_in()): ?>
        <li class = "nav-item"><a class = "nav-link" href="login.php">Login</a></li>
        <li class = "nav-item"><a class = "nav-link" href="register.php">Register</a></li>
    <?php endif; ?>
    <?php if(has_role("Admin")): ?>
        <li><a href="create_checking_account.php">Create Account</a></li>
        <li><a href="list_accounts.php">Accounts</a></li>
        <li><a href = "withdraw.php">Withdraw</a></li>
        <li><a href = "deposit.php">Deposit</a></li>
        <li><a href="transfer.php">Transfer</a></li>
    <?php endif; ?>

    <?php if (is_logged_in()): ?>
        <li class = "nav-item"><a class = "nav-link" href="create_checking_account.php">Create Checking Account</a></li>
        <li class = "nav-item"><a class = "nav-link" href="list_accounts.php">Accounts</a></li>
        <li class = "nav-item"><a class = "nav-link" href = "withdraw.php">Withdraw</a></li>
        <li class = "nav-item"><a class = "nav-link" href = "deposit.php">Deposit</a></li>
        <li class = "nav-item"><a class = "nav-link" href="transfer.php">Transfer</a></li>
	<li class = "nav-item"><a class = "nav-link" href="profile.php">Profile</a></li>
	<li class = "nav-item"><a class = "nav-link" href="external_transfers.php">Transfer Between Users</a></li>
	<li class = "nav-item"><a class = "nav-link" href="logout.php">Logout</a></li>
    <?php endif; ?>
</ul>
</nav>
