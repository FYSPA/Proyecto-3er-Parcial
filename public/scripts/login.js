function initLogin() {
    const form = document.getElementById('loginForm');
    if (!form) {
        return;
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const correo = form.querySelector('input[name="correo"]').value;
        const password = form.querySelector('input[name="password"]').value;

        if (!correo || !password) {
            alert('Por favor completa todos los campos');
            return;
        }

        const submitBtn = document.getElementById('submitBtn');
        const loadingDiv = document.getElementById('loadingDiv');
        const errorDiv = document.getElementById('errorDiv');

        submitBtn.disabled = true;
        loadingDiv.style.display = 'block';
        errorDiv.style.display = 'none';

        const formData = new FormData();
        formData.append('correo', correo);
        formData.append('password', password);

        try {
            console.log('Haciendo POST a login.php...');
            const apiUrl = window.PUBLIC_API_URL || 'http://localhost:8081';
            const res = await fetch(`${apiUrl}/api/auth/login`, {
                method: 'POST',
                body: formData
            });

            console.log('Respuesta status:', res.status);
            const json = await res.json();
            console.log('JSON:', json);

            if (json.success) {
                console.log('Login exitoso! Guardando datos y redirigiendo...');
                // Guardar en localStorage
                localStorage.setItem('user_id', json.user_id);
                localStorage.setItem('user_nombre', json.user_nombre);
                localStorage.setItem('user_correo', json.user_correo);
                localStorage.setItem('logged_in', 'true');

                // También guardar en sessionStorage para la protección de rutas
                sessionStorage.setItem('user_id', json.user_id);
                sessionStorage.setItem('user_nombre', json.user_nombre);
                sessionStorage.setItem('user_correo', json.user_correo);

                setTimeout(() => {
                    window.location.href = '/dashboardpage/Dashboard';
                }, 500);
            } else {
                console.log('Error:', json.message);
                errorDiv.textContent = json.message || 'Email o contraseña incorrectos';
                errorDiv.style.display = 'block';
                submitBtn.disabled = false;
            }
        } catch (err) {
            console.error('Excepción:', err);
            errorDiv.textContent = 'Error: ' + err.message;
            errorDiv.style.display = 'block';
            submitBtn.disabled = false;
        } finally {
            loadingDiv.style.display = 'none';
        }
    });
}

// Intentar inicializar
if (document.getElementById('loginForm')) {
    initLogin();
} else {
    document.addEventListener('astro:page-load', initLogin);
}