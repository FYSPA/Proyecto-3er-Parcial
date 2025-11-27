// Definimos la interfaz para que TS entienda qué es SafeStorage
declare global {
    interface Window {
        SafeStorage: {
            getItem: (key: string) => string | null;
            setItem: (key: string, value: string) => void;
            removeItem: (key: string) => void;
            clear: () => void;
        };
    }
}

// Función autoejecutable para inicializar el storage
(function() {
    let memoryStore: Record<string, string> = {};
    let useMemory = false;

    try {
        const test = window.localStorage; 
        test.setItem('__test__', '1');
        test.removeItem('__test__');
    } catch (e) {
        console.warn('Almacenamiento local bloqueado. Usando memoria temporal (RAM).'+ e);
        useMemory = true;
    }

    // Definimos window.SafeStorage
    window.SafeStorage = {
        getItem: function(key: string) {
            if (useMemory) return memoryStore[key] || null;
            return window.localStorage.getItem(key);
        },
        setItem: function(key: string, value: string) {
            if (useMemory) memoryStore[key] = value;
            else window.localStorage.setItem(key, value);
        },
        removeItem: function(key: string) {
            if (useMemory) delete memoryStore[key];
            else window.localStorage.removeItem(key);
        },
        clear: function() {
            if (useMemory) memoryStore = {};
            else window.localStorage.clear();
        }
    };
})();

export {};