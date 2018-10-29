/**
 * Set Segment Gutenberg block
 *
 * @package segment-cache-for-wp-engine
 */

( function( blocks, element ) {
	var el = element.createElement;

	var blockStyle = {
		backgroundColor: '#900',
		color: '#fff',
		padding: '20px',
	};

	blocks.registerBlockType(
		'gutenberg-examples/example-01-basic',
		{
			title: __( 'Example: Basic', 'gutenberg-examples' ),
			icon: 'universal-access-alt',
			category: 'layout',
			edit: function() {
				return el(
					'p',
					{ style: blockStyle },
					'Hello World, step 1 (from the editor).'
				);
			},
			save: function() {
				return el(
					'p',
					{ style: blockStyle },
					'Hello World, step 1 (from the frontend).'
				);
			},
		}
	);
}(
	window.wp.blocks,
	window.wp.element
) );
