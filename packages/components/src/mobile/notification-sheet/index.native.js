/**
 * External dependencies
 */
import { Platform, View, Text } from 'react-native';

/**
 * WordPress dependencies
 */
import { BottomSheet, Icon } from '@wordpress/components';
import { withPreferredColorScheme, compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import styles from './style.scss';

function NotificationSheet( { notificationOpened, title, getStylesFromColorScheme, closeNotification, type = 'singular' } ) {
	if ( ! notificationOpened ) {
		return null;
	}

	const infoTextStyle = getStylesFromColorScheme( styles.infoText, styles.infoTextDark );
	const infoTitleStyle = getStylesFromColorScheme( styles.infoTitle, styles.infoTitleDark );
	const infoDescriptionStyle = getStylesFromColorScheme( styles.infoDescription, styles.infoDescriptionDark );
	const infoSheetIconStyle = getStylesFromColorScheme( styles.infoSheetIcon, styles.infoSheetIconDark );

	// translators: %s: Name of the block
	const titleFormatSingular = Platform.OS === 'android' ? __( '\'%s\' isn\'t yet supported on WordPress for Android' ) :
		__( '\'%s\' isn\'t yet supported on WordPress for iOS' );

	const titleFormatPlural = Platform.OS === 'android' ? __( '\'%s\' aren\'t yet supported on WordPress for Android' ) :
		__( '\'%s\' aren\'t yet supported on WordPress for iOS' );

	const titleFormat = notificationOpened.type === 'plural' ? titleFormatPlural : titleFormatSingular;

	const infoTitle = sprintf(
		titleFormat,
		notificationOpened.title,
	);

	return (
		<BottomSheet
			isVisible={ ! notificationOpened.isNotificationDismissed }
			hideHeader
			onClose={ closeNotification }
		>
			<View style={ styles.infoContainer } >
				<Icon icon="editor-help" color={ infoSheetIconStyle.color } size={ styles.infoSheetIcon.size } />
				<Text style={ [ infoTextStyle, infoTitleStyle ] }>
					{ infoTitle }
				</Text>
				<Text style={ [ infoTextStyle, infoDescriptionStyle ] }>
					{ __( 'We are working hard to add more blocks with each release. In the meantime, you can also edit this post on the web.' ) }
				</Text>
			</View>
		</BottomSheet>
	);
}

export default compose( [
	withSelect( ( select ) => {
		const {
			isNotificationOpened,
		} = select( 'core/edit-post' );

		return {
			notificationOpened: isNotificationOpened(),
		};
	} ),
	withDispatch( ( dispatch ) => {
		const { closeNotification } = dispatch( 'core/edit-post' );

		return {
			closeNotification,
		};
	} ),
]
)( withPreferredColorScheme( NotificationSheet ) );
