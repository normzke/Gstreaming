<?php
$platform = $_GET['platform'] ?? '';

if (!$platform || !in_array($platform, ['android', 'ios'])) {
    header('Location: ../index.php');
    exit();
}

$message = '';
$messageType = '';

switch ($platform) {
    case 'android':
        $message = 'Android app download coming soon! For now, please contact support for manual installation.';
        $messageType = 'info';
        break;
    case 'ios':
        $message = 'iOS app download coming soon! For now, please contact support for manual installation.';
        $messageType = 'info';
        break;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download <?php echo ucfirst($platform); ?> App - BingeTV</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/components.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .download-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #8B0000 0%, #660000 100%);
            padding: 20px;
        }

        .download-container {
            max-width: 600px;
            margin: 0 auto;
            width: 100%;
        }

        .download-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
        }

        .platform-icon {
            font-size: 80px;
            margin-bottom: 20px;
            color: #8B0000;
        }

        .android-icon { color: #3DDC84; }
        .ios-icon { color: #007AFF; }

        .download-title {
            color: #8B0000;
            font-family: 'Orbitron', sans-serif;
            font-weight: 900;
            margin-bottom: 16px;
        }

        .download-message {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .btn-primary {
            background: linear-gradient(135deg, #8B0000, #660000);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
        }

        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert-info {
            background: #dbeafe;
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }
    </style>
</head>
<body>
    <main class="download-page">
        <div class="download-container">
            <div class="download-card">
                <div class="platform-icon <?php echo $platform; ?>-icon">
                    <i class="fab fa-<?php echo $platform === 'android' ? 'android' : 'apple'; ?>"></i>
                </div>

                <h1 class="download-title">
                    <?php echo ucfirst($platform); ?> App Download
                </h1>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>

                <a href="../support" class="btn-primary">
                    <i class="fas fa-headset"></i>
                    Contact Support
                </a>

                <br><br>
                <a href="../index" style="color: #8B0000; text-decoration: none;">
                    <i class="fas fa-arrow-left"></i>
                    Back to Homepage
                </a>
            </div>
        </div>
    </main>
</body>
</html>
