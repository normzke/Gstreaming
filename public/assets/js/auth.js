const playlistUrl = window.playlistUrl || '';
const isAuthenticated = window.isAuthenticated || false;

// Login Functions
function switchTab(tab) {
    document.querySelectorAll('.login-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.login-form').forEach(f => f.classList.remove('active'));

    if (tab === 'credentials') {
        document.querySelector('.login-tab:first-child').classList.add('active');
        document.getElementById('credentialsForm').classList.add('active');
    } else {
        document.querySelector('.login-tab:last-child').classList.add('active');
        document.getElementById('macForm').classList.add('active');
    }
}

async function loginWithCredentials(e) {
    e.preventDefault();
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const errorDiv = document.getElementById('credentialsError');

    try {
        const response = await fetch('api/auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password, type: 'credentials' })
        });

        const data = await response.json();
        if (data.success) {
            window.location.reload();
        } else {
            errorDiv.textContent = data.message || 'Invalid credentials';
        }
    } catch (error) {
        errorDiv.textContent = 'Login failed. Please try again.';
    }
}

async function loginWithMAC(e) {
    e.preventDefault();
    const mac = document.getElementById('macAddress').value;
    const errorDiv = document.getElementById('macError');

    try {
        const response = await fetch('api/auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ mac_address: mac, type: 'mac' })
        });

        const data = await response.json();
        if (data.success) {
            window.location.href = `player.php?mac=${encodeURIComponent(mac)}`;
        } else {
            errorDiv.textContent = data.message || 'Invalid MAC address';
        }
    } catch (error) {
        errorDiv.textContent = 'Login failed. Please try again.';
    }
}

if (isAuthenticated && window.serverUrl) {
    // Determine if we need to auto-load (this logic is handled by player.js main loop)
    console.log("Auth successful. Player ready.");
}
