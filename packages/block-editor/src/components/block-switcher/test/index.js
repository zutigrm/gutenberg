/**
 * External dependencies
 */
import { shallow } from 'enzyme';

/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { registerBlockType, unregisterBlockType } from '@wordpress/blocks';
import { copy } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import { BlockSwitcher, BlockSwitcherDropdownMenu } from '../';

jest.mock( '@wordpress/data/src/components/use-select', () => jest.fn() );

describe( 'BlockSwitcher', () => {
	test( 'should not render block switcher without blocks', () => {
		useSelect.mockImplementation( () => ( {} ) );
		const wrapper = shallow( <BlockSwitcher /> );
		expect( wrapper.html() ).toBeNull();
	} );

	test( 'should not render block switcher with null blocks', () => {
		useSelect.mockImplementation( () => ( { blocks: [ null ] } ) );
		const wrapper = shallow(
			<BlockSwitcher
				clientIds={ [ 'a1303fd6-3e60-4fff-a770-0e0ea656c5b9' ] }
			/>
		);
		expect( wrapper.html() ).toBeNull();
	} );
} );
describe( 'BlockSwitcherDropdownMenu', () => {
	const headingBlock1 = {
		attributes: {
			content: [ 'How are you?' ],
			level: 2,
		},
		isValid: true,
		name: 'core/heading',
		originalContent: '<h2>How are you?</h2>',
		clientId: 'a1303fd6-3e60-4fff-a770-0e0ea656c5b9',
	};

	const textBlock = {
		attributes: {
			content: [ 'I am great!' ],
		},
		isValid: true,
		name: 'core/paragraph',
		originalContent: '<p>I am great!</p>',
		clientId: 'b1303fdb-3e60-43faf-a770-2e1ea656c5b8',
	};

	const headingBlock2 = {
		attributes: {
			content: [ 'I am the greatest!' ],
			level: 3,
		},
		isValid: true,
		name: 'core/heading',
		originalContent: '<h3>I am the greatest!</h3>',
		clientId: 'c2403fd2-4e63-5ffa-b71c-1e0ea656c5b0',
	};

	beforeAll( () => {
		registerBlockType( 'core/heading', {
			category: 'text',
			title: 'Heading',
			edit: () => {},
			save: () => {},
			transforms: {
				to: [
					{
						type: 'block',
						blocks: [ 'core/paragraph' ],
						transform: () => {},
					},
					{
						type: 'block',
						blocks: [ 'core/paragraph' ],
						transform: () => {},
						isMultiBlock: true,
					},
				],
			},
		} );

		registerBlockType( 'core/paragraph', {
			category: 'text',
			title: 'Paragraph',
			edit: () => {},
			save: () => {},
			transforms: {
				to: [
					{
						type: 'block',
						blocks: [ 'core/heading' ],
						transform: () => {},
					},
				],
			},
		} );
	} );

	afterAll( () => {
		unregisterBlockType( 'core/heading' );
		unregisterBlockType( 'core/paragraph' );
	} );

	test( 'should render switcher with blocks', () => {
		useSelect.mockImplementation( () => ( {
			possibleBlockTransformations: [
				{ name: 'core/heading', frecency: 1 },
				{ name: 'core/paragraph', frecency: 1 },
			],
			canRemove: true,
		} ) );
		const wrapper = shallow(
			<BlockSwitcherDropdownMenu blocks={ [ headingBlock1 ] } />
		);
		expect( wrapper ).toMatchSnapshot();
	} );

	test( 'should render disabled block switcher with multi block of different types when no transforms', () => {
		useSelect.mockImplementation( () => ( {
			possibleBlockTransformations: [],
			icon: copy,
		} ) );
		const wrapper = shallow(
			<BlockSwitcherDropdownMenu
				blocks={ [ headingBlock1, textBlock ] }
			/>
		);
		expect( wrapper ).toMatchSnapshot();
	} );

	test( 'should render enabled block switcher with multi block when transforms exist', () => {
		useSelect.mockImplementation( () => ( {
			possibleBlockTransformations: [
				{ name: 'core/heading', frecency: 1 },
				{ name: 'core/paragraph', frecency: 1 },
			],
			canRemove: true,
		} ) );
		const wrapper = shallow(
			<BlockSwitcherDropdownMenu
				blocks={ [ headingBlock1, headingBlock2 ] }
			/>
		);
		expect( wrapper ).toMatchSnapshot();
	} );
} );
