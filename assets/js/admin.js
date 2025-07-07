jQuery(document).ready(function($) {
    // Copiar CSS
    $('.copy-button').click(function() {
        const textarea = $(this).siblings('textarea');
        textarea.select();
        document.execCommand('copy');
        alert(fancine_i18n.copy_success || 'CSS copiado al portapapeles');
    });
    
    // Mostrar detalles de selector
    $('.selector-item').click(function() {
        const selector = $(this).data('selector');
        const selectorData = fancineCssData.selectors[selector];
        
        $('.selector-title').text(selector);
        $('.selector-desc').text(selectorData.description);
        
        let propertiesHtml = '';
        for (const [prop, value] of Object.entries(selectorData.properties)) {
            propertiesHtml += `<div class="prop-item">
                <span class="prop-name">${prop}</span>
                <span class="prop-value">${value}</span>
            </div>`;
        }
        $('.css-properties').html(propertiesHtml);
        
        // Generar CSS para copiar
        const cssCode = `${selector} {\n${
            Object.entries(selectorData.properties)
                .map(([prop, value]) => `    ${prop}: ${value};`)
                .join('\n')
        }\n}`;
        $('.css-copy-area').val(cssCode);
    });
});