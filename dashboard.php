<?php
session_start();

if (!isset($_SESSION['email'])) {
    header('Location:dashboard.php');
    exit;
}

require 'db.php';

$email = $_SESSION['email'];

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_image'])) {
    $image_name = $_FILES['profile_image']['name'];
    $image_tmp_name = $_FILES['profile_image']['tmp_name'];
    $image_size = $_FILES['profile_image']['size'];
    $image_error = $_FILES['profile_image']['error'];

    $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
    $allowed_ext = array('jpg', 'png', 'jpeg');

    if (in_array($image_ext, $allowed_ext)) {
        if ($image_error === 0) {
            if ($image_size < 5000000) { // 5MB limit
                $new_image_name = uniqid('', true) . '.' . $image_ext;
                $image_destination = 'images/' . $new_image_name;

                if (move_uploaded_file($image_tmp_name, $image_destination)) {
                    // Update image record in the database
                    $sql = "UPDATE users SET image_path = ? WHERE email = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ss", $new_image_name, $email);
                    $stmt->execute();

                    // Redirect to avoid resubmission
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error_message = "Failed to upload image.";
                }
            } else {
                $error_message = "Image size is too big.";
            }
        } else {
            $error_message = "Error uploading image.";
        }
    } else {
        $error_message = "Invalid image format.";
    }
}

// Fetch the user's details
$sql = "SELECT fullname, email, image_path FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <!-- Materialize CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .navbar {
            background-color: #1565c0;
            color: #fff;
            padding: 20px;
        }
        .profile-card {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-radius: 10px;
            background-color: #fff;
        }
        .profile-image img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
        }
        .upload-icon {
            font-size: 24px;
            margin-top: 10px;
            cursor: pointer;
            color: #1565c0;
        }
        .upload-icon:hover {
            text-decoration: underline;
        }
        .logout-btn a {
            background-color: #1565c0;
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            display: inline-block;
        }
        .error-message {
            color: red;
            font-size: 0.9em;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="navbar center-align">
        <h5>Welcome, <?php echo htmlspecialchars($user['fullname']); ?></h5>
    </div>

    <div class="profile-card center-align">
        <div class="profile-image">
            <?php if (!empty($user['image_path'])): ?>
                <img src="images/<?php echo htmlspecialchars($user['image_path']); ?>" alt="Profile Image">
            <?php else: ?>
                <img src="default-profile.jpg" alt="Profile Image">
            <?php endif; ?>
        </div>
        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <div class="upload-icon" onclick="document.getElementById('profile_image').click()"><i class="fas fa-upload"></i></div>
        <form id="uploadForm" action="dashboard.php" method="post" enctype="multipart/form-data">
            <input type="file" name="profile_image" id="profile_image" style="display: none;">
            <input type="submit" value="Upload" style="display: none;">
        </form>
        <div class="profile-name"><?php echo htmlspecialchars($user['fullname']); ?></div>
        <div class="profile-email"><?php echo htmlspecialchars($user['email']); ?></div>
        <div class="logout-btn"><a href="logout.php">Logout</a></div>
    </div>

    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        document.getElementById('profile_image').addEventListener('change', function() {
            document.getElementById('uploadForm').submit();
        });

        // Add an event listener to the logout button
        document.querySelector('.logout-btn a').addEventListener('click', function(event) {
            event.preventDefault();
            fetch('logout.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'logged_out') {
                        localStorage.setItem('logout', Date.now());
                        alert('You have been logged out.');
                        window.location.href = 'myform.html';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });
    </script>
</body>
</html>
