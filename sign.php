<?php
session_start();
include 'db.php';



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['loginEmail'];
    $password = $_POST['loginPassword'];


       // Check if login attempts exceed the limit
       if ($_SESSION['login_attempts'] >= 3 && !isset($_SESSION['lockout'])) {
        $_SESSION['lockout'] = time() + 30; // Lockout for 30 seconds
    }

    // Check if the lockout period is active
    if (isset($_SESSION['lockout']) && $_SESSION['lockout'] > time()) {
        $remainingTime = $_SESSION['lockout'] - time();
        echo "<script>
                var remainingTime = $remainingTime;
                var countdownInterval = setInterval(function() {
                    if (remainingTime > 0) {
                        document.getElementById('countdown').innerText = 'Please wait ' + remainingTime + ' seconds before trying again.';
                        remainingTime--;
                    } else {
                        clearInterval(countdownInterval);
                        window.location.href='login.php';
                    }
                }, 1000);
              </script>";
        echo "<div id='countdown' style='font-size: 20px; color: red;'></div>";
        exit;
    } elseif ($_SESSION['login_attempts'] > 3 && !isset($_SESSION['lockout'])) {
        $_SESSION['lockout'] = time() + 30; // Lockout for 30 seconds
        $_SESSION['login_attempts'] = 0; // Reset login attempts
    }
    // Prepare and bind
    $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($hashedPassword);
    $stmt->fetch();

    if ($hashedPassword && password_verify($password, $hashedPassword)) {
        echo 'Login successful!';
        header('Location: userboard.php');

    } else {
        echo 'Invalid email or password.';
    }

    $stmt->close();
    $conn->close();
} else {
    echo 'Invalid request method.';
}
?>
