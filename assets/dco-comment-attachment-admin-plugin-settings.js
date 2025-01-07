( function ( $ ) {

	const groupClass = '.dco-file-types-group';
	const groupCheckboxClass = '.dco-file-types-group-checkbox';
	const extensionCheckboxClass = '.dco-file-type-extension-checkbox';

	$( document ).ready( () => {

		if( ! $( '.dco-file-types' ).length ) {
			return;
		}

		$( groupClass ).on(
			'click',
			'.dco-file-types-group-show-all-extensions',
			function ( event ) {
				event.preventDefault();

				const $this = $( this );
				const $group = $this.closest(groupClass);

				if ( $group.hasClass('show-less') ) {
					$group.removeClass('show-less')
					$this.text( dco_ca.show_less_label ); // eslint-disable-line no-undef

				} else {
					$group.addClass('show-less')
					$this.text( dco_ca.show_all_label ); // eslint-disable-line no-undef
				}
			}
		);

		$( groupClass ).on(
			'click',
			groupCheckboxClass,
			function () {
				const $this = $( this );
				const $checks = $this
					.closest( groupClass )
					.find( extensionCheckboxClass );

				if ( $checks.not( ':checked' ).length ) {
					$checks.prop( 'checked', true );
				} else {
					$checks.prop( 'checked', false );
				}
			}
		);

		$( groupClass ).on(
			'click',
			extensionCheckboxClass,
			function () {
				const $this = $( this );
				const $type = $this.closest( groupClass );
				const $checks = $type.find( extensionCheckboxClass );
				const $checkAll = $type.find( groupCheckboxClass );

				if ( $checks.not( ':checked' ).length ) {
					$checkAll.prop( 'checked', false );
				} else {
					$checkAll.prop( 'checked', true );
				}
			}
		);

		$( groupClass ).each( function () {
			const $this = $( this );
			const $groupCheckbox = $this.find( groupCheckboxClass );
			const $extensionCheckboxes = $this.find( extensionCheckboxClass );

			if ( ! $extensionCheckboxes.not( ':checked' ).length ) {
				$groupCheckbox.prop( 'checked', true );
			}
		} );

	} );

} )( jQuery ); // eslint-disable-line no-undef
