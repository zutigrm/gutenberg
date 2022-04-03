/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import {
	Button,
	CheckboxControl,
	Flex,
	FlexItem,
	Icon,
	Modal,
} from '@wordpress/components';
import { edit as editIcon, dragHandle, trash } from '@wordpress/icons';
import { useInstanceId } from '@wordpress/compose';
import { useDispatch, useSelect } from '@wordpress/data';
import { isReusableBlock, getBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import useBlockDisplayInformation from '../use-block-display-information';
import { store as blockEditorStore } from '../../store';

export default function BlockLockModal( { clientId, onClose } ) {
	const [ lock, setLock ] = useState( { move: false, remove: false } );
	const { canEdit, canMove, canRemove, isReusable } = useSelect(
		( select ) => {
			const {
				canEditBlock,
				canMoveBlock,
				canRemoveBlock,
				getBlockName,
				getBlockRootClientId,
			} = select( blockEditorStore );
			const rootClientId = getBlockRootClientId( clientId );
			const blockName = getBlockName( clientId );

			return {
				canEdit: canEditBlock( clientId ),
				canMove: canMoveBlock( clientId, rootClientId ),
				canRemove: canRemoveBlock( clientId, rootClientId ),
				isReusable: isReusableBlock( getBlockType( blockName ) ),
			};
		},
		[ clientId ]
	);
	const { updateBlockAttributes } = useDispatch( blockEditorStore );
	const blockInformation = useBlockDisplayInformation( clientId );
	const instanceId = useInstanceId(
		BlockLockModal,
		'block-editor-block-lock-modal__options-title'
	);

	useEffect( () => {
		setLock( {
			move: ! canMove,
			remove: ! canRemove,
			...( isReusable ? { edit: ! canEdit } : {} ),
		} );
	}, [ canEdit, canMove, canRemove, isReusable ] );

	const isAllChecked = Object.values( lock ).every( Boolean );

	let ariaChecked;
	if ( isAllChecked ) {
		ariaChecked = 'true';
	} else if ( Object.values( lock ).some( Boolean ) ) {
		ariaChecked = 'mixed';
	} else {
		ariaChecked = 'false';
	}

	return (
		<Modal
			title={ sprintf(
				/* translators: %s: Name of the block. */
				__( 'Lock %s' ),
				blockInformation.title
			) }
			overlayClassName="block-editor-block-lock-modal"
			closeLabel={ __( 'Close' ) }
			onRequestClose={ onClose }
		>
			<form
				onSubmit={ ( event ) => {
					event.preventDefault();
					updateBlockAttributes( [ clientId ], { lock } );
					onClose();
				} }
			>
				<p>
					{ __(
						'Choose specific attributes to restrict or lock all available options.'
					) }
				</p>
				<div
					role="group"
					aria-labelledby={ instanceId }
					className="block-editor-block-lock-modal__options"
				>
					<CheckboxControl
						className="block-editor-block-lock-modal__options-title"
						label={
							<span id={ instanceId }>{ __( 'Lock all' ) }</span>
						}
						checked={ isAllChecked }
						aria-checked={ ariaChecked }
						onChange={ ( newValue ) =>
							setLock( {
								move: newValue,
								remove: newValue,
								...( isReusable ? { edit: newValue } : {} ),
							} )
						}
					/>
					<ul className="block-editor-block-lock-modal__checklist">
						{ isReusable && (
							<li className="block-editor-block-lock-modal__checklist-item">
								<CheckboxControl
									label={
										<>
											{ __( 'Restrict editing' ) }
											<Icon icon={ editIcon } />
										</>
									}
									checked={ !! lock.edit }
									onChange={ ( edit ) =>
										setLock( ( prevLock ) => ( {
											...prevLock,
											edit,
										} ) )
									}
								/>
							</li>
						) }
						<li className="block-editor-block-lock-modal__checklist-item">
							<CheckboxControl
								label={
									<>
										{ __( 'Disable movement' ) }
										<Icon icon={ dragHandle } />
									</>
								}
								checked={ lock.move }
								onChange={ ( move ) =>
									setLock( ( prevLock ) => ( {
										...prevLock,
										move,
									} ) )
								}
							/>
						</li>
						<li className="block-editor-block-lock-modal__checklist-item">
							<CheckboxControl
								label={
									<>
										{ __( 'Prevent removal' ) }
										<Icon icon={ trash } />
									</>
								}
								checked={ lock.remove }
								onChange={ ( remove ) =>
									setLock( ( prevLock ) => ( {
										...prevLock,
										remove,
									} ) )
								}
							/>
						</li>
					</ul>
				</div>
				<Flex
					className="block-editor-block-lock-modal__actions"
					justify="flex-end"
					expanded={ false }
				>
					<FlexItem>
						<Button variant="tertiary" onClick={ onClose }>
							{ __( 'Cancel' ) }
						</Button>
					</FlexItem>
					<FlexItem>
						<Button variant="primary" type="submit">
							{ __( 'Apply' ) }
						</Button>
					</FlexItem>
				</Flex>
			</form>
		</Modal>
	);
}
