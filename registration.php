<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/vendor/autoload.php';
$page = "Registration";
if(isset($_POST['reg'])){
$db = new MysqliDb();
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reg'])){
  if($_POST['pass1'] != $_POST['pass2']){
    $message = "Passwords do not match";
  }
  else{
$data = [
        'first_name'=> $db->escape($_POST['firstname']),
        'last_name'=> $db->escape($_POST['lastname']),        
        'email'=> $db->escape($_POST['email']),
        'phone'=> $db->escape($_POST['phone']),
        'password' => password_hash($_POST['pass1'],PASSWORD_DEFAULT),
        'role' => "customer"
    ];
    if($db->insert("users",$data)){
      $_SESSION['message'] = "Registration successful!!";
        header("location:login.php");
        exit;
    }
    else{
        $message = "Regitration failed!!";
    }
  }  
}


}
?>

<?php require __DIR__ . '/components/header.php';?>


<!-- content start  -->
 <h1>Registration</h1>
<form class="row g-3 needs-validation" novalidate method="post" action="<?= $_SERVER['PHP_SELF'] ?>">
  <div class="col-md-4 form-floating">
  <input type="text" class="form-control" name="firstname" id="firstname" value="" required placeholder="first name">
<label for="firstname" class="form-label">First name</label>
    
    <div class="valid-feedback">
      Looks good!
    </div>
  </div>
  <div class="col-md-4 form-floating">
  <input type="text" class="form-control" name="lastname" id="lastname" value="" required placeholder="last name">
    <label for="lastname" class="form-label">Last name</label>
    
    <div class="valid-feedback">
      Looks good!
    </div>
  </div>
<!--   <div class="col-md-4 form-floating">
    <input type="text" class="form-control" id="username" name="username" aria-describedby="inputGroupPrepend" required placeholder="user name">
      <label for="username" class="form-label">Username</label>
      <div class="invalid-feedback">
        Please choose a username.
      </div>    
  </div> -->
  <div class="col-md-4 form-floating">
    
    <input type="email" class="form-control" id="email" name="email" required placeholder="yourname@domain.com">
    <label for="email" class="form-label">Email</label>
    <div class="invalid-feedback">
      Please provide a valid email.
    </div>
    <div class="valid-feedback">
      Email Valid!!
    </div>
  </div>
    <div class="col-md-4 form-floating">    
    <input type="text" minlength="10" class="form-control" id="phone" name="phone" required placeholder="01XXXXXXXXX">
    <label for="pass2" class="form-label">Phone Number</label>
    <div class="invalid-feedback">
      Please provide a valid phone number
    </div>
  </div>
  <div class="col-md-4 form-floating">    
    <input type="password" minlength="5" class="form-control" id="pass1" name="pass1" required placeholder="password">
    <label for="pass1" class="form-label">Password</label>
    <div class="invalid-feedback">
      Please provide a valid password.
    </div>
  </div>
  <div class="col-md-4 form-floating">    
    <input type="password" minlength="5" class="form-control" id="pass2" name="pass2" required placeholder="retype password">
    <label for="pass2" class="form-label">Retype Password</label>
    <div class="invalid-feedback">
      Please provide a valid length password.
    </div>
  </div>

  <div class="col-12">
    <div class="form-check">
      
      <label class="form-check-label" for="invalidCheck">
        Agree to terms and conditions
      </label>
      <input class="form-check-input" type="checkbox" value="" id="invalidCheck" required>
      <div class="invalid-feedback">
        You must agree before submitting.
      </div>
    </div>
  </div>
  <div class="col-12">
    <button class="btn btn-primary" type="submit" name="reg" value="Sign Up">Register</button>
  </div>
</form>
<!-- content end -->
<?php
// echo testfunc();
?>
<script>

</script>
<?php require __DIR__ . '/components/footer.php';?>
    
