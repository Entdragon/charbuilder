let customtables_types = [];
let customtables_tables = [];

let customtables_layouts_catalog = [];
let customtables_layouts_edit_form = [];
let customtables_layouts_details = [];

let customtables_prerenderedContent = [];

const definesUtilityFunction = function () {

    // Retrieve the 'blocks' object from the 'wp' namespace in the window
    var blocks = window.wp.blocks;

    // Define metadata for a custom block in WordPress
    var blockMetadata = JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"customtables/dynamic-block","version":"0.1.0","title":"Example: Dynamic Block (ESNext)","category":"text","icon":"universal-access-alt",' +
        '"attributes":{"message":{}},"example":{"attributes":{"message":"CustomTables Block"}},"supports":{"html":false},"textdomain":"dynamic-block","editorScript":"file:./index.js","editorStyle":"file:./index.css","style":"file:./style-index.css"}');

    // Load list of tables, layouts and render the block and the side panel
    CustomTablesLoadTypes();
    CustomTablesLoadTables();
    CustomTablesLoadLayouts();

    // Destructure 'name' property from 'blockMetadata'
    const {name: i} = blockMetadata;

    // Render the custom block
    CustomTablesRenderBlock(blocks, i);
};

definesUtilityFunction();

// Function to find label by value
function findLabelByValue(value) {
    for (let i = 0; i < customtables_types.length; i++) {
        if (customtables_types[i].value === value) {
            return customtables_types[i].label;
        }
    }
    // If value not found, return null or appropriate fallback value
    return null;
}

function CustomTablesLoadTypes() {
    customtables_types = [
        {
            'label': 'Catalog',
            'value': 1
        },
        {
            'label': 'Edit Form',
            'value': 2
        },
        {
            'label': 'Details',
            'value': 4
        }
    ]
}

function CustomTablesLoadTables() {
    //Load list of tables
    let parts = location.href.split("wp-admin/");
    let url = parts[0] + 'wp-admin/admin.php?page=customtables-api-tables';

    fetch(url, {method: 'GET', mode: 'no-cors', credentials: 'same-origin'}).then(function (response) {

        if (response.ok) {
            response.json().then(function (json) {

                customtables_tables = Array.from(json);

            });
        } else {
            console.log('CustomTables - Block widget: Network request for products.json failed with response ' + response.status + ': ' + response.statusText);
        }

    }).catch(function (err) {
        console.log(url, err);
        console.log('CustomTables - Block widget: Cannot load list of Tables.', err);
    });
}

function CustomTablesLoadLayouts() {
    //Load list of tables
    let parts = location.href.split("wp-admin/");
    let url = parts[0] + 'wp-admin/admin.php?page=customtables-api-layouts';

    fetch(url, {method: 'GET', mode: 'no-cors', credentials: 'same-origin'}).then(function (response) {

        if (response.ok) {
            response.json().then(function (json) {
                let customtables_layouts = Array.from(json);

                for (let i = 0; i < customtables_layouts.length; i++) {

                    let t = parseInt(customtables_layouts[i].type);
                    if (t === 1 || t === 5 || t === 8 || t === 9 || t === 10)
                        customtables_layouts_catalog.push(customtables_layouts[i]);
                    else if (t === 2)
                        customtables_layouts_edit_form.push(customtables_layouts[i]);
                    else if (t === 4)
                        customtables_layouts_details.push(customtables_layouts[i]);
                    else if (t === 0) {
                        customtables_layouts_catalog.push(customtables_layouts[i]);
                        customtables_layouts_edit_form.push(customtables_layouts[i]);
                        customtables_layouts_details.push(customtables_layouts[i]);
                    }
                }
            });
        } else {
            console.log('CustomTables - Block widget: Network request for products.json failed with response ' + response.status + ': ' + response.statusText);
        }

    }).catch(function (err) {
        console.log(url, err);
        console.log('CustomTables - Block widget: Cannot load list of Layouts.', err);
    });
}

function CustomTablesLoadPreview(newAttributes, props) {

    //Load list of tables
    let parts = location.href.split("wp-admin/");
    let url = parts[0] + 'wp-admin/admin.php?page=customtables-api-preview&g=y&attributes=' + encodeURIComponent(JSON.stringify(newAttributes));

    fetch(url, {method: 'GET', mode: 'no-cors', credentials: 'same-origin'}).then(function (response) {

        if (response.ok) {
            response.text().then(function (text) {

                let blockId = cyrb53(JSON.stringify(newAttributes));
                customtables_prerenderedContent[blockId] = text;
                setTimeout(function () {
                    props.setAttributes({loading: 1});
                }, 200);

            });

        } else {
            console.log('CustomTables - Block widget: Network request for products.json failed with response ' + response.status + ': ' + response.statusText);
        }

    }).catch(function (err) {
        console.log(url, err);
        console.log('CustomTables - Block widget: Cannot load list of Layouts.', err);
    });
}

