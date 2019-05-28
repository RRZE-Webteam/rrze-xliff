
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
})();
