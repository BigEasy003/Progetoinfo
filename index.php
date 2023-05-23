<!DOCTYPE html>
<html>
   <head>
      <title>| NOME | - Login</title>

      <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
      <link rel="stylesheet" type="text/css" href="stile.css">
   </head>
   <body>
      <?php  
         session_start();  

         include 'database.php';
         
         $action = isset($_GET['action']) ? $_GET['action'] : "";
         
         if($action == 'registered'){
            echo "<div class='alert alert-success'>Utente creato!</div>";
         }
         
         if(isset($_POST["register"])){
            header("location: register.php"); 
         }
          
         try{
            if(isset($_POST["login"])){  
               if(empty($_POST["userName"]) || empty($_POST["pass"])){  
                  $message = '<label>Sono richiesti tutti i campi</label>';  
               } else {  
                  $userName = $_POST['userName'];
                  $pass = $_POST['pass'];
           
                  $query = "SELECT * FROM user WHERE userName = ?";
                  $stmt = $con->prepare($query);
                  $stmt->execute(array($userName));
             	
                  $row = $stmt->fetch(PDO::FETCH_ASSOC);
             		
                  $hashPass = $row['pass']; 
             		
                  $isPassCorrect = password_verify($pass, $row['pass']);
             		
                  if($isPassCorrect){
                     $_SESSION["userName"] = $row["userName"];
                     header("location: info.php");
                     exit;
                  }
               }  
            }  
         } catch(PDOException $error) {
            $message = $error->getMessage();  
         }  
      ?>
      <div class="container" style="width:500px;">
         <?php  
            if(isset($message)){  
               echo '<label class="text-danger">'.$message.'</label>';  
            }  
         ?>  
         <div class="login-container">
            <img src="img/ciao.jpg" class="logo" width="150px" alt="Logo">
            <h3>Login</h3>
            <form method="post">  
               <div class="form-group">
                  <label for="userName">Username</label>  
                  <input type="text" name="userName" class="form-control" />  
               </div>
               <div class="form-group">
                  <label for="pass">Password</label>  
                  <input type="password" name="pass" class="form-control" />  
               </div>
               <div class="form-group">
                  <input type="submit" name="login" class="btn btn-primary" value="Login" />  
                  <input type="submit" name="register" class="btn btn-secondary" value="Registrati" />  
               </div>
            </form>
         </div>
      </div>
   </body>
</html>
