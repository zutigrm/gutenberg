/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { RichTextToolbarButton } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import InlineColorUI from './inline';

const name = 'core/text-color';
const title = __( 'Text Color' );

export const textColor = {
	name,
	title,
	tagName: 'span',
	className: 'has-inline-color',
	edit: class TextColorEdit extends Component {
		constructor() {
			super( ...arguments );

			this.onClose = this.onClose.bind( this );

			this.state = {
				isOpen: false,
			};
		}

		onClose() {
			this.setState( { isOpen: false } );
		}

		render() {
			const {
				value,
				onChange,
				isActive,
				activeAttributes,
			} = this.props;

			return (
				<>
					<RichTextToolbarButton
						icon="editor-textcolor"
						title={ title }
						onClick={ () => this.setState( { isOpen: true } ) }
						isActive={ isActive }
					/>
					{ this.state.isOpen && (
						<InlineColorUI
							name={ name }
							addingLink={ this.state.isOpen }
							onClose={ this.onClose }
							isActive={ isActive }
							activeAttributes={ activeAttributes }
							value={ value }
							onChange={ onChange }
						/>
					) }
				</>
			);
		}
	},
};
