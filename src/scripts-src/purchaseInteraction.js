
export function initPurchaseInteraction() {
    const buyBtn = document.getElementById('buy-btn');
    const canvas = document.getElementById('purchase-particles');

    if (!buyBtn || !canvas) return;

    const ctx = canvas.getContext('2d');
    let animationId;
    let audio;
    let isActive = false;

    // --- CONFIGURACIÓN CLAVE ---
    const NUM_PARTICULAS = 10;
    const TAMAÑO_PARTICULA = 50;
    const RADIO_REPULSION = 35;
    const FUERZA_REPULSION = 10;
    const IMAGEN = new Image();
    IMAGEN.src = '/logoVGS.ico'; // Assuming in public/

    // Coordenadas del ratón
    let mouse = {
        x: null,
        y: null
    };

    const COLORES = ['#a45d06 ', '#d58625 ', '#7e531f '];
    let particulas = [];

    // --- EVENTO DEL MOUSE ---
    window.addEventListener('mousemove', (event) => {
        mouse.x = event.x;
        mouse.y = event.y;
    });

    window.addEventListener('mouseout', () => {
        mouse.x = null;
        mouse.y = null;
    });

    function resizeCanvas() {
        if (!isActive && canvas.style.opacity === '0') return;
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    }
    window.addEventListener('resize', resizeCanvas);

    class Particula {
        constructor(x, y, color, tamaño) {
            this.x = x;
            this.y = canvas.height + tamaño; // Start below screen
            this.color = color;
            this.tamaño = tamaño;
            this.velocidadY = -(Math.random() * 0.375 + 0.275); // Negative for upward movement
            this.velocidadX = (Math.random() - 0.5) * 0.5;
        }

        actualizar() {
            this.y += this.velocidadY;
            this.x += this.velocidadX;

            if (mouse.x !== null) {
                const dx = this.x - mouse.x;
                const dy = this.y - mouse.y;
                const distancia = Math.sqrt(dx * dx + dy * dy);

                if (distancia < RADIO_REPULSION) {
                    const fuerza = FUERZA_REPULSION / distancia;
                    this.x += dx * fuerza;
                    this.y += dy * fuerza;
                }
            }

            // Reset if it goes above the screen
            if (this.y < -this.tamaño || this.x < -this.tamaño || this.x > canvas.width) {
                this.reiniciar();
            }
        }

        dibujar() {
            ctx.drawImage(IMAGEN, this.x, this.y, this.tamaño, this.tamaño);
        }

        reiniciar() {
            this.x = Math.random() * canvas.width;
            this.y = canvas.height + this.tamaño; // Reset to bottom
            this.color = COLORES[Math.floor(Math.random() * COLORES.length)];
            this.velocidadY = -(Math.random() * 0.5 + 0.5); // Upward
            this.velocidadX = (Math.random() - 0.5) * 0.5;
        }
    }

    function inicializarParticulas() {
        particulas = [];
        for (let i = 0; i < NUM_PARTICULAS; i++) {
            const x = Math.random() * canvas.width;
            const y = Math.random() * canvas.height;
            const color = COLORES[Math.floor(Math.random() * COLORES.length)];
            particulas.push(new Particula(x, y, color, TAMAÑO_PARTICULA));
        }
    }

    function animar() {
        if (!isActive && canvas.style.opacity === '0') return; // Stop loop only after fade out
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        particulas.forEach(p => {
            p.actualizar();
            p.dibujar();
        });

        animationId = requestAnimationFrame(animar);
    }

    function startEffect() {
        if (isActive) return;
        isActive = true;

        resizeCanvas();
        inicializarParticulas();

        // Fade in
        canvas.style.opacity = '1';

        animar();

        // Play Audio
        audio = new Audio('/sound-buy.wav');
        audio.play().catch(e => console.error("Audio play failed", e));

        // Stop after 5 seconds
        setTimeout(stopEffect, 5000);
    }

    function stopEffect() {
        isActive = false;
        // Fade out
        canvas.style.opacity = '0';

        if (audio) {
            audio.pause();
            audio.currentTime = 0;
        }
        // Stop animation loop after transition (0.5s)
        setTimeout(() => {
            cancelAnimationFrame(animationId);
        }, 500);
    }

    buyBtn.addEventListener('click', startEffect);
}
