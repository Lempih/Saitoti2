/**
 * Toast Notification System
 * Modern, smooth toast notifications for user feedback
 */

class Toast {
    constructor() {
        this.container = null;
        this.init();
    }

    init() {
        // Create toast container if it doesn't exist
        if (!document.getElementById('toast-container')) {
            this.container = document.createElement('div');
            this.container.id = 'toast-container';
            this.container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                display: flex;
                flex-direction: column;
                gap: 10px;
                pointer-events: none;
            `;
            document.body.appendChild(this.container);
        } else {
            this.container = document.getElementById('toast-container');
        }
    }

    show(message, type = 'info', duration = 4000) {
        const toast = document.createElement('div');
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };

        const colors = {
            success: '#27ae60',
            error: '#e74c3c',
            warning: '#f39c12',
            info: '#3498db'
        };

        toast.className = 'toast-notification';
        toast.style.cssText = `
            background: white;
            color: #333;
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 300px;
            max-width: 400px;
            pointer-events: auto;
            transform: translateX(400px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            border-left: 4px solid ${colors[type] || colors.info};
        `;

        toast.innerHTML = `
            <i class="fa ${icons[type] || icons.info}" style="color: ${colors[type] || colors.info}; font-size: 20px;"></i>
            <span style="flex: 1; font-family: 'Poppins', sans-serif; font-size: 14px; line-height: 1.5;">${message}</span>
            <button class="toast-close" style="background: none; border: none; color: #999; cursor: pointer; font-size: 18px; padding: 0; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;" onclick="this.parentElement.remove()">×</button>
        `;

        this.container.appendChild(toast);

        // Animate in
        setTimeout(() => {
            toast.style.transform = 'translateX(0)';
            toast.style.opacity = '1';
        }, 10);

        // Auto remove
        if (duration > 0) {
            setTimeout(() => {
                this.remove(toast);
            }, duration);
        }

        return toast;
    }

    remove(toast) {
        toast.style.transform = 'translateX(400px)';
        toast.style.opacity = '0';
        setTimeout(() => {
            if (toast.parentElement) {
                toast.parentElement.removeChild(toast);
            }
        }, 300);
    }

    success(message, duration) {
        return this.show(message, 'success', duration);
    }

    error(message, duration) {
        return this.show(message, 'error', duration);
    }

    warning(message, duration) {
        return this.show(message, 'warning', duration);
    }

    info(message, duration) {
        return this.show(message, 'info', duration);
    }
}

// Global toast instance
const toast = new Toast();

// Helper functions for easy access
function showToast(message, type = 'info', duration = 4000) {
    return toast.show(message, type, duration);
}

function showSuccess(message, duration = 4000) {
    return toast.success(message, duration);
}

function showError(message, duration = 5000) {
    return toast.error(message, duration);
}

function showWarning(message, duration = 4000) {
    return toast.warning(message, duration);
}

function showInfo(message, duration = 4000) {
    return toast.info(message, duration);
}

