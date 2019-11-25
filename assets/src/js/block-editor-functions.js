const {registerPlugin} = wp.plugins;
const {PluginPostStatusInfo} = wp.editPost;
const {
    Button,
    TextControl,
    TextareaControl,
    Modal,
    Disabled,
	Notice,
	SelectControl
} = wp.components;
const {withState} = wp.compose;
const {Fragment} = wp.element;

// External dependencies.
import shim from 'string.prototype.matchall/shim';
shim();

registerPlugin( 'rrze-xliff', {
    render: () => {
        const currentUrl = window.location;
		const postId = new URL(currentUrl).searchParams.get('post');
		const langCodes = rrzeXliffJavaScriptData !== undefined && rrzeXliffJavaScriptData.lang_codes ? rrzeXliffJavaScriptData.lang_codes : [];
        let xliffExportUrl = `${currentUrl.protocol}//${currentUrl.host}${currentUrl.pathname}?xliff-export=${postId}`;
        let defaultEmailAdress = rrzeXliffJavaScriptData !== undefined && rrzeXliffJavaScriptData.email_address ? rrzeXliffJavaScriptData.email_address : 'de';

        const ExportModal = withState({
            isOpen: false,
            emailAddress: defaultEmailAdress,
			emailNote: '',
			xliffTargetLangCode: rrzeXliffJavaScriptData !== undefined && rrzeXliffJavaScriptData.default_target_lang_code ? rrzeXliffJavaScriptData.default_target_lang_code : ''
        })(({isOpen, emailAddress, emailNote, xliffTargetLangCode, setState}) => {

            function runExport(emailAddress, emailNote) {
                let xhr = new XMLHttpRequest(),
                    sendExportButton = document.querySelector('#xliff_export_email_button');
    
                sendExportButton.setAttribute('disabled', 'disabled');
    
                xhr.open("POST", ajaxurl, true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.responseType = 'json';
                xhr.onreadystatechange = function() {
                    if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                        setState({isOpen: false});
                        if (xhr.response.length > 0) {
                            xhr.response.forEach(function(currentValue) {
                                wp.data.dispatch('core/notices').createNotice(
                                    currentValue.type,
                                    currentValue.notice
                                )
                            });
                        }
                    }
                }
                emailNote = emailNote.replace(/(\r\n|[\r\n])/g, "<br>");
                xhr.send(`_nonce=${postId}&action=xliff_email_export&xliff_export_post=${postId}&xliff_export_email_address=${emailAddress}&email_export_note=${emailNote}&xliff_target_lang_code=${xliffTargetLangCode}`);
            }
            return (
                <Fragment>
                    <Button isTertiary onClick={() => setState({isOpen: true}) }>{rrzeXliffJavaScriptData.export}</Button>
                    {isOpen && (
                        <Modal
                            title={rrzeXliffJavaScriptData.export_title}
                            onRequestClose={() => setState({isOpen: false})}
                        >
							{langCodes.length > 0 && (
								<SelectControl
									label={rrzeXliffJavaScriptData.lang_code_label}
									value={xliffTargetLangCode}
									options={langCodes}
									onChange={(xliffTargetLangCode) => {setState({xliffTargetLangCode})}}
								/>
							)}
                            <p>
                                <Button
                                    href={`${xliffExportUrl}&xliff_target_lang_code=${xliffTargetLangCode}`}
                                    isDefault={true}
                                >
                                    {rrzeXliffJavaScriptData.download}
                                </Button>
                            </p>
                            <p><strong>{rrzeXliffJavaScriptData.send_via_email}</strong></p>
                            <TextControl
                                label={rrzeXliffJavaScriptData.email_address_label}
                                value={emailAddress}
                                onChange={(emailAddress) => setState({emailAddress})}
                                id='xliff_export_email_address'
                            />
                            <TextareaControl
                                label={rrzeXliffJavaScriptData.email_text_label}
                                id='email_export_note'
                                value={emailNote}
                                onChange={(emailNote) => setState({emailNote})}
                            />
                            <p>
                                <Button
                                    onClick={() => runExport(emailAddress, emailNote)}
                                    isDefault={true}
                                    id='xliff_export_email_button'
                                >
                                    {rrzeXliffJavaScriptData.send_email}
                                </Button>
                            </p>
                        </Modal>
                    )}
                </Fragment>
            )
        });

        function handleFiles( files ) {
            const reader = new FileReader();

            // Funktion, die nach Auslesen der Datei ausgeführt wird.
            reader.onload = (xliffString) => {
				let title,
					contentHtml,
					content,
					metaMatches;
				// Prüfen, ob wir XLIFF 1 oder XLIFF 2 vorliegen haben.
				if (xliffString.target.result.match(/<xliff ([^>]*)version="2\.0"([^>]*)>/) !== null) {
					// Wir haben XLIFF 2.

					// Titel des Beitrags holen.
					title = xliffString.target.result.match(/<unit id="title">(?:(?:.|\s)*?)<target>(?<title>(.|\s)*?)<\/target>/m);
					title = title !== null ? title['groups'].title : '';

					// Inhaltsbereich aus der XLIFF-Datei holen.
					contentHtml = xliffString.target.result.match(/<unit id="body">(?:(?:.|\s)*?)<target>(?<content>(.|\s)*?)<\/target>/m);
					content = contentHtml !== null ? contentHtml['groups'].content : '';

					// Metawerte holen.
					metaMatches = [...xliffString.target.result.matchAll(/<unit id="_meta_(?<metaKey>(?:.*))">(?:(?:.|\s)*?)<target>(?<metaValue>(.|\s)*?)<\/target>/mg)];
				} else {
					// Wir haben XLIFF 1.

					// Titel des Beitrags holen.
					title = xliffString.target.result.match(/<trans-unit ([^>]*)id="title">(?:(?:.|\s)*?)<target>(?<title>(.|\s)*?)<\/target>/m);
					title = title !== null ? title['groups'].title : '';

					// Inhaltsbereich aus der XLIFF-Datei holen.
					contentHtml = xliffString.target.result.match(/<trans-unit ([^>]*)id="body">(?:(?:.|\s)*?)<target>(?<content>(.|\s)*?)<\/target>/m);
					content = contentHtml !== null ? contentHtml['groups'].content : '';

					// <br class="xliff-newline" /> entfernen.
					content = content.replace(/<br ([^>]*)class="xliff-newline"([^>]*)\/>/gm, '');

					// Metawerte holen.
					metaMatches = [...xliffString.target.result.matchAll(/<trans-unit ([^>]*)id="_meta_(?<metaKey>(?:.*))">(?:(?:.|\s)*?)<target>(?<metaValue>(.|\s)*?)<\/target>/mg)];
				}

				// CDATA entfernen.
				const cdataRegex = /^<!\[CDATA\[(.*)]]>$/mg;
				title = title.replace(cdataRegex, '$1');
				content = content.replace(cdataRegex, '$1');

				let submitButton = document.querySelector('#xliff-import-button'),
					metaValues = {};
					

				submitButton.removeAttribute('hidden');

				// Objekt aus Metawerten bauen.
				if (metaMatches.length > 0) {
					for (let i = 0; i < metaMatches.length; i++) {
						const metaMatch = metaMatches[i];
						metaValues = Object.assign(metaValues, {[metaMatch.groups.metaKey]: metaMatch.groups.metaValue.replace(cdataRegex, '$1')})
					}
				}

                submitButton.addEventListener('click', function(e) {
                    // Das HTML des Beitragsinhalts aus der XLIFF-Datei in Blöcke parsen.
                    content = wp.blocks.parse(content);
                    // Die alten Blöcke aus dem Editor löschen.
                    // @link https://wordpress.stackexchange.com/a/305935.
                    wp.data.dispatch('core/block-editor').resetBlocks([]);

                    // Content-Blöcke einfügen und Titel aktualisieren.
                    wp.data.dispatch('core/block-editor').insertBlocks(content);
                    wp.data.dispatch('core/editor').editPost({title});

                    // Update post meta.
                    let meta = wp.data.select('core/editor').getEditedPostAttribute('meta');
                    for(let meta_key in metaValues) {
                        wp.data.dispatch('core/editor').editPost({meta: { ...meta, [meta_key]: metaValues[meta_key]}});
                    }
                })
            };
            // Mit FileReader() die ausgewählte Datei auslesen.
            reader.readAsText(files[0]);
        }

        const ImportModal = withState( {
            isOpen: false,
            hasFile: false,
        } )( ( { isOpen, hasFile, setState } ) => {
            let button = <Button isDefault id="xliff-import-button" onClick={() => setState({isOpen: false})} hidden>{rrzeXliffJavaScriptData.import}</Button>;
            return (
                <Fragment>
                    <Button isTertiary onClick={() => setState({isOpen: true})}>{rrzeXliffJavaScriptData.import}</Button>
                    {isOpen && (
                        <Modal
                            title={rrzeXliffJavaScriptData.import}
                            onRequestClose={ () => setState({isOpen: false})}
                        >
                            <input type="file" id="xliff-file" name="xliff-file" accept=".xliff" onChange={(e) => {
                                    handleFiles(e.target.files);
                                    if (e.target.files) {
                                        setState({hasFile: true})
                                    } else {
                                        setState({hasFile: false})
                                    }
                                }}/>
							{!hasFile ? <Disabled>{button}</Disabled> : button}
                        </Modal>
                    )}
                </Fragment>
            )
        } );
        return (
            <PluginPostStatusInfo
                className="rrze-xliff-export-and-import"
            >
                <div>
                {rrzeXliffJavaScriptData.xliff} <ExportModal/> <ImportModal/>
                </div>
            </PluginPostStatusInfo>
        )
    }
})
