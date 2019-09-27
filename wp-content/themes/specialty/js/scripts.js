jQuery(function( $ ) {
	'use strict';

	var $window = $(window);
	var $html = $('html');
	var $body = $('body');

	/* -----------------------------------------
	Responsive Menus Init with mmenu
	----------------------------------------- */
	var $mainNav   = $( '.navigation-main' );
	var $mobileNav = $( '#mobilemenu' );

	$mainNav.clone().removeAttr( 'id' ).removeClass().appendTo( $mobileNav );
	$mobileNav.find( 'li' ).removeAttr( 'id' );

	$mobileNav.mmenu({
		offCanvas: {
			position: "top",
			zposition: "front"
		},
		autoHeight: true,
		navbars: [
			{
				position: "top",
				content: [
					"prev",
					"title",
					"close"
				]
			}
		]
	});

	/* -----------------------------------------
	Sidebar / Searchform Drawer Toggle
	----------------------------------------- */
	var $sidebarTrigger = $('.sidebar-wrap-trigger');
	var $sidebarDismiss = $('.sidebar-wrap-dismiss');
	var $sidebarWrap = $('.sidebar-wrap');

	var $searchFormTrigger = $('.form-filter-trigger');
	var $searchFormDismiss = $('.form-filter-dismiss');
	var $searchForm = $('.form-filter');

	function isSidebarVisible() {
		return $sidebarWrap.hasClass('sidebar-wrap-visible');
	}

	function dismissSidebar(e) {
		if (e) {
			e.preventDefault();
		}
		$sidebarWrap.removeClass('sidebar-wrap-visible');
		$html.removeClass('mm-blocking mm-front');
	}

	function displaySidebar(e) {
		if (e) {
			e.preventDefault();
		}
		$sidebarWrap.addClass('sidebar-wrap-visible');
		$html.addClass('mm-blocking mm-front')
	}

	function isSearchFormVisible() {
		return $searchForm.hasClass('form-filter-visible');
	}

	function dismissSearchForm(e) {
		if (e) {
			e.preventDefault();
		}
		$searchForm.removeClass('form-filter-visible');
		$html.removeClass('mm-blocking mm-front')
	}

	function displaySearchForm(e) {
		if (e) {
			e.preventDefault();
		}
		$searchForm.addClass('form-filter-visible');
		$html.addClass('mm-blocking mm-front')
	}

	$sidebarTrigger.on('click', displaySidebar);
	$sidebarDismiss.on('click', dismissSidebar);

	$searchFormTrigger.on('click', displaySearchForm);
	$searchFormDismiss.on('click', dismissSearchForm);

	/* Event propagations */
	$(document).on('keydown', function (e) {
		e = e || window.e;
		if (
			e.keyCode === 27
			&& (isSidebarVisible() && isSearchFormVisible())
		) {
			dismissSidebar();
			dismissSearchForm();
		}
	});

	$body
		.on('click', function () {
			if (isSidebarVisible()) {
				dismissSidebar();
			}

			if (isSearchFormVisible()) {
				dismissSearchForm();
			}
		})
		.find('.sidebar-wrap-trigger, .sidebar-wrap, .form-filter-trigger, .form-filter')
		.on('click', function (e) {
			e.stopPropagation();
		});

	/* -----------------------------------------
	Responsive Videos with fitVids
	----------------------------------------- */
	$body.fitVids();

	/* -----------------------------------------
	Image Lightbox
	----------------------------------------- */
	$( ".ci-lightbox, a[data-lightbox^='gal']" ).magnificPopup({
		type: 'image',
		mainClass: 'mfp-with-zoom',
		gallery: {
			enabled: true
		},
		zoom: {
			enabled: true
		}
	} );

	/* -----------------------------------------
	WP Job Board functionality
	----------------------------------------- */
	var $container = $( '.job-listing-container' );
	var $listingsParent = $( '.item-listing' );
	var $listingFiltersForm = $( '.listing-filters-form' );
	var $errorBox = $( '.list-item-error' );
	var $formSubmitBtn = $( '.btn-jobs-filter' );
	var $loadMoreBtn = $( '.btn-load-jobs' );
	var $jobsFoundNo = $( '.jobs-found-no' );
	var xhr;

	/**
	 * Main updater event listener.
	 * Any time the job list needs to be updated it must be done
	 * via this listener.
	 *
	 * The listener's callback receives three arguments:
	 *
	 * @param {object} event - The event itself
	 * @param {number} page - The page to be loaded
	 * @param {bool} append - Whether to append jobs at the end of the already existing
	 * ones or not. For example pagination appends, filtering does not.
	 */
	$container.on( 'ci.update-results', function ( event, page, append ) {
		if ( xhr ) {
			xhr.abort();
		}

		if ( $errorBox.is( ':visible' ) ) {
			$errorBox.fadeOut( 'fast' );
		}

		// Gather all data
		var jobTypes = $( 'input[name="job_type[]"]:checked, input[name="job_type[]"][type="hidden"], input[name="job_type"]' ).map( function () {
			return $( this ).val();
		} ).get();

		var salaries = $( 'input[name="salary_range[]"]:checked, input[name="salary_range[]"][type="hidden"], input[name="salary_range"]' ).map( function () {
			return $( this ).val();
		} ).get();

		var categories = $( ':input[name^="search_categories"]' ).map( function () {
			return $ (this ).val();
		} ).get();

		var keywords = $( 'input[name="search_keywords"]' ).val();
		var location = $( 'input[name="search_location"]' ).val();

		var data = {
			search_keywords: keywords,
			search_location: location,
			search_categories: categories,
			filter_job_type: jobTypes,
			salary_range: salaries,
			per_page: $container.data( 'per_page' ),
			page: page,
			orderby: $container.data( 'orderby' ),
			order: $container.data( 'order' ),
		};

		xhr = $.ajax( {
			type: 'POST',
			dataType: 'json',
			url: job_manager_ajax_filters.ajax_url.toString().replace( '%%endpoint%%', 'get_listings' ),
			data: data,
		} )
			.done( function ( response ) {
				try {
					// Inject the newly fetched jobs
					if ( response.html ) {
						if ( append ) {
							$listingsParent.append( response.html );
						} else {
							$listingsParent.html( response.html );
							$loadMoreBtn.data( 'page', 1 );
						}

						if ( response.hasOwnProperty('jobs_number_found') ) {
							$jobsFoundNo.text(response.jobs_number_found);
						}
					}

					// Hide pagination if there are no more jobs to show
					if ( ! response.found_jobs || response.max_num_pages === page ) {
						$loadMoreBtn.hide();
					} else {
						$loadMoreBtn.show();
					}
				} catch ( err ) {
					$errorBox.fadeIn( 'fast' );
					console.error(err);
				}
			} )
			.fail( function ( error ) {
				if ( error.statusText !== 'abort' ) {
					$errorBox.fadeIn( 'fast' );
				}
			} )
			.always( function ( response ) {
				if ( ! response || response.statusText !== 'abort' ) {
					$loadMoreBtn.removeClass( 'btn-loading' );
					$formSubmitBtn.removeClass( 'btn-loading' );
					$listingsParent.removeClass( 'is-loading' );
				}
			} );
	} );

	// Search Form Filtering
	$listingFiltersForm.on( 'submit', function ( event ) {
		var append = false;

		$formSubmitBtn.addClass( 'btn-loading' );
		$listingsParent.addClass( 'is-loading' );
		$container.triggerHandler( 'ci.update-results', [ 1, append ] );
		event.preventDefault();
	} );

	// Other checkbox filters
	$( '.checkbox-filter' ).on( 'change', function ( event ) {
		var append = false;

		$listingsParent.addClass( 'is-loading' );
		$container.triggerHandler( 'ci.update-results', [ 1, append ] );
		event.preventDefault();
	} );

	// Pagination
	$loadMoreBtn.on( 'click', function ( event ) {
		var $this = $( this );
		var page = $this.data( 'page' ) || 1;
		var append = true;

		page = page + 1;
		$this.data( 'page', page );
		$this.addClass( 'btn-loading' );
		$container.triggerHandler( 'ci.update-results', [ page, append ] );
		event.preventDefault();
	} );

	/* -----------------------------------------
	Google Maps Lightbox
	----------------------------------------- */
	var $gmapLink = $( '.google_map_link' );

	$gmapLink.magnificPopup({
		type: 'iframe'
	} );

	$window.on( 'load', function() {
		/* -----------------------------------------
		Custom Scrollbar
		----------------------------------------- */
		if ($window.width() > 768 && $sidebarWrap.hasClass('sidebar-fixed-default')) {
			$sidebarWrap.find('.sidebar').mCustomScrollbar({
				theme: 'minimal-dark'
			});
		}

		/* -----------------------------------------
		MatchHeight
		----------------------------------------- */
		$( '.row-equal' ).find( '[class^="col"]' ).matchHeight();
	});

});
