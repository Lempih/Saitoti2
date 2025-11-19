/**
 * Form Handling JavaScript
 * Ensures all forms submit properly and buttons work
 */

document.addEventListener('DOMContentLoaded', function() {
    // Handle all form submissions - but DON'T prevent default
    var forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        // Check if form already has a submit handler
        if (form.dataset.handlerAdded) return;
        form.dataset.handlerAdded = 'true';
        
        form.addEventListener('submit', function(e) {
            // DO NOT call e.preventDefault() - let form submit normally
            var submitBtn = form.querySelector('input[type="submit"], button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                // Show loading state
                submitBtn.disabled = true;
                if (submitBtn.tagName === 'INPUT') {
                    var currentValue = submitBtn.value;
                    if (currentValue.indexOf('Sign In') !== -1) {
                        submitBtn.value = 'Signing in...';
                    } else if (currentValue.indexOf('Login') !== -1) {
                        submitBtn.value = 'Logging in...';
                    } else if (currentValue.indexOf('Submit') !== -1) {
                        submitBtn.value = 'Submitting...';
                    } else if (currentValue.indexOf('Register') !== -1) {
                        submitBtn.value = 'Registering...';
                    } else if (currentValue.indexOf('Create') !== -1) {
                        submitBtn.value = 'Creating...';
                    } else if (currentValue.indexOf('Update') !== -1) {
                        submitBtn.value = 'Updating...';
                    } else if (currentValue.indexOf('Delete') !== -1) {
                        submitBtn.value = 'Deleting...';
                    } else {
                        submitBtn.value = 'Processing...';
                    }
                }
                // Form will submit normally - we're not preventing it
            }
        }, false); // Use capture phase false
    });

    // Handle all button clicks
    var buttons = document.querySelectorAll('button, input[type="submit"], input[type="button"]');
    buttons.forEach(function(button) {
        // Skip if already has onclick handler
        if (button.onclick) return;
        
        button.addEventListener('click', function(e) {
            // If it's a submit button, let the form handler take care of it
            if (button.type === 'submit') {
                return true;
            }
            
            // For other buttons, ensure they work
            if (button.onclick && typeof button.onclick === 'function') {
                return button.onclick(e);
            }
        });
    });

    // Ensure all links work
    var links = document.querySelectorAll('a[href]');
    links.forEach(function(link) {
        link.addEventListener('click', function(e) {
            // Allow normal link behavior
            return true;
        });
    });

    console.log('Form handlers initialized');
});

