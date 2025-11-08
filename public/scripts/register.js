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
        console.log('Host:', window.location.hostname);

        const apiHost = window.location.hostname === 'localhost'
            ? 'http://localhost:8000'
            : `http://${window.location.hostname}:8000`;

        console.log('📡 API Host:', apiHost);

        // Crear FormData CORRECTAMENTE
        const fd = new FormData();
        fd.append('nombre', nombre);
        fd.append('correo', correo);
        fd.append('password', password);
        fd.append('host_frontend', window.location.hostname);

        console.log('FormData creado con host:', window.location.hostname);

        try {
            const res = await fetch(apiHost + '/app/registro.php', {
                method: 'POST',
                body: fd
            });

            console.log('Status:', res.status);
            const json = await res.json();
            console.log('Respuesta:', json);

            if (json.success) {
                console.log('Éxito! Yendo a verificación...');

                const verifUrl = apiHost.replace(':8000', '') + ':8000/login_qr.php?code=' + json.codigo_acceso;
                console.log('URL verificación:', verifUrl);

                setTimeout(() => {
                    window.location.href = apiHost + '/app/verificacion.php';
                }, 500);
            } else {
                console.error('Error:', json.message);
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