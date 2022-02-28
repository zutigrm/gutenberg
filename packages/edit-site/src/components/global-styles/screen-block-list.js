/**
 * WordPress dependencies
 */
import { store as blocksStore } from '@wordpress/blocks';
import { __, sprintf, _n } from '@wordpress/i18n';
import {
	FlexItem,
	SearchControl,
	__experimentalHStack as HStack,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useState, useMemo, useEffect, useRef } from '@wordpress/element';
import { BlockIcon } from '@wordpress/block-editor';
import { useDebounce } from '@wordpress/compose';
import { speak } from '@wordpress/a11y';

/**
 * Internal dependencies
 */
import { useHasBorderPanel } from './border-panel';
import { useHasColorPanel } from './color-utils';
import { useHasDimensionsPanel } from './dimensions-panel';
import { useHasTypographyPanel } from './typography-panel';
import ScreenHeader from './header';
import { NavigationButton } from './navigation-button';

function BlockMenuItem( { block } ) {
	const hasTypographyPanel = useHasTypographyPanel( block.name );
	const hasColorPanel = useHasColorPanel( block.name );
	const hasBorderPanel = useHasBorderPanel( block.name );
	const hasDimensionsPanel = useHasDimensionsPanel( block.name );
	const hasLayoutPanel = hasBorderPanel || hasDimensionsPanel;
	const hasBlockMenuItem =
		hasTypographyPanel || hasColorPanel || hasLayoutPanel;

	if ( ! hasBlockMenuItem ) {
		return null;
	}

	return (
		<NavigationButton path={ '/blocks/' + block.name }>
			<HStack justify="flex-start">
				<FlexItem>
					<BlockIcon icon={ block.icon } />
				</FlexItem>
				<FlexItem>{ block.title }</FlexItem>
			</HStack>
		</NavigationButton>
	);
}

function ScreenBlockList() {
	const [ filterValue, setFilterValue ] = useState( '' );
	const debouncedSpeak = useDebounce( speak, 500 );
	const { blockTypes, isMatchingSearchTerm } = useSelect( ( select ) => {
		const { getBlockTypes } = select( blocksStore );
		return {
			blockTypes: getBlockTypes(),
			isMatchingSearchTerm: select( blocksStore ).isMatchingSearchTerm,
		};
	}, [] );
	const filteredBlockTypes = useMemo( () => {
		if ( ! filterValue ) {
			return blockTypes;
		}
		return blockTypes.filter( ( blockType ) =>
			isMatchingSearchTerm( blockType, filterValue )
		);
	}, [ filterValue, blockTypes, isMatchingSearchTerm ] );

	const blockTypesListRef = useRef();

	// Announce search results on change
	useEffect( () => {
		if ( ! filterValue ) {
			return;
		}
		// We extract the results from the wrapper div's `ref` because
		// filtered items can contain items that will eventually not
		// render and there is no reliable way to detect when a child
		// will return `null`.
		const count = blockTypesListRef.current.childElementCount;
		const resultsFoundMessage = sprintf(
			/* translators: %d: number of results. */
			_n( '%d result found.', '%d results found.', count ),
			count
		);
		debouncedSpeak( resultsFoundMessage, count );
	}, [ filterValue, debouncedSpeak ] );

	return (
		<>
			<ScreenHeader
				title={ __( 'Blocks' ) }
				description={ __(
					'Customize the appearance of specific blocks and for the whole site.'
				) }
			/>
			<SearchControl
				className="edit-site-block-types-search"
				onChange={ setFilterValue }
				value={ filterValue }
				label={ __( 'Search for blocks' ) }
				placeholder={ __( 'Search' ) }
			/>
			<div ref={ blockTypesListRef }>
				{ filteredBlockTypes.map( ( block ) => (
					<BlockMenuItem
						block={ block }
						key={ 'menu-itemblock-' + block.name }
					/>
				) ) }
			</div>
		</>
	);
}

export default ScreenBlockList;
