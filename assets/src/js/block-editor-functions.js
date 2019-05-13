const { registerPlugin } = wp.plugins;
const { PluginPostStatusInfo } = wp.editPost;
const {
    Button,
    Panel,
    PanelBody,
    PanelRow,
    TextControl,
    Modal,
} = wp.components;
const { withState } = wp.compose;
const { __ } = wp.i18n;
const { Fragment } = wp.element;

registerPlugin( 'rrzn-xliff', {
    render: () => {
        const currentUrl = window.location;
        const postId = new URL(currentUrl).searchParams.get('post');
        const xliffExportUrl = `${currentUrl.protocol}//${currentUrl.host}${currentUrl.pathname}?xliff-export=${postId}`;
        const ExportModal = withState( {
            isOpen: false,
            emailAddress: '',
        } )( ( { isOpen, emailAddress, setState } ) => (
            <Fragment>
                <Button isTertiary onClick={ () => setState( { isOpen: true } ) }>{ __( 'Export', 'rrzn-xliff' ) }</Button>
                { isOpen && (
                    <Modal
                        title={ __( 'Export post as XLIFF', 'rrzn-xliff' ) }
                        onRequestClose={ () => setState( { isOpen: false } ) }
                    >
                        <p>
                            <Button
                                href={ xliffExportUrl }
                                isDefault={ true }
                            >
                                { __( 'Download XLIFF file', 'rrzn-xliff' ) }
                            </Button>
                        </p>
                        <p><strong>{ __( 'Or send the file to an email address:', 'rrzn-xliff' ) }</strong></p>
                        <TextControl
                            label={ __( 'Email address', 'rrzn-xliff' ) }
                            value={ emailAddress }
                            onChange={ ( emailAddress ) => setState( { emailAddress } ) }
                        />
                        <p>
                            <Button
                                href={`${currentUrl.protocol}//${currentUrl.host}${currentUrl.pathname}?xliff-export=${postId}&email_adress=${emailAddress}`}
                                isDefault={ true }
                            >
                                { __( 'Send XLIFF file', 'rrzn-xliff' ) }
                            </Button>
                        </p>
                    </Modal>
                ) }
            </Fragment>
        ) );
        return (
            <PluginPostStatusInfo
                className="rrzn-xliff-export-and-import"
            >
                <div>
                { __( 'XLIFF:', 'rrzn-xliff' ) } <ExportModal/>
                </div>
            </PluginPostStatusInfo>
        )
    }
})
