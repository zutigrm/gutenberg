/**
 * WordPress dependencies
 */
import { compose } from '@wordpress/compose';
import { useEffect } from '@wordpress/element';
import { withDispatch } from '@wordpress/data';
import { transformMediaQueries } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import Layout from '../layout';

function EditWidgetsInitializer( { setupWidgetAreas, settings } ) {
	useEffect( () => {
		setupWidgetAreas();
		transformMediaQueries( [
			'block-editor/style.css',
			'block-library/style.css',
			'block-library/theme.css',
			'block-library/editor.css',
			'format-library/style.css',
		] );
	}, [] );
	return (
		<Layout
			blockEditorSettings={ settings }
		/>
	);
}

export default compose( [
	withDispatch( ( dispatch ) => {
		const { setupWidgetAreas } = dispatch( 'core/edit-widgets' );
		return {
			setupWidgetAreas,
		};
	} ),
] )( EditWidgetsInitializer );
