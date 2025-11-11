// auth-check.js - Verificación de autenticación inmediata (versión inline)

// Esta función se ejecuta inmediatamente antes de que se cargue la página
(function() {
  // Verificar si el usuario está autenticado
  function isAuthenticated() {
    const userId = sessionStorage.getItem('user_id') || localStorage.getItem('user_id');
    const userNombre = sessionStorage.getItem('user_nombre') || localStorage.getItem('user_nombre');
    return !!(userId && userNombre);
  }

  // Obtener la ruta actual
  const currentPath = window.location.pathname;
  
  // Rutas públicas (accesibles sin autenticación)
  const publicPaths = ['/mainPage', '/', '/LoginRegisterPages/LoginPage', '/LoginRegisterPages/RegisterPage'];
  
  // Rutas privadas (requieren autenticación)
  const privatePaths = ['/dashboardpage/Dashboard', '/dashboardpage'];
  
  // Verificar si la ruta actual es pública
  const isPublicPath = publicPaths.includes(currentPath) || currentPath === '/';
  
  // Verificar si la ruta actual es privada
  const isPrivatePath = privatePaths.some(path => currentPath.startsWith(path));

  // Si está autenticado y trata de acceder a una página pública
  if (isAuthenticated() && isPublicPath) {
    // Redirigir al dashboard
    window.location.replace('/dashboardpage/Dashboard');
    return; // Importante: detener la ejecución
  }

  // Si no está autenticado y trata de acceder a una página privada
  if (!isAuthenticated() && isPrivatePath) {
    // Redirigir al login
    window.location.replace('/LoginRegisterPages/LoginPage');
    return; // Importante: detener la ejecución
  }
})();