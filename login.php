<!DOCTYPE html>
<html>
<head>
    <title>Admin</title>
    <link rel="stylesheet" type="text/css" href="home.css"> <!-- Optional CSS -->
</head>
<body>
    <div class="loginpage">
        <form class ="login"method="post" action="authenticate.php">
            <div class="headerlogin">
                <img src='Sungebudi.png' alt='Logo' class='logoheader'>
                <h2>LOGIN</h2>
            </div>
            <label for="username">Username:</label>
            <input class ="inputloginusername"type="text" id="username" name="username" required>
            <br><br>
            <label for="password">Password:</label>
            <input class ="inputloginpassword"type="password" id="password" name="password" required>
            <br><br>
            <button class ="buttonlogin"type="submit">Login</button>
        </form>
    </div>
</body>
</html>