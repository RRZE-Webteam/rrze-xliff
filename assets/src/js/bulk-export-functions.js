/**
 * Funktionen für den Massenexport.
 */
(function(){
    // Change-Events vom Posts-Filter-Formular abfangen, um auf Änderungen in
    // Bulk-Selects zu reagieren.
    const postsFilterForm = document.querySelector('#posts-filter');
    if (postsFilterForm) {
        // Markup für die Optionen zusammenbauen.
        let advancedOptionsDiv = document.createElement('div'),
            emailWrapper = document.createElement('div'),
            emailFieldWrapper = document.createElement('p'),
            emailField = document.createElement('input'),
            emailLabel = document.createElement('label'),
            noteWrapper = document.createElement('p'),
            noteField = document.createElement('textarea'),
            noteLabel = document.createElement('label'),
            choiceDownloadWrapper = document.createElement('p'),
            choiceDownload = document.createElement('input'),
            choiceDownloadLabel = document.createElement('label'),
            choiceEmailWrapper = document.createElement('p'),
            choiceEmail = document.createElement('input'),
            choiceEmailLabel = document.createElement('label');
            
        advancedOptionsDiv.classList.add('xliff-bulk-export-options');

        choiceDownload.setAttribute('type', 'radio');
        choiceDownload.setAttribute('checked', 'checked');
        choiceDownload.setAttribute('id', 'xliff-bulk-export-choice-download');
        choiceDownload.setAttribute('value', 'xliff-bulk-export-choice-download');
        choiceDownload.setAttribute('name', 'xliff-bulk-export-choice');
        choiceDownloadLabel.append(document.createTextNode('Download'));
        choiceDownloadLabel.setAttribute('for', 'xliff-bulk-export-choice-download');
        choiceDownloadWrapper.append(choiceDownload);
        choiceDownloadWrapper.append(choiceDownloadLabel);

        choiceEmail.setAttribute('type', 'radio');
        choiceEmail.setAttribute('id', 'xliff-bulk-export-choice-email');
        choiceEmail.setAttribute('value', 'xliff-bulk-export-choice-email');
        choiceEmail.setAttribute('name', 'xliff-bulk-export-choice');
        choiceEmailLabel.append(document.createTextNode('Als E-Mail senden'));
        choiceEmailLabel.setAttribute('for', 'xliff-bulk-export-choice-email');
        choiceEmailWrapper.append(choiceEmail);
        choiceEmailWrapper.append(choiceEmailLabel);

        emailWrapper.classList.add('xliff-bulk-export-email-wrapper');
        emailWrapper.setAttribute('hidden', 'hidden');

        emailField.setAttribute('type', 'email');
        emailField.setAttribute('id', 'xliff-bulk-export-email');
        emailField.setAttribute('name', 'xliff-bulk-export-email');
        emailField.value = rrzeXliffJavaScriptData.email_address;
        emailLabel.append(document.createTextNode('E-Mail-Adresse'));
        emailLabel.setAttribute('for', 'xliff-bulk-export-email');
        emailLabel.setAttribute('style', 'display: block;');
        emailFieldWrapper.append(emailLabel);
        emailFieldWrapper.append(emailField);

        noteField.setAttribute('id', 'xliff-bulk-export-note');
        noteField.setAttribute('name', 'xliff-bulk-export-note');
        noteField.setAttribute('style', 'width: 100%;')
        noteLabel.append(document.createTextNode('E-Mail-Text'));
        noteLabel.setAttribute('for', 'xliff-bulk-export-note');
        noteLabel.setAttribute('style', 'display: block;');
        noteWrapper.append(noteLabel);
        noteWrapper.append(noteField);
        
        emailWrapper.append(emailFieldWrapper);
        emailWrapper.append(noteWrapper);

        advancedOptionsDiv.append(choiceDownloadWrapper);
        advancedOptionsDiv.append(choiceEmailWrapper);
        advancedOptionsDiv.append(emailWrapper);

        postsFilterForm.addEventListener('change', function(e) {
            // Prüfen, ob einer der Bulk-Selects geändert wurde.
            if (e.target.id === 'bulk-action-selector-bottom' || e.target.id === 'bulk-action-selector-top') {
                if (e.target.value === 'xliff_bulk_export') {
                    let bulkActionsWrapper = e.target.parentNode;
                    bulkActionsWrapper.append(advancedOptionsDiv);
                } else {
                    let childNodes = e.target.parentNode.childNodes;
                    for (let childNode of childNodes) {
                        if (childNode.classList !== undefined && childNode.classList.contains('xliff-bulk-export-options')) {
                            childNode.remove();
                        }
                    }
                }
            }

            if (e.target.name === 'xliff-bulk-export-choice' && e.target.id === 'xliff-bulk-export-choice-email') {
                emailWrapper.removeAttribute('hidden');
            } else if (e.target.name === 'xliff-bulk-export-choice') {
                emailWrapper.setAttribute('hidden', 'hidden');
            }
        })
    }
})();
