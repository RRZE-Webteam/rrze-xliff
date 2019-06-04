import ally from 'ally.js/ally';
(function(){
    const xliffEmailExportButton = document.querySelector("#xliff-export-email-address-link");
    xliffEmailExportButton.addEventListener("click", function(e){
        let xliffEmailExportField = document.querySelector("#xliff_export_email_address"),
            xliffEmailNote = document.querySelector("#xliff_export_email_note");
        e.preventDefault();
        e.target.setAttribute('disabled', 'disabled');

        let noticesDiv = document.querySelector('.xliff-export-notices');
        if (noticesDiv) {
            noticesDiv.innerHTML = '';
        }

        let xhr = new XMLHttpRequest();
        xhr.open("POST", ajaxurl, true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.responseType = "json";
        xhr.onreadystatechange = function() {
            if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                e.target.removeAttribute('disabled');
                if (xhr.response.length > 0) {
                    let noticeElems = [],
                        noticeTexts = [];
                    if (noticesDiv) {
                        xhr.response.forEach(function(currentValue, index) {
                            noticeElems[index] = document.createElement('div');
                            noticeTexts[index] = document.createElement('p');

                            noticeElems[index].classList.add(currentValue.type);
                            if (currentValue.type !== 'error') {
                                noticeElems[index].classList.add(`notice-${currentValue.type}`);
                                noticeElems[index].classList.add('notice');
                            }
                            noticeElems[index].appendChild(noticeTexts[index]);
                            noticeTexts[index].appendChild(document.createTextNode(currentValue.notice));
                            noticesDiv.appendChild(noticeElems[index]);
                        });
                    }
                }
            }
        }
        xliffEmailNote = xliffEmailNote.value.replace(/(\r\n|[\r\n])/g, '<br>');
        xhr.send(`_nonce=${rrzeXliffJavaScriptData.nonce}&action=xliff_email_export&xliff_export_post=${rrzeXliffJavaScriptData.post_id}&xliff_export_email_address=${xliffEmailExportField.value}&email_export_note=${xliffEmailNote}`);
    });

    // Verhindern, dass beim Import eine Warnung wegen ungespeicherter Inhalte kommt.
    document.querySelector('#xliff_import_button').addEventListener('click', function(e) {
        $(window).off('beforeunload.edit-post');
    });

	// Listen to setup of a TinyMCE instance.
    jQuery(document).on(
        'tinymce-editor-setup',
        function (event, editor) {
            // Add the id of the soon-to-create button to the toolbar1.
            editor.settings.toolbar1 += ',xliff-export-import';

            // Create the button.
            editor.addButton(
                'xliff-export-import',
                {
                    // It is a menubutton.
                    type: 'menubutton',
                    text: 'XLIFF Export/Import',
                    icon: false,

                    // Add the menu items.
                    menu: [
                    {
                        text: 'Export',
                        onclick: function () {
                            // Show export modal.
                            exportModal();
                        },
                    },
                    {
                        text: 'Import',
                        onclick: function () {
                            // Show import modal.
                            importModal();
                        }
                    }
                    ]
                }
            );
        }
    );

    // Export- und Import-Modal bei Klick außerhalb des Modals schließen.
    document.body.addEventListener('click', function (e) {
		if (document.body.classList.contains('modal-open') && (document.querySelector('.rrze-xliff-export-modal-wrapper').style.display === 'block' || document.querySelector('.rrze-xliff-import-modal-wrapper').style.display === 'block')) {
            if (e.explicitOriginalTarget.classList.contains('components-modal__screen-overlay')) {
				disabledHandle.disengage();
                tabHandle.disengage();
                keyHandle.disengage();
                focusedElementBeforeModal.focus();
                document.body.classList.remove('modal-open');
                document.querySelector('.rrze-xliff-export-modal-wrapper').style.display = 'none';
                document.querySelector('.rrze-xliff-import-modal-wrapper').style.display = 'none';
                let noticesDiv = document.querySelector('.xliff-export-notices');
                if (noticesDiv) {
                    noticesDiv.innerHTML = '';
                }
			}
		}
	}, false);
    
    let disabledHandle,
	    tabHandle,
        keyHandle,
        focusedElementBeforeModal;

    // Modals bei Klick auf Schließen-Button ausblenden.
    let closeButtons = document.querySelectorAll('.components-button.close-xliff-modal');
    for (let closeButton of closeButtons) {
        closeButton.addEventListener('click', function(e) {
            document.body.classList.remove('modal-open');
            disabledHandle.disengage();
            tabHandle.disengage();
            keyHandle.disengage();
            focusedElementBeforeModal.focus();
            if (document.querySelector('.rrze-xliff-export-modal-wrapper').style.display === 'block') {
                document.querySelector('.rrze-xliff-export-modal-wrapper').style.display = 'none';
                let noticesDiv = document.querySelector('.xliff-export-notices');
                if (noticesDiv) {
                    noticesDiv.innerHTML = '';
                }
            }
            if (document.querySelector('.rrze-xliff-import-modal-wrapper').style.display === 'block') {
                document.querySelector('.rrze-xliff-import-modal-wrapper').style.display = 'none';
            }
        });
    }

    function exportModal() {
        let modal = document.querySelector('.rrze-xliff-export-modal-wrapper');
        if (modal) {
            document.body.classList.toggle('modal-open');
            if (document.body.classList.contains('modal-open')) {
                modal.style.display = 'block';

                focusedElementBeforeModal = document.activeElement

                let element = ally.query.firstTabbable({
                    context: modal,
                    defaultToContext: true,
                });
                element.focus();

                setTimeout(function() {
                    disabledHandle = ally.maintain.disabled({
                        filter: modal,
                    });
    
                    tabHandle = ally.maintain.tabFocus({
                        context: modal,
                    });
    
                    keyHandle = ally.when.key({
                        escape: exportModal,
                    });
                });    
            } else {
                modal.style.display = 'none';
                disabledHandle.disengage();
                tabHandle.disengage();
                keyHandle.disengage();
                focusedElementBeforeModal.focus();
                let noticesDiv = document.querySelector('.xliff-export-notices');
                if (noticesDiv) {
                    noticesDiv.innerHTML = '';
                }
            }
        }
    }

    function importModal() {
        let modal = document.querySelector('.rrze-xliff-import-modal-wrapper');
        if (modal) {
            document.body.classList.toggle('modal-open');
            if (document.body.classList.contains('modal-open')) {
                modal.style.display = 'block';
             
                focusedElementBeforeModal = document.activeElement

                let element = ally.query.firstTabbable({
                    context: modal,
                    defaultToContext: true,
                });
                element.focus();

                setTimeout(function() {
                    disabledHandle = ally.maintain.disabled({
                        filter: modal,
                    });
    
                    tabHandle = ally.maintain.tabFocus({
                        context: modal,
                    });
    
                    keyHandle = ally.when.key({
                        escape: importModal,
                    });
                });    
            } else {
                modal.style.display = 'none';
                disabledHandle.disengage();
                tabHandle.disengage();
                keyHandle.disengage();
                focusedElementBeforeModal.focus();
            }
        }
    }
})();
