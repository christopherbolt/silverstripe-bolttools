// Add start and type attributes for <ol>
SilverStripe\Forms\HTMLEditor\TinyMCEConfig::get('cms')->setOption(
    'extended_valid_elements',
    TinyMCEConfig::get('cms')->getOption('extended_valid_elements')
	.',ol[start|type],table[border|cellspacing|cellpadding|width|height|class|align|summary|dir|id|style]'
);

// Add custom styles to the editor, this allows us more control than just adding the styles from typography.css
SilverStripe\Forms\HTMLEditor\TinyMCEConfig::get('cms')
->setOption('style_formats_merge', false)
->setOption('importcss_append', true)
->setOption(
	'style_formats',
	array(
		/* Block Styles */
    	array(
    		'title' => 'Lead In',
    		'block' => 'p',
    		'classes' => 'leadin'
    	),
		array(
    		'title' => 'Small',
    		'block' => 'p',
    		'classes' => 'small'
    	),
		array(
    		'title' => 'Large',
    		'block' => 'p',
    		'classes' => 'large'
    	),
		
		/* Columns */
		/*array(
        	'title' => 'Multiple Columns',
        	'block' => 'div',
        	'classes' => 'columns',
        	'wrapper' => true
        ),*/
		
		/* Margins */
		array(
    		'title' => 'Paragraph Space',
    		'selector' => 'p,ul,ol,li,blockquote,address,pre,h1,h2,h3,h4,h5,h6',
    		'classes' => 'margin-bottom'
    	),
		array(
    		'title' => 'No Paragraph Space',
    		'selector' => 'p,ul,ol,li,blockquote,address,pre,h1,h2,h3,h4,h5,h6',
    		'classes' => 'no-margins'
    	),
    	
    	/* Colors and other stuff here... */
    	
    	/*
		// EXAMPLES:
		// Selector:
		array(
    		'title' => 'Button',
    		'selector' => 'a', // selector will add to any EXISTING elements in the selection that match this string, can be comma separated list of elements
    		'classes' => 'button' // Will add this class
    	),
		// Block:
        array(
        	'title' => 'Callout Box',
        	'block' => 'h2', // Will change the selected blocks to this element
        	'classes' => 'mySpecialHeading', // Will add this class
        ),
		// Wrapper:
        array(
        	'title' => 'Callout Box',
        	'block' => 'div', // Element to wrap with
        	'classes' => 'callout', // Will add this class
        	'wrapper' => true // Will wrap around the entire selection
        ),
		// Inline:
        array(
        	'title' => 'Inline Style',
        	'inline' => 'span', // Inline element to wrap selection with
        	'classes' => 'callout', // Will add this class
        )
		*/
    )
)
->insertButtonsBefore('formatselect', array('styleselect'));