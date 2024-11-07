<?php
session_start(); // Start the session
include 'connection.php';
// Initialize variables
$user_error = "";

// Process login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if username indicates admin
    if (strcasecmp($username, 'admin') === 0) {
        $stmt = $conn->prepare("SELECT password FROM Admin WHERE username = BINARY ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($stored_password);
            $stmt->fetch();

            if ($password === $stored_password) {
                header("Location: admin_loading.php");
            } else {
               echo "<script>alert('Invalid username or password.'); window.location.href = 'index.php';</script>";
               exit();
            }
        } else {
            echo "<script>alert('Invalid username or password.'); window.location.href = 'index.php';</script>";
            exit();
        }
    } else {
        $stmt = $conn->prepare("SELECT password, first_name, last_name, user_id FROM Users WHERE username = BINARY ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($hashed_password, $first_name, $last_name, $user_id);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $user_id;
                $_SESSION['first_name'] = $first_name;
                $_SESSION['last_name'] = $last_name;
                header("Location: loading.php");
                exit();
            } else {
                echo "<script>alert('Invalid username or password.'); window.location.href = 'index.php';</script>";
                exit();
            }
        } else {
           echo "<script>alert('Invalid username or password.'); window.location.href = 'index.php';</script>";
           exit();
        }
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: white; /* Solid white background */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            padding: 0 15px;
        }

        .login-container {
            background-color: white; /* Set background to plain white */
            border: none; /* Remove border */
            border-radius: 0; /* No rounded corners */
            padding: 30px;
            width: 100%;
            max-width: 350px;
            text-align: center;
            box-shadow: none; /* Remove any shadow */
        }

        .login-container img {
            width: 200px; /* Fixed width for circular shape */
            height: 200px; /* Fixed height for circular shape */
            margin: 0 auto 1px auto; /* Center horizontally */
        }


        .login-container h2 {
            margin-bottom: 15px;
            color: #1976d2;
            font-size: 1.8rem; /* Adjusted font size */
            font-weight: 700;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 15px;
        }

        .login-container label {
            font-weight: bold;
            color: #1976d2;
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: 80%;
            padding: 10px;
            border: 2px solid #1976d2;
            border-radius: 10px;
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.3s, box-shadow 0.3s;
            outline: none;
            margin: 0 auto; /* Center the input fields */
            text-align: left; /* Center align the placeholder text */
        }

        .login-container input[type="text"]:focus,
        .login-container input[type="password"]:focus {
            border-color: #0d47a1;
            box-shadow: 0 0 5px rgba(13, 71, 161, 0.5);
        }

        .login-container input::placeholder {
            color: #aaa;
        }

        .login-container button {
            width: 80%;
            padding: 12px;
            background-color: #1976d2;
            color: white;
            border: none; /* No border */
            border-radius: 10px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s;
            margin-top: 10px;
        }

        .login-container button:hover {
            background-color: #0d47a1;
            transform: translateY(-2px);
        }

        .footer {
            margin-top: 15px;
            font-size: 0.9rem;
            color: #555;
        }

        .footer a {
            color: #1976d2;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        .error {
            color: #d94e5f;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .show-password-label {
            font-size: 0.1rem; /* Smaller font size */
            color: #1976d2; /* Color to match theme */
            display: inline-block; /* Ensure proper alignment */
            margin-top: 1px; /* Add some spacing */
        }

        /* Responsive Styles */
        @media (max-width: 480px) {
            .login-container {
                padding: 20px;
                max-width: 90%; /* Full width on small screens */
            }

            .login-container h2 {
                font-size: 1.5rem; /* Smaller title on mobile */
            }

            .login-container input[type="text"],
            .login-container input[type="password"],
            .login-container button {
                padding: 10px; /* Adjusted padding */
            }
        }

        /* Icon Input Style */
        .icon-input {
            display: flex;
            align-items: flex-end; /* Align the icon at the bottom */
            width: 80%; /* Align with input fields */
            margin: 0 auto; /* Center the icon-input group */
        }

        .icon-input img {
            width: 24px;
            height: 40px;
            margin-right: 5px;
            margin-bottom: 1mm; /* Adjusted downward */
        }

        .icon-input input {
            flex: 1; /* Allow input to take remaining space */
            padding: 10px;
            border: 2px solid #1976d2;
            border-radius: 10px;
            font-size: 1rem;
            box-sizing: border-box;
            outline: none;
            text-align: center; /* Center align the placeholder text */
        }
    </style>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
        }
    </script>
</head>
<body>

<div class="login-container">
    <img src="explorer_logo.png" alt="Logo"> 
    <?php if (!empty($user_error)): ?>
        <div class="error"><?php echo $user_error; ?></div>
    <?php endif; ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group">
            <label for="username">Username</label>
            <div class="icon-input">
                <img src="../bootstrap/users.svg" alt="User Icon">
                <input type="text" id="username" name="username" placeholder="Enter your username" required>
            </div>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <div class="icon-input">
                <img src="../bootstrap/password.svg" alt="Lock Icon">
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <label class="show-password-label" style="font-size: 0.8rem; color: #1976d2;" style="margin-left: 5px;">
                 <input type="checkbox" onclick="togglePassword()"> Show Password
            </label>

        </div>
        <button type="submit" name="login">Login</button>
    </form>
    <div class="footer">
        <p>Don't have an account? <a href="create_account.php">Sign up</a></p>
    </div>
</div>
</body>
</html>
