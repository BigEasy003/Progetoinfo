<!DOCTYPE html>
<html>
   <head>
      <title>NOME - Register</title>
      <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
      <link rel="stylesheet" type="text/css" href="stile.css">
   </head>
   <body>
      <div class="container">
         <div class="login-container">
            <center>
               <img src="img/ciao.jpg" class="img-responsive center-block" width="150px">
               <h3>Registrati</h3>
            </center>
            
            <?php
               if ($_POST) {
                   include 'database.php';
   
                   $userName = htmlspecialchars(strip_tags($_POST['userName']));
                   $pass = htmlspecialchars(strip_tags($_POST['pass']));

                   if (empty($userName) || empty($pass)) {
                      $message = '<label>Riempi tutti i campi</label>';
                   } else {
                      $validationQuery = "SELECT userName FROM user WHERE userName = ?";
   
                      $validationStmt = $con->prepare($validationQuery);
                      $validationStmt->bindParam(1, $userName);
                      $validationStmt->execute();
      
                      $num = $validationStmt->rowCount();
      
                      if ($num > 0) {
                        $message = '<label>Username already in use</label>'; 
                      } else {
                        try {
                           $hashedPass = password_hash($pass, PASSWORD_DEFAULT);

                           $query = "INSERT INTO user SET userName=:userName, pass=:pass";
                           $stmt = $con->prepare($query);

                           $stmt->bindParam(':userName', $userName);
                           $stmt->bindParam(':pass', $hashedPass);

                           if ($stmt->execute()) {
                              header('Location: index.php?action=registered');
                              exit;
                           } else {
                              die('Impossibile registrare un nuovo player.');
                           }
                        } catch (PDOException $exception) {
                           die('ERROR: ' . $exception->getMessage());
                        }
                      }
                   }
               }
            ?> 

            <?php  
               if (isset($message)) {  
                  echo '<label class="text-danger">' . $message . '</label>';  
               }  
            ?>

            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
               <label>Username</label>  
               <input type="text" name="userName" class="form-control" />  
               <br />  
               <label>Password</label>  
               <input type="password" name="pass" class="form-control" />  
               <div class="form-group"> 
               <br />  
               <input type="submit" name="save" class="btn btn-primary" value="Salva" />
               <a href='index.php' class='btn btn-secondary'>Torna al login</a>  
            </form>
         </div>
      </div>
   </body>
</html>
