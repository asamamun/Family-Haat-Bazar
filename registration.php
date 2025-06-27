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
        // 'phone'=> $db->escape($_POST['phone']),
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
 
 <!-- <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> -->
  <style>
    body {
      background-color:rgb(229, 238, 234);
    }
    .register-container {
      max-width: 800px;
      margin: 50px auto;
      padding: 30px;
      background-color: white;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>

<div class="register-container">
  <h2 class="text-align:center mb-4 ">Registration</h2>

  <form class="row g-3 needs-validation" novalidate method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">

    <div class="col-md-6 form-floating">
      <input type="text" class="form-control" id="firstname" name="firstname" required placeholder="First name">
      <label for="firstname">First name</label>
      <div class="valid-feedback">Looks good!</div>
    </div>

    <div class="col-md-6 form-floating">
      <input type="text" class="form-control" id="lastname" name="lastname" required placeholder="Last name">
      <label for="lastname">Last name</label>
      <div class="valid-feedback">Looks good!</div>
    </div>

    <div class="col-md-6 form-floating">
      <input type="email" class="form-control" id="email" name="email" required placeholder="name@example.com">
      <label for="email">Email</label>
      <div class="invalid-feedback">Please provide a valid email.</div>
      <div class="valid-feedback">Email valid!</div>
    </div>

    <div class="col-md-6 form-floating">
      <input type="text" class="form-control" id="phone" name="phone" minlength="10" placeholder="01XXXXXXXXX" required>
      <label for="phone">Phone Number</label>
      <div class="invalid-feedback">Please enter a valid phone number.</div>
    </div>

    <div class="col-md-6 form-floating">
      <input type="password" class="form-control" id="pass1" name="pass1" minlength="5" required placeholder="Password">
      <label for="pass1">Password</label>
      <div class="invalid-feedback">Please provide a password with at least 5 characters.</div>
    </div>

    <div class="col-md-6 form-floating">
      <input type="password" class="form-control" id="pass2" name="pass2" minlength="5" required placeholder="Retype Password">
      <label for="pass2">Retype Password</label>
      <div class="invalid-feedback">Please retype your password correctly.</div>
    </div>

    <div class="col-12">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" value="" id="invalidCheck" required>
        <label class="form-check-label" for="invalidCheck">
          Agree to terms and conditions
        </label>
        <div class="invalid-feedback">
          You must agree before submitting.
        </div>
      </div>
    </div>

    <div class="col-12">
      <button class="btn btn-primary w-100" type="submit" name="reg">Register</button>
    </div>
  </form>
</div>

<script>
  // Bootstrap validation
  (() => {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
      form.addEventListener('submit', event => {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });
  })();
</script>

</body>
</html>

<!-- content end -->
<?php
// echo testfunc();
?>
<script>

</script>
<?php require __DIR__ . '/components/footer.php';?>
    
