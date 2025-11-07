const CLIENT_ID = '173744872751-h1e1j2d4c58p00gcguut3vebja2n9sjd.apps.googleusercontent.com';

// ============ TEMA OSCURO ============
function activarTemaHeader() {
    const boton = document.getElementById('modoLecturaBtn');
    const cuerpo = document.body;
    const moonIcon = document.getElementById('moonIcon');
    const sunIcon = document.getElementById('sunIcon');

    if (!boton || !moonIcon || !sunIcon) return;

    const temaSaved = localStorage.getItem('tema');
    if (temaSaved === 'oscuro') {
        cuerpo.classList.add('modo-oscuro');
        moonIcon.style.display = 'none';
        sunIcon.style.display = 'block';
    } else {
        cuerpo.classList.remove('modo-oscuro');
        moonIcon.style.display = 'block';
        sunIcon.style.display = 'none';
    }

    boton.onclick = () => {
        cuerpo.classList.toggle('modo-oscuro');
        if (cuerpo.classList.contains('modo-oscuro')) {
            moonIcon.style.display = 'none';
            sunIcon.style.display = 'block';
            localStorage.setItem('tema', 'oscuro');
        } else {
            moonIcon.style.display = 'block';
            sunIcon.style.display = 'none';
            localStorage.removeItem('tema');
        }
    };
}

// ============ GOOGLE SIGN-IN ============
window.handleCodeResponse = function(response) {
    console.log('✅ Respuesta recibida:', response);
    
    const apiHost = window.location.hostname === 'localhost' 
        ? 'http://localhost:8000'
        : `http://${window.location.hostname}:8000`;

    const btn = document.querySelector('#btn-google');
    if (btn) {
        btn.disabled = true;
        btn.style.opacity = '0.6';
    }

    fetch(apiHost + '/google/exchange-code.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ code: response.code })
    })
    .then(res => {
        if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
        return res.json();
    })
    .then(data => {
        if (data.success) {
            localStorage.setItem('user_id', data.user_id);
            localStorage.setItem('user_nombre', data.user_nombre);
            localStorage.setItem('user_correo', data.user_correo);
            localStorage.setItem('logged_in', 'true');
            
            setTimeout(() => {
                window.location.href = '/MainPageLogeado/landingMainPage';
            }, 100);
        } else {
            console.error('Error:', data.message);
            alert('Error: ' + (data.message || 'Error desconocido'));
            if (btn) {
                btn.disabled = false;
                btn.style.opacity = '1';
            }
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Error: ' + err.message);
        if (btn) {
            btn.disabled = false;
            btn.style.opacity = '1';
        }
    });
};

function initGoogleSignIn() {
    if (window.google?.accounts?.oauth2) {
        console.log('✅ Google OAuth2 disponible');
        
        try {
            let codeClient = window.google.accounts.oauth2.initCodeClient({
                client_id: CLIENT_ID,
                scope: 'openid email profile',
                ux_mode: 'popup',
                redirect_uri: 'http://localhost:4321',
                callback: window.handleCodeResponse
            });
            
            console.log('✅ CodeClient inicializado');
            
            const btn = document.querySelector('#btn-google');
            if (btn) {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    console.log('🔐 Pidiendo code a Google...');
                    codeClient.requestCode();
                });
                console.log('✅ Event listener agregado al botón');
            }
        } catch (error) {
            console.error('❌ Error inicializando OAuth2:', error);
        }
    }
}

// ============ INICIALIZAR AMBOS ============
function initAll() {
    activarTemaHeader();
    initGoogleSignIn();
}

document.addEventListener('DOMContentLoaded', initAll);
document.addEventListener('astro:after-swap', initAll);
