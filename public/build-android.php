<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../lib/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Build Android APK - BingeTV</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .build-guide {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 12px;
        }
        .step {
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-left: 4px solid #00A8FF;
            border-radius: 8px;
        }
        .step h3 {
            color: #00A8FF;
            margin-bottom: 15px;
        }
        code {
            background: #1a1a1a;
            color: #00ff00;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }
        pre {
            background: #1a1a1a;
            color: #00ff00;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="build-guide">
        <h1><i class="fas fa-tools"></i> Build Android APK Without Android Studio</h1>
        
        <div class="step">
            <h3>Step 1: Install Java JDK</h3>
            <p><strong>macOS:</strong></p>
            <pre>brew install openjdk@11</pre>
            <p><strong>Or download from:</strong> <a href="https://adoptium.net/" target="_blank">https://adoptium.net/</a></p>
        </div>
        
        <div class="step">
            <h3>Step 2: Install Android SDK Command Line Tools</h3>
            <p><strong>Download:</strong> <a href="https://developer.android.com/studio#command-tools" target="_blank">Android Command Line Tools</a></p>
            <p><strong>Extract and install SDK components:</strong></p>
            <pre>cd ~/android-sdk/cmdline-tools/bin
./sdkmanager "platform-tools" "platforms;android-34" "build-tools;34.0.0"</pre>
        </div>
        
        <div class="step">
            <h3>Step 3: Set Environment Variables</h3>
            <p>Add to <code>~/.zshrc</code> or <code>~/.bashrc</code>:</p>
            <pre>export ANDROID_HOME=$HOME/android-sdk
export PATH=$PATH:$ANDROID_HOME/platform-tools</pre>
            <p>Then: <code>source ~/.zshrc</code></p>
        </div>
        
        <div class="step">
            <h3>Step 4: Build APK</h3>
            <p><strong>Option A: Using Build Script (Easiest)</strong></p>
            <pre>cd ~/Downloads/BingeTV/apps/android
chmod +x build-apk.sh
./build-apk.sh</pre>
            
            <p><strong>Option B: Using Gradle Wrapper</strong></p>
            <pre>cd ~/Downloads/BingeTV/apps/android
echo "sdk.dir=$ANDROID_HOME" > local.properties
./gradlew assembleDebug</pre>
        </div>
        
        <div class="step">
            <h3>Step 5: Find Your APK</h3>
            <p>After build, APK will be at:</p>
            <pre>app/build/outputs/apk/debug/app-debug.apk</pre>
            <p>The script automatically copies it to <code>public/apps/android/BingeTV-debug.apk</code></p>
        </div>
        
        <div class="step">
            <h3>Quick Build Script</h3>
            <p>We've created <code>build-apk.sh</code> that does everything automatically!</p>
            <p>Just run: <code>./build-apk.sh</code> from the <code>apps/android</code> directory</p>
        </div>
        
        <div style="margin-top: 40px; padding: 20px; background: #e3f2fd; border-radius: 8px;">
            <h3><i class="fas fa-info-circle"></i> Need Help?</h3>
            <p>See detailed guide: <a href="../apps/android/QUICK_BUILD.md">QUICK_BUILD.md</a></p>
            <p>Or contact support: <a href="support">support@bingetv.co.ke</a></p>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>

