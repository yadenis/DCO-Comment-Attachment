( function( $ ) {
	var attachmentNoticeNeedHide;

	var showAttachmentNotice = function( url ) {
		$( '.dco-attachment' ).addClass( 'dco-hidden' );
		$( '.dco-attachment-notice a' ).attr( 'href', url );
		$( '.dco-attachment-notice' ).removeClass( 'dco-hidden' );

		attachmentNoticeNeedHide = false;
	};

	var hideAttachmentNotice = function() {
		$( '.dco-attachment' ).removeClass( 'dco-hidden' );
		$( '.dco-attachment-notice' ).addClass( 'dco-hidden' );
	};

	$( document ).ready( function() {
		$( document ).on( 'click', '.dco-del-attachment', function( e ) {
			e.preventDefault();

			if ( 1 == dcoCA.delete_attachment_action && ! confirm( dcoCA.delete_attachment_confirm ) ) {
				return;
			}

			let $this = $( this );
			let nonce = $this.data( 'nonce' );
			let id = $this.data( 'id' );

			let data = {
				action: 'delete_attachment',
				id: id,
				_ajax_nonce: nonce // eslint-disable-line camelcase
			};

			$.post( ajaxurl, data, function( response ) {
				if ( response.success ) {
					let $comment = $this.closest( '.comment' );
					let $attachment = $comment.children( '.dco-attachment' );
					$attachment.remove();
					$this.remove();
				}
			});
		});

		$( '#dco-set-attachment' ).on( 'click', function( e ) {
			e.preventDefault();

			let frame = new wp.media.view.MediaFrame.Select({
				title: dcoCA.set_attachment_title,
				multiple: false,
				library: {
					uploadedTo: null
				},

				button: {
					text: dcoCA.set_attachment_title
				}
			});

			frame.on( 'select', function() {

				// We set multiple to false so only get one image from the uploader.
				let selection = frame.state().get( 'selection' ).first().toJSON();

				$( '#dco-attachment-id' ).val( selection.id );

				attachmentNoticeNeedHide = true;

				switch ( selection.type ) {
					case 'image':
						let thumbnail;
						if ( selection.sizes.hasOwnProperty( 'medium' ) ) {
							thumbnail = selection.sizes.medium;
						} else {
							thumbnail = selection.sizes.full;
						}

						if ( ! $( '.dco-image-attachment' ).length ) {
							showAttachmentNotice( thumbnail.url );
							break;
						}

						$( '.dco-image-attachment img' )
								.attr({
									src: thumbnail.url,
									width: thumbnail.width,
									height: thumbnail.height
								})
								.removeAttr( 'srcset' )
								.removeAttr( 'sizes' );
						break;
					case 'video':
						if ( ! $( '.dco-video-attachment' ).length ) {
							showAttachmentNotice( selection.url );
							break;
						}

						$( '.dco-video-attachment video' )[0].setSrc( selection.url );
						break;
					case 'audio':
						if ( ! $( '.dco-audio-attachment' ).length ) {
							showAttachmentNotice( selection.url );
							break;
						}

						$( '.dco-audio-attachment audio' )[0].setSrc( selection.url );
						break;
					default:
						if ( ! $( '.dco-misc-attachment' ).length ) {
							showAttachmentNotice( selection.url );
							break;
						}

						$( '.dco-misc-attachment a' )
								.attr( 'href', selection.url )
								.text( selection.title );
				}

				if ( attachmentNoticeNeedHide ) {
					hideAttachmentNotice();
				}
				$( '#dco-remove-attachment' ).removeClass( 'dco-hidden' );
				$( '.dco-set-attachment' ).text( dcoCA.replace_attachment_label );
			});

			frame.open();
		});

		$( '#dco-remove-attachment' ).click( function( e ) {
			e.preventDefault();

			$( '#dco-attachment-id' ).val( 0 );
			$( '.dco-attachment' ).addClass( 'dco-hidden' );
			$( '.dco-attachment-notice' ).addClass( 'dco-hidden' );
			$( '#dco-remove-attachment' ).addClass( 'dco-hidden' );

			$( '.dco-set-attachment' ).text( dcoCA.add_attachment_label );
		});
	});
}( jQuery ) );
