(function ($) {
	var attachment_notice_need_hide;

	var show_attachment_notice = function (url) {
		$('.dco-attachment').addClass('dco-hidden');
		$('.dco-attachment-notice a').attr('href', url);
		$('.dco-attachment-notice').removeClass('dco-hidden');

		attachment_notice_need_hide = false;
	}

	var hide_attachment_notice = function () {
		$('.dco-attachment').removeClass('dco-hidden');
		$('.dco-attachment-notice').addClass('dco-hidden');
	}

	$(document).ready(function () {
		$(document).on('click', '.dco-del-attachment', function (e) {
			e.preventDefault();

			var $this = $(this);
			var nonce = $this.data('nonce');
			var id = $this.data('id');

			var data = {
				action: 'delete_attachment',
				id: id,
				_ajax_nonce: nonce
			};

			$.post(ajaxurl, data, function (response) {
				if(response.success) {
					var $comment = $this.closest('.comment');
					var $attachment = $comment.children('.dco-attachment');
					$attachment.remove();
				}
			});
		});

		$('#dco-add-attachment').on('click', function (e) {
			e.preventDefault();

			var frame = new wp.media.view.MediaFrame.Select({
				title: dco_ca.set_attachment_title,
				multiple: false,
				library: {
					uploadedTo: null
				},

				button: {
					text: dco_ca.set_attachment_title
				}
			});

			frame.on('select', function () {
				// We set multiple to false so only get one image from the uploader
				var selection = frame.state().get('selection').first().toJSON();

				$('#dco-attachment-id').val(selection.id);

				attachment_notice_need_hide = true;

				switch (selection.type) {
					case 'image':
						if (selection.sizes.hasOwnProperty('medium')) {
							var thumbnail = selection.sizes.medium;
						} else {
							var thumbnail = selection.sizes.full;
						}

						if (!$('.dco-image-attachment').length) {
							show_attachment_notice(thumbnail.url);
							break;
						}

						$('.dco-image-attachment img')
								.attr({
									src: thumbnail.url,
									width: thumbnail.width,
									height: thumbnail.height
								})
								.removeAttr('srcset')
								.removeAttr('sizes');
						break;
					case 'video':
						if (!$('.dco-video-attachment').length) {
							show_attachment_notice(selection.url);
							break;
						}

						$('.dco-video-attachment video')[0].setSrc(selection.url);
						break;
					case 'audio':
						if (!$('.dco-audio-attachment').length) {
							show_attachment_notice(selection.url);
							break;
						}

						$('.dco-audio-attachment audio')[0].setSrc(selection.url);
						break;
					default:
						if (!$('.dco-misc-attachment').length) {
							show_attachment_notice(selection.url);
							break;
						}

						$('.dco-misc-attachment a')
								.attr('href', selection.url)
								.text(selection.title);
				}

				if (attachment_notice_need_hide) {
					hide_attachment_notice();
				}
				$('#dco-remove-attachment').removeClass('dco-hidden');
			});

			frame.open();
		});

		$('#dco-remove-attachment').click(function (e) {
			e.preventDefault();

			$('#dco-attachment-id').val(0);
			$('.dco-attachment-notice').addClass('dco-hidden');
			$('#dco-remove-attachment').addClass('dco-hidden');
		});
	});
})(jQuery);