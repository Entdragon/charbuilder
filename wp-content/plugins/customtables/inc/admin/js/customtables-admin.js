(function ($) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     *
     * The file is enqueued from inc/admin/class-admin.php.
     */

    $(document).ready(function () {

        $('[data-toggle="tab"]').click(function () {
            let tabs = jQuery(this).attr('data-tabs');
            let tab = jQuery(this).attr("data-tab");
            $(tabs).find(".gtab").removeClass("active");
            $('[data-toggle="tab"]').removeClass("nav-tab-active");
            $(tabs).find(tab).addClass("active");
            jQuery(this).addClass("nav-tab-active");
        });

        if (document.getElementById('layoutcode')) {
            let wpObject;

            //In reverse order because we need editor 0 to be active
            if(proversion == true) {
                wpObject = wp.codeEditor.initialize($('#layoutjs'), cm_settings_layoutjs);
                addCMEvent(wpObject);
                codemirror_editors[3] = wpObject;

                wpObject = wp.codeEditor.initialize($('#layoutcss'), cm_settings_layoutcss);
                addCMEvent(wpObject);
                codemirror_editors[2] = wpObject;

                wpObject = wp.codeEditor.initialize($('#layoutmobile'), cm_settings_layoutmobile);
                addCMEvent(wpObject);
                codemirror_editors[1] = wpObject;
            }

            wpObject = wp.codeEditor.initialize($('#layoutcode'), cm_settings_layoutcode);
            addCMEvent(wpObject);
            codemirror_editors[0] = wpObject;
            wpObject.codemirror.setOption("mode", 'text/html');

            loadTagParams("layouttype", "textareatabid");
            loadTypes_silent();

            languages = [];
            loadFields("table", "fieldWizardBox");
            loadLayout(6);
            adjustEditorHeight();
        }
    });

})(jQuery);

function addCMEvent(wpObject) {
    let cm = wpObject.codemirror;

    cm.on('dblclick', function (e) {
        let cr = e.getCursor();
        let lineNumber = cr.line;
        let lineString = e.doc.getLine(lineNumber);//state.doc.cm.line(lineNumber).text;
        let mousePos = cm.cursorCoords(cr, "window");
        doExtraCodeMirrorEvent(cr.ch, lineString, lineNumber, mousePos);
    }, true);
}

function CustomTablesAdminLayoutsTabClicked(index, id) {

    setTimeout(function () {
        codemirror_active_index = index;
        codemirror_active_areatext_id = id;
        let cm = codemirror_editors[index];

        if (index === 0 || index === 1)
            cm.codemirror.setOption("mode", 'text/html');
        else if (index === 2)
            cm.codemirror.setOption("mode", 'css');
        else if (index === 3)
            cm.codemirror.setOption("mode", 'javascript');

        adjustEditorHeight();
    }, 100);
    return false;
}

function readmoreOpenClose(itemid) {
    const obj = document.getElementById(itemid);
    let c = obj.className;
    if (c.indexOf("ct_readmoreOpen") != -1)
        c = c.replace("ct_readmoreOpen", "ct_readmoreClose");
    else if (c.indexOf("ct_readmoreClosed") != -1)
        c = c.replace("ct_readmoreClosed", "ct_readmoreOpen");
    else if (c.indexOf("ct_readmoreClose") != -1)
        c = c.replace("ct_readmoreClose", "ct_readmoreOpen");

    obj.className = c;
}

function showFieldTagModalForm() {
    current_table_id = document.getElementById("table").value;
    if(current_table_id ==="" || parseInt(current_table_id) === 0)
    {
        alert("Please select a Table.")
        return;
    }

    showModalFieldTagsList();
    activateTabsWordPress('layouteditor_fields');
}

function showLayoutTagModalForm() {
    current_table_id = document.getElementById("table").value;
    showModalTagsList();
    activateTabsWordPress('layouteditor_fields');
}


function createCopyButton(text) {
    const button = document.createElement('button');
    button.title = 'Copy';
    button.className = 'copy-button';
    button.innerHTML = `
        <svg class="copy-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
        </svg>`;

    button.addEventListener('click', async (event) => {

        event.preventDefault();

        try {
            await navigator.clipboard.writeText(text);
            button.textContent = 'Copied!';
            setTimeout(() => {
                button.innerHTML = `
                    <svg class="copy-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>`;
            }, 2000);
        } catch (err) {
            console.error('Failed to copy text: ', err);
        }
    });

    return button;
}

// Initialize copy buttons when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('.CustomTablesDocumentationTips');
    if (container) {
        const pres = document.querySelectorAll('pre');
        pres.forEach(pre => {
            // Create container
            const container = document.createElement('div');
            container.className = 'shortcode-container';

            // Move the pre content to the container
            const text = pre.textContent;
            pre.textContent = ''; // Clear original content

            // Add text and copy button to container
            const textSpan = document.createElement('span');
            textSpan.textContent = text;
            container.appendChild(textSpan);
            container.appendChild(createCopyButton(text));

            // Add container to pre
            pre.appendChild(container);
        });
    }
});