function CustomTablesRenderBlock(e, i) {

    (0, e.registerBlockType)(i, {

        edit: function (props) {
            customtables_setAttribute = props.setAttribute;

            //With Control Panel
            let blockProps = wp.blockEditor.useBlockProps();
            blockProps.className = 'block-editor-block-list__block wp-block is-selected wp-block-image';
            props.attributes.loading = 0;
            let blockId = cyrb53(JSON.stringify(props.attributes));

            let el = wp.element.createElement;
            let InspectorControls = wp.editor.InspectorControls;
            let PanelBody = wp.components.PanelBody;
            let SelectControl = wp.components.SelectControl;
            let TextControl = wp.components.TextControl;
            let __ = wp.i18n.__;
            let generatedPreview;

            if (customtables_prerenderedContent[blockId] === undefined) {
                CustomTablesLoadPreview(props.attributes, props);
            } else {
                generatedPreview = (customtables_prerenderedContent[blockId] !== '' ? customtables_prerenderedContent[blockId] : '<p>CustomTables Block:<br/>Please select a Table and Layout.</p>')
            }

            const selectTypeBox = el(
                PanelBody,
                {
                    title: __('Type'),
                    initialOpen: true,
                    className: '2grw-toggle grw-builder-connect 2grw-connect-business'
                },

                el(
                    SelectControl,
                    {
                        id: 'customtables_block_type',
                        name: 'customtables_block_type',
                        value: props.attributes.type,
                        options: customtables_types,
                        onChange: function (newValue) {
                            props.setAttributes({type: newValue});
                            props.setAttributes({loading: 0});

                            let newAttributes = props.attributes;
                            newAttributes.type = newValue;

                            CustomTablesLoadPreview(newAttributes, props);
                        }
                    }
                )
            )

            const selectTableBox = el(
                PanelBody,
                {
                    title: __('Table'),
                    initialOpen: true,
                    className: '2grw-toggle grw-builder-connect 2grw-connect-business'
                },

                el(
                    SelectControl,
                    {
                        id: 'customtables_block_table',
                        name: 'customtables_block_table',
                        value: props.attributes.table,
                        options: customtables_tables,
                        onChange: function (newValue) {
                            props.setAttributes({table: newValue});
                            props.setAttributes({loading: 0});

                            let newAttributes = props.attributes;
                            newAttributes.table = newValue;

                            CustomTablesLoadPreview(newAttributes, props);
                        }
                    }
                )
            );

            const selectCatalogLayoutBox = el(
                PanelBody,
                {
                    title: __('Catalog Layout'),
                    initialOpen: true
                },
                el(
                    SelectControl,
                    {
                        id: 'customtables_block_cataloglayout',
                        name: 'customtables_block_cataloglayout',
                        value: props.attributes.cataloglayout,
                        options: customtables_layouts_catalog,
                        onChange: function (newValue) {
                            props.setAttributes({cataloglayout: newValue});
                            props.setAttributes({loading: 0});

                            let newAttributes = props.attributes;
                            newAttributes.cataloglayout = newValue;

                            CustomTablesLoadPreview(newAttributes, props);
                        }
                    }
                )
            );

            const selectEditFormLayoutBox = el(
                PanelBody,
                {
                    title: __('Edit Form Layout'),
                    initialOpen: true
                },
                el(
                    SelectControl,
                    {
                        id: 'customtables_block_editlayout',
                        name: 'customtables_block_editlayout',
                        value: props.attributes.editlayout,
                        options: customtables_layouts_edit_form,
                        onChange: function (newValue) {
                            props.setAttributes({editlayout: newValue});
                            props.setAttributes({loading: 0});

                            let newAttributes = props.attributes;
                            newAttributes.editlayout = newValue;

                            CustomTablesLoadPreview(newAttributes, props);
                        }
                    }
                )
            );

            const selectDetailsLayoutBox = el(
                PanelBody,
                {
                    title: __('Details View Layout'),
                    initialOpen: true
                },
                el(
                    SelectControl,
                    {
                        id: 'customtables_block_detailslayout',
                        name: 'customtables_block_detailslayout',
                        value: props.attributes.detailslayout,
                        options: customtables_layouts_details,
                        onChange: function (newValue) {
                            props.setAttributes({detailslayout: newValue});
                            props.setAttributes({loading: 0});

                            let newAttributes = props.attributes;
                            newAttributes.detailslayout = newValue;

                            CustomTablesLoadPreview(newAttributes, props);
                        }
                    }
                )
            );

            const selectFilterBox = el(
                PanelBody,
                {
                    title: __('Filter'),
                    initialOpen: false
                },
                el(
                    TextControl,
                    {
                        label: 'Filter (Where clause)',
                        value: props.attributes.filter,
                        onChange: function (newValue) {
                            props.setAttributes({filter: newValue});
                            props.setAttributes({loading: 0});

                            let newAttributes = props.attributes;
                            newAttributes.filter = newValue;

                            CustomTablesLoadPreview(newAttributes, props);
                        }
                    }
                ),
            );

            const selectSortingBox = el(
                PanelBody,
                {
                    title: __('Sorting'),
                    initialOpen: false
                },
                el(
                    TextControl,
                    {
                        label: 'Order By',
                        value: props.attributes.orderby,
                        onChange: function (newValue) {
                            props.setAttributes({orderby: newValue});
                            props.setAttributes({loading: 0});

                            let newAttributes = props.attributes;
                            newAttributes.orderby = newValue;

                            CustomTablesLoadPreview(newAttributes, props);
                        }
                    }
                ),
                el(
                    TextControl,
                    {
                        label: 'Direction',
                        value: props.attributes.order,
                        onChange: function (newValue) {
                            props.setAttributes({order: newValue});
                            props.setAttributes({loading: 0});

                            let newAttributes = props.attributes;
                            newAttributes.order = newValue;

                            CustomTablesLoadPreview(newAttributes, props);
                        }
                    }
                )
            );

            const selectLimitBox = el(
                PanelBody,
                {
                    title: __('Limit'),
                    initialOpen: false
                },
                el(
                    TextControl,
                    {
                        label: 'Number of records pre page',
                        value: props.attributes.limit,
                        onChange: function (newValue) {
                            props.setAttributes({limit: newValue});
                            props.setAttributes({loading: 0});

                            let newAttributes = props.attributes;
                            newAttributes.limit = newValue;

                            CustomTablesLoadPreview(newAttributes, props);
                        }
                    }
                )
            );

            const myElements = [selectTypeBox, selectTableBox];

            let layoutType = parseInt(props.attributes.type);
            console.log("layoutType: " + layoutType);

            if (layoutType === 1) {
                myElements.push(selectCatalogLayoutBox);
                myElements.push(selectFilterBox);
                myElements.push(selectSortingBox);
                myElements.push(selectLimitBox);

                myElements.push(selectEditFormLayoutBox);
                myElements.push(selectDetailsLayoutBox);
            } else if (layoutType === 2) {
                myElements.push(selectEditFormLayoutBox);

            } else if (layoutType === 4) {
                myElements.push(selectDetailsLayoutBox);
            }

            return el(
                'div',
                blockProps,
                el(
                    InspectorControls,
                    {
                        key: 'inspector'
                    },
                    el(
                        'div',
                        {
                            id: '2grw-builder-option',
                            className: '2grw-builder-options 2grw-block-options'
                        },
                        el(
                            TextControl,
                            {
                                id: 'customtables_block_loading',
                                name: 'customtables_block_loading',
                                value: 0,
                                /*options: [{label: 'state 0',value: 0},{label: 'state 1',value: 1}],*/
                                onChange: function (newValue) {
                                    props.setAttributes({loading: 0});
                                },
                                type: 'hidden'
                            }
                        )
                        , myElements
                        ,
                    )
                ),
                el(
                    'fieldset',
                    {
                        id: 'customtables-block-wizard',
                        title: 'CustomTables Block',
                        style: {
                            'display': 'block',
                            'padding': '10px 20px',
                            'overflow': 'hidden',
                            'width': '100%',
                        },
                        class: 'components-placeholder block-editor-media-placeholder is-large has-illustration'
                    },
                    el(
                        'legend',
                        {
                            style: {
                                'background-color': 'white'
                            }
                        },
                        'Type: ' + findLabelByValue(parseInt(props.attributes.type))
                    ),
                    el(
                        wp.element.RawHTML,
                        {
                            style: {
                                'display': 'block',
                                'overflow': 'hidden'
                            }
                        },
                        generatedPreview
                    )
                )
            );
            //End of control panel
        },
        save: props => {
            props.attributes.loading = 1;
            let attributes = props.attributes;
            let newAttributes = {
                table: attributes.table,
                cataloglayout: attributes.cataloglayout,
                editlayout: attributes.editlayout,
                detailslayout: attributes.detailslayout,
                filter: attributes.filter,
                orderby: attributes.orderby,
                order: attributes.order,
                limit: attributes.limit,
                loading: 0
            }
            return newAttributes;
        },
    })
}

const cyrb53 = (str, seed = 0) => {
    let h1 = 0xdeadbeef ^ seed, h2 = 0x41c6ce57 ^ seed;
    for (let i = 0, ch; i < str.length; i++) {
        ch = str.charCodeAt(i);
        h1 = Math.imul(h1 ^ ch, 2654435761);
        h2 = Math.imul(h2 ^ ch, 1597334677);
    }
    h1 = Math.imul(h1 ^ (h1 >>> 16), 2246822507);
    h1 ^= Math.imul(h2 ^ (h2 >>> 13), 3266489909);
    h2 = Math.imul(h2 ^ (h2 >>> 16), 2246822507);
    h2 ^= Math.imul(h1 ^ (h1 >>> 13), 3266489909);

    return 4294967296 * (2097151 & h2) + (h1 >>> 0);
};

