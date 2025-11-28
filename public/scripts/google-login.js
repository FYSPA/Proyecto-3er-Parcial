const CLIENT_ID = '173744872751-9mrennmah063ulu8mn2c3m4n875rb1j7.apps.googleusercontent.com';

window.handleCodeResponse = function (response) {
    const btn = document.querySelector('#btn-google');
    if (btn) {
        btn.disabled = true;
        btn.style.opacity = '0.6';
    }
    if (!response || !response.code) {
        alert('Error: No se recibió el code de Google');
        if (btn) {
            btn.disabled = false;
            btn.style.opacity = '1';
        }
        return;
    }
    const apiUrl = window.PUBLIC_API_URL || 'http://localhost:8081';
    fetch(`${apiUrl}/exchange-code.php`, {
        method: 'POST',
        body: new URLSearchParams({ code: response.code })
    })
        .then(res => {
            const contentType = res.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Respuesta del servidor no es JSON válido');
            }
            return res.json();
        })
        .then(data => {
            if (data.success) {
                localStorage.setItem('user_id', data.user_id);
                localStorage.setItem('user_nombre', data.user_nombre);
                localStorage.setItem('user_correo', data.user_correo);
                if (data.user_photo) localStorage.setItem('user_photo', data.user_photo);
                localStorage.setItem('autenticado', 'true');
                window.location.href = '/dashboardpage/Dashboard';
            } else {
                alert('Error: ' + (data.message || 'Autenticación fallida'));
            }
        })
        .catch(error => {
            alert('Error en la autenticación: ' + error.message);
        })
        .finally(() => {
            if (btn) {
                btn.disabled = false;
                btn.style.opacity = '1';
            }
        });
};

window.handleCredentialResponse = function () { };

function initGoogleLogin() {
    const btn = document.querySelector('#btn-google');
    if (!btn || !window.google || !google.accounts || !google.accounts.oauth2) return;
    const codeClient = google.accounts.oauth2.initCodeClient({
        client_id: CLIENT_ID,
        scope: 'openid email profile',
        ux_mode: 'popup',
        callback: handleCodeResponse,
        redirect_uri: 'postmessage'
    });
    btn.addEventListener('click', () => {
        codeClient.requestCode();
    });
}

if (document.readyState === 'complete' || document.readyState === 'interactive') {
    initGoogleLogin();
} else {
    window.addEventListener('DOMContentLoaded', initGoogleLogin);
    document.addEventListener('astro:page-load', initGoogleLogin);
}
