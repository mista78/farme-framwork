/*!
 * FarmeJS v1.0.0
 * Modern vanilla JavaScript components for Farme Framework
 * Works seamlessly with FarmeCSS utility framework
 */

(function(window) {
    'use strict';

    // Core FarmeJS object
    const Farme = {
        version: '1.0.0',
        components: {},
        utils: {},
        events: {}
    };

    // ===== UTILITY FUNCTIONS =====
    Farme.utils = {
        // DOM selection utilities
        $(selector, context = document) {
            return context.querySelector(selector);
        },

        $$(selector, context = document) {
            return Array.from(context.querySelectorAll(selector));
        },

        // Event handling utilities
        on(element, event, handler, options = {}) {
            if (typeof element === 'string') {
                element = this.$(element);
            }
            if (element) {
                element.addEventListener(event, handler, options);
            }
        },

        off(element, event, handler) {
            if (typeof element === 'string') {
                element = this.$(element);
            }
            if (element) {
                element.removeEventListener(event, handler);
            }
        },

        // Class manipulation utilities
        addClass(element, className) {
            if (typeof element === 'string') {
                element = this.$(element);
            }
            if (element) {
                element.classList.add(className);
            }
        },

        removeClass(element, className) {
            if (typeof element === 'string') {
                element = this.$(element);
            }
            if (element) {
                element.classList.remove(className);
            }
        },

        toggleClass(element, className) {
            if (typeof element === 'string') {
                element = this.$(element);
            }
            if (element) {
                element.classList.toggle(className);
            }
        },

        hasClass(element, className) {
            if (typeof element === 'string') {
                element = this.$(element);
            }
            return element ? element.classList.contains(className) : false;
        },

        // Animation utilities
        fadeIn(element, duration = 300) {
            if (typeof element === 'string') {
                element = this.$(element);
            }
            if (!element) return;

            element.style.opacity = '0';
            element.style.display = 'block';
            element.style.transition = `opacity ${duration}ms ease-in-out`;
            
            requestAnimationFrame(() => {
                element.style.opacity = '1';
            });
        },

        fadeOut(element, duration = 300) {
            if (typeof element === 'string') {
                element = this.$(element);
            }
            if (!element) return;

            element.style.transition = `opacity ${duration}ms ease-in-out`;
            element.style.opacity = '0';
            
            setTimeout(() => {
                element.style.display = 'none';
            }, duration);
        },

        slideDown(element, duration = 300) {
            if (typeof element === 'string') {
                element = this.$(element);
            }
            if (!element) return;

            element.style.display = 'block';
            element.style.height = '0';
            element.style.overflow = 'hidden';
            element.style.transition = `height ${duration}ms ease-in-out`;
            
            const height = element.scrollHeight;
            requestAnimationFrame(() => {
                element.style.height = height + 'px';
            });
            
            setTimeout(() => {
                element.style.height = '';
                element.style.overflow = '';
                element.style.transition = '';
            }, duration);
        },

        slideUp(element, duration = 300) {
            if (typeof element === 'string') {
                element = this.$(element);
            }
            if (!element) return;

            const height = element.scrollHeight;
            element.style.height = height + 'px';
            element.style.overflow = 'hidden';
            element.style.transition = `height ${duration}ms ease-in-out`;
            
            requestAnimationFrame(() => {
                element.style.height = '0';
            });
            
            setTimeout(() => {
                element.style.display = 'none';
                element.style.height = '';
                element.style.overflow = '';
                element.style.transition = '';
            }, duration);
        }
    };

    // ===== MODAL COMPONENT =====
    Farme.components.Modal = class {
        constructor(selector, options = {}) {
            this.element = typeof selector === 'string' ? Farme.utils.$(selector) : selector;
            this.options = {
                backdrop: true,
                keyboard: true,
                focus: true,
                ...options
            };
            this.isOpen = false;
            this.init();
        }

        init() {
            if (!this.element) return;

            // Create backdrop
            this.backdrop = document.createElement('div');
            this.backdrop.className = 'f-fixed f-inset-0 f-bg-black f-bg-opacity-50 f-z-40 f-hidden';
            document.body.appendChild(this.backdrop);

            // Setup modal styling
            this.element.classList.add('f-fixed', 'f-z-50', 'f-hidden', 'f-top-1/2', 'f-left-1/2', 'f-transform', 'f-transition-all', 'f-duration-300');
            this.element.style.transform = 'translate(-50%, -50%) scale(0.9)';

            // Event listeners
            if (this.options.backdrop) {
                Farme.utils.on(this.backdrop, 'click', () => this.close());
            }

            if (this.options.keyboard) {
                Farme.utils.on(document, 'keydown', (e) => {
                    if (e.key === 'Escape' && this.isOpen) {
                        this.close();
                    }
                });
            }

            // Close button handler
            const closeBtn = Farme.utils.$('[data-modal-close]', this.element);
            if (closeBtn) {
                Farme.utils.on(closeBtn, 'click', () => this.close());
            }
        }

        open() {
            if (this.isOpen) return;
            
            this.isOpen = true;
            this.backdrop.classList.remove('f-hidden');
            this.element.classList.remove('f-hidden');
            
            requestAnimationFrame(() => {
                this.backdrop.style.opacity = '1';
                this.element.style.transform = 'translate(-50%, -50%) scale(1)';
            });

            if (this.options.focus) {
                this.element.focus();
            }

            // Prevent body scroll
            document.body.style.overflow = 'hidden';
        }

        close() {
            if (!this.isOpen) return;
            
            this.isOpen = false;
            this.backdrop.style.opacity = '0';
            this.element.style.transform = 'translate(-50%, -50%) scale(0.9)';
            
            setTimeout(() => {
                this.backdrop.classList.add('f-hidden');
                this.element.classList.add('f-hidden');
            }, 300);

            // Restore body scroll
            document.body.style.overflow = '';
        }

        toggle() {
            this.isOpen ? this.close() : this.open();
        }
    };

    // ===== DROPDOWN COMPONENT =====
    Farme.components.Dropdown = class {
        constructor(selector, options = {}) {
            this.trigger = typeof selector === 'string' ? Farme.utils.$(selector) : selector;
            this.options = {
                placement: 'bottom-start',
                offset: 4,
                ...options
            };
            this.isOpen = false;
            this.init();
        }

        init() {
            if (!this.trigger) return;

            this.menu = Farme.utils.$('[data-dropdown-menu]', this.trigger.parentElement) || 
                       Farme.utils.$(this.trigger.getAttribute('data-dropdown-target'));
            
            if (!this.menu) return;

            // Setup menu styling
            this.menu.classList.add('f-absolute', 'f-z-50', 'f-hidden', 'f-bg-white', 'f-border', 'f-rounded-md', 'f-shadow-lg');

            // Event listeners
            Farme.utils.on(this.trigger, 'click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggle();
            });

            Farme.utils.on(document, 'click', (e) => {
                if (!this.trigger.contains(e.target) && !this.menu.contains(e.target)) {
                    this.close();
                }
            });

            Farme.utils.on(document, 'keydown', (e) => {
                if (e.key === 'Escape' && this.isOpen) {
                    this.close();
                }
            });
        }

        open() {
            if (this.isOpen) return;
            
            this.isOpen = true;
            this.menu.classList.remove('f-hidden');
            this.positionMenu();
            
            requestAnimationFrame(() => {
                this.menu.style.opacity = '1';
                this.menu.style.transform = 'translateY(0) scale(1)';
            });
        }

        close() {
            if (!this.isOpen) return;
            
            this.isOpen = false;
            this.menu.style.opacity = '0';
            this.menu.style.transform = 'translateY(-8px) scale(0.95)';
            
            setTimeout(() => {
                this.menu.classList.add('f-hidden');
            }, 150);
        }

        toggle() {
            this.isOpen ? this.close() : this.open();
        }

        positionMenu() {
            const triggerRect = this.trigger.getBoundingClientRect();
            const menuRect = this.menu.getBoundingClientRect();
            
            let top = triggerRect.bottom + this.options.offset;
            let left = triggerRect.left;
            
            // Adjust if menu goes off-screen
            if (left + menuRect.width > window.innerWidth) {
                left = triggerRect.right - menuRect.width;
            }
            
            if (top + menuRect.height > window.innerHeight) {
                top = triggerRect.top - menuRect.height - this.options.offset;
            }
            
            this.menu.style.top = top + 'px';
            this.menu.style.left = left + 'px';
            this.menu.style.transition = 'opacity 150ms ease-in-out, transform 150ms ease-in-out';
            this.menu.style.transform = 'translateY(-8px) scale(0.95)';
        }
    };

    // ===== TABS COMPONENT =====
    Farme.components.Tabs = class {
        constructor(selector, options = {}) {
            this.container = typeof selector === 'string' ? Farme.utils.$(selector) : selector;
            this.options = {
                activeClass: 'f-border-primary-500 f-text-primary-600',
                inactiveClass: 'f-border-transparent f-text-gray-500',
                ...options
            };
            this.init();
        }

        init() {
            if (!this.container) return;

            this.tabs = Farme.utils.$$('[data-tab]', this.container);
            this.panels = Farme.utils.$$('[data-tab-panel]', this.container);
            
            this.tabs.forEach(tab => {
                Farme.utils.on(tab, 'click', (e) => {
                    e.preventDefault();
                    const target = tab.getAttribute('data-tab');
                    this.showTab(target);
                });
            });

            // Show first tab by default
            if (this.tabs.length > 0) {
                const firstTab = this.tabs[0].getAttribute('data-tab');
                this.showTab(firstTab);
            }
        }

        showTab(target) {
            // Update tab states
            this.tabs.forEach(tab => {
                const isActive = tab.getAttribute('data-tab') === target;
                tab.className = tab.className
                    .replace(this.options.activeClass, '')
                    .replace(this.options.inactiveClass, '') + 
                    ' ' + (isActive ? this.options.activeClass : this.options.inactiveClass);
            });

            // Update panel states
            this.panels.forEach(panel => {
                const isActive = panel.getAttribute('data-tab-panel') === target;
                if (isActive) {
                    panel.classList.remove('f-hidden');
                } else {
                    panel.classList.add('f-hidden');
                }
            });
        }
    };

    // ===== ACCORDION COMPONENT =====
    Farme.components.Accordion = class {
        constructor(selector, options = {}) {
            this.container = typeof selector === 'string' ? Farme.utils.$(selector) : selector;
            this.options = {
                multiple: false,
                ...options
            };
            this.init();
        }

        init() {
            if (!this.container) return;

            this.items = Farme.utils.$$('[data-accordion-item]', this.container);
            
            this.items.forEach(item => {
                const trigger = Farme.utils.$('[data-accordion-trigger]', item);
                const content = Farme.utils.$('[data-accordion-content]', item);
                
                if (trigger && content) {
                    content.style.display = 'none';
                    
                    Farme.utils.on(trigger, 'click', () => {
                        this.toggle(item);
                    });
                }
            });
        }

        toggle(item) {
            const trigger = Farme.utils.$('[data-accordion-trigger]', item);
            const content = Farme.utils.$('[data-accordion-content]', item);
            const isOpen = !Farme.utils.hasClass(content, 'f-hidden') && content.style.display !== 'none';
            
            if (!this.options.multiple) {
                // Close all other items
                this.items.forEach(otherItem => {
                    if (otherItem !== item) {
                        this.close(otherItem);
                    }
                });
            }
            
            if (isOpen) {
                this.close(item);
            } else {
                this.open(item);
            }
        }

        open(item) {
            const content = Farme.utils.$('[data-accordion-content]', item);
            const trigger = Farme.utils.$('[data-accordion-trigger]', item);
            
            if (content) {
                Farme.utils.slideDown(content);
                trigger?.classList.add('f-accordion-open');
            }
        }

        close(item) {
            const content = Farme.utils.$('[data-accordion-content]', item);
            const trigger = Farme.utils.$('[data-accordion-trigger]', item);
            
            if (content) {
                Farme.utils.slideUp(content);
                trigger?.classList.remove('f-accordion-open');
            }
        }
    };

    // ===== FORM VALIDATION =====
    Farme.components.FormValidator = class {
        constructor(selector, options = {}) {
            this.form = typeof selector === 'string' ? Farme.utils.$(selector) : selector;
            this.options = {
                errorClass: 'f-border-red-500 f-text-red-600',
                successClass: 'f-border-green-500',
                ...options
            };
            this.init();
        }

        init() {
            if (!this.form) return;

            Farme.utils.on(this.form, 'submit', (e) => {
                if (!this.validate()) {
                    e.preventDefault();
                }
            });

            // Real-time validation
            const inputs = Farme.utils.$$('input, textarea, select', this.form);
            inputs.forEach(input => {
                Farme.utils.on(input, 'blur', () => this.validateField(input));
                Farme.utils.on(input, 'input', () => this.clearFieldError(input));
            });
        }

        validate() {
            const inputs = Farme.utils.$$('input, textarea, select', this.form);
            let isValid = true;

            inputs.forEach(input => {
                if (!this.validateField(input)) {
                    isValid = false;
                }
            });

            return isValid;
        }

        validateField(field) {
            const value = field.value.trim();
            const rules = field.getAttribute('data-rules');
            
            if (!rules) return true;

            const ruleList = rules.split('|');
            let isValid = true;
            let errorMessage = '';

            for (const rule of ruleList) {
                const [ruleName, ruleValue] = rule.split(':');
                
                switch (ruleName) {
                    case 'required':
                        if (!value) {
                            isValid = false;
                            errorMessage = 'This field is required';
                        }
                        break;
                    case 'email':
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (value && !emailRegex.test(value)) {
                            isValid = false;
                            errorMessage = 'Please enter a valid email address';
                        }
                        break;
                    case 'min':
                        if (value.length < parseInt(ruleValue)) {
                            isValid = false;
                            errorMessage = `Minimum ${ruleValue} characters required`;
                        }
                        break;
                    case 'max':
                        if (value.length > parseInt(ruleValue)) {
                            isValid = false;
                            errorMessage = `Maximum ${ruleValue} characters allowed`;
                        }
                        break;
                }
                
                if (!isValid) break;
            }

            if (isValid) {
                this.showFieldSuccess(field);
            } else {
                this.showFieldError(field, errorMessage);
            }

            return isValid;
        }

        showFieldError(field, message) {
            this.clearFieldError(field);
            field.className += ' ' + this.options.errorClass;
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'f-text-red-500 f-text-sm f-mt-1';
            errorDiv.textContent = message;
            errorDiv.setAttribute('data-field-error', '');
            
            field.parentNode.insertBefore(errorDiv, field.nextSibling);
        }

        showFieldSuccess(field) {
            this.clearFieldError(field);
            field.className += ' ' + this.options.successClass;
        }

        clearFieldError(field) {
            field.className = field.className
                .replace(this.options.errorClass, '')
                .replace(this.options.successClass, '');
            
            const errorDiv = field.parentNode.querySelector('[data-field-error]');
            if (errorDiv) {
                errorDiv.remove();
            }
        }
    };

    // ===== TOAST NOTIFICATIONS =====
    Farme.components.Toast = class {
        static container = null;

        static init() {
            if (!this.container) {
                this.container = document.createElement('div');
                this.container.className = 'f-fixed f-top-4 f-right-4 f-z-50 f-space-y-2';
                document.body.appendChild(this.container);
            }
        }

        static show(message, type = 'info', duration = 3000) {
            this.init();

            const toast = document.createElement('div');
            toast.className = `f-p-4 f-rounded-md f-shadow-lg f-transition-all f-duration-300 f-transform f-translate-x-full`;
            
            const colors = {
                success: 'f-bg-green-500 f-text-white',
                error: 'f-bg-red-500 f-text-white',
                warning: 'f-bg-yellow-500 f-text-black',
                info: 'f-bg-blue-500 f-text-white'
            };
            
            toast.className += ' ' + (colors[type] || colors.info);
            toast.textContent = message;
            
            this.container.appendChild(toast);
            
            // Animate in
            requestAnimationFrame(() => {
                toast.classList.remove('f-translate-x-full');
            });
            
            // Auto remove
            if (duration > 0) {
                setTimeout(() => {
                    this.remove(toast);
                }, duration);
            }
            
            // Click to dismiss
            Farme.utils.on(toast, 'click', () => this.remove(toast));
            
            return toast;
        }

        static remove(toast) {
            toast.classList.add('f-translate-x-full');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }

        static success(message, duration) {
            return this.show(message, 'success', duration);
        }

        static error(message, duration) {
            return this.show(message, 'error', duration);
        }

        static warning(message, duration) {
            return this.show(message, 'warning', duration);
        }

        static info(message, duration) {
            return this.show(message, 'info', duration);
        }
    };

    // ===== AUTO-INITIALIZATION =====
    Farme.init = function() {
        // Auto-initialize components with data attributes
        document.addEventListener('DOMContentLoaded', () => {
            // Initialize modals
            Farme.utils.$$('[data-modal]').forEach(modal => {
                new Farme.components.Modal(modal);
            });

            // Initialize dropdowns
            Farme.utils.$$('[data-dropdown]').forEach(dropdown => {
                new Farme.components.Dropdown(dropdown);
            });

            // Initialize tabs
            Farme.utils.$$('[data-tabs]').forEach(tabs => {
                new Farme.components.Tabs(tabs);
            });

            // Initialize accordions
            Farme.utils.$$('[data-accordion]').forEach(accordion => {
                new Farme.components.Accordion(accordion);
            });

            // Initialize forms
            Farme.utils.$$('[data-validate]').forEach(form => {
                new Farme.components.FormValidator(form);
            });

            // Handle modal triggers
            Farme.utils.$$('[data-modal-target]').forEach(trigger => {
                const target = trigger.getAttribute('data-modal-target');
                const modal = Farme.utils.$(target);
                if (modal) {
                    const modalInstance = new Farme.components.Modal(modal);
                    Farme.utils.on(trigger, 'click', (e) => {
                        e.preventDefault();
                        modalInstance.open();
                    });
                }
            });
        });
    };

    // Initialize on load
    Farme.init();

    // Export to global namespace
    window.Farme = Farme;

})(window);