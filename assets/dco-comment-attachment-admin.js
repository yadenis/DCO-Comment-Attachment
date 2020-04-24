( function( $ ) {
	var attachmentNoticeNeedHide, $wrap;

	var showAttachmentNotice = function( url ) {
		$wrap.find( '.dco-attachment' ).addClass( 'dco-hidden' );

		let $notice = $wrap.find( '.dco-attachment-notice' );
		$notice.children( 'a' ).attr( 'href', url );
		$notice.removeClass( 'dco-hidden' );

		attachmentNoticeNeedHide = false;
	};

	var hideAttachmentNotice = function() {
		$wrap.find( '.dco-attachment' ).removeClass( 'dco-hidden' );
		$wrap.find( '.dco-attachment-notice' ).addClass( 'dco-hidden' );
	};

	$( document ).ready( function() {
		$( '#the-comment-list' ).on( 'click', '.dco-del-attachment', function( e ) {
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

		$( '#dco-comment-attachment' ).on('click', '.dco-set-attachment', function( e ) {
			e.preventDefault();
			
			$wrap = $( this ).closest('.dco-attachment-wrap');

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
				var $attachment;
				var $removeAttachment = $wrap.find( '.dco-remove-attachment' );

				// We set multiple to false so only get one image from the uploader.
				let selection = frame.state().get( 'selection' ).first().toJSON();

				if( $removeAttachment.hasClass( 'dco-hidden' ) ) {
					$wrap.trigger('dco_ca_before_adding');
				} else {
					$wrap.trigger('dco_ca_before_replacing');
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

						$attachment.children( 'img' )
								.attr({
									src: thumbnail.url,
									width: thumbnail.width,
									height: thumbnail.height
								})
								.removeAttr( 'srcset' )
								.removeAttr( 'sizes' );
						break;
					case 'video':
						$attachment = $wrap.find( '.dco-video-attachment' );
						if ( ! $attachment.length ) {
							showAttachmentNotice( selection.url );
							break;
						}

						$attachment.find( 'video' )[0].setSrc( selection.url );
						break;
					case 'audio':
						$attachment = $wrap.find( '.dco-audio-attachment' );
						if ( ! $attachment.length ) {
							showAttachmentNotice( selection.url );
							break;
						}

						$attachment.find( 'audio' )[0].setSrc( selection.url );
						break;
					default:
						$attachment = $wrap.find( '.dco-misc-attachment' );
						if ( ! $attachment.length ) {
							showAttachmentNotice( selection.url );
							break;
						}

						$attachment.children( 'a' )
								.attr( 'href', selection.url )
								.text( selection.title );
				}

				if ( attachmentNoticeNeedHide ) {
					hideAttachmentNotice();
				}
				$removeAttachment.removeClass( 'dco-hidden' );
				$wrap.find( '.dco-set-attachment' ).text( dcoCA.replace_attachment_label );
			});

			frame.open();
		});

		$( '#dco-comment-attachment' ).on('click', '.dco-remove-attachment', function( e ) {
			e.preventDefault();

			let $this = $( this );
			$wrap = $this.closest('.dco-attachment-wrap');

			$wrap.find( '.dco-attachment-id' ).val( 0 );
			$wrap.find( '.dco-attachment' ).addClass( 'dco-hidden' );
			$wrap.find( '.dco-attachment-notice' ).addClass( 'dco-hidden' );
			$this.addClass( 'dco-hidden' );

			$wrap.find( '.dco-set-attachment' ).text( dcoCA.add_attachment_label );
			
			$wrap.trigger('dco_ca_removed');
		});

		$( '#dco-file-types' ).on( 'click', '.dco-show-all', function( e ) {
			e.preventDefault();

			let $this = $( this );
			let $more = $this.prev();

			if ( $more.is( ':visible' ) ) {
				$more.removeClass( 'show' );
				$this.text( dcoCA.show_all );
			} else {
				$more.addClass( 'show' );
				$this.text( dcoCA.show_less );
			}
		});

		$( '#dco-file-types' ).on( 'click', '.dco-file-type-name', function() {
			let $this = $( this );
			let $type = $this.parent();
			let $checks = $type.find( 'input' );

			if ( $checks.not( ':checked' ).length ) {
				$checks.prop( 'checked', true );
			} else {
				$checks.prop( 'checked', false );
			}
		});
	});
}( jQuery ) );
