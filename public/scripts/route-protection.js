// route-protection.js - Versión sin export para uso inline

// Función para verificar si el usuario está autenticado
function isAuthenticated() {
  // Verificar tanto sessionStorage como localStorage
  const userId = sessionStorage.getItem('user_id') || localStorage.getItem('user_id');
  const userNombre = sessionStorage.getItem('user_nombre') || localStorage.getItem('user_nombre');
  return !!(userId && userNombre);
}

// Función para proteger rutas públicas (redirigir a dashboard si está autenticado)
function protectPublicRoutes() {
  if (isAuthenticated()) {
    // Si está autenticado y trata de acceder a una página pública, redirigir al dashboard
    const publicPaths = ['/mainPage', '/', '/LoginRegisterPages/LoginPage', '/LoginRegisterPages/RegisterPage'];
    const currentPath = window.location.pathname;
    
    if (publicPaths.includes(currentPath)) {
      window.location.replace('/dashboardpage/Dashboard');
      return true;
    }
  }
  return false;
}

// Función para proteger rutas privadas (redirigir a login si no está autenticado)
function protectPrivateRoutes() {
  if (!isAuthenticated()) {
    // Si no está autenticado y trata de acceder al dashboard, redirigir al login
    const privatePaths = ['/dashboardpage/Dashboard', '/dashboardpage'];
    const currentPath = window.location.pathname;
    
    if (privatePaths.some(path => currentPath.startsWith(path))) {
      window.location.replace('/LoginRegisterPages/LoginPage');
      return true;
    }
  }
  return false;
}

// Función para obtener la ruta de redirección según el estado de autenticación
function getRedirectPath() {
  if (isAuthenticated()) {
    return '/dashboardpage/Dashboard';
  } else {
    return '/LoginRegisterPages/LoginPage';
  }
}

// Inicializar protección de rutas cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
  // Primero proteger rutas privadas (más importante)
  protectPrivateRoutes();
  // Luego proteger rutas públicas
  protectPublicRoutes();
});

// También ejecutar inmediatamente por si ya está cargado el DOM
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', function() {
    protectPrivateRoutes();
    protectPublicRoutes();
  });
} else {
  // DOM ya está listo
  protectPrivateRoutes();
  protectPublicRoutes();
}