<?php
require_once __DIR__ . '/functions.php';

// Force the user to be logged in to access this page
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$message = '';
$uploadedFilePath = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        
        // Calling your intentionally vulnerable function
        $fileName = uploadProfilePicture($_FILES['profile_picture']);
        
        if ($fileName) {
            $uploadedFilePath = "uploads/" . $fileName;
            $message = "<div class='success-msg'>File '<strong>" . htmlspecialchars($fileName) . "</strong>' uploaded successfully!</div>";
        } else {
            $message = "<div class='error-msg'>Failed to move uploaded file. Check folder permissions.</div>";
        }
    } else {
        $message = "<div class='error-msg'>Please select a valid file to upload.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Profile Picture — SecureVault Bank</title>
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --navy: #0a1628; --blue: #1a3a5c; --gold: #c9a84c;
            --gold-light: #e2cc7e; --white: #f4f4f4; --gray: #d1d5db; --bg: #f0f2f5;
        }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: var(--bg); color: #333; line-height: 1.6; }
        a { text-decoration: none; color: inherit; }

        .navbar { background: var(--navy); padding: 0 2rem; display: flex; align-items: center; justify-content: space-between; height: 64px; box-shadow: 0 2px 8px rgba(0,0,0,.3); }
        .navbar .logo { font-size: 1.4rem; font-weight: 700; color: var(--gold); letter-spacing: 1px; }
        .navbar .logo span { color: var(--white); }
        .navbar nav a { color: var(--gray); margin-left: 1.8rem; font-size: .95rem; transition: color .2s; }
        .navbar nav a:hover { color: var(--gold); }

        .upload-wrapper { display: flex; justify-content: center; align-items: center; min-height: calc(100vh - 64px); padding: 2rem; flex-direction: column; }
        .upload-card {
            background: #fff; border-radius: 10px; box-shadow: 0 8px 32px rgba(0,0,0,.1);
            width: 100%; max-width: 500px; padding: 2.5rem 2rem; text-align: center;
        }
        .upload-card h2 { color: var(--navy); margin-bottom: .3rem; font-size: 1.6rem; }
        .upload-card .subtitle { color: #888; margin-bottom: 2rem; font-size: .9rem; }

        .form-group { margin-bottom: 1.5rem; text-align: left; }
        .form-group label { display: block; font-weight: 600; margin-bottom: .5rem; font-size: .9rem; color: var(--blue); }
        
        /* Style the file input to look decent */
        input[type="file"] {
            display: block; width: 100%; padding: .75rem; border: 2px dashed #ccc; 
            border-radius: 6px; background: #fafafa; cursor: pointer; transition: border-color .2s;
        }
        input[type="file"]:hover { border-color: var(--gold); }

        .btn-submit {
            width: 100%; padding: .85rem; background: var(--gold); color: var(--navy);
            border: none; border-radius: 6px; font-size: 1rem; font-weight: 700;
            cursor: pointer; transition: background .2s;
        }
        .btn-submit:hover { background: var(--gold-light); }

        .error-msg { background: #fee2e2; color: #b91c1c; padding: .7rem 1rem; border-radius: 6px; margin-bottom: 1.2rem; font-size: .9rem; text-align: left; }
        .success-msg { background: #dcfce7; color: #15803d; padding: .7rem 1rem; border-radius: 6px; margin-bottom: 1.2rem; font-size: .9rem; text-align: left; }
        
        .preview-img { margin-top: 1.5rem; max-width: 150px; border-radius: 50%; border: 4px solid var(--gold); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        
        .footer { text-align: center; padding: 2rem; font-size: .85rem; color: #888; border-top: 1px solid #ddd; width: 100%; }
    </style>
</head>
<body>

<header class="navbar">
    <a href="index.php" class="logo">Secure<span>Vault</span></a>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="upload.php" style="color: var(--gold);">Profile</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main class="upload-wrapper">
    <div class="upload-card">
        <h2>Update Profile Picture</h2>
        <p class="subtitle">Personalize your SecureVault account</p>

        <?php echo $message; ?>

        <form method="POST" action="upload.php" enctype="multipart/form-data">
            <div class="form-group">
                <label for="profile_picture">Select Image</label>
                <input type="file" id="profile_picture" name="profile_picture" required>
            </div>
            <button type="submit" class="btn-submit">Upload File</button>
        </form>

        <?php if ($uploadedFilePath): ?>
            <div>
                <p style="margin-top: 2rem; font-size: 0.9rem; color: #666;">Current Profile Picture:</p>
                <img src="<?php echo htmlspecialchars($uploadedFilePath); ?>" alt="Profile Picture" class="preview-img">
            </div>
        <?php endif; ?>
    </div>
</main>

<footer class="footer">&copy; 2026 SecureVault Bank &mdash; <em>Training Environment Only</em></footer>

</body>
</html>