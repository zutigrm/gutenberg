/**
 * External dependencies
 */
import { act } from '@testing-library/react';

/**
 * WordPress dependencies
 */
import { unmountComponentAtNode } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { initialize } from '../';

let container;

beforeEach( () => {
	container = document.createElement( 'div' );
	container.id = 'root';
	document.body.appendChild( container );
} );

afterEach( () => {
	unmountComponentAtNode( container );
	document.body.removeChild( container );
} );

test( 'it renders', () => {
	act( () => {
		initialize( 'root', {} );
	} );

	expect( container ).toMatchInlineSnapshot();
} );
