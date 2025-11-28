// Definición de tipos
interface ApiResponse {
    success: boolean;
    message: string;
    [key: string]: any;
}

function initRegister(): void {
    const form = document.getElementById('registerForm') as HTMLFormElement | null;
    if (!form) return;

    const newForm = form.cloneNode(true) as HTMLFormElement;
    form.parentNode?.replaceChild(newForm, form);

    newForm.addEventListener('submit', async (e: SubmitEvent) => {
        e.preventDefault();

        const nombreInput = newForm.querySelector('input[name="nombre"]') as HTMLInputElement;
        const correoInput = newForm.querySelector('input[name="correo"]') as HTMLInputElement;
        const passwordInput = newForm.querySelector('input[name="password"]') as HTMLInputElement;

        const nombre = nombreInput?.value.trim() || '';
        const correo = correoInput?.value.trim() || '';
        const password = passwordInput?.value || '';

        if (!nombre || !correo || !password) {
            alert('Por favor completa todos los campos');
            return;
        }

        const PUBLIC_API_URL = import.meta.env.PUBLIC_API_URL
        const PUBLIC_FRONTEND_URL = import.meta.env.PUBLIC_FRONTEND_URL
        const apiHost = PUBLIC_API_URL || 'http://localhost:8000';

        const fd = new FormData();
        fd.append('nombre', nombre);
        fd.append('correo', correo);
        fd.append('password', password);

        try {
            const res = await fetch(`${apiHost}/api/auth/registro`, {
                method: 'POST',
                body: fd
            });

            const json = await res.json() as ApiResponse;

            if (json.success) {
                setTimeout(() => {
                    window.location.href = `${PUBLIC_FRONTEND_URL}/verificacion?correo=${encodeURIComponent(correo)}`;
                }, 500);
            } else {
                console.error('Error:', json.message);
                if (json.message && json.message.includes("Duplicate entry")) {
                    console.log("Usuario duplicado, intentando resend login...");
                    alert('Este usuario ya se encuentra registrado, se te enviará al login')
                    setTimeout(() => {
                        window.location.href = `${PUBLIC_FRONTEND_URL}/LoginRegisterPages/loginPage`;
                    }, 500);
                    return;
                }

                alert(json.message || 'Ocurrió un error en el registro');
            }
        } catch (err) {
            const error = err as Error;
            console.error('Excepción:', error);
            alert('Error de conexión: ' + error.message);
        }
    });
}

// Inicialización compatible con Astro
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRegister);
} else {
    initRegister();
}

document.addEventListener('astro:page-load', initRegister);