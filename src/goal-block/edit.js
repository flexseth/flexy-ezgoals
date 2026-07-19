/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	TextControl,
	Button,
	DateTimePicker,
	Dropdown,
	Notice,
	Spinner,
} from '@wordpress/components';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { registerPlugin } from '@wordpress/plugins';
import { useState, useEffect } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';

/**
 * Goal Manager Component
 *
 * Manages goals via REST API and displays them in the editor sidebar.
 */
function GoalManagerPanel() {
	const [ goals, setGoals ] = useState( [] );
	const [ loading, setLoading ] = useState( true );
	const [ saving, setSaving ] = useState( false );
	const [ error, setError ] = useState( null );

	// New goal form state
	const [ newGoalType, setNewGoalType ] = useState( 'near-term' );
	const [ newGoalLabel, setNewGoalLabel ] = useState( '' );
	const [ newGoalDeadline, setNewGoalDeadline ] = useState( null );

	const postId = useSelect( ( select ) => {
		return select( 'core/editor' ).getCurrentPostId();
	}, [] );

	// Debug logging
	console.log( '[EZ Goals] Component mounted, postId:', postId );

	// Fetch goals on component mount
	useEffect( () => {
		console.log( '[EZ Goals] useEffect triggered, postId:', postId );

		if ( ! postId ) {
			console.log( '[EZ Goals] No postId, skipping fetch' );
			return;
		}

		setLoading( true );
		console.log( '[EZ Goals] Fetching goals from:', `/flexy-ezgoals/v1/goals/${ postId }` );

		apiFetch( {
			path: `/flexy-ezgoals/v1/goals/${ postId }`,
			method: 'GET',
		} )
			.then( ( response ) => {
				console.log( '[EZ Goals] Fetch success:', response );
				setGoals( response.goals || [] );
				setLoading( false );
			} )
			.catch( ( err ) => {
				console.error( '[EZ Goals] Error fetching goals:', err );
				setError( err.message );
				setLoading( false );
			} );
	}, [ postId ] );

	// Save goals to REST API
	const saveGoals = ( updatedGoals ) => {
		if ( ! postId ) {
			return;
		}

		setSaving( true );
		apiFetch( {
			path: `/flexy-ezgoals/v1/goals/${ postId }`,
			method: 'POST',
			data: { goals: updatedGoals },
		} )
			.then( ( response ) => {
				setGoals( response.goals || [] );
				setSaving( false );
				setError( null );
			} )
			.catch( ( err ) => {
				console.error( 'Error saving goals:', err );
				setError( err.message );
				setSaving( false );
			} );
	};

	// Add new goal
	const handleAddGoal = () => {
		if ( ! newGoalLabel || ! newGoalDeadline ) {
			setError( __( 'Please fill in all fields', 'flexy-ezgoals' ) );
			return;
		}

		const newGoal = {
			type: newGoalType,
			label: newGoalLabel,
			deadline: newGoalDeadline,
			status: 'active',
		};

		const updatedGoals = [ ...goals, newGoal ];
		saveGoals( updatedGoals );

		// Reset form
		setNewGoalLabel( '' );
		setNewGoalDeadline( null );
		setNewGoalType( 'near-term' );
	};

	// Delete goal
	const handleDeleteGoal = ( index ) => {
		const updatedGoals = goals.filter( ( _, i ) => i !== index );
		saveGoals( updatedGoals );
	};

	// Mark goal as complete
	const handleCompleteGoal = ( index ) => {
		const updatedGoals = goals.map( ( goal, i ) => {
			if ( i === index ) {
				return { ...goal, status: 'completed' };
			}
			return goal;
		} );
		saveGoals( updatedGoals );
	};

	// Reactivate goal
	const handleReactivateGoal = ( index ) => {
		const updatedGoals = goals.map( ( goal, i ) => {
			if ( i === index ) {
				return { ...goal, status: 'active' };
			}
			return goal;
		} );
		saveGoals( updatedGoals );
	};

	const goalTypeOptions = [
		{ label: __( 'Near Term Goal', 'flexy-ezgoals' ), value: 'near-term' },
		{ label: __( 'Long Term Goal', 'flexy-ezgoals' ), value: 'long-term' },
		{ label: __( 'Stretch Goal', 'flexy-ezgoals' ), value: 'stretch' },
		{ label: __( 'Daily Goal', 'flexy-ezgoals' ), value: 'daily' },
	];

	return (
		<PluginDocumentSettingPanel
			name="ezgoals-panel"
			title={ __( 'Goals', 'flexy-ezgoals' ) }
			className="ezgoals-panel"
		>
			{ loading && <Spinner /> }

			{ error && (
				<Notice status="error" isDismissible={ false }>
					{ error }
				</Notice>
			) }

			{ ! loading && (
				<>
					<div className="ezgoals-add-form">
						<SelectControl
							label={ __( 'Goal Type', 'flexy-ezgoals' ) }
							value={ newGoalType }
							options={ goalTypeOptions }
							onChange={ setNewGoalType }
							__next40pxDefaultSize
						/>

						<TextControl
							label={ __( 'Goal Label', 'flexy-ezgoals' ) }
							value={ newGoalLabel }
							onChange={ setNewGoalLabel }
							placeholder={ __(
								'e.g., Publish 5 posts',
								'flexy-ezgoals'
							) }
						/>

						<SelectControl
							label={ __( 'Quick Deadline', 'flexy-ezgoals' ) }
							help={ __( 'Or pick a custom date below', 'flexy-ezgoals' ) }
							options={ [
								{ label: __( 'Select preset...', 'flexy-ezgoals' ), value: '' },
								{ label: __( 'Today', 'flexy-ezgoals' ), value: 'today' },
								{ label: __( 'Tomorrow', 'flexy-ezgoals' ), value: 'tomorrow' },
								{ label: __( 'Next Week', 'flexy-ezgoals' ), value: 'next-week' },
								{ label: __( 'Next Month', 'flexy-ezgoals' ), value: 'next-month' },
							] }
							onChange={ ( value ) => {
								const now = new Date();
								let newDate = null;

								switch ( value ) {
									case 'today':
										newDate = new Date();
										break;
									case 'tomorrow':
										newDate = new Date( now.getTime() + 24 * 60 * 60 * 1000 );
										break;
									case 'next-week':
										newDate = new Date( now.getTime() + 7 * 24 * 60 * 60 * 1000 );
										break;
									case 'next-month':
										newDate = new Date( now.getTime() + 30 * 24 * 60 * 60 * 1000 );
										break;
								}

								if ( newDate ) {
									setNewGoalDeadline( newDate.toISOString() );
								}
							} }
							__next40pxDefaultSize
						/>

						<div className="ezgoals-deadline-picker">
							<label className="components-base-control__label">
								{ __( 'Custom Deadline', 'flexy-ezgoals' ) }
							</label>
							<Dropdown
								renderToggle={ ( { isOpen, onToggle } ) => (
									<Button
										variant="secondary"
										onClick={ onToggle }
										aria-expanded={ isOpen }
									>
										{ newGoalDeadline
											? new Date(
													newGoalDeadline
											  ).toLocaleString()
											: __(
													'Select Date & Time',
													'flexy-ezgoals'
											  ) }
									</Button>
								) }
								renderContent={ () => (
									<DateTimePicker
										currentDate={ newGoalDeadline }
										onChange={ setNewGoalDeadline }
										is12Hour={ true }
									/>
								) }
							/>
						</div>

						<Button
							variant="primary"
							onClick={ handleAddGoal }
							disabled={ saving }
						>
							{ saving
								? __( 'Adding...', 'flexy-ezgoals' )
								: __( 'Add Goal', 'flexy-ezgoals' ) }
						</Button>
					</div>

					<div className="ezgoals-list">
						<h3>{ __( 'Active Goals', 'flexy-ezgoals' ) }</h3>
						{ goals.filter( ( g ) => g.status !== 'completed' )
							.length === 0 && (
							<p className="ezgoals-empty">
								{ __(
									'No active goals. Add one above!',
									'flexy-ezgoals'
								) }
							</p>
						) }

						{ goals.map( ( goal, index ) => {
							if ( goal.status === 'completed' ) {
								return null;
							}

							return (
								<div
									key={ index }
									className="ezgoals-list-item"
								>
									<div className="ezgoals-list-item-header">
										<strong>{ goal.label }</strong>
									</div>
									<div className="ezgoals-list-item-meta">
										<span className="ezgoals-type">
											{
												goalTypeOptions.find(
													( opt ) =>
														opt.value === goal.type
												)?.label
											}
										</span>
										<span className="ezgoals-deadline-display">
											{ new Date(
												goal.deadline
											).toLocaleString() }
										</span>
									</div>
									<div className="ezgoals-list-item-actions">
										<Button
											variant="secondary"
											isSmall
											onClick={ () =>
												handleCompleteGoal( index )
											}
										>
											{ __(
												'Mark Complete',
												'flexy-ezgoals'
											) }
										</Button>
										<Button
											variant="tertiary"
											isSmall
											isDestructive
											onClick={ () =>
												handleDeleteGoal( index )
											}
										>
											{ __( 'Delete', 'flexy-ezgoals' ) }
										</Button>
									</div>
								</div>
							);
						} ) }

						{ goals.filter( ( g ) => g.status === 'completed' )
							.length > 0 && (
							<>
								<h3>
									{ __(
										'Completed Goals',
										'flexy-ezgoals'
									) }
								</h3>
								{ goals.map( ( goal, index ) => {
									if ( goal.status !== 'completed' ) {
										return null;
									}

									return (
										<div
											key={ index }
											className="ezgoals-list-item ezgoals-completed"
										>
											<div className="ezgoals-list-item-header">
												<del>{ goal.label }</del>
											</div>
											<div className="ezgoals-list-item-actions">
												<Button
													variant="tertiary"
													isSmall
													onClick={ () =>
														handleReactivateGoal(
															index
														)
													}
												>
													{ __(
														'Reactivate',
														'flexy-ezgoals'
													) }
												</Button>
												<Button
													variant="tertiary"
													isSmall
													isDestructive
													onClick={ () =>
														handleDeleteGoal(
															index
														)
													}
												>
													{ __(
														'Delete',
														'flexy-ezgoals'
													) }
												</Button>
											</div>
										</div>
									);
								} ) }
							</>
						) }
					</div>
				</>
			) }
		</PluginDocumentSettingPanel>
	);
}

