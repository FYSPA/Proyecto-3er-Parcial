// Para limpiar los datos cuando el usuario cierra sesión

function logout() {
    // Limpiar sessionStorage
    sessionStorage.removeItem('user_id');
    sessionStorage.removeItem('user_nombre');
    sessionStorage.removeItem('user_correo');
    sessionStorage.removeItem('user_photo');

    // Limpiar localStorage (opcional)
    localStorage.removeItem('user_nombre');
    localStorage.removeItem('user_correo');
    localStorage.removeItem('user_photo');

    // Redirigir al login
    window.location.href = '/LoginRegisterPages/LoginPage';
}
