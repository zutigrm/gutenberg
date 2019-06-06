/**
 * External dependencies
 */
import { get } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { select } from '@wordpress/data';
import { BlockControls } from '@wordpress/editor';
import {
	applyFormat,
	removeFormat,
	getActiveFormat,
} from '@wordpress/rich-text';
import { Toolbar, IconButton, Popover, ColorPalette } from '@wordpress/components';

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

			this.state = {
				isOpen: false,
			};
		}

		render() {
			const {
				value,
				onChange,
			} = this.props;

			let activeColor;

			const colors = get( select( 'core/block-editor' ).getSettings(), [ 'colors' ], [] );
			const activeColorFormat = getActiveFormat( value, name );

			if ( activeColorFormat ) {
				const styleColor = activeColorFormat.attributes.style;
				if ( styleColor ) {
					activeColor = styleColor.replace( new RegExp( `^color:\\s*` ), '' );
				}
			}

			return (
				<>
					<BlockControls>
						<Toolbar>
							<IconButton
								className="components-button components-icon-button components-inline-color-icon-button components-toolbar__control"
								icon="editor-textcolor"
								aria-haspopup="true"
								tooltip={ title }
								onClick={ () => this.setState( { isOpen: ! this.state.isOpen } ) }
							>
								<span
									className="components-inline-color__indicator"
									style={ {
										backgroundColor: activeColor,
									} }
								/>
							</IconButton>

							{ this.state.isOpen && (
								<Popover
									position="bottom center"
									className="components-inline-color-popover"
									focusOnMount="container"
									onClickOutside={ ( onClickOutside ) => {
										if ( ( ! onClickOutside.target.classList.contains( 'components-dropdown-menu__toggle' ) && ! document.querySelector( '.components-dropdown-menu__toggle' ).contains( onClickOutside.target ) ) && ( ! document.querySelector( '.components-color-palette__picker' ) || ( document.querySelector( '.components-color-palette__picker' ) && ! document.querySelector( '.components-color-palette__picker' ).contains( onClickOutside.target ) ) ) ) {
											this.setState( { isOpen: ! this.state.isOpen } );
										}
									} }
								>
									<ColorPalette
										colors={ colors }
										value={ activeColor }
										onChange={ ( color ) => {
											if ( color ) {
												onChange(
													applyFormat( value, {
														type: name,
														attributes: {
															style: `color:${ color }`,
														},
													} )
												);
											} else {
												onChange( removeFormat( value, name ) );
											}
										} }
									>
									</ColorPalette>
								</Popover>
							) }

						</Toolbar>
					</BlockControls>
				</>
			);
		}
	},
};
