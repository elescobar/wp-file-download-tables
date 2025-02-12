( function( blocks, element, components ) {
    var el = element.createElement;
    var registerBlockType = blocks.registerBlockType;
    var useState = element.useState;
    var useEffect = element.useEffect;
    var SelectControl = components.SelectControl;
    var apiFetch = wp.apiFetch;

    registerBlockType( 'wp-fdt/file-table', {
        title: 'File Download Table',
        description: 'A table for listing downloadable files.', // Add this line
        icon: 'media-document',
        category: 'common',
        attributes: {
            tableId: { type: 'string', default: '' }
        },
        edit: function( props ) {
            var [tables, setTables] = useState([]);
    
            useEffect(() => {
                apiFetch({ path: '/wp-fdt/v1/tables/' })
                    .then((data) => {
                        if (Array.isArray(data)) {
                            setTables(data);
                        } else {
                            console.error("Invalid API response:", data);
                            setTables([]);
                        }
                    })
                    .catch((error) => {
                        console.error("Error fetching tables:", error);
                        setTables([]);
                    });
            }, []);
    
            function updateTableId( value ) {
                props.setAttributes({ tableId: value });
            }
    
            return el( 'div', {},
                el( SelectControl, {
                    label: 'Select File Table:',
                    value: props.attributes.tableId,
                    options: [{ label: 'Choose a Table', value: '' }].concat(
                        tables.map( table => ({ label: table.name, value: table.id }) )
                    ),
                    onChange: updateTableId
                })
            );
        },
        save: function( props ) {
            return el( 'div', {}, '[file_table id="' + props.attributes.tableId + '"]' );
        }
    });
    

} )( window.wp.blocks, window.wp.element, window.wp.components );
