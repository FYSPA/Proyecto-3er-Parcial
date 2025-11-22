function cargarDatosUsuario() {
    let userName = sessionStorage.getItem('user_nombre') || localStorage.getItem('user_nombre');
    let userPhoto = sessionStorage.getItem('user_photo') || localStorage.getItem('user_photo');
    const greeting = document.getElementById('userGreeting');
    const avatarImg = document.getElementById('avatarImg');

    if (greeting && userName) {
        greeting.textContent = `${userName}`;
    }

    // Fallback universal de avatar
    if (avatarImg) {
        function setAvatarIniciales() {
            if (userName) {
                const initials = userName.split(' ').map(n => n[0]).join('');
                avatarImg.src = `https://ui-avatars.com/api/?name=${initials}&background=4CAF50&color=fff&font-size=0.4&bold=true`;
            } else {
                setAvatarIniciales();
            }
        }
    }
    document.addEventListener('DOMContentLoaded', cargarDatosUsuario);
    cargarDatosUsuario();