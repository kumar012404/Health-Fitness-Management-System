/**
 * Form Validation - Health and Fitness Management System
 * Client-side validation for forms
 */

// Email validation regex
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

// Password requirements
const passwordRequirements = {
    minLength: 8,
    hasUppercase: /[A-Z]/,
    hasLowercase: /[a-z]/,
    hasNumber: /[0-9]/
};

/**
 * Validate email format
 * @param {string} email 
 * @returns {boolean}
 */
function validateEmail(email) {
    return emailRegex.test(email);
}

/**
 * Validate password strength
 * @param {string} password 
 * @returns {object}
 */
function validatePassword(password) {
    const result = {
        isValid: true,
        errors: [],
        strength: 0
    };

    if (password.length < passwordRequirements.minLength) {
        result.errors.push('Password must be at least 8 characters');
        result.isValid = false;
    } else {
        result.strength += 25;
    }

    if (!passwordRequirements.hasUppercase.test(password)) {
        result.errors.push('Password must contain an uppercase letter');
        result.isValid = false;
    } else {
        result.strength += 25;
    }

    if (!passwordRequirements.hasLowercase.test(password)) {
        result.errors.push('Password must contain a lowercase letter');
        result.isValid = false;
    } else {
        result.strength += 25;
    }

    if (!passwordRequirements.hasNumber.test(password)) {
        result.errors.push('Password must contain a number');
        result.isValid = false;
    } else {
        result.strength += 25;
    }

    return result;
}

/**
 * Validate required fields
 * @param {HTMLFormElement} form 
 * @returns {boolean}
 */
function validateRequired(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        clearError(field);
        
        if (!field.value.trim()) {
            showError(field, 'This field is required');
            isValid = false;
        }
    });
    
    return isValid;
}

/**
 * Show error message for a field
 * @param {HTMLElement} field 
 * @param {string} message 
 */
function showError(field, message) {
    field.classList.add('is-invalid');
    
    let errorDiv = field.nextElementSibling;
    if (!errorDiv || !errorDiv.classList.contains('invalid-feedback')) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        field.parentNode.appendChild(errorDiv);
    }
    errorDiv.textContent = message;
}

/**
 * Clear error from a field
 * @param {HTMLElement} field 
 */
function clearError(field) {
    field.classList.remove('is-invalid');
    const errorDiv = field.parentNode.querySelector('.invalid-feedback');
    if (errorDiv) {
        errorDiv.remove();
    }
}

/**
 * Validate numeric range
 * @param {number} value 
 * @param {number} min 
 * @param {number} max 
 * @returns {boolean}
 */
function validateRange(value, min, max) {
    const num = parseFloat(value);
    return !isNaN(num) && num >= min && num <= max;
}

/**
 * Validate height (50-300 cm)
 * @param {number} height 
 * @returns {boolean}
 */
function validateHeight(height) {
    return validateRange(height, 50, 300);
}

/**
 * Validate weight (20-500 kg)
 * @param {number} weight 
 * @returns {boolean}
 */
function validateWeight(weight) {
    return validateRange(weight, 20, 500);
}

/**
 * Validate age (1-150)
 * @param {number} age 
 * @returns {boolean}
 */
function validateAge(age) {
    return validateRange(age, 1, 150);
}

/**
 * Add real-time validation to a form
 * @param {string} formId 
 */
function initFormValidation(formId) {
    const form = document.getElementById(formId);
    if (!form) return;

    // Real-time validation on input
    form.querySelectorAll('input, select, textarea').forEach(field => {
        field.addEventListener('blur', function() {
            validateField(this);
        });

        field.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                validateField(this);
            }
        });
    });

    // Form submission validation
    form.addEventListener('submit', function(e) {
        if (!validateRequired(form)) {
            e.preventDefault();
        }
    });
}

/**
 * Validate individual field
 * @param {HTMLElement} field 
 */
function validateField(field) {
    clearError(field);
    
    const value = field.value.trim();
    const type = field.type;
    const name = field.name;
    
    // Required check
    if (field.required && !value) {
        showError(field, 'This field is required');
        return false;
    }
    
    // Email validation
    if (type === 'email' && value && !validateEmail(value)) {
        showError(field, 'Please enter a valid email address');
        return false;
    }
    
    // Number range validation
    if (type === 'number' && value) {
        const min = parseFloat(field.min);
        const max = parseFloat(field.max);
        const num = parseFloat(value);
        
        if (!isNaN(min) && num < min) {
            showError(field, `Minimum value is ${min}`);
            return false;
        }
        if (!isNaN(max) && num > max) {
            showError(field, `Maximum value is ${max}`);
            return false;
        }
    }
    
    return true;
}

// Auto-initialize forms
document.addEventListener('DOMContentLoaded', function() {
    // Login form
    initFormValidation('loginForm');
    
    // Register form
    initFormValidation('registerForm');
    
    // Profile form
    initFormValidation('profileForm');
    
    // BMI form
    initFormValidation('bmiForm');
});

// Export functions for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        validateEmail,
        validatePassword,
        validateHeight,
        validateWeight,
        validateAge,
        validateRange
    };
}
