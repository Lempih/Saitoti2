/**
 * Form Handling JavaScript
 * Ensures all forms submit properly and buttons work
 */

document.addEventListener('DOMContentLoaded', function() {
    // Handle all form submissions
    var forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            var submitBtn = form.querySelector('input[type="submit"], button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                // Show loading state
                var originalValue = submitBtn.value || submitBtn.textContent;
                submitBtn.disabled = true;
                if (submitBtn.tagName === 'INPUT') {
                    submitBtn.value = submitBtn.value.replace('Submit', 'Submitting...')
                                                     .replace('Login', 'Logging in...')
                                                     .replace('Register', 'Registering...')
                                                     .replace('Sign In', 'Signing in...')
                                                     .replace('Create', 'Creating...')
                                                     .replace('Update', 'Updating...')
                                                     .replace('Delete', 'Deleting...')
                                                     || 'Processing...';
                }
                // Allow form to submit - don't prevent default
                return true;
            }
        });
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

