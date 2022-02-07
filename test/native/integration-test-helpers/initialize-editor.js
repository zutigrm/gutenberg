/**
 * External dependencies
 */
import { render, fireEvent } from '@testing-library/react-native';
import { v4 as uuid } from 'uuid';

/**
 * WordPress dependencies
 */
// Editor component is not exposed in the pacakge because is meant to be consumed
// internally, however we require it for rendering the editor in integration tests,
// for this reason it's imported with path access.
// eslint-disable-next-line no-restricted-syntax
import Editor from '@wordpress/edit-post/src/editor';

/**
 * Internal dependencies
 */
import { executeStoreResolvers } from './execute-store-resolvers';

/**
 * Initialize an editor for test assertions.
 *
 * @param {Object}                    props               Properties passed to the editor component.
 * @param {string}                    props.initialHtml   String of block editor HTML to parse and render.
 * @param {Object}                    [options]           Configuration options for the editor.
 * @param {import('react').ReactNode} [options.component] A specific editor component to render.
 * @return {import('@testing-library/react-native').RenderAPI} A Testing Library screen.
 */
export async function initializeEditor( props, { component = Editor } = {} ) {
	return executeStoreResolvers( () => {
		const EditorComponent = component;
		const screen = render(
			<EditorComponent
				postId={ `post-id-${ uuid() }` }
				postType="post"
				initialTitle="test"
				{ ...props }
			/>
		);

		// A layout event must be explicitly dispatched in BlockList component,
		// otherwise the inner blocks are not rendered.
		fireEvent( screen.getByTestId( 'block-list-wrapper' ), 'layout', {
			nativeEvent: {
				layout: {
					width: 100,
				},
			},
		} );

		return screen;
	} );
}
