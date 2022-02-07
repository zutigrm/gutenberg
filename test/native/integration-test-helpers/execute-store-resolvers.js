/**
 * External dependencies
 */
import { act } from '@testing-library/react-native';

/**
 * Executes a function that triggers store resolvers.
 *
 * Asynchronous store resolvers leverage `setTimeout` to run at the end of
 * the current JavaScript block execution. In order to prevent "act" warnings
 * triggered by updates to the React tree, we manually tick fake timers and
 * await the resolution of the current block execution before proceeding.
 *
 * @param {Function} fn Function that triggers store resolvers.
 * @return {*} The result of the function call.
 */
export async function executeStoreResolvers( fn ) {
	// Portions of the React Native Animation API rely upon these APIs. However,
	// Jest's 'legacy' fake timers mutate these globals, which breaks the Animated
	// API. We preserve the original implementations to restore them later.
	const originalRAF = global.requestAnimationFrame;
	const originalCAF = global.cancelAnimationFrame;

	jest.useFakeTimers( 'legacy' );

	const result = fn();

	// Advance all timers allowing store resolvers to resolve.
	act( () => jest.runAllTimers() );

	// The store resolvers might perform API fetch requests. The most
	// straightforward approach to ensure all of them resolve before we consider
	// the function finished is to flush micro tasks, similar to the approach found
	// in `@testing-library/react-native`.
	// https://github.com/callstack/react-native-testing-library/blob/a010ffdbca906615279ecc3abee423525e528101/src/flushMicroTasks.js#L15-L23
	await act( async () => {} );

	// Restore the default timer APIs for remainder of test arrangement, act, and
	// assertion.
	jest.useRealTimers();

	// Restore the global animation frame APIs to their original state for the
	// React Native Animated API.
	global.requestAnimationFrame = originalRAF;
	global.cancelAnimationFrame = originalCAF;

	return result;
}
