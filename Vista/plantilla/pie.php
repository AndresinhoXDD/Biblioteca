        </div>
    </div>
    
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Sistema de Biblioteca</p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para validar formularios en tiempo real
        function validarFormulario(formId, reglas) {
            const form = document.getElementById(formId);
            if (!form) return;
            
            const inputs = form.querySelectorAll('input, select, textarea');
            
            // Validar al enviar
            form.addEventListener('submit', function(e) {
                let formValido = true;
                
                inputs.forEach(input => {
                    if (!validarCampo(input)) {
                        formValido = false;
                    }
                });
                
                if (!formValido) {
                    e.preventDefault();
                }
            });
            
            // Validar en tiempo real
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    validarCampo(input);
                });
                
                input.addEventListener('blur', function() {
                    validarCampo(input);
                });
            });
            
            function validarCampo(input) {
                const nombre = input.name;
                if (!reglas[nombre]) return true;
                
                const valor = input.value.trim();
                const regla = reglas[nombre];
                const mensajeElement = document.getElementById(`${nombre}-error`);
                
                let valido = true;
                let mensaje = '';
                
                // Validar requerido
                if (regla.requerido && valor === '') {
                    valido = false;
                    mensaje = regla.mensajeRequerido || 'Este campo es obligatorio';
                }
                // Validar patrón
                else if (regla.patron && !regla.patron.test(valor) && valor !== '') {
                    valido = false;
                    mensaje = regla.mensajePatron || 'Formato inválido';
                }
                // Validar longitud mínima
                else if (regla.minLength && valor.length < regla.minLength && valor !== '') {
                    valido = false;
                    mensaje = `Debe tener al menos ${regla.minLength} caracteres`;
                }
                // Validar longitud máxima
                else if (regla.maxLength && valor.length > regla.maxLength) {
                    valido = false;
                    mensaje = `Debe tener máximo ${regla.maxLength} caracteres`;
                }
                // Validar función personalizada
                else if (regla.validador && !regla.validador(valor)) {
                    valido = false;
                    mensaje = regla.mensajeValidador || 'Valor inválido';
                }
                
                // Mostrar/ocultar mensaje de error
                if (mensajeElement) {
                    mensajeElement.textContent = mensaje;
                    mensajeElement.style.display = valido ? 'none' : 'block';
                }
                
                // Aplicar clases de validación
                input.classList.remove('is-valid', 'is-invalid');
                input.classList.add(valido ? 'is-valid' : 'is-invalid');
                
                return valido;
            }
        }
        
        // Función para alternar visibilidad de contraseña
        function setupPasswordToggles() {
            const toggles = document.querySelectorAll('.password-toggle');
            toggles.forEach(toggle => {
                toggle.addEventListener('click', function() {
                    const input = this.previousElementSibling;
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
                });
            });
        }
        
        // Inicializar funciones cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            setupPasswordToggles();
        });
    </script>
</body>
</html>