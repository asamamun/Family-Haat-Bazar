<hr>
<a href="index.php">ShopEase</a> |

<?php
if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 'true'){
    ?>
<a href="logout.php">Logout</a> |
    <?php
}
else{
    ?>
<a href="registration.php">Sign Up</a> |
<a href="login.php">Sign In</a> |
    <?php
}
?>

<hr>
