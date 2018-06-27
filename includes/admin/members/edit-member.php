<?php
/**
 * Edit Member Page
 *
 * @package     Restrict Content Pro
 * @subpackage  Admin/Edit Member
 * @copyright   Copyright (c) 2017, Restrict Content Pro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

global $rcp_options;

$view = isset( $_GET['view'] ) ? sanitize_text_field( $_GET['view'] ) : 'overview';

if( isset( $_GET['edit_member'] ) ) {
	$member_id = absint( $_GET['edit_member'] );
} elseif( isset( $_GET['view_member'] ) ) {
	$member_id = absint( $_GET['view_member'] );
}
$member = new RCP_Member( $member_id );

$member_tabs           = rcp_member_tabs();
$current_status        = $member->get_status();
$subscription_level_id = $member->get_subscription_id();
$expiration_date       = $member->get_expiration_date( false );

// If member is pending, get pending details.
if ( 'pending' == $current_status ) {

	$pending_subscription_id = $member->get_pending_subscription_id();

	if ( ! empty( $pending_subscription_id ) ) {
		$subscription_level_id = $pending_subscription_id;
	}

	if ( empty( $expiration_date ) ) {
		$expiration_date = $member->calculate_expiration( true );
	}
}

$subscription = rcp_get_subscription_details( $subscription_level_id );
?>
<h1>
	<?php _e( 'Member Details', 'rcp' ); ?>
</h1>

<?php if( ! $member->exists() ) : ?>
	<div class="error settings-error">
		<p><?php _e( 'Error: Invalid member ID.', 'rcp' ); ?></p>
	</div>
	<?php return; ?>
<?php endif; ?>

<div id="rcp-item-wrapper" class="rcp-item-has-tabs">
	<div id="rcp-item-tab-wrapper" class="rcp-member-tab-wrapper-list">
		<ul id="rcp-item-tab-wrapper-list" class="member-tab-wrapper-list">
			<?php foreach ( $member_tabs as $key => $tab ) :
				$active = ( $key === $view ) ? true : false;
				$class  = $active ? 'active' : 'inactive';
				?>

				<li class="<?php echo sanitize_html_class( $class ); ?>">

					<?php if ( ! $active ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=rcp-members&edit_member=' . urlencode( $member_id ) . '&view=' . urlencode( $key ) ) ); ?>" aria-label="<?php echo esc_attr( $tab['title'] ); ?>">
					<?php endif; ?>

					<span class="rcp-item-tab-label-wrap"<?php echo $active ? ' aria-label="' . esc_attr( $tab['title'] ) . '"' : ''; ?>>
						<span class="dashicons <?php echo sanitize_html_class( $tab['dashicon'] ); ?>" aria-hidden="true"></span>
						<span class="rcp-item-tab-label"><?php echo esc_html( $tab['title'] ); ?></span>
					</span>

					<?php if ( ! $active ) : ?>
						</a>
					<?php endif; ?>

				</li>

			<?php endforeach; ?>
		</ul>
	</div>

	<div id="rcp-item-card-wrapper" class="rcp-member-card-wrapper">
		<?php
		switch ( $view ) {

			case 'notes' :
				/**
				 * Render member notes.
				 */
				?>
				<div id="rcp-item-notes-wrapper">

					<div class="rcp-item-notes-header">
						<?php echo get_avatar( $member->user_email, 30 ); ?> <span><?php echo $member->first_name . ' ' . $member->last_name; ?></span>
					</div>
					<?php
					if ( ! empty( $rcp_options['enable_terms'] ) ) {
						echo '<h3>' . __( 'Agreed to Terms', 'rcp' ) . '</h3>';
						$terms_agreed = get_user_meta( $member->ID, 'rcp_terms_agreed', true );
						if ( ! empty( $terms_agreed ) && is_array( $terms_agreed ) ) {
							foreach ( $terms_agreed as $terms_agreed_date ) {
								echo date_i18n( get_option( 'date_format' ) . ' H:i:s', $terms_agreed_date ) . '<br />';
							}
						} else {
							_e( 'None', 'rcp' );
						}
					}

					if ( ! empty( $rcp_options['enable_privacy_policy'] ) ) {
						echo '<h3>' . __( 'Agreed to Privacy Policy', 'rcp' ) . '</h3>';
						$privacy_policy_agreed = get_user_meta( $member->ID, 'rcp_privacy_policy_agreed', true );
						if ( ! empty( $privacy_policy_agreed ) && is_array( $privacy_policy_agreed ) ) {
							foreach ( $privacy_policy_agreed as $privacy_policy_agreed_date ) {
								echo date_i18n( get_option( 'date_format' ) . ' H:i:s', $privacy_policy_agreed_date ) . '<br />';
							}
						} else {
							_e( 'None', 'rcp' );
						}
					}
					?>

					<h3><?php _e( 'Notes', 'rcp' ); ?></h3>

					<form id="rcp-edit-member-notes" method="POST">
						<label for="rcp-member-notes" class="screen-reader-text"><?php _e( 'Member notes', 'rcp' ); ?></label>
						<textarea id="rcp-member-notes" class="rcp-member-note-input" name="notes" rows="10"><?php echo esc_textarea( $member->get_notes() ); ?></textarea>
						<input type="hidden" name="rcp-action" value="edit-member-notes"/>
						<input type="hidden" name="user" value="<?php echo esc_attr( $member->ID ); ?>"/>
						<?php wp_nonce_field( 'rcp_edit_member_notes_nonce', 'rcp_edit_member_notes_nonce' ); ?>
						<input type="submit" id="rcp-edit-member-notes-save" class="button-primary right" value="<?php esc_attr_e( 'Update Notes', 'rcp' ); ?>" />
					</form>

				</div>
				<?php
				break;

			default :
				/**
				 * Render member overview.
				 */
				?>
				<div class="rcp-info-wrapper rcp-member-section">

					<form id="rcp-edit-member-info" method="POST">

						<?php do_action( 'rcp_edit_member_before', $member->ID ); ?>

						<div class="rcp-item-info rcp-member-info">

							<div id="rcp-member-avatar" class="rcp-avatar-wrap left">
								<?php echo get_avatar( $member->user_email ); ?>
								<span class="rcp-info-item rcp-member-edit-link"><a href="#" id="rcp-edit-member"><?php _e( 'Edit Member', 'rcp' ); ?></a></span>
							</div>

							<div class="rcp-member-id right">
								#<?php echo $member->ID; ?>
							</div>

							<div class="rcp-member-main-wrapper left">

								<span class="rcp-member-first-name rcp-edit-item">
									<label for="rcp-member-first-name" class="screen-reader-text"><?php _e( 'First name', 'rcp' ); ?></label>
									<input type="text" id="rcp-member-first-name" name="first_name" value="<?php echo esc_attr( $member->first_name ); ?>" placeholder="<?php esc_attr_e( 'First name', 'rcp' ); ?>">
								</span>
								<span class="rcp-member-first-name rcp-info-item rcp-editable">
									<?php echo $member->first_name; ?>
								</span>

								<span class="rcp-member-last-name rcp-edit-item">
									<label for="rcp-member-last-name" class="screen-reader-text"><?php _e( 'Last name', 'rcp' ); ?></label>
									<input type="text" id="rcp-member-last-name" name="last_name" value="<?php echo esc_attr( $member->last_name ); ?>" placeholder="<?php esc_attr_e( 'Last name', 'rcp' ); ?>">
								</span>
								<span class="rcp-member-last-name rcp-info-item rcp-editable">
									<?php echo $member->last_name; ?>
								</span>

								<span class="rcp-member-login rcp-info-item">
									<?php echo $member->user_login; ?>
								</span>

								<span class="rcp-member-email rcp-edit-item">
									<label for="rcp-member-email" class="screen-reader-text"><?php _e( 'Email address', 'rcp' ); ?></label>
									<input type="text" id="rcp-member-email" name="email" value="<?php echo esc_attr( $member->user_email ); ?>" placeholder="<?php esc_attr_e( 'Email address', 'rcp' ); ?>">
								</span>
								<span class="rcp-member-email rcp-info-item rcp-editable">
									<?php echo $member->user_email; ?>
								</span>

								<span class="rcp-member-since rcp-info-item">
									<?php _e( 'Member since:', 'rcp' ); ?>
									<?php echo date_i18n( get_option( 'date_format' ), strtotime( $member->get_joined_date() ) ); ?>
								</span>

								<span class="rcp-member-user-id">
									<?php _e( 'User ID:', 'rcp' ); ?> <a href="<?php echo esc_url( admin_url( 'user-edit.php?user_id=' . urlencode( $member->ID ) ) ); ?>" title="<?php esc_attr_e( 'Edit user account', 'rcp' ); ?>"><?php echo $member->ID; ?></a>
									<?php if( $switch_to_url = rcp_get_switch_to_url( $member->ID ) ) { ?>
										 &ndash; <a href="<?php echo esc_url( $switch_to_url ); ?>" class="rcp_switch"><?php _e('Switch to User', 'rcp'); ?></a>
									<?php } ?>
								</span>

								<?php if ( $member->is_pending_verification() ) : ?>
									<span class="rcp-member-email-verification rcp-info-item">
										<?php printf( __( 'Pending Email Verification &ndash; <a href="%s">Click to manually verify email.</a>', 'rcp' ), esc_url( wp_nonce_url( add_query_arg( array(
											'rcp-action' => 'verify_email',
											'member_id'  => $member->ID
										), add_query_arg( 'edit_member', $member->ID, admin_url( 'admin.php?page=rcp-members' ) ) ), 'rcp-manually-verify-email-nonce' ) ) ); ?>
									</span>
								<?php endif; ?>

							</div>

						</div>

						<?php
						// @todo Is this the best place for this?
						if ( has_action( 'rcp_edit_member_after' ) ) {
							echo '<table id="rcp-member-additional-info" class="wp-list-table widefat striped">';
							do_action( 'rcp_edit_member_after', $member->ID );
							echo '</table>';
							submit_button( __( 'Update Member', 'rcp' ), 'secondary' ); // I put this here since it may not be clear how to save otherwise?
						}
						?>

						<span id="rcp-member-edit-actions" class="rcp-edit-item">
							<input type="hidden" name="rcp-action" value="edit-member"/>
							<input type="hidden" name="user" value="<?php echo esc_attr( $member->ID ); ?>"/>
							<?php wp_nonce_field( 'rcp_edit_member_nonce', 'rcp_edit_member_nonce' ); ?>
							<input type="submit" id="rcp-edit-member-save" class="button-secondary" value="<?php esc_attr_e( 'Update Member', 'rcp' ); ?>" />
							<a href="#" id="rcp-edit-member-cancel" class="delete"><?php _e( 'Cancel', 'rcp' ); ?></a>
						</span>

					</form>

				</div>

				<div id="rcp-item-stats-wrapper" class="rcp-member-stats-wrapper rcp-member-section">
					<span class="dashicons dashicons-chart-area"></span>
					<?php echo rcp_currency_filter( $member->get_lifetime_value() ); ?> <?php _e( 'Lifetime Value', 'rcp' ); ?>
				</div>

				<div id="rcp-item-tables-wrapper" class="rcp-member-tables-wrapper rcp-member-section">

					<h3><?php _e( 'Subscription', 'rcp' ); ?></h3>

					<table id="rcp-member-subscriptions" class="wp-list-table widefat striped">
						<thead>
							<tr>
								<th scope="col" class="column-primary"><?php _e( 'Level', 'rcp' ); ?></th>
								<th scope="col"><?php _e( 'Amount', 'rcp' ); ?></th>
								<th scope="col"><?php _e( 'Status', 'rcp' ); ?></th>
								<th scope="col"><?php _e( 'Actions', 'rcp' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if ( ! empty( $subscription_level_id ) ) : ?>
								<tr>
									<td class="column-primary" data-colname="<?php esc_attr_e( 'Level', 'rcp' ); ?>">
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=rcp-member-levels&edit_subscription=' . urlencode( $subscription->id ) ) ); ?>" title="<?php esc_attr_e( 'Edit subscription level', 'rcp' ); ?>"><?php echo esc_html( $subscription->name ); ?></a>
										<button type="button" class="toggle-row"><span class="screen-reader-text"><?php _e( 'Show more details', 'rcp' ); ?></span></button>
									</td>
									<td data-colname="<?php esc_attr_e( 'Amount', 'rcp' ); ?>">
										<?php
										// @todo Duration unit needs translation
										// @todo Need to consider discount code usage.
										printf( _x( '%s every %s', 'billing amount and cycle', 'rcp' ), rcp_currency_filter( $subscription->price ), $subscription->duration_unit );
										?>
									</td>
									<td data-colname="<?php esc_attr_e( 'Status', 'rcp' ); ?>">
										<?php rcp_print_status( $member_id ); ?>
									</td>
									<td data-colname="<?php esc_attr_e( 'Actions', 'rcp' ); ?>">
										<!-- @todo Include subscription ID? -->
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=rcp-members&edit_member=' . absint( $member->ID ) . '&edit_subscription=' ) ); ?>"><?php _e( 'View Details', 'rcp' ); ?></a>
									</td>
								</tr>
							<?php else : ?>
								<tr>
									<td colspan="4"><?php _e( 'No membership found.', 'rcp' ); ?></td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>

					<h3><?php _e( 'Recent Payments', 'rcp' ); ?></h3>

					<?php $payments = $member->get_payments(); ?>

					<table id="rcp-member-subscriptions" class="wp-list-table widefat striped">
						<thead>
							<tr>
								<th scope="col" class="column-primary"><?php _e( 'ID', 'rcp' ); ?></th>
								<th scope="col"><?php _e( 'Amount', 'rcp' ); ?></th>
								<th scope="col"><?php _e( 'Date', 'rcp' ); ?></th>
								<th scope="col"><?php _e( 'Status', 'rcp' ); ?></th>
								<th scope="col"><?php _e( 'Actions', 'rcp' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if ( ! empty( $payments ) ) : ?>
								<?php foreach ( $payments as $payment ) : ?>
									<tr>
										<td class="column-primary" data-colname="<?php esc_attr_e( 'ID', 'rcp' ); ?>">
											<?php echo $payment->id; ?>
											<button type="button" class="toggle-row"><span class="screen-reader-text"><?php _e( 'Show more details', 'rcp' ); ?></span></button>
										</td>
										<td data-colname="<?php esc_attr_e( 'Amount', 'rcp' ); ?>">
											<?php echo esc_html( rcp_currency_filter( $payment->amount ) ); ?>
										</td>
										<td data-colname="<?php esc_attr_e( 'Date', 'rcp' ); ?>" title="<?php echo esc_attr( $payment->date ); ?>">
											<?php echo date_i18n( get_option( 'date_format' ), strtotime( $payment->date ) ); ?>
										</td>
										<td data-colname="<?php esc_attr_e( 'Status', 'rcp' ); ?>">
											<?php echo rcp_get_payment_status_label( $payment ); ?>
										</td>
										<td data-colname="<?php esc_attr_e( 'Actions', 'rcp' ); ?>">
											<a href="<?php echo esc_url( admin_url( 'admin.php?page=rcp-payments&payment_id=' . urlencode( $payment->id ) . '6&view=edit-payment' ) ); ?>"><?php _e( 'View Details', 'rcp' ); ?></a>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php else : ?>
								<tr>
									<td colspan="5"><?php _e( 'No payments found.', 'rcp' ); ?></td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>

					<?php if ( 20 == count( $payments ) ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=rcp-payments&user_id=' . urlencode( $member_id ) ) ); ?>"><?php printf( __( 'View all payments for %s', 'rcp' ), $member->first_name . ' ' . $member->last_name ); ?></a>
					<?php endif; ?>

				</div>
				<?php

		}
		?>
	</div>
</div>