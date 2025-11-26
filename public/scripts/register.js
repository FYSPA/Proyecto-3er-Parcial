function initRegister() {
    const form = document.getElementById('registerForm');
    if (!form) return;

    console.log('Formulario de Registro encontrado');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const nombre = form.querySelector('input[name="nombre"]').value;
        const correo = form.querySelector('input[name="correo"]').value;
        const password = form.querySelector('input[name="password"]').value;

        console.log('Datos:', { nombre, correo });

        const apiHost = window.PUBLIC_API_URL || 'http://localhost:8000';
        console.log('📡 API Host:', apiHost);

        const fd = new FormData();
        fd.append('nombre', nombre);
        fd.append('correo', correo);
        fd.append('password', password);
        fd.append('host_frontend', window.location.hostname);

        try {
            const res = await fetch(`${apiHost}/api/auth/registro`, {
                method: 'POST',
                body: fd
            });

            console.log('Status:', res.status);
            const json = await res.json();
            console.log('Respuesta:', json);

            if (json.success) {
                console.log('Éxito! Yendo a verificación...');

                setTimeout(() => {
                    // Redirigir a la página de verificación del backend
                    // Pasamos el origen del frontend para que sepa volver
                    const frontUrl = window.location.origin;
                    window.location.href = `${apiHost}/verificacion.php?frontend=${encodeURIComponent(frontUrl)}`;
                }, 500);
            } else {
                console.error('Error:', json.message);
                if (json.message.includes("Duplicate entry")) {
                    const res = await fetch(`${apiHost}/api/auth/resendlogin`, {
                        method: 'POST',
                        body: fd
                    });
                    console.log(res)
                    return
                }
                alert(json.message || 'Error');
            }
        } catch (err) {
            console.error('Error:', err);
            alert('Error: ' + err.message);
        }
    });
}

if (document.getElementById('registerForm')) {
    initRegister();
} else {
    document.addEventListener('astro:page-load', initRegister);
}