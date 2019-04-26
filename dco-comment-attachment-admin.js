(function ($) {
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
	});
})(jQuery);