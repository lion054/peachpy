<?php
session_start();
error_reporting(0);

$db_config_path = '../application/config/database.php';

if (!isset($_SESSION["license_code"])) {
    $_SESSION["error"] = "Invalid purchase code!";
    header("Location: index.php");
    exit();
}

if (isset($_POST["btn_admin"])) {

    $_SESSION["db_host"] = $_POST['db_host'];
    $_SESSION["db_name"] = $_POST['db_name'];
    $_SESSION["db_user"] = $_POST['db_user'];
    $_SESSION["db_password"] = $_POST['db_password'];


    /* Database Credentials */
    defined("DB_HOST") ? null : define("DB_HOST", $_SESSION["db_host"]);
    defined("DB_USER") ? null : define("DB_USER", $_SESSION["db_user"]);
    defined("DB_PASS") ? null : define("DB_PASS", $_SESSION["db_password"]);
    defined("DB_NAME") ? null : define("DB_NAME", $_SESSION["db_name"]);

    /* Connect */
    $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $connection->query("SET CHARACTER SET utf8");
    $connection->query("SET NAMES utf8");

    /* check connection */
    if (mysqli_connect_errno()) {
        $error = 0;
    } else {
        
        mysqli_query($connection, "UPDATE settings SET version = '2.6' WHERE id = 1;");

        mysqli_query($connection, "INSERT INTO `package_features` (`id`, `name`, `slug`, `basic`, `standared`, `premium`, `year_basic`, `year_standared`, `year_premium`, `type`, `text`) VALUES (NULL, 'HRM', 'hrm', '-1', '-1', '-1', '-1', '-1', '-1', '', NULL)");

        mysqli_query($connection, "ALTER TABLE `users` ADD `referral_id` VARCHAR(155) NULL DEFAULT NULL AFTER `role`;");
        mysqli_query($connection, "ALTER TABLE `users` ADD `referral_earn` VARCHAR(155) NULL DEFAULT '0' AFTER `referral_id`;");

        mysqli_query($connection, "ALTER TABLE `referrals` ADD `user_id` INT NULL DEFAULT '0' AFTER `order_id`, ADD `status` INT NULL DEFAULT '0' AFTER `user_id`;");

        mysqli_query($connection, "ALTER TABLE `business` ADD `default_check_in` VARCHAR(155) NULL DEFAULT NULL AFTER `price_separator`, ADD `default_check_out` VARCHAR(155) NULL DEFAULT NULL AFTER `default_check_in`;");


        // import database table
        $query = '';
          $sqlScript = file('sql/import_7_tables.sql');
          foreach ($sqlScript as $line) {
            
            $startWith = substr(trim($line), 0 ,2);
            $endWith = substr(trim($line), -1 ,1);
            
            if (empty($line) || $startWith == '--' || $startWith == '/*' || $startWith == '//') {
              continue;
            }
              
            $query = $query . $line;
            if ($endWith == ';') {
              mysqli_query($connection, $query) or die('<div class="error-response sql-import-response">Problem in executing the SQL query <b>' . $query. '</b></div>');
              $query= '';   
            }
        }

        
        mysqli_query($connection, "INSERT INTO `lang_values` (`type`, `label`, `keyword`, `english`) VALUES
        ('admin', 'Reset Password', 'reset-password', 'Reset Password'),
        ('admin', 'HRM', 'hrm', 'HRM'),
        ('admin', 'Department', 'department', 'Department'),
        ('admin', 'Edit department', 'edit-department', 'Edit Department'),
        ('admin', 'Add new department', 'add-new-department', 'Add new department'),
        ('admin', 'Departments', 'departments', 'Departments'),
        ('admin', 'Department name', 'department-name', 'Department name'),
        ('admin', 'Show', 'show', 'Show'),
        ('admin', 'Hide', 'hide', 'Hide'),
        ('admin', 'Employees', 'employee', 'Employees'),
        ('admin', 'Employees', 'employees', 'Employees'),
        ('admin', 'Edit employee', 'edit-employee', 'Edit employee'),
        ('admin', 'Add new employee', 'add-new-employee', 'Add new employee'),
        ('admin', 'Employee name', 'employee-name', 'Employee name'),
        ('admin', 'Image', 'image', 'Image'),
        ('admin', 'Attendence', 'attendence', 'Attendance'),
        ('admin', 'Edit attendence', 'edit-attendence', 'Edit attandence'),
        ('admin', 'Check in', 'check-in', 'Check in'),
        ('admin', 'Check out', 'check-out', 'Check out'),
        ('admin', 'Note', 'note', 'Note'),
        ('admin', 'Add new attendence', 'add-new-attendence', 'Add new attendence'),
        ('admin', 'Salary', 'salary', 'Salary'),
        ('admin', 'Add new salary', 'add-new-salary', 'Add new salary'),
        ('admin', 'Edit salary', 'edit-salary', 'Edit salary'),
        ('admin', 'Acount', 'acount', 'Account'),
        ('admin', 'Method', 'method', 'Method'),
        ('admin', 'Cash', 'cash', 'Cash'),
        ('admin', 'Check', 'check', 'Check'),
        ('admin', 'Card', 'card', 'Card'),
        ('admin', 'Hrm settings', 'hrm-settings', 'HRM Settings'),
        ('admin', 'Default check in', 'default-check-in', 'Default check in'),
        ('admin', 'Default check out', 'default-check-out', 'Default check out'),
        ('admin', 'Withdrawal Method', 'withdrawal-method', 'Withdrawal Method'),
        ('admin', 'Withdrawal Amount', 'withdrawal-amount', 'Withdrawal Amount'),
        ('admin', 'Balance', 'balance', 'Balance'),
        ('admin', 'Transaction Id', 'transaction-id', 'Transaction Id'),
        ('admin', 'Request Sent', 'request-sent', 'Request Sent'),
        ('admin', 'Total Referrals', 'total-referrals', 'Total Referrals'),
        ('admin', 'Total Earnings', 'total-earnings', 'Total Earnings'),
        ('admin', 'Total Withdraw', 'total-withdraw', 'Total Withdraw'),
        ('admin', 'Minimum Payout Amounts', 'minimum-payout-amounts', 'Minimum Payout Amounts'),
        ('admin', 'My Referral URL', 'my-referral-url', 'Referral URL'),
        ('admin', 'Referral policy', 'referral-policy', 'Referral policy'),
        ('admin', 'First Successful Payment by Referred Person', 'first-successful-payment-by-referred-person', 'First Successful Payment by Referred Person'),
        ('admin', 'Every Successful Payment by Referred Person', 'every-successful-payment-by-referred-person', 'Every Successful Payment by Referred Person'),
        ('admin', 'Referral guidelines', 'referral-guidelines', 'Referral guidelines'),
        ('admin', 'How It works', 'how-it-works', 'How It works'),
        ('admin', 'Send Invitation', 'send-invitation', 'Send Invitation'),
        ('admin', 'Send your referral link to your friends and tell them how cool is this', 'send-your-referral-link-to-your-friends-and-tell-them-how-cool-is-this', 'Send your referral link to your friends and tell them how cool is this'),
        ('admin', 'Registration', 'registration', 'Registration'),
        ('admin', 'Let them register using your referral link', 'let-them-register-using-your-referral-link', 'Let them register using your referral link'),
        ('admin', 'Get Commissions', 'get-commissions', 'Get Commissions'),
        ('admin', 'Earn commission for their first subscription plan payments!', 'earn-commission-for-their-first-subscription-plan-payments', 'Earn commission for their first subscription plan payments!'),
        ('admin', 'Paypal Email', 'paypal-email', 'Paypal Email'),
        ('admin', 'Bank Details', 'bank-details', 'Bank Details'),
        ('admin', 'Referrals', 'referrals', 'Referrals'),
        ('admin', 'Referrar Id', 'referrar-id', 'Referrar Id'),
        ('admin', 'Order Id', 'order-id', 'Order Id'),
        ('admin', 'Commision', 'commision', 'Commision'),
        ('admin', 'Commision Amount', 'commision-amount', 'Commision Amount'),
        ('admin', 'Send Payout Request', 'send-payout-request', 'Send Payout Request'),
        ('admin', 'Payouts', 'payouts', 'Payouts'),
        ('admin', 'Select your payment method', 'select-your-payment-method', 'Select your payment method'),
        ('admin', 'Paypal', 'paypal', 'Paypal'),
        ('admin', 'Bank', 'bank', 'Bank'),
        ('admin', 'Method Details', 'method-details', 'Method Details'),
        ('admin', 'Enable Referral', 'enable-referral', 'Enable Referral'),
        ('admin', 'Choose Referral policy', 'choose-referral-policy', 'Choose Referral policy'),
        ('admin', 'Commission only on first purchase', 'commission-only-on-first-purchase', 'Commission only on first purchase'),
        ('admin', 'Commission on every purchase', 'commission-on-every-purchase', 'Commission on every purchase'),
        ('admin', 'Commision Rate(%)', 'commision-rate', 'Commision Rate(%)'),
        ('admin', 'Minimum Payout', 'minimum-payout', 'Minimum Payout'),
        ('admin', 'Refferal Guidelines', 'refferal-guidelines', 'Referral Guidelines'),
        ('admin', 'Payout Request', 'payout-request', 'Payout Request'),
        ('admin', 'Completed Payout', 'completed-payout', 'Completed Payout'),
        ('admin', 'Affiliate', 'affiliate', 'Affiliate');");




      /* close connection */
      mysqli_close($connection);

      $redir = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
      $redir .= "://" . $_SERVER['HTTP_HOST'];
      $redir .= str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);
      $redir = str_replace('updates/update_v2.5_v2.6/', '', $redir);
      header("refresh:5;url=" . $redir);
      $success = 1;
    }



}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accufy &bull; Update Installer</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/libs/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,500,600,700&display=swap" rel="stylesheet">
    <script src="assets/js/jquery-1.12.4.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <div class="container">
        <div class="row">
            <div class="col-md-8 col-sm-12 col-md-offset-2">

                <div class="row">
                    <div class="col-sm-12 logo-cnt">
                        <p>
                           <img src="assets/img/logo.png" alt="">
                       </p>
                       <h1>Welcome to the update installer</h1>
                   </div>
               </div>

               <div class="row">
                <div class="col-sm-12">

                    <div class="install-box">

                        <div class="steps">
                            <div class="step-progress">
                                <div class="step-progress-line" data-now-value="100" data-number-of-steps="3" style="width: 100%;"></div>
                            </div>
                            <div class="step" style="width: 50%">
                                <div class="step-icon"><i class="fa fa-arrow-circle-right"></i></div>
                                <p>Start</p>
                            </div>
                            <div class="step active" style="width: 50%">
                                <div class="step-icon"><i class="fa fa-database"></i></div>
                                <p>Database</p>
                            </div>
                        </div>

                        <div class="messages">
                            <?php if (isset($message)) { ?>
                            <div class="alert alert-danger">
                                <strong><?php echo htmlspecialchars($message); ?></strong>
                            </div>
                            <?php } ?>
                            <?php if (isset($success)) { ?>
                            <div class="alert alert-success">
                                <strong>Completing Updates ... <i class="fa fa-spinner fa-spin fa-2x fa-fw"></i> Please wait 5 second </strong>
                            </div>
                            <?php } ?>
                        </div>

                        <div class="step-contents">
                            <div class="tab-1">
                                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                                    <div class="tab-content">
                                        <div class="tab_1">
                                            <h1 class="step-title">Database</h1>
                                            <div class="form-group">
                                                <label for="email">Host</label>
                                                <input type="text" class="form-control form-input" name="db_host" placeholder="Host"
                                                value="<?php echo isset($_SESSION["db_host"]) ? $_SESSION["db_host"] : 'localhost'; ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="email">Database Name</label>
                                                <input type="text" class="form-control form-input" name="db_name" placeholder="Database Name" value="<?php echo @$_SESSION["db_name"]; ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="email">Username</label>
                                                <input type="text" class="form-control form-input" name="db_user" placeholder="Username" value="<?php echo @$_SESSION["db_user"]; ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="email">Password</label>
                                                <input type="password" class="form-control form-input" name="db_password" placeholder="Password" value="<?php echo @$_SESSION["db_password"]; ?>">
                                            </div>

                                        </div>
                                    </div>

                                    <div class="buttons">
                                        <a href="index.php" class="btn btn-success btn-custom pull-left">Prev</a>
                                        <button type="submit" name="btn_admin" class="btn btn-success btn-custom pull-right">Finish</button>
                                    </div>
                                </form>
                            </div>
                        </div>


                    </div>
                </div>
            </div>


        </div>


    </div>


</div>

<?php

unset($_SESSION["error"]);
unset($_SESSION["success"]);

?>

</body>
</html>

