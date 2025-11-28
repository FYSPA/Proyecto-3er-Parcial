const CLIENT_ID_CODE = '173744872751-9mrennmah063ulu8mn2c3m4n875rb1j7.apps.googleusercontent.com';

declare global {
    interface Window {
        google?: any;
    }
}

// 1. Interfaces y Tipos
interface GoogleCodeResponse {
    code: string;
    scope?: string;
    state?: string;
    error?: string;
}

interface AuthResponse {
    success: boolean;
    message?: string;
    user_id: string;
    user_nombre: string;
    user_correo: string;
    user_photo?: string;
}

let googleRetries = 0;
const MAX_RETRIES = 20;

const handleCodeResponse = (response: GoogleCodeResponse) => {
    const btn = document.querySelector('#btn-google') as HTMLButtonElement;

    if (btn) {
        btn.disabled = true;
        btn.style.opacity = '0.6';
    }

    if (!response?.code) {
        alert('Error: No se recibió el code de Google');
        if (btn) {
            btn.disabled = false;
            btn.style.opacity = '1';
        }
        return;
    }
    const PUBLIC_API_URL = import.meta.env.PUBLIC_API_URL
    const apiUrl = PUBLIC_API_URL || 'http://localhost:8081';
    fetch(`${apiUrl}/api/auth/logingoogle`, {
        method: 'POST',
        body: new URLSearchParams({ code: response.code })
    })
    .then(res => {
        const contentType = res.headers.get('content-type');
        if (!contentType?.includes('application/json')) {
            throw new Error('Respuesta del servidor no es JSON válido');
        }
        return res.json() as Promise<AuthResponse>;
    })
    .then(data => {
        if (data.success) {
            window.SafeStorage.setItem('user_id', data.user_id);
            window.SafeStorage.setItem('user_nombre', data.user_nombre);
            window.SafeStorage.setItem('user_correo', data.user_correo);
            
            if (data.user_photo) {
                window.SafeStorage.setItem('user_photo', data.user_photo);
            }
            window.SafeStorage.setItem('autenticado', 'true');
            window.location.href = '/dashboardpage/Dashboard';
        } else {
            alert('Error: ' + (data.message || 'Autenticación fallida'));
        }
    })
    .catch((error: Error) => {
        console.error(error);
        alert('Error en la autenticación: ' + error.message);
    })
    .finally(() => {
        if (btn) {
            btn.disabled = false;
            btn.style.opacity = '1';
        }
    });
};

function initGoogleLogin(): void {
    const oldBtn = document.querySelector('#btn-google') as HTMLButtonElement;
    if (!oldBtn) {
        return;
    }

    // 2. Verificar si Google ha cargado
    if (!window.google || !window.google.accounts || !window.google.accounts.oauth2) {
        if (googleRetries < MAX_RETRIES) {
            console.log(`Esperando a Google GSI... Intento ${googleRetries + 1}`);
            googleRetries++;
            setTimeout(initGoogleLogin, 500);
        } else {
            console.error('Error: La librería de Google no cargó después de 10 segundos.');
        }
        return;
    }

    console.log('Google GSI cargado y botón encontrado. Inicializando...');

    const btn = oldBtn.cloneNode(true) as HTMLButtonElement;
    oldBtn.parentNode?.replaceChild(btn, oldBtn);

    // Inicializar cliente
    const codeClient = window.google.accounts.oauth2.initCodeClient({
        client_id: CLIENT_ID_CODE,
        scope: 'openid email profile',
        ux_mode: 'popup',
        callback: handleCodeResponse,
        redirect_uri: 'postmessage'
    });

    btn.addEventListener('click', (e) => {
        e.preventDefault();
        console.log('Click en Google Login');
        codeClient.requestCode();
    });
}


document.addEventListener('astro:before-preparation', () => {
    googleRetries = 0;
});

document.addEventListener('astro:page-load', initGoogleLogin);
if (document.readyState === 'complete') {
    initGoogleLogin();
} else {
    document.addEventListener('DOMContentLoaded', initGoogleLogin);
}

export {};