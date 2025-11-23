// auth.js - Versión compatible con módulos y script inline

// Función para manejar login (guardar datos del usuario)
async function loginHandler(response) {
  try {
    const result = await fetch('/api/exchange-code.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ code: response.code })
    });
    const data = await result.json();
    console.log('Respuesta completa del servidor:', data);
    console.log('user_photo del servidor:', data.user_photo);
    if (data.success) {
      sessionStorage.setItem('user_id', data.user_id);
      sessionStorage.setItem('user_nombre', data.user_nombre);
      sessionStorage.setItem('user_correo', data.user_correo);
      if (data.user_photo) {
        sessionStorage.setItem('user_photo', data.user_photo);
      }
      // También guardar en localStorage para persistencia
      localStorage.setItem('user_id', data.user_id);
      localStorage.setItem('user_nombre', data.user_nombre);
      localStorage.setItem('user_correo', data.user_correo);
      if (data.user_photo) {
        localStorage.setItem('user_photo', data.user_photo);
      }
      window.location.href = '/dashboardpage/Dashboard';
    } else {
      console.error('Login fallido:', data.message);
    }
  } catch (error) {
    console.error('Error en login:', error);
  }
}

// Función para limpiar datos y redirigir en logout
function logout() {
  sessionStorage.clear();
  localStorage.clear();
  window.location.href = '/LoginRegisterPages/loginPage'; // cambia si usas otra ruta para login
}

// Inicializa evento de logout en botón si existe
document.addEventListener('DOMContentLoaded', function () {
  const btnLogout = document.getElementById('logoutBtn');
  if (btnLogout) {
    btnLogout.addEventListener('click', e => {
      e.preventDefault();
      logout();
    });
  }
});

// Hacer las funciones disponibles globalmente para uso inline
window.loginHandler = loginHandler;
window.logout = logout;

// Exportar para uso como módulo si es posible (no causará error si no se usa como módulo)
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { loginHandler, logout };
}
