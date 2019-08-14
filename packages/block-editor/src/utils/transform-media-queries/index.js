/**
 * External dependencies
 */
import { filter, get, map, includes, some } from 'lodash';

const HANDLED_PROPERTIES = [
	'min-width',
	'max-width',
	'min-height',
	'max-height',
	'orientation',
	'min-aspect-ratio',
	'man-aspect-ratio',
];

function canTransformRule( rule ) {
	// Make sure there are no multiple media rules.
	if ( ! rule.media || rule.media.length !== 1 ) {
		return false;
	}

	const mediaQueryCondition = rule.media[ 0 ];
	// Verify if it is a simple media query that maybe could be transformed into an element query.
	const mediaMatches = mediaQueryCondition.match( /^\(([^\(]*?):[^\(]*?\)$/ );
	if ( ! mediaMatches || ! mediaMatches[ 1 ] ) {
		return false;
	}
	const property = mediaMatches[ 1 ];

	return includes(
		HANDLED_PROPERTIES,
		property
	);
}

export default function transformMediaQueries( partialPaths ) {
	const { EQCSS } = window;
	if ( ! EQCSS ) {
		return;
	}
	const styleSheets = filter(
		get( window, [ 'document', 'styleSheets' ], [] ),
		( styleSheet ) => {
			return (
				styleSheet.href &&
				some(
					partialPaths,
					( partialPath ) => {
						return styleSheet.href.includes( partialPath );
					}
				)
			);
		}
	);

	const rulesToProcess = [];
	styleSheets.forEach(
		( styleSheet ) => {
			for ( let i = 0; i < styleSheet.rules.length; ) {
				const rule = styleSheet.rules[ i ];
				if ( canTransformRule( rule ) ) {
					const innerRulesText = map(
						rule.cssRules,
						( { cssText } ) => ( cssText )
					);
					rulesToProcess.push( [
						`@element .editor-styles-wrapper and ${ rule.media[ 0 ] } {`,
						...innerRulesText,
						`}`,
					].join( '\n' ) );
					styleSheet.removeRule( i );
				} else {
					++i;
				}
			}
		}
	);
	if ( ! rulesToProcess.length ) {
		return;
	}
	const elementQueriesCode = rulesToProcess.join( '\n' );
	console.log( elementQueriesCode );
	EQCSS.process( elementQueriesCode );
}
