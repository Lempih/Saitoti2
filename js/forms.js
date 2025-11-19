/**
 * Form Handling JavaScript
 * MINIMAL - Only shows loading states, never prevents submission
 */

document.addEventListener('DOMContentLoaded', function() {
    // Only handle forms that don't already have handlers
    var forms = document.querySelectorAll('form:not([data-no-handler])');
    forms.forEach(function(form) {
        // Skip if already handled
        if (form.dataset.handlerAdded) return;
        form.dataset.handlerAdded = 'true';
        
        form.addEventListener('submit', function(e) {
            // NEVER prevent default - just show loading
            var submitBtn = form.querySelector('input[type="submit"], button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.disabled = true;
                if (submitBtn.tagName === 'INPUT') {
                    var val = submitBtn.value;
                    if (val.includes('Sign In')) submitBtn.value = 'Signing in...';
                    else if (val.includes('Login')) submitBtn.value = 'Logging in...';
                    else if (val.includes('Submit')) submitBtn.value = 'Submitting...';
                    else if (val.includes('Register')) submitBtn.value = 'Registering...';
                    else if (val.includes('Create')) submitBtn.value = 'Creating...';
                    else if (val.includes('Update')) submitBtn.value = 'Updating...';
                    else if (val.includes('Delete')) submitBtn.value = 'Deleting...';
                    else submitBtn.value = 'Processing...';
                }
            }
            // Form submits normally - no return false, no preventDefault
        });
    });
});
