/*!
 * FarmeDynamic v1.0.0
 * Dynamic CSS generation system like Tailwind JIT
 * Generates utility classes on-demand
 */

(function(window) {
    'use strict';

    // Core FarmeDynamic object
    const FarmeDynamic = {
        version: '1.0.0',
        generatedClasses: new Set(),
        observers: [],
        config: {
            prefix: 'f-',
            scan: {
                include: ['class', 'className'],
                exclude: []
            },
            breakpoints: {
                'sm': '640px',
                'md': '768px', 
                'lg': '1024px',
                'xl': '1280px',
                '2xl': '1536px'
            }
        }
    };

    // CSS Generation Rules
    const CSS_GENERATORS = {
        // Spacing utilities
        spacing: {
            pattern: /^(p|m)(t|r|b|l|x|y)?-(\d+(?:\.\d+)?|px|auto)$/,
            generate(match) {
                const [, type, direction, value] = match;
                const property = type === 'p' ? 'padding' : 'margin';
                const cssValue = getCSSValue(value, 'spacing');
                
                const directions = {
                    't': `${property}-top`,
                    'r': `${property}-right`, 
                    'b': `${property}-bottom`,
                    'l': `${property}-left`,
                    'x': [`${property}-left`, `${property}-right`],
                    'y': [`${property}-top`, `${property}-bottom`],
                    '': property
                };
                
                const props = directions[direction || ''];
                if (Array.isArray(props)) {
                    return props.map(prop => `${prop}: ${cssValue}`).join('; ');
                }
                return `${props}: ${cssValue}`;
            }
        },

        // Color utilities
        colors: {
            pattern: /^(bg|text|border)-(primary|secondary|success|warning|error|gray|white|black|transparent|current|blue|red|green|purple|pink|indigo|yellow|cyan|teal|rose)-?(\d+)?$/,
            generate(match) {
                const [, type, color, shade] = match;
                const cssValue = getCSSColorValue(color, shade);
                
                const properties = {
                    'bg': 'background-color',
                    'text': 'color',
                    'border': 'border-color'
                };
                
                return `${properties[type]}: ${cssValue}`;
            }
        },

        // Gradient backgrounds
        gradients: {
            pattern: /^bg-gradient-to-(r|l|t|b|tr|tl|br|bl)$/,
            generate(match) {
                const [, direction] = match;
                const directions = {
                    'r': 'to right',
                    'l': 'to left', 
                    't': 'to top',
                    'b': 'to bottom',
                    'tr': 'to top right',
                    'tl': 'to top left',
                    'br': 'to bottom right',
                    'bl': 'to bottom left'
                };
                return `background-image: linear-gradient(${directions[direction]}, var(--tw-gradient-stops))`;
            }
        },

        // Gradient color stops
        gradientStops: {
            pattern: /^(from|via|to)-(primary|secondary|success|warning|error|gray|white|black|blue|red|green|purple|pink|indigo|yellow|cyan|teal|rose)-?(\d+)?$/,
            generate(match) {
                const [, stop, color, shade] = match;
                const cssValue = getCSSColorValue(color, shade);
                
                if (stop === 'from') {
                    return `--tw-gradient-from: ${cssValue}; --tw-gradient-to: rgb(255 255 255 / 0); --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to)`;
                } else if (stop === 'via') {
                    return `--tw-gradient-to: rgb(255 255 255 / 0); --tw-gradient-stops: var(--tw-gradient-from), ${cssValue}, var(--tw-gradient-to)`;
                } else if (stop === 'to') {
                    return `--tw-gradient-to: ${cssValue}`;
                }
                return '';
            }
        },

        // Typography
        typography: {
            pattern: /^(text|font)-(xs|sm|base|lg|xl|2xl|3xl|4xl|5xl|6xl|7xl|8xl|9xl|thin|light|normal|medium|semibold|bold|extrabold|black|left|center|right|justify|uppercase|lowercase|capitalize|underline|line-through|no-underline)$/,
            generate(match) {
                const [, type, value] = match;
                
                if (type === 'text') {
                    // Font sizes
                    const sizes = {
                        'xs': '0.75rem', 'sm': '0.875rem', 'base': '1rem', 'lg': '1.125rem',
                        'xl': '1.25rem', '2xl': '1.5rem', '3xl': '1.875rem', '4xl': '2.25rem',
                        '5xl': '3rem', '6xl': '3.75rem', '7xl': '4.5rem', '8xl': '6rem', '9xl': '8rem'
                    };
                    
                    if (sizes[value]) {
                        return `font-size: ${sizes[value]}`;
                    }
                    
                    // Text alignment
                    if (['left', 'center', 'right', 'justify'].includes(value)) {
                        return `text-align: ${value}`;
                    }
                    
                    // Text transform
                    const transforms = {
                        'uppercase': 'uppercase',
                        'lowercase': 'lowercase', 
                        'capitalize': 'capitalize'
                    };
                    if (transforms[value]) {
                        return `text-transform: ${transforms[value]}`;
                    }
                    
                    // Text decoration
                    const decorations = {
                        'underline': 'underline',
                        'line-through': 'line-through',
                        'no-underline': 'none'
                    };
                    if (decorations[value]) {
                        return `text-decoration: ${decorations[value]}`;
                    }
                }
                
                if (type === 'font') {
                    // Font weights
                    const weights = {
                        'thin': '100', 'light': '300', 'normal': '400', 'medium': '500',
                        'semibold': '600', 'bold': '700', 'extrabold': '800', 'black': '900'
                    };
                    if (weights[value]) {
                        return `font-weight: ${weights[value]}`;
                    }
                }
                
                return '';
            }
        },

        // Display
        display: {
            pattern: /^(block|inline|inline-block|flex|inline-flex|grid|inline-grid|table|table-row|table-cell|hidden)$/,
            generate(match) {
                const [className] = match;
                const displays = {
                    'block': 'block', 'inline': 'inline', 'inline-block': 'inline-block',
                    'flex': 'flex', 'inline-flex': 'inline-flex', 'grid': 'grid',
                    'inline-grid': 'inline-grid', 'table': 'table', 'table-row': 'table-row',
                    'table-cell': 'table-cell', 'hidden': 'none'
                };
                return `display: ${displays[className]}`;
            }
        },

        // Flexbox
        flexbox: {
            pattern: /^(flex-row|flex-col|flex-wrap|flex-nowrap|items-start|items-end|items-center|items-baseline|items-stretch|justify-start|justify-end|justify-center|justify-between|justify-around|justify-evenly|flex-1|flex-auto|flex-initial|flex-none)$/,
            generate(match) {
                const [className] = match;
                const flexProperties = {
                    'flex-row': 'flex-direction: row',
                    'flex-col': 'flex-direction: column',
                    'flex-wrap': 'flex-wrap: wrap',
                    'flex-nowrap': 'flex-wrap: nowrap',
                    'items-start': 'align-items: flex-start',
                    'items-end': 'align-items: flex-end',
                    'items-center': 'align-items: center',
                    'items-baseline': 'align-items: baseline',
                    'items-stretch': 'align-items: stretch',
                    'justify-start': 'justify-content: flex-start',
                    'justify-end': 'justify-content: flex-end',
                    'justify-center': 'justify-content: center',
                    'justify-between': 'justify-content: space-between',
                    'justify-around': 'justify-content: space-around',
                    'justify-evenly': 'justify-content: space-evenly',
                    'flex-1': 'flex: 1 1 0%',
                    'flex-auto': 'flex: 1 1 auto',
                    'flex-initial': 'flex: 0 1 auto',
                    'flex-none': 'flex: none'
                };
                return flexProperties[className] || '';
            }
        },

        // Grid
        grid: {
            pattern: /^(grid-cols-\d+|col-span-\d+|col-span-full|gap-\d+(?:\.\d+)?|row-span-\d+|grid-rows-\d+)$/,
            generate(match) {
                const [className] = match;
                
                // Grid columns
                const colsMatch = className.match(/^grid-cols-(\d+)$/);
                if (colsMatch) {
                    const cols = colsMatch[1];
                    return `grid-template-columns: repeat(${cols}, minmax(0, 1fr))`;
                }
                
                // Column span
                const colSpanMatch = className.match(/^col-span-(\d+|full)$/);
                if (colSpanMatch) {
                    const span = colSpanMatch[1];
                    if (span === 'full') {
                        return 'grid-column: 1 / -1';
                    }
                    return `grid-column: span ${span} / span ${span}`;
                }
                
                // Gap
                const gapMatch = className.match(/^gap-(\d+(?:\.\d+)?)$/);
                if (gapMatch) {
                    const value = getCSSValue(gapMatch[1], 'spacing');
                    return `gap: ${value}`;
                }
                
                // Row span
                const rowSpanMatch = className.match(/^row-span-(\d+)$/);
                if (rowSpanMatch) {
                    const span = rowSpanMatch[1];
                    return `grid-row: span ${span} / span ${span}`;
                }
                
                // Grid rows
                const rowsMatch = className.match(/^grid-rows-(\d+)$/);
                if (rowsMatch) {
                    const rows = rowsMatch[1];
                    return `grid-template-rows: repeat(${rows}, minmax(0, 1fr))`;
                }
                
                return '';
            }
        },

        // Sizing
        sizing: {
            pattern: /^(w|h|min-w|min-h|max-w|max-h)-(\d+(?:\.\d+)?|px|auto|full|screen|fit|min|max)$/,
            generate(match) {
                const [, property, value] = match;
                const cssValue = getCSSValue(value, 'sizing');
                
                const properties = {
                    'w': 'width',
                    'h': 'height', 
                    'min-w': 'min-width',
                    'min-h': 'min-height',
                    'max-w': 'max-width',
                    'max-h': 'max-height'
                };
                
                return `${properties[property]}: ${cssValue}`;
            }
        },

        // Border
        border: {
            pattern: /^(border|border-t|border-r|border-b|border-l|border-x|border-y)-?(\d+)?$|^rounded(-\w+)?(-\w+)?$/,
            generate(match) {
                const [className] = match;
                
                // Border width
                const borderMatch = className.match(/^(border|border-t|border-r|border-b|border-l|border-x|border-y)-?(\d+)?$/);
                if (borderMatch) {
                    const [, direction, width] = borderMatch;
                    const borderWidth = width ? `${width}px` : '1px';
                    
                    const directions = {
                        'border': 'border-width',
                        'border-t': 'border-top-width',
                        'border-r': 'border-right-width',
                        'border-b': 'border-bottom-width', 
                        'border-l': 'border-left-width',
                        'border-x': ['border-left-width', 'border-right-width'],
                        'border-y': ['border-top-width', 'border-bottom-width']
                    };
                    
                    const props = directions[direction];
                    if (Array.isArray(props)) {
                        return props.map(prop => `${prop}: ${borderWidth}`).join('; ');
                    }
                    return `${props}: ${borderWidth}`;
                }
                
                // Border radius
                const roundedMatch = className.match(/^rounded(-\w+)?(-\w+)?$/);
                if (roundedMatch) {
                    const size = roundedMatch[1] || '';
                    const direction = roundedMatch[2] || '';
                    
                    const sizes = {
                        '': '0.25rem',
                        '-sm': '0.125rem',
                        '-md': '0.375rem', 
                        '-lg': '0.5rem',
                        '-xl': '0.75rem',
                        '-2xl': '1rem',
                        '-3xl': '1.5rem',
                        '-full': '9999px',
                        '-none': '0px'
                    };
                    
                    const radius = sizes[size] || '0.25rem';
                    
                    if (!direction) {
                        return `border-radius: ${radius}`;
                    }
                    
                    // Handle directional radius
                    const directions = {
                        '-t': ['border-top-left-radius', 'border-top-right-radius'],
                        '-r': ['border-top-right-radius', 'border-bottom-right-radius'],
                        '-b': ['border-bottom-left-radius', 'border-bottom-right-radius'],
                        '-l': ['border-top-left-radius', 'border-bottom-left-radius'],
                        '-tl': 'border-top-left-radius',
                        '-tr': 'border-top-right-radius',
                        '-br': 'border-bottom-right-radius',
                        '-bl': 'border-bottom-left-radius'
                    };
                    
                    const props = directions[direction];
                    if (Array.isArray(props)) {
                        return props.map(prop => `${prop}: ${radius}`).join('; ');
                    }
                    return `${props}: ${radius}`;
                }
                
                return '';
            }
        },

        // Position
        position: {
            pattern: /^(static|fixed|absolute|relative|sticky|top|right|bottom|left|inset)-?(\d+(?:\.\d+)?|px|auto|full)?$/,
            generate(match) {
                const [className] = match;
                
                // Position types
                if (['static', 'fixed', 'absolute', 'relative', 'sticky'].includes(className)) {
                    return `position: ${className}`;
                }
                
                // Position values
                const posMatch = className.match(/^(top|right|bottom|left|inset)-?(\d+(?:\.\d+)?|px|auto|full)?$/);
                if (posMatch) {
                    const [, direction, value] = posMatch;
                    const cssValue = getCSSValue(value || '0', 'spacing');
                    
                    if (direction === 'inset') {
                        return `top: ${cssValue}; right: ${cssValue}; bottom: ${cssValue}; left: ${cssValue}`;
                    }
                    
                    return `${direction}: ${cssValue}`;
                }
                
                return '';
            }
        },

        // Shadow
        shadow: {
            pattern: /^shadow(-sm|-md|-lg|-xl|-2xl|-inner|-none)?$/,
            generate(match) {
                const [, size] = match;
                const shadows = {
                    '': '0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)',
                    '-sm': '0 1px 2px 0 rgb(0 0 0 / 0.05)',
                    '-md': '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)',
                    '-lg': '0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)',
                    '-xl': '0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1)',
                    '-2xl': '0 25px 50px -12px rgb(0 0 0 / 0.25)',
                    '-inner': 'inset 0 2px 4px 0 rgb(0 0 0 / 0.05)',
                    '-none': 'none'
                };
                return `box-shadow: ${shadows[size || '']}`;
            }
        },

        // Transform and Transitions
        transform: {
            pattern: /^(transform|transition-all|transition-colors|transition-opacity|transition-shadow|transition-transform|duration-\d+|scale-\d+|rotate-\d+|hover:scale-\d+|hover:rotate-\d+)$/,
            generate(match) {
                const [className] = match;
                
                if (className === 'transform') {
                    return 'transform: translateX(var(--tw-translate-x)) translateY(var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))';
                }
                
                if (className.startsWith('transition-')) {
                    const transitions = {
                        'transition-all': 'transition: all 150ms cubic-bezier(0.4, 0, 0.2, 1)',
                        'transition-colors': 'transition: color, background-color, border-color, text-decoration-color, fill, stroke 150ms cubic-bezier(0.4, 0, 0.2, 1)',
                        'transition-opacity': 'transition: opacity 150ms cubic-bezier(0.4, 0, 0.2, 1)',
                        'transition-shadow': 'transition: box-shadow 150ms cubic-bezier(0.4, 0, 0.2, 1)',
                        'transition-transform': 'transition: transform 150ms cubic-bezier(0.4, 0, 0.2, 1)'
                    };
                    return transitions[className] || '';
                }
                
                const durationMatch = className.match(/^duration-(\d+)$/);
                if (durationMatch) {
                    return `transition-duration: ${durationMatch[1]}ms`;
                }
                
                const scaleMatch = className.match(/^(hover:)?scale-(\d+)$/);
                if (scaleMatch) {
                    const scale = parseInt(scaleMatch[2]) / 100;
                    return `transform: scale(${scale})`;
                }
                
                const rotateMatch = className.match(/^(hover:)?rotate-(\d+)$/);
                if (rotateMatch) {
                    return `transform: rotate(${rotateMatch[2]}deg)`;
                }
                
                return '';
            }
        },

        // Z-Index
        zIndex: {
            pattern: /^z-(\d+|auto)$/,
            generate(match) {
                const [, value] = match;
                return `z-index: ${value}`;
            }
        },

        // Overflow
        overflow: {
            pattern: /^overflow-(auto|hidden|visible|scroll|x-auto|x-hidden|x-visible|x-scroll|y-auto|y-hidden|y-visible|y-scroll)$/,
            generate(match) {
                const [, value] = match;
                if (value.startsWith('x-')) {
                    return `overflow-x: ${value.slice(2)}`;
                }
                if (value.startsWith('y-')) {
                    return `overflow-y: ${value.slice(2)}`;
                }
                return `overflow: ${value}`;
            }
        },

        // Cursor
        cursor: {
            pattern: /^cursor-(auto|default|pointer|wait|text|move|help|not-allowed)$/,
            generate(match) {
                const [, value] = match;
                return `cursor: ${value}`;
            }
        },

        // Table utilities
        table: {
            pattern: /^(table|table-auto|table-fixed|table-caption|table-cell|table-column|table-column-group|table-footer-group|table-header-group|table-row-group|table-row)$/,
            generate(match) {
                const [className] = match;
                
                if (className === 'table') {
                    return 'width: 100%; border-collapse: collapse; margin-bottom: 1rem; background-color: transparent; border: 1px solid #dee2e6';
                }
                
                const displays = {
                    'table-auto': 'table-layout: auto',
                    'table-fixed': 'table-layout: fixed',
                    'table-caption': 'display: table-caption',
                    'table-cell': 'display: table-cell',
                    'table-column': 'display: table-column',
                    'table-column-group': 'display: table-column-group',
                    'table-footer-group': 'display: table-footer-group',
                    'table-header-group': 'display: table-header-group',
                    'table-row-group': 'display: table-row-group',
                    'table-row': 'display: table-row'
                };
                
                return displays[className] || '';
            }
        },

        // Button utilities
        button: {
            pattern: /^btn(-primary|-secondary|-success|-danger|-warning|-info|-light|-dark|-link|-outline-primary|-outline-secondary|-outline-success|-outline-danger|-outline-warning|-outline-info|-outline-light|-outline-dark|-sm|-lg|-block)?$/,
            generate(match) {
                const [, variant] = match;
                
                // Base button styles
                let baseStyles = 'display: inline-block; font-weight: 400; color: #212529; text-align: center; vertical-align: middle; cursor: pointer; user-select: none; background-color: transparent; border: 1px solid transparent; padding: 0.375rem 0.75rem; font-size: 1rem; line-height: 1.5; border-radius: 0.375rem; text-decoration: none; transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out';
                
                if (!variant || variant === '') {
                    return baseStyles + '; color: #0d6efd; border-color: #0d6efd';
                }
                
                const variants = {
                    '-primary': '; color: #fff; background-color: #0d6efd; border-color: #0d6efd',
                    '-secondary': '; color: #fff; background-color: #6c757d; border-color: #6c757d',
                    '-success': '; color: #fff; background-color: #198754; border-color: #198754',
                    '-danger': '; color: #fff; background-color: #dc3545; border-color: #dc3545',
                    '-warning': '; color: #000; background-color: #ffc107; border-color: #ffc107',
                    '-info': '; color: #000; background-color: #0dcaf0; border-color: #0dcaf0',
                    '-light': '; color: #000; background-color: #f8f9fa; border-color: #f8f9fa',
                    '-dark': '; color: #fff; background-color: #212529; border-color: #212529',
                    '-link': '; font-weight: 400; color: #0d6efd; text-decoration: underline; background-color: transparent; border-color: transparent',
                    '-sm': '; padding: 0.25rem 0.5rem; font-size: 0.875rem; border-radius: 0.25rem',
                    '-lg': '; padding: 0.5rem 1rem; font-size: 1.25rem; border-radius: 0.5rem',
                    '-block': '; display: block; width: 100%'
                };
                
                return baseStyles + (variants[variant] || '');
            }
        },

        // Opacity
        opacity: {
            pattern: /^opacity-(\d+)$/,
            generate(match) {
                const [, value] = match;
                const opacity = parseInt(value) / 100;
                return `opacity: ${opacity}`;
            }
        },

        // Leading/Line Height
        leading: {
            pattern: /^leading-(none|tight|snug|normal|relaxed|loose|\d+)$/,
            generate(match) {
                const [, value] = match;
                const lineHeights = {
                    'none': '1',
                    'tight': '1.25',
                    'snug': '1.375',
                    'normal': '1.5',
                    'relaxed': '1.625',
                    'loose': '2'
                };
                
                if (lineHeights[value]) {
                    return `line-height: ${lineHeights[value]}`;
                }
                
                if (!isNaN(value)) {
                    return `line-height: ${value}`;
                }
                
                return '';
            }
        },

        // Font family
        fontFamily: {
            pattern: /^font-(sans|serif|mono)$/,
            generate(match) {
                const [, family] = match;
                const families = {
                    'sans': 'ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif',
                    'serif': 'ui-serif, Georgia, Cambria, "Times New Roman", Times, serif',
                    'mono': 'ui-monospace, SFMono-Regular, "SF Mono", Consolas, "Liberation Mono", Menlo, monospace'
                };
                return `font-family: ${families[family]}`;
            }
        },

        // Backdrop Filter (for glassmorphism)
        backdropFilter: {
            pattern: /^backdrop-(blur|brightness|contrast|grayscale|hue-rotate|invert|saturate|sepia)-(.+)$/,
            generate(match) {
                const [, filter, value] = match;
                
                if (filter === 'blur') {
                    const blurValues = {
                        'none': '0',
                        'sm': '4px',
                        '': '8px',
                        'md': '12px',
                        'lg': '16px',
                        'xl': '24px',
                        '2xl': '40px',
                        '3xl': '64px'
                    };
                    const blurValue = blurValues[value] || value + 'px';
                    return `backdrop-filter: blur(${blurValue})`;
                }
                
                return `backdrop-filter: ${filter}(${value})`;
            }
        },

        // Filter effects
        filter: {
            pattern: /^(blur|brightness|contrast|grayscale|hue-rotate|invert|saturate|sepia)-(.+)$/,
            generate(match) {
                const [, filter, value] = match;
                
                if (filter === 'blur') {
                    const blurValues = {
                        'none': '0',
                        'sm': '4px',
                        '': '8px',
                        'md': '12px',
                        'lg': '16px',
                        'xl': '24px',
                        '2xl': '40px',
                        '3xl': '64px'
                    };
                    const blurValue = blurValues[value] || value + 'px';
                    return `filter: blur(${blurValue})`;
                }
                
                return `filter: ${filter}(${value})`;
            }
        },

        // Animation utilities
        animation: {
            pattern: /^animate-(none|spin|ping|pulse|bounce)$/,
            generate(match) {
                const [, animation] = match;
                const animations = {
                    'none': 'none',
                    'spin': 'spin 1s linear infinite',
                    'ping': 'ping 1s cubic-bezier(0, 0, 0.2, 1) infinite',
                    'pulse': 'pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    'bounce': 'bounce 1s infinite'
                };
                
                // Add keyframes if not already added
                if (animation !== 'none' && !document.getElementById('farme-keyframes')) {
                    const keyframes = `
                        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
                        @keyframes ping { 75%, 100% { transform: scale(2); opacity: 0; } }
                        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
                        @keyframes bounce { 0%, 100% { transform: translateY(-25%); animation-timing-function: cubic-bezier(0.8, 0, 1, 1); } 50% { transform: translateY(0); animation-timing-function: cubic-bezier(0, 0, 0.2, 1); } }
                    `;
                    
                    let keyframeStyle = document.createElement('style');
                    keyframeStyle.id = 'farme-keyframes';
                    keyframeStyle.textContent = keyframes;
                    document.head.appendChild(keyframeStyle);
                }
                
                return `animation: ${animations[animation]}`;
            }
        }
    };

    // Helper functions
    function getCSSValue(value, type) {
        if (value === 'auto') return 'auto';
        if (value === 'px') return '1px';
        if (value === 'full') return '100%';
        if (value === 'screen') return '100vh';
        if (value === 'fit') return 'fit-content';
        if (value === 'min') return 'min-content';
        if (value === 'max') return 'max-content';
        
        if (type === 'spacing') {
            const spacingMap = {
                '0': '0px', '0.5': '0.125rem', '1': '0.25rem', '1.5': '0.375rem',
                '2': '0.5rem', '2.5': '0.625rem', '3': '0.75rem', '3.5': '0.875rem',
                '4': '1rem', '5': '1.25rem', '6': '1.5rem', '7': '1.75rem',
                '8': '2rem', '9': '2.25rem', '10': '2.5rem', '11': '2.75rem',
                '12': '3rem', '14': '3.5rem', '16': '4rem', '20': '5rem',
                '24': '6rem', '28': '7rem', '32': '8rem', '36': '9rem',
                '40': '10rem', '44': '11rem', '48': '12rem', '52': '13rem',
                '56': '14rem', '60': '15rem', '64': '16rem', '72': '18rem',
                '80': '20rem', '96': '24rem'
            };
            return spacingMap[value] || `${value}rem`;
        }
        
        if (type === 'sizing') {
            // Handle special sizing values
            const sizingMap = {
                'auto': 'auto',
                'full': '100%',
                'screen': '100vh',
                'fit': 'fit-content',
                'min': 'min-content',
                'max': 'max-content',
                'px': '1px'
            };
            
            if (sizingMap[value]) {
                return sizingMap[value];
            }
            
            // Handle numeric values (convert to rem using 0.25rem per unit)
            if (value.includes('.')) {
                return `${(parseFloat(value) * 0.25)}rem`;
            }
            
            const numValue = parseFloat(value);
            if (!isNaN(numValue)) {
                return `${numValue * 0.25}rem`;
            }
            
            return value;
        }
        
        return value;
    }

    function getCSSColorValue(color, shade) {
        if (color === 'white') return '#ffffff';
        if (color === 'black') return '#000000';
        if (color === 'transparent') return 'transparent';
        if (color === 'current') return 'currentColor';
        
        const shadeValue = shade || '500';
        return `var(--f-${color}-${shadeValue})`;
    }

    // CSS Generation Engine
    function generateCSS(className) {
        // Debug logging
        const DEBUG = false; // Set to true to enable debug logging
        if (DEBUG) console.log(`[DEBUG] Processing class: ${className}`);
        
        // Remove prefix if present
        let unprefixed = className.startsWith(FarmeDynamic.config.prefix) 
            ? className.slice(FarmeDynamic.config.prefix.length)
            : className;
        
        if (DEBUG) console.log(`[DEBUG] After prefix removal: ${unprefixed}`);
        
        // Check responsive prefixes
        let responsivePrefix = '';
        const responsiveMatch = unprefixed.match(/^(sm|md|lg|xl|2xl):/);
        if (responsiveMatch) {
            responsivePrefix = responsiveMatch[1];
            unprefixed = unprefixed.slice(responsiveMatch[0].length);
            if (DEBUG) console.log(`[DEBUG] Responsive prefix: ${responsivePrefix}, remaining: ${unprefixed}`);
        }
        
        // Check hover/focus/active prefixes
        let pseudoClass = '';
        const pseudoMatch = unprefixed.match(/^(hover|focus|active):/);
        if (pseudoMatch) {
            pseudoClass = pseudoMatch[1];
            unprefixed = unprefixed.slice(pseudoMatch[0].length);
            if (DEBUG) console.log(`[DEBUG] Pseudo class: ${pseudoClass}, remaining: ${unprefixed}`);
        }
        
        // Skip common icon sizing classes that are pre-defined in base CSS
        const preDefinedSizes = ['w-3', 'h-3', 'w-4', 'h-4', 'w-5', 'h-5', 'w-6', 'h-6', 'w-8', 'h-8', 'w-10', 'h-10', 'w-12', 'h-12', 'w-16', 'h-16', 'w-20', 'h-20', 'w-24', 'h-24'];
        if (preDefinedSizes.includes(unprefixed)) {
            if (DEBUG) console.log(`[DEBUG] Skipping pre-defined class: ${unprefixed}`);
            return null;
        }

        // Try each generator
        for (const [name, generator] of Object.entries(CSS_GENERATORS)) {
            const match = unprefixed.match(generator.pattern);
            if (match) {
                if (DEBUG) console.log(`[DEBUG] Matched generator: ${name}, pattern: ${generator.pattern}`);
                const css = generator.generate(match);
                if (css) {
                    if (DEBUG) console.log(`[DEBUG] Generated CSS: ${css}`);
                    
                    // Fix selector generation - use original className and escape special characters
                    let selectorClass = className.startsWith(FarmeDynamic.config.prefix) 
                        ? className 
                        : `${FarmeDynamic.config.prefix}${className}`;
                    
                    // Escape colons and other special characters for CSS selectors
                    const escapedClass = selectorClass.replace(/:/g, '\\:');
                    let selector = `.${escapedClass}`;
                    
                    // Add pseudo-class if present
                    if (pseudoClass) {
                        selector += `:${pseudoClass}`;
                    }
                    
                    let rule = `${selector} { ${css}; }`;
                    
                    // Wrap in media query if responsive
                    if (responsivePrefix) {
                        const breakpoint = FarmeDynamic.config.breakpoints[responsivePrefix];
                        rule = `@media (min-width: ${breakpoint}) { ${rule} }`;
                        if (DEBUG) console.log(`[DEBUG] Wrapped in media query: ${rule}`);
                    }
                    
                    return rule;
                }
            }
        }
        
        if (DEBUG) console.log(`[DEBUG] No generator matched for: ${unprefixed}`);
        return null;
    }

    // CSS Injection System
    function injectCSS(css) {
        let styleElement = document.getElementById('farme-dynamic-styles');
        if (!styleElement) {
            styleElement = document.createElement('style');
            styleElement.id = 'farme-dynamic-styles';
            styleElement.type = 'text/css';
            document.head.appendChild(styleElement);
        }
        
        styleElement.textContent += css + '\n';
    }

    // Check if a class should be processed
    function shouldProcessClass(className) {
        // Remove prefix if present for checking
        let unprefixed = className.startsWith(FarmeDynamic.config.prefix) 
            ? className.slice(FarmeDynamic.config.prefix.length)
            : className;
        
        // Check responsive prefixes
        const responsiveMatch = unprefixed.match(/^(sm|md|lg|xl|2xl):/);
        if (responsiveMatch) {
            unprefixed = unprefixed.slice(responsiveMatch[0].length);
        }
        
        // Check pseudo-class prefixes
        const pseudoMatch = unprefixed.match(/^(hover|focus|active):/);
        if (pseudoMatch) {
            unprefixed = unprefixed.slice(pseudoMatch[0].length);
        }
        
        // Check if matches any generator pattern
        for (const generator of Object.values(CSS_GENERATORS)) {
            if (generator.pattern.test(unprefixed)) {
                return true;
            }
        }
        
        return false;
    }

    // DOM Scanning
    function scanForClasses(element = document.documentElement) {
        // Pattern matches both prefixed and non-prefixed utility classes
        const classPattern = new RegExp(`\\b(?:${FarmeDynamic.config.prefix})?[\\w-:]+`, 'g');
        const foundClasses = new Set();
        
        // Scan all elements
        const walker = document.createTreeWalker(
            element,
            NodeFilter.SHOW_ELEMENT,
            null,
            false
        );
        
        let node = walker.currentNode;
        do {
            if (node.className && typeof node.className === 'string') {
                // Split classes and check each individually
                const classes = node.className.split(/\s+/);
                classes.forEach(className => {
                    if (className && !FarmeDynamic.generatedClasses.has(className)) {
                        // Check if this class matches any utility pattern
                        if (shouldProcessClass(className)) {
                            foundClasses.add(className);
                        }
                    }
                });
            }
        } while (node = walker.nextNode());
        
        return foundClasses;
    }

    // Process new classes
    function processClasses(classes) {
        const cssRules = [];
        
        for (const className of classes) {
            if (!FarmeDynamic.generatedClasses.has(className)) {
                const css = generateCSS(className);
                if (css) {
                    cssRules.push(css);
                    FarmeDynamic.generatedClasses.add(className);
                }
            }
        }
        
        if (cssRules.length > 0) {
            injectCSS(cssRules.join('\n'));
        }
    }

    // Main scan function
    function scanAndGenerate() {
        const foundClasses = scanForClasses();
        if (foundClasses.size > 0) {
            processClasses(foundClasses);
        }
    }

    // Mutation Observer for dynamic content
    function setupObserver() {
        const observer = new MutationObserver((mutations) => {
            let shouldScan = false;
            
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            shouldScan = true;
                        }
                    });
                } else if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    shouldScan = true;
                }
            });
            
            if (shouldScan) {
                scanAndGenerate();
            }
        });
        
        observer.observe(document.documentElement, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['class']
        });
        
        FarmeDynamic.observers.push(observer);
    }

    // Public API
    FarmeDynamic.scan = scanAndGenerate;
    FarmeDynamic.generate = generateCSS;
    FarmeDynamic.addClass = function(className) {
        const css = generateCSS(className);
        if (css && !FarmeDynamic.generatedClasses.has(className)) {
            injectCSS(css);
            FarmeDynamic.generatedClasses.add(className);
        }
    };
    
    FarmeDynamic.init = function() {
        // Initial scan
        scanAndGenerate();
        
        // Setup observer for dynamic content
        setupObserver();
        
        console.log('FarmeDynamic initialized - Dynamic CSS generation active');
    };

    // Auto-initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', FarmeDynamic.init);
    } else {
        FarmeDynamic.init();
    }

    // Export to global namespace
    window.FarmeDynamic = FarmeDynamic;

})(window);