const {registerPlugin} = wp.plugins;
const {PluginPostStatusInfo} = wp.editPost;
const {
    Button,
    TextControl,
    Modal,
    Disabled,
} = wp.components;
const {withState} = wp.compose;
const {__} = wp.i18n;
const {Fragment} = wp.element;

registerPlugin( 'rrze-xliff', {
    render: () => {
        const currentUrl = window.location;
        const postId = new URL(currentUrl).searchParams.get('post');
        const xliffExportUrl = `${currentUrl.protocol}//${currentUrl.host}${currentUrl.pathname}?xliff-export=${postId}`;
        let defaultEmailAdress = rrzeXliffJavaScriptData !== undefined && rrzeXliffJavaScriptData.email_address ? rrzeXliffJavaScriptData.email_address : '';
        const ExportModal = withState({
            isOpen: false,
            emailAddress: defaultEmailAdress,
        })(({isOpen, emailAddress, setState}) => {
            return (
                <Fragment>
                    <Button isTertiary onClick={() => setState({isOpen: true}) }>{__('Export', 'rrze-xliff')}</Button>
                    { isOpen && (
                        <Modal
                            title={__('Export post as XLIFF', 'rrze-xliff')}
                            onRequestClose={ () => setState( { isOpen: false } ) }
                        >
                            <p>
                                <Button
                                    href={ xliffExportUrl }
                                    isDefault={ true }
                                >
                                    {__('Download XLIFF file', 'rrze-xliff')}
                                </Button>
                            </p>
                            <p><strong>{__( 'Or send the file to an email address:', 'rrze-xliff')}</strong></p>
                            <TextControl
                                label={__('Email address', 'rrze-xliff')}
                                value={ emailAddress }
                                onChange={ ( emailAddress ) => setState( { emailAddress } ) }
                            />
                            <p>
                                <Button
                                    href={`${currentUrl.protocol}//${currentUrl.host}${currentUrl.pathname}?xliff-export=${postId}&xliff_export_email_address=${emailAddress}`}
                                    isDefault={ true }
                                >
                                    {__('Send XLIFF file', 'rrze-xliff')}
                                </Button>
                            </p>
                        </Modal>
                    ) }
                </Fragment>
            )
        } );

        function handleFiles( files ) {
            const reader = new FileReader();

            // Funktion, die nach Auslesen der Datei ausgeführt wird.
            reader.onload = (xliffString) => {
                let oParser = new DOMParser(),
                    oDOM = oParser.parseFromString(xliffString.target.result, 'application/xml'),
                    submitButton = document.querySelector('#xliff-import-button'),
                    title,
                    content,
                    metaValues = {};

                submitButton.removeAttribute('hidden');

                // Die Knoten der XLIFF-Datei durchlaufen und die Strings zusammensetzen, die
                // in den Editor kommen.
                for (let xliffNode of oDOM.childNodes) {
                    if (xliffNode.nodeName === 'xliff') {
                        for (let childNode of xliffNode.childNodes) {
                            if (childNode.nodeName === 'file') {
                                for (let fileChild of childNode.childNodes) {
                                    if (fileChild.nodeName === 'unit') {
                                        if (fileChild.id === 'title') {
                                            for (let titleNodes of fileChild.childNodes) {
                                                if (titleNodes.nodeName === 'segment') {
                                                    for (let titleNodeSegment of titleNodes.childNodes) {
                                                        if (titleNodeSegment.nodeName === 'target') {
                                                            title = titleNodeSegment.textContent;
                                                        }
                                                    }
                                                }
                                            }
                                        } else if (fileChild.id === 'body') {
                                            for (let bodyNodes of fileChild.childNodes) {
                                                if (bodyNodes.nodeName === 'segment') {
                                                    for (let bodyNodeSegment of bodyNodes.childNodes) {
                                                        if (bodyNodeSegment.nodeName === 'target') {
                                                            content = bodyNodeSegment.textContent;
                                                        }
                                                    }
                                                }
                                            }
                                        } else if (fileChild.id.indexOf('_meta_') === 0) {
                                            for (let metaNodes of fileChild.childNodes) {
                                                if (metaNodes.nodeName === 'segment') {
                                                    for (let metaNodesegment of metaNodes.childNodes) {
                                                        if (metaNodesegment.nodeName === 'target') {
                                                            metaValues = Object.assign(metaValues, {[`${fileChild.id.replace('_meta_', '')}`]: metaNodesegment.textContent})
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }    
                }

                submitButton.addEventListener('click', function(e) {
                    // Das HTML des Beitragsinhalts aus der XLIFF-Datei in Blöcke parsen.
                    content = wp.blocks.parse(content);
                    // Die alten Blöcke aus dem Editor löschen.
                    // @link https://wordpress.stackexchange.com/a/305935.
                    wp.data.dispatch('core/editor').resetBlocks([]);

                    // Content-Blöcke einfügen und Titel aktualisieren.
                    wp.data.dispatch('core/editor').insertBlocks(content);
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
            let button = <Button isDefault id="xliff-import-button" onClick={() => setState({isOpen: false})} hidden="true">{__('Import', 'rrze-xliff')}</Button>;
            return (
                <Fragment>
                    <Button isTertiary onClick={() => setState({isOpen: true})}>{__( 'Import', 'rrze-xliff') }</Button>
                    {isOpen && (
                        <Modal
                            title={__( 'Import', 'rrze-xliff') }
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
                            <p>
                                {!hasFile ? <Disabled>{button}</Disabled> : button}
                            </p>
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
                {__( 'XLIFF:', 'rrze-xliff') } <ExportModal/> <ImportModal/>
                </div>
            </PluginPostStatusInfo>
        )
    }
})
