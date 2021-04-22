/**
 * Internal dependencies
 */
import useRegistry from '../registry-provider/use-registry';

/** @typedef {import('./types').WPDataStore} WPDataStore */

/**
 * Custom react hook for retrieving registered selectors.
 *
 * In general, this custom React hook follows the
 * [rules of hooks](https://reactjs.org/docs/hooks-rules.html).
 *
 * @param {string|WPDataStore} storeNameOrDefinition The name of the store or
 *                                                   its definition from which
 *                                                   to retrieve selectors.
 *
 * @example
 * When data is only used in an event callback, the data should not be retrieved
 * on render, so it may be useful to get the selector functions.
 *
 * **Don't use `useSelectForCallbacks` when calling the selectors in the render
 * function because your component won't re-render on a data change. Use
 * `useSelect` instead.**
 *
 * ```js
 * import { useSelectForCallbacks } from '@wordpress/data';
 *
 * function Paste( { children } ) {
 *   const { getSettings } = useSelectForCallbacks( 'my-shop' );
 *   function onPaste() {
 *     // Do something with the settings.
 *     const settings = getSettings();
 *   }
 *   return <div onPaste={ onPaste }>{ children }</div>;
 * }
 * ```
 *
 * @return {Function}  A custom react hook.
 */
export function useSelectForCallbacks( storeNameOrDefinition ) {
	return useRegistry().select( storeNameOrDefinition );
}
