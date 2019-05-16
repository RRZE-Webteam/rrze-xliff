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

registerPlugin( 'rrze-xliff', {
    render: () => {
        const currentUrl = window.location;
        const postId = new URL(currentUrl).searchParams.get('post');
        const xliffExportUrl = `${currentUrl.protocol}//${currentUrl.host}${currentUrl.pathname}?xliff-export=${postId}`;
        const ExportModal = withState( {
            isOpen: false,
            emailAddress: '',
        } )( ( { isOpen, emailAddress, setState } ) => (
            <Fragment>
                <Button isTertiary onClick={ () => setState( { isOpen: true } ) }>{ __( 'Export', 'rrze-xliff' ) }</Button>
                { isOpen && (
                    <Modal
                        title={ __( 'Export post as XLIFF', 'rrze-xliff' ) }
                        onRequestClose={ () => setState( { isOpen: false } ) }
                    >
                        <p>
                            <Button
                                href={ xliffExportUrl }
                                isDefault={ true }
                            >
                                { __( 'Download XLIFF file', 'rrze-xliff' ) }
                            </Button>
                        </p>
                        <p><strong>{ __( 'Or send the file to an email address:', 'rrze-xliff' ) }</strong></p>
                        <TextControl
                            label={ __( 'Email address', 'rrze-xliff' ) }
                            value={ emailAddress }
                            onChange={ ( emailAddress ) => setState( { emailAddress } ) }
                        />
                        <p>
                            <Button
                                href={`${currentUrl.protocol}//${currentUrl.host}${currentUrl.pathname}?xliff-export=${postId}&email_address=${emailAddress}`}
                                isDefault={ true }
                            >
                                { __( 'Send XLIFF file', 'rrze-xliff' ) }
                            </Button>
                        </p>
                    </Modal>
                ) }
            </Fragment>
        ) );
        return (
            <PluginPostStatusInfo
                className="rrze-xliff-export-and-import"
            >
                <div>
                { __( 'XLIFF:', 'rrze-xliff' ) } <ExportModal/>
                </div>
            </PluginPostStatusInfo>
        )
    }
})
