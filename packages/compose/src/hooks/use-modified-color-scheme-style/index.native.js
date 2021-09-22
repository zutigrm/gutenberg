import { useMemo } from '@wordpress/element';
import usePreferredColorScheme from '../use-preferred-color-scheme';
import useModifiedStyle from '../use-modified-style';

function useModifiedColorSchemeStyle( baseStyles, modifierStates = {} ) {
	const isDark = usePreferredColorScheme() === 'dark';
	const modifierStatesString = Object.values( modifierStates ).toString();
	const darkModifierStates = useMemo(
		() =>
			Object.keys( modifierStates ).reduce(
				( _modifierStates, modifier ) => ( {
					..._modifierStates,
					...{
						[ modifier ]: [ modifierStates[ modifier ] ].flat(),
						[ `${ modifier }-dark` ]: [
							...[ modifierStates[ modifier ] ].flat(),
							isDark,
						],
					},
				} ),
				{ dark: [ isDark ] }
			),
		[ modifierStatesString, isDark ]
	);

	return useModifiedStyle( baseStyles, darkModifierStates );
}

export default useModifiedColorSchemeStyle;
