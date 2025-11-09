const CLIENT_ID = '173744872751-9mrennmah063ulu8mn2c3m4n875rb1j7.apps.googleusercontent.com';

// ============ GOOGLE SIGN-IN ============
window.handleCodeResponse = function(response) {
    console.log('✓ Respuesta recibida:', response);
    
    const apiHost = window.location.hostname === 'localhost' 
        ? 'http://localhost:8000'
        : `http://${window.location.hostname}:8000`;

    const btn = document.querySelector('#btn-google');
    if (btn) {
        btn.disabled = true;
        btn.style.opacity = '0.6';
    }

    // Debug: Verifica que el code existe
    if (!response.code) {
        console.error('ERROR: response.code no existe', response);
        alert('Error: No se recibió el code de Google');
        if (btn) {
            btn.disabled = false;
            btn.style.opacity = '1';
        }
        return;
    }

    console.log('Enviando code:', response.code);

    fetch('http://localhost:8000/exchange-code.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ code: response.code })
    })
    .then(res => {
        console.log('Respuesta del servidor:', res.status);
        if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
        return res.json();
    })
    .then(data => {
        console.log('Datos recibidos:', data);
        if (data.success) {
            localStorage.setItem('user_id', data.user_id);
            localStorage.setItem('user_nombre', data.user_nombre);
            localStorage.setItem('user_correo', data.user_correo);
            localStorage.setItem('logged_in', 'true');
            
            setTimeout(() => {
                window.location.href = '/dashboardpage/Dashboard';
            }, 100);
        } else {
            console.error('Error del servidor:', data.message);
            alert('Error: ' + (data.message || 'Error desconocido'));
            if (btn) {
                btn.disabled = false;
                btn.style.opacity = '1';
            }
        }
    })
    .catch(err => {
        console.error('Error en fetch:', err);
        alert('Error: ' + err.message);
        if (btn) {
            btn.disabled = false;
            btn.style.opacity = '1';
        }
    });
};

function initGoogleSignIn() {
    if (window.google?.accounts?.oauth2) {
        console.log('✓ Google OAuth2 disponible');
        
        try {
            let codeClient = window.google.accounts.oauth2.initCodeClient({
                client_id: CLIENT_ID,
                scope: 'openid email profile',
                ux_mode: 'popup',
                redirect_uri: 'http://localhost:4321',
                callback: window.handleCodeResponse
            });
            
            console.log('✓ CodeClient inicializado');
            
            const btn = document.querySelector('#btn-google');
            if (btn) {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    console.log('✓ Pidiendo code a Google...');
                    codeClient.requestCode();
                });
                console.log('✓ Event listener agregado al botón');
            } else {
                console.error('ERROR: Botón #btn-google no encontrado');
            }
        } catch (error) {
            console.error('ERROR inicializando OAuth2:', error);
        }
    } else {
        console.error('ERROR: Google OAuth2 NO está disponible');
    }
}

function initAll() {
    console.log('Inicializando Google Sign-In...');
    initGoogleSignIn();
}

document.addEventListener('DOMContentLoaded', initAll);
document.addEventListener('astro:after-swap', initAll);
