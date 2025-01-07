( function ( $ ) {

	const setAttachmentClass = '.dco-set-attachment';
	const detachAttachmentClass = '.dco-detach-attachment';
	const detachAttachmentHiddenClass = 'dco-detach-attachment--hidden';
	const editorClass = '.dco-comment-attachment-editor';
	const editorIdClass = '.dco-comment-attachment-editor-id';
	const markupClass = '.dco-comment-attachment-editor-markup';
	const markupHiddenClass = 'dco-comment-attachment-editor-markup--hidden';
	const noticeClass = '.dco-comment-attachment-editor-notice';
	const noticeHiddenClass = 'dco-comment-attachment-editor-notice--hidden';

	$( document ).ready( () => {

		const editorTemplate = $('#dco-comment-attachment-editor').prop('content');
		const $editorsContainer = $( '.dco-comment-attachment-editors' );

		if( ! $editorsContainer.length || ! editorTemplate ) {
			return;
		}

		renderAttachmentEditors(editorTemplate, $editorsContainer);

		$editorsContainer.on(
			'click',
			setAttachmentClass,
			function ( event ) {
				event.preventDefault();

				$editor = $( this ).closest( editorClass );

				const frame = getMediaFrameSelect();

				frame.on( 'select', function () {
					const $detachAttachment = $editor.find( detachAttachmentClass );

					if ( $detachAttachment.hasClass( detachAttachmentHiddenClass ) ) {
						renderEditorPlaceholder(editorTemplate, $editorsContainer);
					}

					const selection = frame.state().get( 'selection' ).first().toJSON();

					$editor.find( editorIdClass ).val( selection.id );

					switch ( selection.type ) {
						case 'image':
							renderImageMarkup($editor, selection);
							break;
						case 'video':
							renderVideoMarkup($editor, selection);
							break;
						case 'audio':
							renderAudioMarkup($editor, selection);
							break;
						default:
							renderMiscMarkup($editor, selection);
					}

					$detachAttachment.removeClass( detachAttachmentHiddenClass );
					$editor
						.find( setAttachmentClass )
						.text( dco_ca.replace_attachment_label ); // eslint-disable-line no-undef
				} );

				frame.open();
			}
		);

		$editorsContainer.on(
			'click',
			detachAttachmentClass,
			function ( event ) {
				event.preventDefault();

				$editor = $( this ).closest( editorClass ).remove();
			}
		);

	} );

	const showAttachmentNotice = ( $editor, url ) => {
		$editor.find( markupClass ).addClass( markupHiddenClass );

		const $notice = $editor.find( noticeClass );
		$notice.children( 'a' ).attr( 'href', url );
		$notice.removeClass( noticeHiddenClass );
	};

	const hideAttachmentNotice = () => {
		$editor.find( markupClass ).removeClass( markupHiddenClass );
		$editor.find( noticeClass ).addClass( noticeHiddenClass );
	};

	const renderAttachmentEditors = (template, $container) => {
		$( dco_ca.comment_attachments ).each( (i, attachment) => {

			const $editor = $(template).clone();

			$editor.find(markupClass).html(attachment.markup);
			$editor.find(editorIdClass).val(attachment.id);

			$container.append($editor);
		} );

		renderEditorPlaceholder(template, $container);
	}

	const getMediaFrameSelect = () => {
		return new wp.media.view.MediaFrame.Select( {
			title: dco_ca.set_attachment_title, // eslint-disable-line no-undef
			multiple: false,
			library: {
				uploadedTo: null,
			},
			button: {
				text: dco_ca.set_attachment_title, // eslint-disable-line no-undef
			},
		} );
	}

	const renderEditorPlaceholder = (template, $container) => {
		const $editor = $(template).clone();

		$editor.find(setAttachmentClass).text(dco_ca.add_attachment_label);
		$editor.find(detachAttachmentClass).addClass(detachAttachmentHiddenClass);

		$container.append($editor);
	}

	const renderImageMarkup = ($editor, selection) => {
		let thumbnail = selection.sizes.full;
		if ( selection.sizes.hasOwnProperty( 'medium' ) ) {
			thumbnail = selection.sizes.medium;
		}

		const $attachment = $editor.find( '.dco-image-attachment' );
		if ( ! $attachment.length ) {
			showAttachmentNotice( $editor, selection.sizes.full.url );
			return;
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

		hideAttachmentNotice();
	}

	const renderVideoMarkup = ($editor, selection) => {
		const $attachment = $editor.find( '.dco-video-attachment' );
		if ( ! $attachment.length ) {
			showAttachmentNotice( $editor, selection.url );
			return;
		}

		$attachment
			.find( 'video' )[ 0 ]
			.setSrc( selection.url );

		hideAttachmentNotice();
	}

	const renderAudioMarkup = ($editor, selection) => {
		const $attachment = $editor.find( '.dco-audio-attachment' );
		if ( ! $attachment.length ) {
			showAttachmentNotice( $editor, selection.url );
			return;
		}

		$attachment
			.find( 'audio' )[ 0 ]
			.setSrc( selection.url );

		hideAttachmentNotice();
	}

	const renderMiscMarkup = ($editor, selection) => {
		const $attachment = $editor.find( '.dco-misc-attachment' );
		if ( ! $attachment.length ) {
			showAttachmentNotice( $editor, selection.url );
			return;
		}

		$attachment
			.children( 'a' )
			.attr( 'href', selection.url )
			.text( selection.title );

		hideAttachmentNotice();
	}

} )( jQuery ); // eslint-disable-line no-undef