// Register the plugin sidebar panel
registerPlugin( 'ezgoals-sidebar', {
	render: GoalManagerPanel,
	icon: 'flag',
} );

/**
 * Helper function to determine urgency class based on deadline.
 */
function getUrgencyClass( deadline ) {
	if ( ! deadline ) {
		return 'plenty-time';
	}

	const deadlineTime = new Date( deadline ).getTime();
	const now = new Date().getTime();
	const diff = deadlineTime - now;
	const daysUntil = Math.floor( diff / ( 24 * 60 * 60 * 1000 ) );

	if ( daysUntil < 0 || daysUntil < 1 ) {
		return 'due-soon';
	} else if ( daysUntil < 3 ) {
		return 'approaching';
	} else if ( daysUntil < 7 ) {
		return 'moderate';
	} else {
		return 'plenty-time';
	}
}

/**
 * Edit function for the block.
 * Shows a preview of active goals in the editor.
 */
export default function Edit() {
	const blockProps = useBlockProps();
	const [ goals, setGoals ] = useState( [] );

	const postId = useSelect( ( select ) => {
		return select( 'core/editor' ).getCurrentPostId();
	}, [] );

	// Fetch goals for preview
	useEffect( () => {
		if ( ! postId ) {
			return;
		}

		apiFetch( {
			path: `/flexy-ezgoals/v1/goals/${ postId }`,
			method: 'GET',
		} )
			.then( ( response ) => {
				setGoals( response.goals || [] );
			} )
			.catch( ( err ) => {
				console.error( 'Error fetching goals for preview:', err );
			} );
	}, [ postId ] );

	const activeGoals = goals.filter( ( g ) => g.status !== 'completed' );

	return (
		<div { ...blockProps }>
			<div className="ezgoals-block-placeholder">
				<p>
					{ __(
						'🎯 Manage goals in the sidebar panel (Post tab) →',
						'flexy-ezgoals'
					) }
				</p>
			</div>

			{ activeGoals.length > 0 && (
				<div className="ezgoals-editor-preview">
					<p className="ezgoals-preview-label">
						<strong>
							{ __( 'Preview (how goals will appear):', 'flexy-ezgoals' ) }
						</strong>
					</p>
					<div className="ezgoals-callout-container">
						{ activeGoals.map( ( goal, index ) => {
							const urgencyClass = getUrgencyClass( goal.deadline );
							const deadlineFormatted = goal.deadline
								? new Date( goal.deadline ).toLocaleString()
								: '';

							return (
								<div
									key={ index }
									className={ `ezgoals-callout ezgoals-callout-${ urgencyClass }` }
								>
									<div className="ezgoals-callout-header">
										<span className="ezgoals-type">
											{ goal.type }
										</span>
										<span className="ezgoals-urgency">
											{ urgencyClass.replace( '-', ' ' ) }
										</span>
									</div>
									<div className="ezgoals-callout-body">
										<p className="ezgoals-label">
											{ goal.label }
										</p>
										<p className="ezgoals-deadline">
											<strong>
												{ __( 'Deadline:', 'flexy-ezgoals' ) }
											</strong>{ ' ' }
											{ deadlineFormatted }
										</p>
									</div>
								</div>
							);
						} ) }
					</div>
				</div>
			) }
		</div>
	);
}
