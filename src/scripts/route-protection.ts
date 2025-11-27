const PUBLIC_ROUTES = [
    '/mainPage',
    '/',
    '/LoginRegisterPages/LoginPage',
    '/LoginRegisterPages/RegisterPage'
];

const PRIVATE_ROUTES_PREFIX = [
    '/dashboardpage'
];

function getSafeSessionItem(key: string): string | null {
    try {
        return window.sessionStorage.getItem(key);
    } catch (e) {
        return null;
    }
}

// 3. Verificar autenticación
function isAuthenticated(): boolean {
    const userId = getSafeSessionItem('user_id') || window.SafeStorage.getItem('user_id');
    const userNombre = getSafeSessionItem('user_nombre') || window.SafeStorage.getItem('user_nombre');

    // Retorna true si ambos existen
    return !!(userId && userNombre);
}

// 4. Función principal de chequeo
function checkAuthAndRedirect() {
    const currentPath = window.location.pathname;
    const normalizedPath = currentPath.endsWith('/') && currentPath.length > 1 
        ? currentPath.slice(0, -1) 
        : currentPath;

    const isAuth = isAuthenticated();
    const isPrivate = PRIVATE_ROUTES_PREFIX.some(prefix => normalizedPath.startsWith(prefix));

    if (isPrivate && !isAuth) {
        console.warn('Acceso no autorizado. Redirigiendo a Login...');
        window.location.replace('/LoginRegisterPages/LoginPage');
        return; 
    }
    const isPublic = PUBLIC_ROUTES.includes(normalizedPath);

    if (isPublic && isAuth) {
        console.info('Usuario ya autenticado. Redirigiendo a Dashboard...');
        window.location.replace('/dashboardpage/Dashboard');
        return;
    }
}

document.addEventListener('astro:page-load', () => {
    checkAuthAndRedirect();
});

checkAuthAndRedirect();