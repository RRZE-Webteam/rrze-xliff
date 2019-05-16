/**
 * Funktionen für den Massenexport.
 */
(function(){
    // Change-Events vom Posts-Filter-Formular abfangen, um auf Änderungen in
    // Bulk-Selects zu reagieren.
    const postsFilterForm = document.querySelector('#posts-filter');
    if (postsFilterForm) {
        // Markup für die Optionen zusammenbauen.
        let advancedOptionsDiv = document.createElement('div')
            emailField = document.createElement('input'),
            emailLabel = document.createElement('label'),
            noteField = document.createElement('textarea'),
            noteLabel = document.createElement('label'),
            choiceDownload = document.createElement('input'),
            choiceDownloadLabel = document.createElement('label'),
            choiceEmail = document.createElement('input'),
            choiceEmailLabel = document.createElement('label');
            
        advancedOptionsDiv.classList.add('xliff-bulk-export-options');

        choiceDownload.setAttribute('type', 'radio');
        choiceDownload.setAttribute('id', 'xliff-bulk-export-choice-download');
        choiceDownload.setAttribute('value', 'xliff-bulk-export-choice-download');
        choiceDownload.setAttribute('name', 'xliff-bulk-export-choice');
        choiceDownloadLabel.append(document.createTextNode('Download'));
        choiceDownloadLabel.setAttribute('for', 'xliff-bulk-export-choice-download');

        choiceEmail.setAttribute('type', 'radio');
        choiceEmail.setAttribute('id', 'xliff-bulk-export-choice-email');
        choiceEmail.setAttribute('value', 'xliff-bulk-export-choice-email');
        choiceEmail.setAttribute('name', 'xliff-bulk-export-choice');
        choiceEmailLabel.append(document.createTextNode('Als E-Mail senden'));
        choiceEmailLabel.setAttribute('for', 'xliff-bulk-export-choice-email');

        emailField.setAttribute('type', 'email');
        emailField.setAttribute('id', 'xliff-bulk-export-email');
        emailField.setAttribute('name', 'xliff-bulk-export-email');
        emailLabel.append(document.createTextNode('E-Mail-Adresse'));
        emailLabel.setAttribute('for', 'xliff-bulk-export-email');

        noteField.setAttribute('id', 'xliff-bulk-export-note');
        noteField.setAttribute('name', 'xliff-bulk-export-note');
        noteLabel.append(document.createTextNode('E-Mail-Text'));
        noteLabel.setAttribute('for', 'xliff-bulk-export-note');

        advancedOptionsDiv.append(choiceDownload);
        advancedOptionsDiv.append(choiceDownloadLabel);
        advancedOptionsDiv.append(choiceEmail);
        advancedOptionsDiv.append(choiceEmailLabel);
        advancedOptionsDiv.append(emailLabel);
        advancedOptionsDiv.append(emailField);
        advancedOptionsDiv.append(noteLabel);
        advancedOptionsDiv.append(noteField);

        postsFilterForm.addEventListener('change', function(e) {
            // Prüfen, ob einer der Bulk-Selects geändert wurde.
            if (e.target.id === 'bulk-action-selector-bottom' || e.target.id === 'bulk-action-selector-top') {
                if (e.target.value === 'xliff_bulk_export') {
                    let bulkActionsWrapper = e.target.parentNode;
                    bulkActionsWrapper.append(advancedOptionsDiv);
                }
            }
        })
    }
})();
