/**
 * Password Visibility Toggle Utility
 * Adds eye icon to password fields to show/hide password
 */

class PasswordToggle {
    constructor() {
        this.init();
    }

    init() {
        // Find all password inputs
        const passwordInputs = document.querySelectorAll('input[type="password"]');
        
        passwordInputs.forEach(input => {
            // Skip if already wrapped
            if (input.parentElement.classList.contains('password-wrapper')) {
                return;
            }
            
            // Create wrapper
            const wrapper = document.createElement('div');
            wrapper.className = 'password-wrapper';
            wrapper.style.cssText = 'position: relative; width: 100%;';
            
            // Insert wrapper before input
            input.parentNode.insertBefore(wrapper, input);
            
            // Move input into wrapper
            wrapper.appendChild(input);
            
            // Create toggle button
            const toggleBtn = document.createElement('button');
            toggleBtn.type = 'button';
            toggleBtn.className = 'password-toggle-btn';
            toggleBtn.setAttribute('aria-label', 'Show password');
            toggleBtn.innerHTML = '<i class="fa fa-eye"></i>';
            toggleBtn.style.cssText = `
                position: absolute;
                right: 15px;
                top: 50%;
                transform: translateY(-50%);
                background: none;
                border: none;
                color: #999;
                cursor: pointer;
                font-size: 1.1rem;
                padding: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: color 0.3s ease;
                outline: none;
                z-index: 1;
            `;
            
            // Add hover effect
            toggleBtn.addEventListener('mouseenter', () => {
                toggleBtn.style.color = '#27ae60';
            });
            toggleBtn.addEventListener('mouseleave', () => {
                if (input.type === 'password') {
                    toggleBtn.style.color = '#999';
                } else {
                    toggleBtn.style.color = '#27ae60';
                }
            });
            
            // Toggle password visibility
            toggleBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                if (input.type === 'password') {
                    input.type = 'text';
                    toggleBtn.innerHTML = '<i class="fa fa-eye-slash"></i>';
                    toggleBtn.style.color = '#27ae60';
                    toggleBtn.setAttribute('aria-label', 'Hide password');
                } else {
                    input.type = 'password';
                    toggleBtn.innerHTML = '<i class="fa fa-eye"></i>';
                    toggleBtn.style.color = '#999';
                    toggleBtn.setAttribute('aria-label', 'Show password');
                }
            });
            
            // Adjust input padding to make room for icon
            input.style.paddingRight = '50px';
            
            // Append toggle button
            wrapper.appendChild(toggleBtn);
        });
    }

    // Static method to initialize all password toggles
    static initAll() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                new PasswordToggle();
            });
        } else {
            new PasswordToggle();
        }
    }
}

// Auto-initialize
PasswordToggle.initAll();

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PasswordToggle;
}

