interface ApiResponse {
    success: boolean;
    message: string;
    code?: string;
}

interface LoginResponse extends ApiResponse {
    user_id: string;
    user_nombre: string;
    user_correo: string;
}

const setSafeSession = (key: string, value: string) => {
    try {
        window.sessionStorage.setItem(key, value);
    } catch (e) {
        console.warn('SessionStorage no disponible. Error: ' + e);
    }
};

// 2. Función para reenviar validación
async function resendValidate(correo: string): Promise<void> {
    const PUBLIC_API_URL = import.meta.env.PUBLIC_API_URL
    const apiUrl = PUBLIC_API_URL || 'http://localhost:8000';
    const formData = new FormData();
    formData.append('correo', correo);

    try {
        const res = await fetch(`${apiUrl}/api/auth/resendtoken`, {
            method: 'POST',
            body: formData
        });

        console.log('Respuesta status:', res.status);
        const json = await res.json() as ApiResponse;
        console.log(json);

        if (json.success) {
            alert("Reenvío de validación correcto");
            return;
        }

        alert(json.message || "Sucedió algo inesperado");

    } catch (error) {
        console.error(error);
        alert("Error de conexión al reenviar validación");
    }
}

// 3. Función principal de Login
function initLogin(): void {
    const PUBLIC_API_URL = import.meta.env.PUBLIC_API_URL
    const form = document.getElementById('loginForm') as HTMLFormElement | null;
    if (!form) {
        return;
    }

    const newForm = form.cloneNode(true) as HTMLFormElement;
    form.parentNode?.replaceChild(newForm, form);

    newForm.addEventListener('submit', async (e: SubmitEvent) => {
        e.preventDefault();

        const correoInput = newForm.querySelector('input[name="correo"]') as HTMLInputElement;
        const passwordInput = newForm.querySelector('input[name="password"]') as HTMLInputElement;

        const correo = correoInput?.value || '';
        const password = passwordInput?.value || '';

        if (!correo || !password) {
            alert('Por favor completa todos los campos');
            return;
        }

        const submitBtn = document.getElementById('submitBtn') as HTMLButtonElement | null;
        const loadingDiv = document.getElementById('loadingDiv') as HTMLDivElement | null;
        const errorDiv = document.getElementById('errorDiv') as HTMLDivElement | null;

        if (!submitBtn || !loadingDiv || !errorDiv) return;

        submitBtn.disabled = true;
        loadingDiv.style.display = 'block';
        errorDiv.style.display = 'none';

        const formData = new FormData();
        formData.append('correo', correo);
        formData.append('password', password);

        try {
            console.log('Haciendo POST a login...');
            const apiUrl = PUBLIC_API_URL || 'http://localhost:8000';
            
            const res = await fetch(`${apiUrl}/api/auth/login`, {
                method: 'POST',
                body: formData
            });

            console.log('Respuesta status:', res.status);
            const json = await res.json() as LoginResponse;
            console.log('JSON:', json);

            if (json.success) {
                console.log('Login exitoso! Guardando datos y redirigiendo...');
                
                window.SafeStorage.setItem('user_id', json.user_id);
                window.SafeStorage.setItem('user_nombre', json.user_nombre);
                window.SafeStorage.setItem('user_correo', json.user_correo);
                window.SafeStorage.setItem('logged_in', 'true');

                setSafeSession('user_id', json.user_id);
                setSafeSession('user_nombre', json.user_nombre);
                setSafeSession('user_correo', json.user_correo);

                setTimeout(() => {
                    window.location.href = '/dashboardpage/Dashboard';
                }, 500);
            } else {
                console.log('Error:', json.message);
                
                if (json.code === 'email_not_validate') {
                    const confirmar = confirm(
                        "Tu correo aún no está validado. ¿Deseas reenviar el correo de validación?"
                    );

                    if (confirmar) {
                        await resendValidate(correo);
                    } else {
                        alert("Validación cancelada.");
                    }
                }
                
                errorDiv.textContent = json.message || 'Email o contraseña incorrectos';
                errorDiv.style.display = 'block';
                submitBtn.disabled = false;
            }
        } catch (err) {
            const error = err as Error;
            console.error('Excepción:', error);
            errorDiv.textContent = 'Error: ' + error.message;
            errorDiv.style.display = 'block';
            submitBtn.disabled = false;
        } finally {
            loadingDiv.style.display = 'none';
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLogin);
} else {
    initLogin();
}

document.addEventListener('astro:page-load', initLogin);