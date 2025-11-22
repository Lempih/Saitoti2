/**
 * Image Preview Utility
 * Handles image upload preview functionality
 */

class ImagePreview {
    constructor(options = {}) {
        this.defaultOptions = {
            inputSelector: 'input[type="file"][accept*="image"]',
            previewSelector: '.image-preview',
            maxSize: 5 * 1024 * 1024, // 5MB default
            allowedTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'],
            defaultImage: null,
            showRemoveButton: true,
            previewClass: 'image-preview-container',
            errorCallback: null
        };
        
        this.options = { ...this.defaultOptions, ...options };
        this.init();
    }

    init() {
        // Find all file inputs with image accept type
        const inputs = document.querySelectorAll(this.options.inputSelector);
        
        inputs.forEach(input => {
            // Create preview container if it doesn't exist
            let previewContainer = input.parentElement.querySelector(`.${this.options.previewClass}`);
            
            if (!previewContainer) {
                previewContainer = this.createPreviewContainer(input);
                input.parentElement.insertBefore(previewContainer, input.nextSibling);
            }
            
            // Add event listener
            input.addEventListener('change', (e) => this.handleFileSelect(e, input, previewContainer));
            
            // Show default image if set
            if (this.options.defaultImage) {
                this.showImage(this.options.defaultImage, previewContainer);
            }
        });
    }

    createPreviewContainer(input) {
        const container = document.createElement('div');
        container.className = this.options.previewClass;
        container.style.cssText = `
            margin: 15px 0;
            text-align: center;
            display: none;
        `;
        
        const preview = document.createElement('div');
        preview.className = 'image-preview';
        preview.style.cssText = `
            position: relative;
            display: inline-block;
            max-width: 300px;
            max-height: 300px;
            border: 2px dashed #ddd;
            border-radius: 10px;
            padding: 10px;
            background: #f9f9f9;
        `;
        
        const img = document.createElement('img');
        img.style.cssText = `
            max-width: 100%;
            max-height: 280px;
            border-radius: 5px;
            display: none;
        `;
        img.alt = 'Image preview';
        
        const placeholder = document.createElement('div');
        placeholder.className = 'image-placeholder';
        placeholder.innerHTML = '<i class="fa fa-image" style="font-size: 3rem; color: #ccc;"></i><p style="color: #999; margin-top: 10px;">No image selected</p>';
        
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'remove-image-btn';
        removeBtn.innerHTML = '<i class="fa fa-times"></i> Remove';
        removeBtn.style.cssText = `
            margin-top: 10px;
            padding: 8px 15px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: none;
        `;
        removeBtn.onclick = () => this.removeImage(input, container);
        
        preview.appendChild(img);
        preview.appendChild(placeholder);
        container.appendChild(preview);
        
        if (this.options.showRemoveButton) {
            container.appendChild(removeBtn);
        }
        
        return container;
    }

    handleFileSelect(event, input, previewContainer) {
        const file = event.target.files[0];
        
        if (!file) {
            this.hidePreview(previewContainer);
            return;
        }
        
        // Validate file type
        if (!this.options.allowedTypes.includes(file.type)) {
            const errorMsg = `Invalid file type. Please select: ${this.options.allowedTypes.map(t => t.split('/')[1]).join(', ')}`;
            this.showError(errorMsg, previewContainer);
            input.value = '';
            return;
        }
        
        // Validate file size
        if (file.size > this.options.maxSize) {
            const maxSizeMB = (this.options.maxSize / (1024 * 1024)).toFixed(1);
            const errorMsg = `File size too large. Maximum size is ${maxSizeMB}MB`;
            this.showError(errorMsg, previewContainer);
            input.value = '';
            return;
        }
        
        // Show preview
        const reader = new FileReader();
        reader.onload = (e) => {
            this.showImage(e.target.result, previewContainer);
        };
        reader.onerror = () => {
            this.showError('Error reading file. Please try again.', previewContainer);
            input.value = '';
        };
        reader.readAsDataURL(file);
    }

    showImage(imageSrc, previewContainer) {
        previewContainer.style.display = 'block';
        
        const img = previewContainer.querySelector('img');
        const placeholder = previewContainer.querySelector('.image-placeholder');
        const removeBtn = previewContainer.querySelector('.remove-image-btn');
        const errorMsg = previewContainer.querySelector('.image-error');
        
        if (errorMsg) errorMsg.remove();
        
        img.src = imageSrc;
        img.style.display = 'block';
        if (placeholder) placeholder.style.display = 'none';
        if (removeBtn) removeBtn.style.display = 'inline-block';
        
        // Add hover effect
        img.onmouseenter = () => {
            img.style.opacity = '0.8';
            img.style.cursor = 'pointer';
        };
        img.onmouseleave = () => {
            img.style.opacity = '1';
        };
        img.onclick = () => {
            window.open(imageSrc, '_blank');
        };
    }

    removeImage(input, previewContainer) {
        input.value = '';
        this.hidePreview(previewContainer);
    }

    hidePreview(previewContainer) {
        previewContainer.style.display = 'none';
        const img = previewContainer.querySelector('img');
        const placeholder = previewContainer.querySelector('.image-placeholder');
        const removeBtn = previewContainer.querySelector('.remove-image-btn');
        
        if (img) {
            img.src = '';
            img.style.display = 'none';
        }
        if (placeholder) placeholder.style.display = 'block';
        if (removeBtn) removeBtn.style.display = 'none';
    }

    showError(message, previewContainer) {
        // Remove existing error
        const existingError = previewContainer.querySelector('.image-error');
        if (existingError) existingError.remove();
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'image-error';
        errorDiv.style.cssText = `
            color: #e74c3c;
            padding: 10px;
            margin-top: 10px;
            background: #ffe6e6;
            border-radius: 5px;
            font-size: 0.9rem;
        `;
        errorDiv.innerHTML = `<i class="fa fa-exclamation-circle"></i> ${message}`;
        
        previewContainer.appendChild(errorDiv);
        
        if (this.options.errorCallback) {
            this.options.errorCallback(message);
        } else if (typeof showError === 'function') {
            showError(message);
        } else {
            alert(message);
        }
    }

    // Static method to initialize all image previews on page
    static initAll(options = {}) {
        document.addEventListener('DOMContentLoaded', () => {
            new ImagePreview(options);
        });
    }
}

// Auto-initialize if DOM is ready, or wait for it
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        ImagePreview.initAll();
    });
} else {
    ImagePreview.initAll();
}

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ImagePreview;
}

