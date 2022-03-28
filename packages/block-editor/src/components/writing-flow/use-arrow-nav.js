/**
 * WordPress dependencies
 */
import { UP, DOWN, LEFT, RIGHT } from '@wordpress/keycodes';
import { useSelect } from '@wordpress/data';
import { useRefEffect } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import { store as blockEditorStore } from '../../store';

/**
 * Returns true if the element should consider edge navigation upon a keyboard
 * event of the given directional key code, or false otherwise.
 *
 * @param {Element} element     HTML element to test.
 * @param {number}  keyCode     KeyboardEvent keyCode to test.
 * @param {boolean} hasModifier Whether a modifier is pressed.
 *
 * @return {boolean} Whether element should consider edge navigation.
 */
export function isNavigationCandidate( element, keyCode, hasModifier ) {
	const isVertical = keyCode === UP || keyCode === DOWN;

	// Currently, all elements support unmodified vertical navigation.
	if ( isVertical && ! hasModifier ) {
		return true;
	}

	// Native inputs should not navigate horizontally.
	const { tagName } = element;
	return tagName !== 'INPUT' && tagName !== 'TEXTAREA';
}

export default function useArrowNav() {
	const { hasMultiSelection } = useSelect( blockEditorStore );
	return useRefEffect( ( node ) => {
		function onKeyDown( event ) {
			const { keyCode, target } = event;
			const isUp = keyCode === UP;
			const isDown = keyCode === DOWN;
			const isLeft = keyCode === LEFT;
			const isRight = keyCode === RIGHT;
			const isHorizontal = isLeft || isRight;
			const isVertical = isUp || isDown;
			const isNav = isHorizontal || isVertical;
			const isShift = event.shiftKey;
			const hasModifier =
				isShift || event.ctrlKey || event.altKey || event.metaKey;

			if ( hasMultiSelection() ) {
				return;
			}

			// Abort if navigation has already been handled (e.g. RichText
			// inline boundaries).
			if ( event.defaultPrevented ) {
				return;
			}

			if ( ! isNav ) {
				return;
			}

			// Abort if our current target is not a candidate for navigation
			// (e.g. preserve native input behaviors).
			if ( ! isNavigationCandidate( target, keyCode, hasModifier ) ) {
				return;
			}

			node.contentEditable = true;
			// Firefox doesn't automatically move focus.
			node.focus();
		}

		node.addEventListener( 'keydown', onKeyDown );
		return () => {
			node.removeEventListener( 'keydown', onKeyDown );
		};
	}, [] );
}
