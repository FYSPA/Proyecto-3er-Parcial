try {
    const res = await fetch(apiHost + '/registro.php', {
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
            window.location.href = apiHost + '/verificacion.php';
        }, 500);
    } else {
        console.error('Error:', json.message);
        alert(json.message || 'Error');
    }
} catch (err) {
    console.error('Error:', err);
    alert('Error: ' + err.message);
}

if (document.getElementById('registerForm')) {
    initRegister();
} else {
    document.addEventListener('astro:page-load', initRegister);
}