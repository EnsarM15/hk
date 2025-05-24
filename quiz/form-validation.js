function validateForm(formId, rules) {
    const form = document.getElementById(formId);
    
    if (!form) return;
    
    form.addEventListener('submit', function(event) {
        let isValid = true;
        
        // Clear previous error messages
        form.querySelectorAll('.form-error').forEach(error => error.remove());
        
        for (const fieldName in rules) {
            const field = form.querySelector(`[name="${fieldName}"]`);
            
            if (!field) continue;
            
            const fieldRules = rules[fieldName];
            const fieldValue = field.value.trim();
            
            // Required validation
            if (fieldRules.required && fieldValue === '') {
                showError(field, 'This field is required');
                isValid = false;
                continue;
            }
            
            // Email validation
            if (fieldRules.email && fieldValue !== '') {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(fieldValue)) {
                    showError(field, 'Please enter a valid email address');
                    isValid = false;
                }
            }
            
            // Min length validation
            if (fieldRules.minLength && fieldValue.length < fieldRules.minLength) {
                showError(field, `Must be at least ${fieldRules.minLength} characters`);
                isValid = false;
            }
            
            // Max length validation
            if (fieldRules.maxLength && fieldValue.length > fieldRules.maxLength) {
                showError(field, `Cannot exceed ${fieldRules.maxLength} characters`);
                isValid = false;
            }
            
            // Match validation (for password confirmation)
            if (fieldRules.match) {
                const matchField = form.querySelector(`[name="${fieldRules.match}"]`);
                if (matchField && fieldValue !== matchField.value) {
                    showError(field, fieldRules.matchError || 'Fields do not match');
                    isValid = false;
                }
            }
            
            // Pattern validation
            if (fieldRules.pattern && fieldValue !== '') {
                const pattern = new RegExp(fieldRules.pattern);
                if (!pattern.test(fieldValue)) {
                    showError(field, fieldRules.patternError || 'Invalid format');
                    isValid = false;
                }
            }
            
            // Custom validation
            if (fieldRules.custom && typeof fieldRules.custom === 'function') {
                const customResult = fieldRules.custom(fieldValue, form);
                if (customResult !== true) {
                    showError(field, customResult || 'Invalid value');
                    isValid = false;
                }
            }
        }
        
        if (!isValid) {
            event.preventDefault();
            
            // Focus on the first field with an error
            const firstErrorField = form.querySelector('.input-error');
            if (firstErrorField) {
                firstErrorField.focus();
            }
        }
    });
    
    // Live validation for fields as they change
    if (rules.liveValidation) {
        for (const fieldName in rules) {
            if (fieldName === 'liveValidation') continue;
            
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (!field) continue;
            
            field.addEventListener('blur', function() {
                validateField(field, rules[fieldName], form);
            });
            
            // For password fields, validate confirmation field when password changes
            if (fieldName === 'password') {
                field.addEventListener('input', function() {
                    const confirmField = form.querySelector('[name="confirm_password"]');
                    if (confirmField && confirmField.value) {
                        validateField(confirmField, rules['confirm_password'], form);
                    }
                });
            }
        }
    }
}

// Validate a single field
function validateField(field, rules, form) {
    // Clear previous error
    const formGroup = field.closest('.form-group');
    const existingError = formGroup.querySelector('.form-error');
    if (existingError) {
        existingError.remove();
    }
    
    field.classList.remove('input-error');
    
    const fieldValue = field.value.trim();
    
    // Required validation
    if (rules.required && fieldValue === '') {
        showError(field, 'This field is required');
        return false;
    }
    
    // Email validation
    if (rules.email && fieldValue !== '') {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(fieldValue)) {
            showError(field, 'Please enter a valid email address');
            return false;
        }
    }
    
    // Min length validation
    if (rules.minLength && fieldValue.length < rules.minLength) {
        showError(field, `Must be at least ${rules.minLength} characters`);
        return false;
    }
    
    // Max length validation
    if (rules.maxLength && fieldValue.length > rules.maxLength) {
        showError(field, `Cannot exceed ${rules.maxLength} characters`);
        return false;
    }
    
    // Match validation
    if (rules.match) {
        const matchField = form.querySelector(`[name="${rules.match}"]`);
        if (matchField && fieldValue !== matchField.value) {
            showError(field, rules.matchError || 'Fields do not match');
            return false;
        }
    }
    
    // Pattern validation
    if (rules.pattern && fieldValue !== '') {
        const pattern = new RegExp(rules.pattern);
        if (!pattern.test(fieldValue)) {
            showError(field, rules.patternError || 'Invalid format');
            return false;
        }
    }
    
    // Custom validation
    if (rules.custom && typeof rules.custom === 'function') {
        const customResult = rules.custom(fieldValue, form);
        if (customResult !== true) {
            showError(field, customResult || 'Invalid value');
            return false;
        }
    }
    
    return true;
}

// Display error message for a field
function showError(field, message) {
    const errorElement = document.createElement('div');
    errorElement.classList.add('form-error');
    errorElement.textContent = message;
    
    const formGroup = field.closest('.form-group');
    formGroup.appendChild(errorElement);
    
    // Add error class to input
    field.classList.add('input-error');
}

// Export the validation function
window.validateForm = validateForm;