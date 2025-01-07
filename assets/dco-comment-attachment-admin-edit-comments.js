( function ( $ ) {

	$( document ).ready( () => {

		if( ! $( '#the-comment-list' ).length ) {
			return;
		}

		$( '#the-comment-list' ).on(
			'click',
			'.dco-delete-attachment',
			function ( event ) {
				event.preventDefault();

				const $this = $( this );
				const nonce = $this.data( 'nonce' );
				const comment_id = $this.data( 'comment-id' );

				const $comment = $this.closest( '.comment' );
				const $attachment = $comment.find( '.dco-attachment' );
				const $row_actions = $comment.find('.row-actions');

				const confirm_text = $attachment.length > 1 ? dco_ca.delete_attachments_confirm_text : dco_ca.delete_attachment_confirm_text;
				const notice_text = $attachment.length > 1 ? dco_ca.detach_attachments_notice : dco_ca.detach_attachment_notice;

				/* eslint-disable no-undef, no-alert */
				if (
					dco_ca.is_delete_attachment &&
					! confirm( confirm_text )
				) {
					return;
				}
				/* eslint-enable no-undef, no-alert */

				const data = {
					action: 'delete_comment_attachment',
					c: comment_id,
					_ajax_nonce: nonce, // eslint-disable-line camelcase
				};

				// eslint-disable-next-line no-undef
				$.post( ajaxurl, data, ( response ) => {
					if ( response.success ) {

						$notice = $('<p/>')
							.addClass('detach-attachment-notice')
							.html(notice_text);
									
						$row_actions.before($notice);

						$attachment.fadeOut(400, () => $notice.fadeIn());
						
						$this.hide();
					}
				} );
			}
		);

		$( '#the-comment-list' ).on(
			'click',
			'.detach-attachment-notice a',
			function ( event ) {
				event.preventDefault();

				$this = $(this);
				$comment = $this.closest('.comment');

				$delete_attachment = $comment.find('.dco-delete-attachment');
				const nonce = $delete_attachment.data( 'nonce' );
				const comment_id = $delete_attachment.data( 'comment-id' );
				const attachment_ids = $delete_attachment.data('attachment-ids');

				const $attachment = $comment.find('.dco-attachment');
				const $notice = $this.parent();

				const data = {
					action: 'undo_delete_comment_attachment',
					c: comment_id,
					undo_attachment_ids: attachment_ids.toString().split(','),
					_ajax_nonce: nonce, // eslint-disable-line camelcase
				};

				// eslint-disable-next-line no-undef
				$.post( ajaxurl, data, ( response ) => {
					if ( response.success ) {

						$notice.fadeOut(400, () => {$attachment.fadeIn(); $notice.remove();});
						
						$delete_attachment.show();
					}
				} );
			}
		);

		// Only for DeleteCommentAttachmentBulkAdminAction::show_bulk_action_success_message().
		const $referer = $( '[name="_wp_http_referer"]' );
		const referer = $referer.val();
		const refererQueryStringIndex = referer.indexOf( '?' );
		
		if ( refererQueryStringIndex === -1 ) {
			return;
		}
		
		const refererUrl = referer.substr( 0, refererQueryStringIndex );
		let refererQueryString = referer.substr( refererQueryStringIndex );
		
		const params = new URLSearchParams( refererQueryString );
		params.delete( 'delete_comment_attachment_bulk' );
		
		refererQueryString = params.toString();
		$referer.val( refererUrl + ( refererQueryString.length ? '?' + refererQueryString : '' ) );
		
	} );

} )( jQuery ); // eslint-disable-line no-undef
