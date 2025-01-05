( function ( $ ) {
	let attachmentNoticeNeedHide, $wrap;

	const showAttachmentNotice = function ( url ) {
		$wrap.find( '.dco-attachment' ).addClass( 'dco-hidden' );

		const $notice = $wrap.find( '.dco-attachment-notice' );
		$notice.children( 'a' ).attr( 'href', url );
		$notice.removeClass( 'dco-hidden' );

		attachmentNoticeNeedHide = false;
	};

	const hideAttachmentNotice = function () {
		$wrap.find( '.dco-attachment' ).removeClass( 'dco-hidden' );
		$wrap.find( '.dco-attachment-notice' ).addClass( 'dco-hidden' );
	};

	$( document ).ready( function () {
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
				$.post( ajaxurl, data, function ( response ) {
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
				$.post( ajaxurl, data, function ( response ) {
					if ( response.success ) {

						$notice.fadeOut(400, () => {$attachment.fadeIn(); $notice.remove();});
						
						$delete_attachment.show();
					}
				} );
			}
		);

		// Only for DCO_CA_Admin::show_bulk_action_message()
		if ( $( '#the-comment-list' ).length ) {
			const $referer = $( '[name="_wp_http_referer"]' );
			const referer = $referer.val();
			const refererQueryStringIndex = referer.indexOf( '?' );
			
			if ( refererQueryStringIndex === -1 ) {
				return;
			}
			
			const refererUrl = referer.substr( 0, refererQueryStringIndex );
			let refererQueryString = referer.substr( refererQueryStringIndex );
			
			const params = new URLSearchParams( refererQueryString );
			params.delete( 'deletedattachment' );
			
			refererQueryString = params.toString();
			$referer.val( refererUrl + ( refererQueryString.length ? '?' + refererQueryString : '' ) );
		}

		$( '#dco-comment-attachment' ).on(
			'click',
			'.dco-set-attachment',
			function ( event ) {
				event.preventDefault();

				$wrap = $( this ).closest( '.dco-attachment-wrap' );

				const frame = new wp.media.view.MediaFrame.Select( {
					title: dco_ca.set_attachment_title, // eslint-disable-line no-undef
					multiple: false,
					library: {
						uploadedTo: null,
					},
					button: {
						text: dco_ca.set_attachment_title, // eslint-disable-line no-undef
					},
				} );

				frame.on( 'select', function () {
					let $attachment;
					const $removeAttachment = $wrap.find(
						'.dco-remove-attachment'
					);

					// We set multiple to false so only get one image from the uploader.
					const selection = frame
						.state()
						.get( 'selection' )
						.first()
						.toJSON();

					if ( $removeAttachment.hasClass( 'dco-hidden' ) ) {
						$wrap.trigger( 'dco_ca_before_adding' );

						const $clone = $wrap.clone( true, true );
						$( '#dco-comment-attachment .inside' ).append( $clone );
					} else {
						$wrap.trigger( 'dco_ca_before_replacing' );
					}

					$wrap.find( '.dco-attachment-id' ).val( selection.id );

					attachmentNoticeNeedHide = true;

					switch ( selection.type ) {
						case 'image':
							let thumbnail;
							if ( selection.sizes.hasOwnProperty( 'medium' ) ) {
								thumbnail = selection.sizes.medium;
							} else {
								thumbnail = selection.sizes.full;
							}

							$attachment = $wrap.find( '.dco-image-attachment' );
							if ( ! $attachment.length ) {
								showAttachmentNotice( thumbnail.url );
								break;
							}

							$attachment
								.children( 'img' )
								.attr( {
									src: thumbnail.url,
									width: thumbnail.width,
									height: thumbnail.height,
								} )
								.removeAttr( 'srcset' )
								.removeAttr( 'sizes' );
							break;
						case 'video':
							$attachment = $wrap.find( '.dco-video-attachment' );
							if ( ! $attachment.length ) {
								showAttachmentNotice( selection.url );
								break;
							}

							$attachment
								.find( 'video' )[ 0 ]
								.setSrc( selection.url );
							break;
						case 'audio':
							$attachment = $wrap.find( '.dco-audio-attachment' );
							if ( ! $attachment.length ) {
								showAttachmentNotice( selection.url );
								break;
							}

							$attachment
								.find( 'audio' )[ 0 ]
								.setSrc( selection.url );
							break;
						default:
							$attachment = $wrap.find( '.dco-misc-attachment' );
							if ( ! $attachment.length ) {
								showAttachmentNotice( selection.url );
								break;
							}

							$attachment
								.children( 'a' )
								.attr( 'href', selection.url )
								.text( selection.title );
					}

					if ( attachmentNoticeNeedHide ) {
						hideAttachmentNotice();
					}
					$removeAttachment.removeClass( 'dco-hidden' );
					$wrap
						.find( '.dco-set-attachment' )
						.text( dco_ca.replace_attachment_label ); // eslint-disable-line no-undef
				} );

				frame.open();
			}
		);

		$( '#dco-comment-attachment' ).on(
			'click',
			'.dco-remove-attachment',
			function ( event ) {
				event.preventDefault();

				$wrap = $( this ).closest( '.dco-attachment-wrap' ).remove();
				$wrap.trigger( 'dco_ca_removed' );
			}
		);

		$( '#dco-file-types' ).on(
			'click',
			'.dco-show-all',
			function ( event ) {
				event.preventDefault();

				const $this = $( this );
				const $more = $this.prev();

				if ( $more.is( ':visible' ) ) {
					$more.removeClass( 'show' );
					$this.text( dco_ca.show_all ); // eslint-disable-line no-undef
				} else {
					$more.addClass( 'show' );
					$this.text( dco_ca.show_less ); // eslint-disable-line no-undef
				}
			}
		);

		$( '.dco-file-type' ).each( function () {
			const $this = $( this );
			const $checks = $this.find( '.dco-file-type-item-checkbox' );
			const $checkAll = $this.find( '.dco-file-type-name-checkbox' );

			if ( ! $checks.not( ':checked' ).length ) {
				$checkAll.prop( 'checked', true );
			}
		} );

		$( '#dco-file-types' ).on(
			'click',
			'.dco-file-type-name-checkbox',
			function () {
				const $this = $( this );
				const $checks = $this
					.closest( '.dco-file-type' )
					.find( '.dco-file-type-item-checkbox' );

				if ( $checks.not( ':checked' ).length ) {
					$checks.prop( 'checked', true );
				} else {
					$checks.prop( 'checked', false );
				}
			}
		);

		$( '#dco-file-types' ).on(
			'click',
			'.dco-file-type-item-checkbox',
			function () {
				const $this = $( this );
				const $type = $this.closest( '.dco-file-type' );
				const $checks = $type.find( '.dco-file-type-item-checkbox' );
				const $checkAll = $type.find( '.dco-file-type-name-checkbox' );

				if ( $checks.not( ':checked' ).length ) {
					$checkAll.prop( 'checked', false );
				} else {
					$checkAll.prop( 'checked', true );
				}
			}
		);
	} );
} )( jQuery ); // eslint-disable-line no-undef
