// JavaScript Document

function open_admin(popstate) {
	if (typeof popstate == 'undefined') {
		const popstate = false;
	}

	const browser_hour_format_test = (/chrom(e|ium)/i.test(navigator.userAgent) && !/msie|edge/i.test(navigator.userAgent)) ? false : (new Date(2020, 4, 8, 18, 0, 0, 0).toLocaleTimeString()),
		notes = (document.getElementById('special-note-new') != null),
		hour_types = ['regular', 'special'],
		hour_format_12 = '12_dot_trim',
		hour_format_24 = '24_colon';

	let browser_hour_format = ((typeof browser_hour_format_test == 'string' && parseInt(browser_hour_format_test) == 18) ? 24 : 12),
		place_id = null,
		google_api_key = null,
		section = null,
		regular = null,
		special = null,
		time_format = null,
		week_start = null,
		data = [],
		weekdays = [],
		weekend = [],
		e = null,
		i = 0,
		k = null,
		t = null,
		regex = null,
		hour_type = null,
		html = '',
		count = null;
	
	if (jQuery('#opening-hours-settings').length) {
		place_id = jQuery('#place-id').val();
		google_api_key = jQuery('#api-key').val();
		
		if (window.matchMedia("(hover: none)").matches) {
			document.getElementById('opening-hours-settings').setAttribute('data-no-hover', true);
			document.getElementById('opening-hours-settings').querySelector(':scope .keyboard-navigation').remove();
		}
	}
	
	if (jQuery('.section', '#wpbody-content').length) {
		if (!jQuery('.nav-tab-active', jQuery('nav:eq(0)', '#wpbody-content')).length || typeof window.location.hash == 'string' && window.location.hash.length) {
			jQuery('.section', '#wpbody-content').each(function(section_index) {
				section = (typeof window.location.hash == 'string' && window.location.hash.length) ? window.location.hash.replace(/^#([\w-]+)/, '$1') : null;
				if (section == null && section_index == 0 || section != null && section == jQuery(this).attr('id')) {
					if (jQuery(this).hasClass('hide')) {
						jQuery(this).removeClass('hide');
					}
					return;
				}

				if (!jQuery(this).hasClass('hide')) {
					jQuery(this).addClass('hide');
				}
			});
			
			if (jQuery('.nav-tab-active', jQuery('nav:eq(0)', '#wpbody-content')).length >= 1) {
				jQuery('.nav-tab-active', jQuery('nav:eq(0)', '#wpbody-content')).each(function(section_index) {
					if (section != null && jQuery(this).attr('href') != '#' + section || section == null && section_index == 0) {
						jQuery(this).removeClass('nav-tab-active');
					}
				});
			}
			
			jQuery('.nav-tab', jQuery('nav:eq(0)', '#wpbody-content')).each(function(tab_index) {
				section = (typeof jQuery(this).attr('href') == 'string') ? jQuery(this).attr('href').replace(/^.*#([\w-]+)/, '$1') : null;
				
				if ((tab_index == 0 && (section == null || typeof window.location.hash == 'undefined' || !window.location.hash.length)) || typeof window.location.hash == 'string' && window.location.hash.length && window.location.hash.replace(/^#([\w-]+)/, '$1') == section) {
					jQuery(this).addClass('nav-tab-active').prop('aria-current', 'page');
				}
			});
		}
	}
	
	if (popstate) {
		if (jQuery('.section', '#wpbody-content').length) {
			jQuery('.nav-tab', jQuery('nav:eq(0)', '#wpbody-content')).removeClass('nav-tab-active').removeProp('aria-current');
		}
		return;
	}
	
	if (jQuery('.is-dismissible').length) {
		jQuery('.is-dismissible').each(function(index, element) {
			if (!jQuery(this).hasClass('notice-success') && !jQuery(this).hasClass('notice-error')) {
				jQuery(this).remove();
			}
		});
	}
	
	if (jQuery('div', '#widgets-right').length) {
		jQuery('div', '#widgets-right').each(function() {
			if (typeof jQuery(this).attr('id') == 'string' && jQuery(this).attr('id').match(/(?:(?:we[_-]?are[_-]?)?open|opening[_-]?hours)/i) != null) {
				// console.log('Open - Widget');
			}
		});
	}
	
	if (!jQuery('#opening-hours, #opening-hours-settings').length) {
		return;
	}
	
	if (jQuery('#opening-hours, #opening-hours-settings').hasClass('closed')) {
		jQuery('#opening-hours, #opening-hours-settings').removeClass('closed');
	}
	
	if (!browser_hour_format_test) {
		jQuery('.opening-hours:eq(0)', '#wpbody-content').append('<input type="time" id="open-time-test" name="open-time-test">');
		browser_hour_format = (jQuery('#open-time-test').width() < 81) ? 24 : 12;
		jQuery('#open-time-test').remove();
	}

	if (jQuery('#opening-hours').length && browser_hour_format == 12) {
		jQuery('#opening-hours').addClass('hours-12');
	}
	
	if (jQuery('#opening-hours-settings').length && jQuery('#time-format').length && !jQuery('#time-format').val().length) {
		if (browser_hour_format == 12) {
			jQuery('#time-format').val(hour_format_12);
			jQuery('#time-type-12').prop('checked', true);
			jQuery('#time-format').closest('td').removeClass('hours-24').addClass('hours-12');
		}
		else {
			jQuery('#time-format').val(hour_format_24);
			jQuery('#time-type-24').prop('checked', true);
			jQuery('#time-format').closest('td').removeClass('hours-12').addClass('hours-24');
		}
	}
	
	jQuery('.is-dismissible').each(function() {
		if (jQuery(this).hasClass('notice-success') || jQuery(this).hasClass('notice-error')) {
			jQuery(this).addClass('visible');
			return;
		}

		jQuery(this).remove();
	});
	
	setTimeout(function() {
		if (jQuery('.is-dismissible').length) {
			jQuery('.is-dismissible').slideUp(300, function() { jQuery(this).remove(); });
		}
	}, 15000);

	if (jQuery('#open-regular').length && jQuery('#open-special').length) {
		if (typeof sessionStorage.getItem('we_are_open_hours') == 'string' && JSON.parse(sessionStorage.getItem('we_are_open_hours')) != null) {
			jQuery('.paste.disabled', '#opening-hours').each(function() {
				jQuery(this).removeClass('disabled');
			});
		}

		jQuery('#open-save, #open-delete').on('click', function(event) {
			event.preventDefault();

			if (jQuery(this).hasClass('disabled')) {
				return false;
			}

			let i = 0;

			if (!notes && jQuery('#open-notes').length) {
				jQuery('#open-notes').addClass('disabled').addClass('hide');
			}
			
			if (jQuery(this).is('#open-delete')) {				
				jQuery('.check-column > :checkbox', '#open-special').each(function() {
					if (jQuery(this).is(':checked') && typeof jQuery(this).closest('tr').attr('id') == 'string' && jQuery(this).closest('tr').attr('id').match(/^special-hours-(new|\d+)$/i) != null && jQuery(':input[type=date]:eq(0)', jQuery(this).closest('tr')).length) {
						jQuery(':input[type=date]:eq(0)', jQuery(this).closest('tr')).val('');
						
						if (jQuery(this).closest('tr').attr('id').match(/^special-hours-\d+$/i) != null) {
							jQuery(this).closest('tr').addClass('delete');
							jQuery(this).closest('tr').fadeOut(300, function() { jQuery(this).remove(); });
						}
					}
				});
			}
			
			data = {
				action: 'we_are_open_admin_ajax',
				type: (jQuery(this).is('#open-delete')) ? 'delete' : 'update',
				regular: new Array(6),
				special: new Array(),
				closure: null,
				nonce: jQuery('#opening-hours').data('nonce')
			};

			for (t in hour_types) {
				jQuery('tr:gt(0)', '#open-' + hour_types[t]).each(function() {
					hour_type = hour_types[t];
					
					if (hour_type == 'regular' || hour_type == 'special' && !jQuery(this).hasClass('delete')) { 
						count = (hour_type == 'regular') ? jQuery(this).data('id') : ((jQuery(':input:eq(0)', jQuery('.date-column', this)).val().length) ? i : null);
						k = (hour_type == 'regular') ? count : jQuery(this).attr('id').replace(/^[\w-]+-([^-]+)$/, '$1');
						
						if (count != null) {
							if (jQuery('.hours-column', this).hasClass('closed') || jQuery('.closed-text', this).is(':visible')) {
								data[hour_type][count] = {
									closed: true,
									date: (hour_type == 'special') ? jQuery(':input:eq(0)', jQuery('.date-column', this)).val() : null,
									label: (hour_type == 'special') ? jQuery(':input:eq(0)', jQuery('.label-column', this)).val() : null,
									note: (notes && hour_type == 'special') ? jQuery(':input:eq(0)', jQuery('.note', jQuery('.hours-column', this))).val() : null,
									hours: [],
									hours_24: false
								};
							}
							else if (jQuery('.hours-column', this).hasClass('hours-24') || jQuery('#' + hour_type + '-time-' + k + '-start').length && jQuery('#' + hour_type + '-time-' + k + '-start').is(':visible') && jQuery('#' + hour_type + '-time-' + k + '-start').val().match(/^00:00$/) != null && jQuery('#' + hour_type + '-time-' + k + '-end').length && jQuery('#' + hour_type + '-time-' + k + '-end').is(':visible') && jQuery('#' + hour_type + '-time-' + k + '-end').val().match(/^(?:00:00|23:5[5-9])$/) != null) {
								data[hour_type][count] = {
									closed: false,
									date: (hour_type == 'special') ? jQuery(':input:eq(0)', jQuery('.date-column', this)).val() : null,
									label: (hour_type == 'special') ? jQuery(':input:eq(0)', jQuery('.label-column', this)).val() : null,
									note: (notes && hour_type == 'special') ? jQuery(':input:eq(0)', jQuery('.note', jQuery('.hours-column', this))).val() : null,
									hours: [],
									hours_24: true
								};
							}
							else {
								data[hour_type][count] = {
									closed: false,
									date: (hour_type == 'special') ? jQuery(':input:eq(0)', jQuery('.date-column', this)).val() : null,
									label: (hour_type == 'special') ? jQuery(':input:eq(0)', jQuery('.label-column', this)).val() : null,
									note: (notes && hour_type == 'special') ? jQuery(':input:eq(0)', jQuery('.note', jQuery('.hours-column', this))).val() : null,
									hours: [
										[
											(jQuery('#' + hour_type + '-time-' + k + '-start').length && jQuery('#' + hour_type + '-time-' + k + '-start').is(':visible')) ? jQuery('#' + hour_type + '-time-' + k + '-start').val() : null,
											(jQuery('#' + hour_type + '-time-' + k + '-end').length && jQuery('#' + hour_type + '-time-' + k + '-end').is(':visible')) ? jQuery('#' + hour_type + '-time-' + k + '-end').val() : null
										],
										[
											(jQuery('#' + hour_type + '-time-' + k + '-start-extended').length && jQuery('#' + hour_type + '-time-' + k + '-start-extended').is(':visible')) ? jQuery('#' + hour_type + '-time-' + k + '-start-extended').val() : null,
											(jQuery('#' + hour_type + '-time-' + k + '-end-extended').length && jQuery('#' + hour_type + '-time-' + k + '-end-extended').is(':visible')) ? jQuery('#' + hour_type + '-time-' + k + '-end-extended').val() : null
										],
										[
											(jQuery('#' + hour_type + '-time-' + k + '-start-extended-2').length && jQuery('#' + hour_type + '-time-' + k + '-start-extended-2').is(':visible')) ? jQuery('#' + hour_type + '-time-' + k + '-start-extended-2').val() : null,
											(jQuery('#' + hour_type + '-time-' + k + '-end-extended-2').length && jQuery('#' + hour_type + '-time-' + k + '-end-extended-2').is(':visible')) ? jQuery('#' + hour_type + '-time-' + k + '-end-extended-2').val() : null
										]
									],
									hours_24: false
								};
							}
							
							if (hour_type == 'special') {
								i++;
							}
						}
					}
				});
				
				if (jQuery('#closure-start').length && jQuery('#closure-start').is(':visible') && jQuery('#closure-start').val().length) {
					data.closure = [ jQuery('#closure-start').val(), jQuery('#closure-end').val() ];
				}
			}
			
			jQuery.post(we_are_open_admin_ajax.url, data, function(response) {
				if (!response.success) {
					if (typeof response.message == 'object' || typeof response.message == 'string') {
						open_message(response.message, 'error');
					}

					return;
				}

				if (!jQuery('#open-delete').hasClass('disabled')) {
					jQuery('.check-column > :checkbox:checked', '#open-special').each(function() {
						jQuery(this).prop('checked', false).removeAttr('checked');
					});
					
					jQuery('#open-delete').addClass('disabled');
				}
				
				if (typeof response.closure == 'object' && typeof response.closure.count == 'number' && jQuery('#closure-dates').length && jQuery('.closed-text', '#closure-dates').length && jQuery('.closed-text', '#closure-dates').is(':visible')) {
					if (response.closure.count == 1) {
						jQuery('.closed-text:eq(0)', '#closure-dates').text(jQuery('.closed-text:eq(0)', '#closure-dates').data('singular').replace(/%[us]/, response.closure.count));
					}
					else if (response.closure.count > 1) {
						jQuery('.closed-text:eq(0)', '#closure-dates').text(jQuery('.closed-text:eq(0)', '#closure-dates').data('plural').replace(/%[us]/, response.closure.count));
					}
				}

				if (!notes && jQuery('#open-notes').length && jQuery('#open-notes').hasClass('disabled')) {
					jQuery('#open-notes').removeClass('disabled').removeClass('hide');
				}
				
				open_message(response.message, 'success');

				return;
			}, 'json');
		});

		jQuery('#closure-toggle').on('click', function(event) {
			event.preventDefault();
			
			if (!notes && jQuery('#open-notes').length) {
				jQuery('#open-notes').addClass('disabled').addClass('hide');
			}

			if (jQuery('#closure-dates').is(':hidden')) {
				jQuery('#closure-information').slideDown(200, function() {
					jQuery('#closure-toggle').html(jQuery('#closure-toggle').data('hide') + ' ' + jQuery('.dashicons', '#closure-toggle')[0].outerHTML.replace(/([\b_-])down([\b_-]|$)/i, '$1up$2'));
					jQuery('#closure-start').val('');
					jQuery('#closure-end').val('');
					jQuery('#closure-dates').fadeIn(500, function() {
						jQuery(':input:visible:eq(0)', this).trigger('focus');
					});
				});

				return;
			}
			
			jQuery('#closure-dates').fadeOut(500, function() {
				jQuery('#closure-toggle').html(jQuery('#closure-toggle').data('show') + ' ' + jQuery('.dashicons', '#closure-toggle')[0].outerHTML.replace(/([\b_-])up([\b_-]|$)/i, '$1down$2'));
				jQuery('#closure-information').slideUp(200);
			});

			return;
		});

		jQuery('#open-google-business-populate').on('click', function(event) {
			event.preventDefault();
			data = {
				action: 'we_are_open_admin_ajax',
				type: 'google_business',
				nonce: jQuery('#opening-hours').data('nonce')
			};
			
			jQuery.post(we_are_open_admin_ajax.url, data, function(response) {
				if (!response.success) {
					open_message(response.message, 'error');
					return;
				}

				if (typeof response.regular == 'object' && response.regular != null) {
					for (i in response.regular) {
						regular = response.regular[i];

						if (regular.closed) {
							if (!jQuery('#regular-hours-' + i + '-closed').is(':hidden')) {
								continue;
							}

							jQuery('.dashicons-minus', jQuery('#regular-hours-' + i)).removeClass('dashicons-minus').addClass('dashicons-plus');
							jQuery('#regular-hours-' + i + '-base').hide();
							jQuery('#regular-hours-' + i + '-extended').hide();
							jQuery('#regular-hours-' + i + '-extended-2').hide();
							jQuery('#regular-hours-' + i + '-closed').show();
							jQuery('.hours-column', jQuery('#regular-hours-' + i)).removeClass('hours-24').addClass('closed');
							jQuery('#regular-time-' + i + '-start').val('');
							jQuery('#regular-time-' + i + '-end').val('');

							continue;
						}

						if (regular.hours_24) {
							if (jQuery('.hours', jQuery('#regular-hours-' + i)).hasClass('hours-24')) {
								continue;
							}

							jQuery('.dashicons-minus', jQuery('#regular-hours-' + i)).removeClass('dashicons-minus').addClass('dashicons-plus');
							jQuery('#regular-hours-' + i + '-closed').hide();
							jQuery('#regular-hours-' + i + '-extended').hide();
							jQuery('#regular-hours-' + i + '-extended-2').hide();

							if (jQuery('#regular-hours-' + i + '-base').is(':hidden')) {
								jQuery('#regular-hours-' + i + '-base').show();
							}

							jQuery('.hours-column', jQuery('#regular-hours-' + i)).removeClass('closed').addClass('hours-24');
							jQuery('#regular-time-' + i + '-start').val('00:00');
							jQuery('#regular-time-' + i + '-end').val('00:00');
							
							continue;
						}
					
						jQuery('#regular-hours-' + i + '-closed').hide();
						jQuery('.dashicons-minus', jQuery('#regular-hours-' + i + '-closed')).removeClass('dashicons-minus').addClass('dashicons-plus');
						jQuery('.hours-column', jQuery('#regular-hours-' + i)).removeClass('closed').removeClass('hours-24');
						
						if (regular.hours.length < 3 && jQuery('#regular-hours-' + i + '-extended-2').is(':visible')) {
							jQuery('#regular-time-' + i + '-start-extended-2').val('');
							jQuery('#regular-time-' + i + '-end-extended-2').val('');
							jQuery('#regular-hours-' + i + '-extended-2').hide();
							jQuery('.dashicons-minus', jQuery('#regular-hours-' + i + '-extended')).removeClass('dashicons-minus').addClass('dashicons-plus');
						}
						
						if (regular.hours.length < 2 && jQuery('#regular-hours-' + i + '-extended').is(':visible')) {
							jQuery('#regular-time-' + i + '-start-extended').val('');
							jQuery('#regular-time-' + i + '-end-extended').val('');
							jQuery('#regular-hours-' + i + '-extended').hide();
							jQuery('.dashicons-minus', jQuery('#regular-hours-' + i)).removeClass('dashicons-minus').addClass('dashicons-plus');
						}

						if (jQuery('#regular-hours-' + i + '-base').is(':hidden')) {
							jQuery('#regular-hours-' + i + '-base').show();
						}
						
						if (regular.hours.length >= 2 && jQuery('#regular-hours-' + i + '-extended').is(':hidden')) {
							jQuery('#regular-hours-' + i + '-extended').show();
							jQuery('.dashicons-minus', jQuery('#regular-hours-' + i + '-base')).removeClass('dashicons-plus').addClass('dashicons-minus');
						}

						if (regular.hours.length >= 3 && jQuery('#regular-hours-' + i + '-extended-2').is(':hidden')) {
							jQuery('#regular-hours-' + i + '-extended-2').show();
							jQuery('.dashicons-minus', jQuery('#regular-hours-' + i + '-extended')).removeClass('dashicons-plus').addClass('dashicons-minus');
						}
						
						if (regular.hours.length >= 1 && typeof regular.hours[0] == 'object' && regular.hours[0].length == 2) {
							jQuery('#regular-time-' + i + '-start').val(regular.hours[0][0]);
							jQuery('#regular-time-' + i + '-end').val(regular.hours[0][1]);
						}
						
						if (regular.hours.length >= 2 && typeof regular.hours[1] == 'object' && regular.hours[1].length == 2) {
							jQuery('#regular-time-' + i + '-start-extended').val(regular.hours[1][0]);
							jQuery('#regular-time-' + i + '-end-extended').val(regular.hours[1][1]);
						}
						
						if (regular.hours.length >= 3 && typeof regular.hours[2] == 'object' && regular.hours[2].length == 2) {
							jQuery('#regular-time-' + i + '-start-extended-2').val(regular.hours[2][0]);
							jQuery('#regular-time-' + i + '-end-extended-2').val(regular.hours[2][1]);
						}
					}
				}

				if (typeof response.special == 'object' && response.special != null) {
					if (jQuery('#special-hours-new-closed').is(':hidden')) {
						jQuery('.dashicons-minus', '#special-hours-new').removeClass('dashicons-minus').addClass('dashicons-plus');
						jQuery('#special-hours-new-base').hide();
						jQuery('#special-hours-new-extended').hide();
						jQuery('#special-hours-new-extended-2').hide();
						jQuery('#special-hours-new-closed').show();
						jQuery('.hours-column', '#special-hours-new').removeClass('hours-24').addClass('closed');
						jQuery('#special-time-new-start').val('');
						jQuery('#special-time-new-end').val('');
						jQuery('#special-date-status-new').prop('disabled', true);
					}

					i = 0;
					html = jQuery('#special-hours-new')[0].outerHTML;

					jQuery('tbody > tr', '#open-special').each(function() {
						if (jQuery(this).is(':last-child')) {
							jQuery(this).remove();
							jQuery('tbody:eq(0)', '#open-special').append(html);
							return;
						}

						jQuery(this).remove();
					});

					for (t in response.special) {
						special = response.special[t];
						jQuery('#special-hours-new').before(html.replace(/(id="special-hours-)(?:new|\d+)(")/, '$1' + i + '$2').replace(/\s+(?:hours-24|disabled(?:=["'][\w-]*["'])?)/g, '').replace(/value="[^"]*"/g, 'value=""').replace(/([\b_"\[-])new([\b_"\]-])/g, '$1' + i + '$2'));
						e = jQuery('#special-hours-' + i);
						jQuery(':input[type=date]:eq(0)', e).val(special.date_display);
						jQuery(':input[type=text]:eq(0)', e).val(special.label);

						if (typeof special.modified_display == 'string' && special.modified_display.length) {
							jQuery('.modified-column:eq(0)', e).html(special.modified_display);
						}

						open_special(jQuery(':input[type=checkbox]:eq(0)', e));
						open_special(jQuery(':input[type=text]:eq(0)', e));
						open_special(jQuery(':input[type=date]:eq(0)', e));
						jQuery('.extended:eq(0)', e).hide();
						jQuery('.extended-2:eq(0)', e).hide();
						jQuery('a.closed-text, a.closed, a.hours-24, a.copy, a.paste, a.add-subtract-toggle', e).each(function() {
							open_regular(this);
						});

						if (special.closed) {
							if (!jQuery('#special-hours-' + i + '-closed').is(':hidden')) {
								i++;
								continue;
							}

							jQuery('.dashicons-minus', e).removeClass('dashicons-minus').addClass('dashicons-plus');
							jQuery('#special-hours-' + i + '-base').hide();
							jQuery('#special-hours-' + i + '-extended').hide();
							jQuery('#special-hours-' + i + '-extended-2').hide();
							jQuery('#special-hours-' + i + '-closed').show();
							jQuery('.hours-column', e).removeClass('hours-24').addClass('closed');
							jQuery('#special-time-' + i + '-start').val('');
							jQuery('#special-time-' + i + '-end').val('');
							i++;

							continue;
						}

						if (special.hours_24) {
							if (jQuery('.hours', e).hasClass('hours-24')) {
								i++;
								continue;
							}

							jQuery('.dashicons-minus', e).removeClass('dashicons-minus').addClass('dashicons-plus');
							jQuery('#special-hours-' + i + '-closed').hide();
							jQuery('#special-hours-' + i + '-extended').hide();
							jQuery('#special-hours-' + i + '-extended-2').hide();

							if (jQuery('#special-hours-' + i + '-base').is(':hidden')) {
								jQuery('#special-hours-' + i + '-base').show();
							}

							jQuery('.hours-column', e).removeClass('closed').addClass('hours-24');
							jQuery('#special-time-' + i + '-start').val('00:00');
							jQuery('#special-time-' + i + '-end').val('00:00');
							i++;
							
							continue;
						}
					
						jQuery('#special-hours-' + i + '-closed').hide();
						jQuery('.dashicons-minus', jQuery('#special-hours-' + i + '-closed')).removeClass('dashicons-minus').addClass('dashicons-plus');
						jQuery('.hours-column', e).removeClass('closed').removeClass('hours-24');
						
						if (special.hours.length < 3 && jQuery('#special-hours-' + i + '-extended-2').is(':visible')) {
							jQuery('#special-time-' + i + '-start-extended-2').val('');
							jQuery('#special-time-' + i + '-end-extended-2').val('');
							jQuery('#special-hours-' + i + '-extended-2').hide();
							jQuery('.dashicons-minus', jQuery('#special-hours-' + i + '-extended')).removeClass('dashicons-minus').addClass('dashicons-plus');
						}
						
						if (special.hours.length < 2 && jQuery('#special-hours-' + i + '-extended').is(':visible')) {
							jQuery('#special-time-' + i + '-start-extended').val('');
							jQuery('#special-time-' + i + '-end-extended').val('');
							jQuery('#special-hours-' + i + '-extended').hide();
							jQuery('.dashicons-minus', e).removeClass('dashicons-minus').addClass('dashicons-plus');
						}

						if (jQuery('#special-hours-' + i + '-base').is(':hidden')) {
							jQuery('#special-hours-' + i + '-base').show();
						}
						
						if (special.hours.length >= 2 && jQuery('#special-hours-' + i + '-extended').is(':hidden')) {
							jQuery('#special-hours-' + i + '-extended').show();
							jQuery('.dashicons-minus', jQuery('#special-hours-' + i + '-base')).removeClass('dashicons-plus').addClass('dashicons-minus');
						}

						if (special.hours.length >= 3 && jQuery('#special-hours-' + i + '-extended-2').is(':hidden')) {
							jQuery('#special-hours-' + i + '-extended-2').show();
							jQuery('.dashicons-minus', jQuery('#special-hours-' + i + '-extended')).removeClass('dashicons-plus').addClass('dashicons-minus');
						}
						
						if (special.hours.length >= 1 && typeof special.hours[0] == 'object' && special.hours[0].length == 2) {
							jQuery('#special-time-' + i + '-start').val(special.hours[0][0]);
							jQuery('#special-time-' + i + '-end').val(special.hours[0][1]);
						}
						
						if (special.hours.length >= 2 && typeof special.hours[1] == 'object' && special.hours[1].length == 2) {
							jQuery('#special-time-' + i + '-start-extended').val(special.hours[1][0]);
							jQuery('#special-time-' + i + '-end-extended').val(special.hours[1][1]);
						}
						
						if (special.hours.length >= 3 && typeof special.hours[2] == 'object' && special.hours[2].length == 2) {
							jQuery('#special-time-' + i + '-start-extended-2').val(special.hours[2][0]);
							jQuery('#special-time-' + i + '-end-extended-2').val(special.hours[2][1]);
						}

						i++;
					}
				}

				if (!notes && jQuery('#open-notes').length && jQuery('#open-notes').hasClass('disabled')) {
					jQuery('#open-notes').removeClass('disabled').removeClass('hide');
				}

				open_message(response.message, 'success');
			}, 'json');
		});
				
		jQuery('a.closed-text, a.closed, a.hours-24, a.copy, a.paste, a.add-subtract-toggle, :input[type=time]', '#open-regular, #open-special').each(function() {
			open_regular(this);
		});
		
		jQuery(':input', '#open-special').each(function() {
			open_special(this);
		});

		open_special(jQuery(':input[type=date]:eq(0)', jQuery('tbody > tr:last', '#open-special')), true);
	}

	if (jQuery('#general.section', '#wpbody-content').length && typeof jQuery('#general.section', '#wpbody-content').data('hunter') == 'object' && jQuery('#general.section', '#wpbody-content').data('hunter') != null) {
		data = jQuery('#general.section', '#wpbody-content').data('hunter');
		google_api_key = (typeof data.api_key == 'string' && data.api_key.length > 10) ? data.api_key : null;
		place_id = (typeof data.place_id == 'string' && data.place_id.length > 10) ? data.place_id : null;
		time_format = (typeof data.time_format == 'string' && data.time_format.length > 2) ? data.time_format : null;
		week_start = (typeof data.week_start == 'number' && data.week_start >= 0 || typeof data.week_start == 'string' && parseInt(data.week_start) >= 0) ? parseInt(data.week_start) : null;
		update = (typeof data.update == 'number' && data.update > 0 || typeof data.week_start == 'string' && parseInt(data.week_start) > 0) ? parseInt(data.update) : null;

		if (!jQuery('#place-id').val().length) {
			if (!jQuery('#api-key').val().length) {
				jQuery('#api-key').val(google_api_key);
			}
	
			jQuery('#place-id').val(place_id);
		}

		if (!jQuery('#time-format').val().length) {
			if (time_format != null) {
				jQuery('option', '#time-format').each(function() {
					if (!jQuery('#time-format').val().length && typeof jQuery(this).data('php') == 'string' && jQuery(this).data('php') == time_format) {
						jQuery('#time-format').val(jQuery(this).attr('value'));
					}
				});
				
				if (time_format.match(/^g.*$/) != null) {
					if (!jQuery('#time-format').val().length) {
						jQuery('#time-format').val(hour_format_12);
					}
					
					jQuery('#time-type-12').prop('checked', true);
					jQuery('#time-format').closest('td').removeClass('hours-24').addClass('hours-12');
				}
				else if (time_format.match(/^H.*$/) != null) {
					if (!jQuery('#time-format').val().length) {
						jQuery('#time-format').val(hour_format_24);
					}
					
					jQuery('#time-type-24').prop('checked', true);
					jQuery('#time-format').closest('td').removeClass('hours-12').addClass('hours-24');
				}
			}
			
			if (!jQuery('#time-format').val().length) {
				jQuery('#time-format').val('24_colon');
				jQuery('#time-type-24').prop('checked', true);
				jQuery('#time-format').closest('td').removeClass('hours-12').addClass('hours-24');
			}
		}
		
		if (!jQuery(':input:checked', jQuery('#week-start-0').closest('td')).length) {
			jQuery('#week-start-0').prop('checked', true);
		}
	}

	if (document.getElementById('opening-hours-settings') != null && document.getElementById('opening-hours-settings').querySelector(':scope .plugin-notification') != null) {
		document.getElementById('opening-hours-settings').querySelector(':scope .plugin-notification').querySelectorAll(':scope a').forEach(a => {
			a.addEventListener('click', event => {
				if (event.currentTarget.getAttribute('data-notification-action') == null || event.currentTarget.getAttribute('data-notification-action').match(/\bnow\b/i) == null) {
					event.preventDefault();
					event.stopPropagation();
				}
				
				document.getElementById('opening-hours-settings').querySelector(':scope .plugin-notification').classList.remove('visible');
				document.getElementById('opening-hours-settings').querySelector(':scope .plugin-notification').classList.add('hide');

				data = {
					action: 'we_are_open_admin_ajax',
					type: 'notification_action',
					notification_action: event.currentTarget.getAttribute('data-notification-action'),
					link: event.currentTarget.getAttribute('href')
				};

				jQuery.post(we_are_open_admin_ajax.url, data, function(response) {
					document.getElementById('opening-hours-settings').querySelector(':scope .plugin-notification').remove();
				}, 'json');

				return;
			})
		});

		setTimeout(function() {
			if (document.getElementById('opening-hours-settings').querySelector(':scope .plugin-notification') == null || document.querySelector('.nav-tab-wrapper .general') == null) {
				return;
			}

			if (document.querySelector('.nav-tab-wrapper .general').classList.contains('nav-tab-active')) {
				document.getElementById('opening-hours-settings').querySelector(':scope .plugin-notification').classList.add('active');
				return;
			}

			document.getElementById('opening-hours-settings').querySelector(':scope .plugin-notification').classList.remove('active');
		}, 100);
	}

	if (jQuery('.section', '#wpbody-content').length) {
		jQuery('.section-bookmarks', '#opening-hours-settings').each(function() {
			jQuery('a', this).each(function() {
				jQuery(this).on('click', function(event) {
					event.preventDefault();
					event.stopPropagation();

					if (!jQuery(String(jQuery(this).attr('href'))).length || Math.round(parseInt(jQuery(String(jQuery(this).attr('href'))).offset().top) - parseInt(jQuery(String(jQuery(this).attr('href'))).height())) < 25) {
						jQuery('html, body').animate({
							scrollTop: 0
						}, 800);
						return;
					}

					jQuery('html, body').animate({
						scrollTop: Math.round(parseInt(jQuery(String(jQuery(this).attr('href'))).offset().top) - parseInt(jQuery(String(jQuery(this).attr('href'))).height()) - 20)
					}, 800);
					jQuery(String(jQuery(this).attr('href'))).addClass('highlight');
					setTimeout(function(e) { jQuery(e).removeClass('highlight'); }, 1200, jQuery(String(jQuery(this).attr('href'))).addClass('highlight'));
				});
			});
		});

		jQuery('#time-separator, #time-group-separator, #day-separator, #day-range-separator, #day-range-suffix, #day-range-suffix-special, #hours-24-text, #weekdays-text, #weekend-text, #everyday-text, #midday-text, #midnight-text', '#open-settings-separators').each(function() {
			if (jQuery(this).val().match(/^"([^"]*)"$/) == null && jQuery(this).val().match(/^\s+|\s+$/) != null) {
				jQuery(this).val('"' + jQuery(this).val() + '"');
			}
			
			jQuery(this).on('focus keyup change blur', function(event) {
				if (event.type == 'focus') {
					if (jQuery(this).val().match(/^".*"$/) != null) {
						jQuery(this).val(jQuery(this).val().replace(/^"(.*)"$/, '$1'));
					}
					return;
				}
				
				if (jQuery(this).parent().hasClass('value-text-empty') || jQuery(this).parent().hasClass('value-text-suffix-empty')) {
					if (!jQuery(this).val().length && jQuery('.dashicons', jQuery(this).siblings('.value-text')).hasClass('dashicons-yes-alt')) {
						jQuery('.dashicons', jQuery(this).siblings('.value-text')).removeClass('dashicons-yes-alt').addClass('dashicons-marker');
						jQuery('.dashicons', jQuery(this).siblings('.value-empty')).removeClass('dashicons-marker').addClass('dashicons-yes-alt');
						return;
					}
					
					if (jQuery(this).val().length && jQuery('.dashicons', jQuery(this).siblings('.value-empty')).hasClass('dashicons-yes-alt')) {
						jQuery('.dashicons', jQuery(this).siblings('.value-empty')).removeClass('dashicons-yes-alt').addClass('dashicons-marker');
						jQuery('.dashicons', jQuery(this).siblings('.value-text')).removeClass('dashicons-marker').addClass('dashicons-yes-alt');
					}
					
					if (!jQuery(this).val().length || jQuery(this).parent().hasClass('value-text-empty')) {
						return;
					}
					
					regex = new RegExp('^.+' + ((jQuery('#day-range-suffix').val().length) ? jQuery('#day-range-suffix').val().replace(/^(?:['"](.+)['"]|(.+))$/, '$1$2') : '[\w]') + '$');
					
					if (jQuery('.dashicons', jQuery(this).siblings('.value-suffix')).hasClass('dashicons-marker') && jQuery(this).val().match(regex) != null) {
						jQuery('.dashicons', jQuery(this).siblings('.value-suffix')).removeClass('dashicons-marker').addClass('dashicons-yes-alt');
					}
					else if (jQuery('.dashicons', jQuery(this).siblings('.value-suffix')).hasClass('dashicons-yes-alt') && jQuery(this).val().match(regex) == null) {
						jQuery('.dashicons', jQuery(this).siblings('.value-suffix')).removeClass('dashicons-yes-alt').addClass('dashicons-marker');
					}
					
					return;
				}

				if (!jQuery(this).siblings('.leading-space').hasClass('disabled')) {
					if (jQuery(this).val().match(/^"?\s+.*"?$/) != null) {
						jQuery('.dashicons', jQuery(this).siblings('.leading-space')).removeClass('dashicons-marker').addClass('dashicons-yes-alt');
					}
					else {
						jQuery('.dashicons', jQuery(this).siblings('.leading-space')).removeClass('dashicons-yes-alt').addClass('dashicons-marker');
					}
				}
				else if (jQuery(this).val().match(/^\s+[^\s]+.*$/) != null) {
					jQuery(this).val(jQuery(this).val().replace(/^\s+([^\s]+.*)$/, '$1'));
				}

				if (!jQuery(this).siblings('.trailing-space').hasClass('disabled')) {
					if (jQuery(this).val().match(/^"?.*\s+"?$/) != null) {
						jQuery('.dashicons', jQuery(this).siblings('.trailing-space')).removeClass('dashicons-marker').addClass('dashicons-yes-alt');
					}
					else {
						jQuery('.dashicons', jQuery(this).siblings('.trailing-space')).removeClass('dashicons-yes-alt').addClass('dashicons-marker');
					}
				}
				else if (jQuery(this).val().match(/^.*[^\s]+\s+$/) != null) {
					jQuery(this).val(jQuery(this).val().replace(/^(.*[^\s]+)\s+$/, '$1'));
				}
				
				if (jQuery(this).is('#day-range-suffix')) {
					regex = (jQuery(this).val().length) ? new RegExp('^(.+)(' + jQuery(this).val().replace(/^(?:['"](.+)['"]|(.+))$/, '$1$2') + ')$') : null;
					
					jQuery('.value-text-suffix-empty', '#open-settings-separators').each(function() {
						if (regex != null && jQuery('#day-range-suffix').val().length) {
							if (jQuery(':input:eq(0)', this).val().length && jQuery(':input:eq(0)', this).val().match(regex) != null && jQuery('.dashicons', jQuery('.value-suffix:eq(0)', this)).hasClass('dashicons-marker')) {
								jQuery('.dashicons', jQuery('.value-suffix:eq(0)', this)).removeClass('dashicons-marker').addClass('dashicons-yes-alt');
							}
							else if ((!jQuery(':input:eq(0)', this).val().length || jQuery(':input:eq(0)', this).val().match(regex) == null) && jQuery('.dashicons', jQuery('.value-suffix:eq(0)', this)).hasClass('dashicons-yes-alt')) {
								jQuery('.dashicons', jQuery('.value-suffix:eq(0)', this)).removeClass('dashicons-yes-alt').addClass('dashicons-marker');
							}
						}
						else if ((regex == null || !jQuery(':input:eq(0)', this).val().length || regex != null && jQuery(':input:eq(0)', this).val().match(regex) == null) && jQuery('.dashicons', jQuery('.value-suffix:eq(0)', this)).hasClass('dashicons-yes-alt')) {
							jQuery('.dashicons', jQuery('.value-suffix:eq(0)', this)).removeClass('dashicons-yes-alt').addClass('dashicons-marker');
						}
					});
				}
				
				if (event.type == 'keyup') {
					return;
				}

				if ((event.type == 'change' || event.type == 'blur') && jQuery(this).val().match(/^"([^"]*)"$/) == null && jQuery(this).val().match(/^\s+|\s+$/) != null) {
					jQuery(this).val('"' + jQuery(this).val() + '"');
				}
			});
			
			if (jQuery(this).siblings('.action').length) {
				jQuery(this).siblings('.action').each(function() {
					jQuery(this).on('click', function(event) {
						event.preventDefault();
						
						if (jQuery(this).hasClass('value-empty')) {
							if (jQuery('.dashicons', this).hasClass('dashicons-marker')) {
								jQuery('.dashicons', jQuery(this).siblings('.value-text')).removeClass('dashicons-yes-alt').addClass('dashicons-marker');
								jQuery('.dashicons', this).removeClass('dashicons-marker').removeClass('dashicons-marker').addClass('dashicons-yes-alt');
								jQuery(this).siblings(':input:eq(0)').val('').trigger('focus');
							}
							
							return;
						}
						
						if (jQuery(this).hasClass('value-suffix')) {
							if (!jQuery('#day-range-suffix').val().length || !jQuery(this).siblings(':input:eq(0)').val().length) {
								return;
							}
							
							regex = new RegExp('^(.+)(' + jQuery('#day-range-suffix').val().replace(/^(?:['"](.+)['"]|(.+))$/, '$1$2') + ')$');
							
							if (jQuery(this).siblings(':input:eq(0)').val().match(regex) == null && jQuery('.dashicons', this).hasClass('dashicons-marker')) {
								jQuery('.dashicons', this).removeClass('dashicons-marker').addClass('dashicons-yes-alt');
								jQuery(this).siblings(':input:eq(0)').val(jQuery(this).siblings(':input:eq(0)').val() + jQuery('#day-range-suffix').val().replace(/^(?:['"](.+)['"]|(.+))$/, '$1$2')).trigger('focus');
							}
							else if (jQuery(this).siblings(':input:eq(0)').val().match(regex) != null && jQuery('.dashicons', this).hasClass('dashicons-yes-alt')) {
								jQuery('.dashicons', this).removeClass('dashicons-yes-alt').addClass('dashicons-marker');
								jQuery(this).siblings(':input:eq(0)').val(jQuery(this).siblings(':input:eq(0)').val().replace(regex, '$1')).trigger('focus');
							}
							
							return;
						}
					})
				});
			}
			
			jQuery('.highlight').each(function() {
				jQuery(this).on('click', function() {
					if (jQuery(this).text().match(/^[0-9a-f][0-9a-f:.-]{7,80}$/) == null) {
						return;
					}
					
					if (window.getSelection && document.createRange) {
						selection = window.getSelection();
						range = document.createRange();
						range.selectNodeContents(this);
						selection.removeAllRanges();
						selection.addRange(range);
						return;
					}
					
					if (document.selection && document.body.createTextRange) {
						range = document.body.createTextRange();
						range.moveToElementText(this);
						range.trigger('select');
						return;
					}
				});
			});
		});
		
		jQuery('#time-format', '#open-general').on('change', function() {
			if (jQuery('option[value="' + jQuery(this).val() + '"]', jQuery(this)).hasClass('hours-12')) {
				if (jQuery(this).closest('td').hasClass('hours-24')) {
					jQuery(this).closest('td').removeClass('hours-24').addClass('hours-12');
				}

				jQuery('#time-type-12').prop('checked', true);
				
				return;
			}
		
			if (jQuery(this).closest('td').hasClass('hours-12')) {
				jQuery(this).closest('td').removeClass('hours-12').addClass('hours-24');
			}

			jQuery('#time-type-24').prop('checked', true);
			
			return;
		});
	
		jQuery('#time-type-12', '#open-general').on('change', function() {
			if (jQuery(this).closest('td').hasClass('hours-24')) {
				jQuery(this).closest('td').removeClass('hours-24').addClass('hours-12');
			}

			if (typeof jQuery('#time-format').val() == 'string' && jQuery('#time-format').val().match(/^12.*$/) == null) {
				jQuery('#time-format').val(hour_format_12);
			}
		});
	
		jQuery('#time-type-24', '#open-general').on('change', function() {
			if (jQuery(this).closest('td').hasClass('hours-12')) {
				jQuery(this).closest('td').removeClass('hours-12').addClass('hours-24');
			}

			if (typeof jQuery('#time-format').val() == 'string' && jQuery('#time-format').val().match(/^24.*$/) == null) {
				jQuery('#time-format').val(hour_format_24);
			}
		});

		jQuery('#time-separator', '#open-settings-separators').on('change', function() {
			jQuery('option', '#time-format').each(function() {
				if (typeof jQuery(this).data('initial') == 'string') {
					jQuery(this).html(jQuery(this).data('initial').replace(/^(.+)\s+–\s+(.+)$/i, '$1' + jQuery('#time-separator').val().replace(/^"|"$/g, '') + '$2'));
				}
			});
		});
			
		weekdays = (jQuery('#weekdays', '#open-general').val().length) ? jQuery('#weekdays', '#open-general').val().split(',') : [];

		jQuery(':checkbox', jQuery('#weekdays', '#open-general').closest('td')).each(function() {
			jQuery(this).prop('checked', (weekdays.indexOf(String(jQuery(this).attr('value'))) >= 0));
			
			jQuery(this).on('change', function() {
				weekdays = [];
				if (jQuery(this).is(':checked') && jQuery('#weekend-' + jQuery(this).val(), '#open-general').is(':checked')) {
					jQuery('#weekend-' + jQuery(this).val(), '#open-general').prop('checked', false).removeAttr('checked');
					weekend = [];
					jQuery(':checkbox:checked', jQuery('#weekend', '#open-general').closest('td')).each(function() {
						weekend.push(jQuery(this).val());
					});
					jQuery('#weekend', '#open-general').val(weekend.join(','));
				}
				
				jQuery(':checkbox:checked', jQuery('#weekdays', '#open-general').closest('td')).each(function() {
					weekdays.push(jQuery(this).val());
				});
				jQuery('#weekdays', '#open-general').val(weekdays.join(','));
			});
		});
		
		weekend = (jQuery('#weekend', '#open-general').val().length) ? jQuery('#weekend', '#open-general').val().split(',') : [];

		jQuery(':checkbox', jQuery('#weekend', '#open-general').closest('td')).each(function() {
			jQuery(this).prop('checked', (weekend.indexOf(String(jQuery(this).attr('value'))) >= 0));
			
			jQuery(this).on('change', function() {
				weekend = [];
				if (jQuery(this).is(':checked') && jQuery('#weekdays-' + jQuery(this).val(), '#open-general').is(':checked')) {
					jQuery('#weekdays-' + jQuery(this).val(), '#open-general').prop('checked', false).removeAttr('checked');
					weekdays = [];
					jQuery(':checkbox:checked', jQuery('#weekdays', '#open-general').closest('td')).each(function() {
						weekdays.push(jQuery(this).val());
					});
					jQuery('#weekdays', '#open-general').val(weekdays.join(','));
				}
				
				jQuery(':checkbox:checked', jQuery('#weekend', '#open-general').closest('td')).each(function() {
					weekend.push(jQuery(this).val());
				});
				jQuery('#weekend', '#open-general').val(weekend.join(','));
			});
		});
		
		if (jQuery('#structured-data', '#wpbody-content').length && jQuery('#name:visible', '#wpbody-content').length && !jQuery('#name:visible', '#wpbody-content').val().length && jQuery('#place-name:visible', '#wpbody-content').length && jQuery('#place-name:visible', '#wpbody-content').val().length) {
			jQuery('#name', '#wpbody-content').val(jQuery('#place-name', '#wpbody-content').val());
		}

		jQuery('#structured-data', '#wpbody-content').on('change', function() {
			open_synchronization(this);
		});

		jQuery('#google-sync', '#wpbody-content').on('change', function() {
			open_synchronization(this);
		});

		jQuery(':input', jQuery('.google-sync:eq(0)', jQuery('.google-sync:eq(0)', '#wpbody-content'))).on('change', function() {
			if (!jQuery('#google-sync-regular').is(':checked') && !jQuery('#google-sync-special').is(':checked')) {
				if (jQuery('#google-sync-regular').is(':focus')) {
					jQuery('#google-sync-special').prop('checked', true).trigger('focus');
					jQuery('#google-sync').val('2').prop('checked', true);
					jQuery('.description:eq(0)', jQuery(this).closest('.google-sync')).slideDown(300);
					return;
				}

				jQuery('#google-sync-regular').prop('checked', true).trigger('focus');
				jQuery('#google-sync').val('1').prop('checked', true);
				jQuery('.description:eq(0)', jQuery(this).closest('.google-sync')).slideUp(300);
				return;
			}
			
			jQuery('#google-sync').val((jQuery('#google-sync-regular').is(':checked')) ? ((jQuery('#google-sync-special').is(':checked')) ? '3' : '1') : ((jQuery('#google-sync-special').is(':checked')) ? '2' : '1')).prop('checked', true);

			if (jQuery('#google-sync-special').is(':checked')) {
				jQuery('.description:eq(0)', jQuery(this).closest('.google-sync')).slideDown(300);
				return;
			}

			jQuery('.description:eq(0)', jQuery(this).closest('.google-sync')).slideUp(300);
			return;
		});
		
		jQuery('#timezone').on('click', function (event) {
			event.preventDefault();
			document.location.href = jQuery('#timezone').siblings('a').attr('href');
			return false;
		});

		jQuery('#sync-button').on('click', function (event) {
			event.preventDefault();

			data = {
				action: 'we_are_open_admin_ajax',
				type: 'sync',
				structured_data: (document.getElementById('structured-data').checked && (typeof document.getElementById('structured-data').value == 'number' || typeof document.getElementById('structured-data').value == 'string' && document.getElementById('structured-data').value.match(/^-?\d+$/) != null)) ? parseInt(document.getElementById('structured-data').value) : 0, 
				google_sync: (document.getElementById('google-sync') != null && document.getElementById('google-sync').checked && (typeof document.getElementById('google-sync').value == 'number' || typeof document.getElementById('google-sync').value == 'string' && document.getElementById('google-sync').value.match(/^-?\d+$/) != null)) ? parseInt(document.getElementById('google-sync').value) : 0, 
				name: (document.getElementById('structured-data').checked && document.getElementById('name').value.length) ? document.getElementById('name').value : null,
				address: (document.getElementById('structured-data').checked && document.getElementById('address').value.length) ? document.getElementById('address').value : null,
				telephone: (document.getElementById('structured-data').checked && document.getElementById('telephone').value.length) ? document.getElementById('telephone').value : null,
				business_type: (document.getElementById('structured-data').checked && document.getElementById('business-type').value.length) ? document.getElementById('business-type').value : null,
				price_range: (document.getElementById('structured-data').checked && document.getElementById('price-range').value.length) ? parseInt(document.getElementById('price-range').value) : null,
				logo: (document.getElementById('structured-data').checked && document.getElementById('logo-image-id').value.length) ? parseInt(document.getElementById('logo-image-id').value) : null,
				nonce: jQuery('#opening-hours-settings').data('nonce')
			};
			
			
			jQuery.post(we_are_open_admin_ajax.url, data, function(response) {
				if (!response.success) {
					if (typeof response.message == 'object' || typeof response.message == 'string') {
						open_message(response.message, 'error');
					}

					return;
				}

				open_message(response.message, 'success');
			}, 'json');

			return;
		});
		
		jQuery('#structured-data-preview').on('click', function (event) {
			event.preventDefault();
			if (jQuery('#open-overlay').length) {
				jQuery('#open-overlay').remove();
			}
			
			jQuery('#structured-data-preview').after('<div id="open-overlay"></div>');
			jQuery('#open-overlay').on('click', function(event) {
				if (jQuery(event.target).attr('id') == 'open-overlay') {
					jQuery(this).fadeOut(300, function() { jQuery(this).remove(); });
				}
			});
			
			jQuery('#open-overlay').append('<div id="open-close" class="close"><span class="dashicons dashicons-no" title="Close"></span></div><pre id="open-structured-data"></pre>');

			jQuery('#open-close').on('click', function() {
				jQuery('#open-overlay').fadeOut(300, function() { jQuery('#open-overlay').remove(); });
			});
			
			data = {
				action: 'we_are_open_admin_ajax',
				type: 'structured_data',
				structured_data: (document.getElementById('structured-data').checked && (typeof document.getElementById('structured-data').value == 'number' || typeof document.getElementById('structured-data').value == 'string' && document.getElementById('structured-data').value.match(/^-?\d+$/) != null)) ? parseInt(document.getElementById('structured-data').value) : 0,
				name: (document.getElementById('structured-data').checked && document.getElementById('name').value.length) ? document.getElementById('name').value : null,
				address: (document.getElementById('structured-data').checked && document.getElementById('address').value.length) ? document.getElementById('address').value : null,
				telephone: (document.getElementById('structured-data').checked && document.getElementById('telephone').value.length) ? document.getElementById('telephone').value : null,
				business_type: (document.getElementById('structured-data').checked && document.getElementById('business-type').value.length) ? document.getElementById('business-type').value : null,
				price_range: (document.getElementById('structured-data').checked && document.getElementById('price-range').value.length) ? parseInt(document.getElementById('price-range').value) : null,
				logo: (document.getElementById('structured-data').checked && document.getElementById('logo-image-id').value.length) ? parseInt(document.getElementById('logo-image-id').value) : null,
				nonce: jQuery('#opening-hours-settings').data('nonce')
			};

			jQuery.post(we_are_open_admin_ajax.url, data, function(response) {
				if (!response.success) {
					jQuery(this).fadeOut(300, function() { jQuery(this).remove(); });
					return;
				}

				jQuery('#open-structured-data').html(response.data);
				open_syntax_highlight(jQuery('#open-structured-data'));
			}, 'json');
		});
		
		jQuery('#google-data-preview').on('click', function (event) {
			event.preventDefault();
			if (jQuery('#open-overlay').length) {
				jQuery('#open-overlay').remove();
			}
			
			jQuery('#google-data-preview').after('<div id="open-overlay"></div>');
			jQuery('#open-overlay').on('click', function(event) {
				if (jQuery(event.target).attr('id') == 'open-overlay') {
					jQuery(this).fadeOut(300, function() { jQuery(this).remove(); });
				}
			});
			
			jQuery('#open-overlay').append('<div id="open-close" class="close"><span class="dashicons dashicons-no" title="Close"></span></div><pre id="open-google-data"></pre>');

			jQuery('#open-close').on('click', function() {
				jQuery('#open-overlay').fadeOut(300, function() { jQuery('#open-overlay').remove(); });
			});
			
			data = {
				action: 'we_are_open_admin_ajax',
				type: 'google_data_preview',
				nonce: jQuery('#opening-hours-settings').data('nonce')
			};

			jQuery.post(we_are_open_admin_ajax.url, data, function(response) {
				if (!response.success || !response.data.length) {
					jQuery(this).fadeOut(300, function() { jQuery(this).remove(); });
				}

				jQuery('#open-google-data').html(response.data);
				open_syntax_highlight(jQuery('#open-google-data'));
			}, 'json');
		});
		
		jQuery('#separators-button').on('click', function (event) {
			event.preventDefault();

			data = {
				action: 'we_are_open_admin_ajax',
				type: 'separators',
				time_separator: jQuery('#time-separator').val(),
				time_group_separator: jQuery('#time-group-separator').val(),
				day_separator: jQuery('#day-separator').val(),
				day_range_separator: jQuery('#day-range-separator').val(),
				day_range_suffix: jQuery('#day-range-suffix').val(),
				day_range_suffix_special: jQuery('#day-range-suffix-special').val(),
				closed_text: jQuery('#closed-text').val(),
				hours_24_text: jQuery('#hours-24-text').val(),
				weekdays_text: jQuery('#weekdays-text').val(),
				weekend_text: jQuery('#weekend-text').val(),
				everyday_text: jQuery('#everyday-text').val(),
				midday_text: jQuery('#midday-text').val(),
				midnight_text: jQuery('#midnight-text').val(),
				nonce: jQuery('#opening-hours-settings').data('nonce')
			};
			
			jQuery.post(we_are_open_admin_ajax.url, data, function(response) {
				if (!response.success) {
					if (typeof response.message == 'object' || typeof response.message == 'string') {
						open_message(response.message, 'error');
					}

					return;
				}

				open_message(response.message, 'success');
			}, 'json');
		});
		
		jQuery('#google-credentials-help').on('click', function (event) {
			event.preventDefault();

			if (jQuery('#google-credentials-steps').is(':visible')) {
				jQuery('#google-credentials-steps').slideUp(300);
				return;
			}

			jQuery('#google-credentials-steps').slideDown(300);
		});
		
		jQuery('#google-credentials-button').on('click', function () {
			existing_button = jQuery('#google-credentials-button').html();
			jQuery('#google-credentials-button').html('Saving&hellip;');
			data = {
				action: 'we_are_open_admin_ajax',
				type: 'google_business_credentials',
				api_key: jQuery('#api-key').val(),
				place_id: jQuery('#place-id').val(),
				nonce: jQuery('#opening-hours-settings').data('nonce')
			};

			jQuery.post(we_are_open_admin_ajax.url, data, function(response) {
				if (!response.success) {
					if (typeof response.message == 'object' || typeof response.message == 'string') {
						open_message(response.message, 'error');
					}
					
					jQuery('#google-credentials-button').html('Retry');

					return;
				}

				jQuery('#google-credentials-button').html('Saved');
				
				if (typeof response.google_data_exists == 'boolean' && response.google_data_exists && jQuery('#google-data-preview').is(':hidden')) {
					jQuery('#google-data-preview').fadeIn(300);
				}
				else if ((typeof response.google_data_exists != 'boolean' || typeof response.google_data_exists == 'boolean' && !response.google_data_exists) && jQuery('#google-data-preview').is(':visible')) {
					jQuery('#google-data-preview').fadeOut(300);
				}
				
				if (response.business_name == null || response.business_name != null && !response.business_name.length) {
					jQuery('#place-name').val('');
					
					if (jQuery('#place-name').is(':visible')) {
						jQuery('#place-name').closest('.google-data').hide();
					}
				}
				else if (response.business_name != null && response.business_name.length) {
					if (jQuery('#place-name').is(':hidden')) {
						jQuery('#place-name').closest('.google-data').show();
					}
					
					jQuery('#place-name').val(response.business_name);
				}
				
				if (typeof response.message == 'string') {
					open_message(response.message);
				}
				
				setTimeout(function() { jQuery('#google-credentials-button').html(existing_button); }, 1200);
			}, 'json');
		});

		jQuery('#custom-styles-button').on('click', function () {
			existing_button = jQuery('#custom-styles-button').html();
			jQuery('#custom-styles-button').html('Saving&hellip;');
			data = {
				action: 'we_are_open_admin_ajax',
				type: 'custom_styles',
				custom_styles: jQuery('#custom-styles').val(),
				nonce: jQuery('#opening-hours-settings').data('nonce')
			};

			jQuery.post(we_are_open_admin_ajax.url, data, function(response) {
				if (!response.success) {
					if (typeof response.message == 'object' || typeof response.message == 'string') {
						open_message(response.message, 'error');
					}

					jQuery('#custom-styles-button').html('Retry');
					return;
				}

				jQuery('#custom-styles-button').html('Saved');
				setTimeout(function() { jQuery('#custom-styles-button').html(existing_button); }, 1200);
			}, 'json');
		});

		jQuery('a[href*="#"]', '#shortcodes').on('click', function(event) {
			event.preventDefault();
			if (jQuery(jQuery(this).attr('href'), '#shortcodes').length) {
				jQuery([document.documentElement, document.body]).animate({
					scrollTop: jQuery(jQuery(this).attr('href'), '#shortcodes').offset().top - 35
				}, 150);
				return;
			}
			
			if (jQuery(jQuery(this).attr('href')).length && jQuery(jQuery(this).attr('href')).hasClass('section')) {
				section = jQuery(this).attr('href').replace(/^#/, '');
				
				jQuery('.nav-tab', jQuery('nav:eq(0)', '#wpbody-content')).each(function(section_index) {
					if (section != null && jQuery(this).attr('href') != '#' + section || section == null && section_index > 0) {
						jQuery(this).removeClass('nav-tab-active');
						return;
					}

					if (jQuery(this).attr('href') == '#' + section || section == null && section_index == 0) {
						jQuery(this).addClass('nav-tab-active');
						return;
					}
				});

				jQuery('.section', '#wpbody-content').each(function() {
					if (section == jQuery(this).attr('id')) {
						if (jQuery(this).hasClass('hide')) {
							jQuery(this).removeClass('hide');
							return;
						}
						return;
					}

					if (!jQuery(this).hasClass('hide')) {
						jQuery(this).addClass('hide');
						return;
					}
				});
				
				data = {
					action: 'we_are_open_admin_ajax',
					type: 'section',
					section: (typeof section == 'string' && section.match(/^general$/i) == null) ? section : null,
					nonce: jQuery('#opening-hours-settings').data('nonce')
				};
				
				jQuery.post(we_are_open_admin_ajax.url, data, function(response) {
					if (!response.success) {
						return;
					}

					if (document.getElementById('opening-hours-settings') != null && document.getElementById('opening-hours-settings').querySelector(':scope .plugin-notification') != null && document.querySelector('.nav-tab-wrapper .general') != null && (data.section != null) == document.getElementById('opening-hours-settings').querySelector(':scope .plugin-notification').classList.contains('active')) {
						document.getElementById('opening-hours-settings').querySelector(':scope .plugin-notification').classList.toggle('active');
					}

					if (window.history && window.history.pushState) {
						history.pushState(null, null, '#' + section);
						return;
					}

					location.hash = '#' + section;
					return;
				}, 'json');
						
				setTimeout(function() {
					window.scrollTo(0, 0);
					setTimeout(function() {
						window.scrollTo(0, 0);
						}, 100);
					}, 10);
				return;
			}
		});
		
		jQuery('#clear-cache-button').on('click', function () {
			sessionStorage.removeItem('we_are_open_hours');
			jQuery('#clear-cache-button').html('Clearing&hellip;');
			data = {
				action: 'we_are_open_admin_ajax',
				type: 'clear_cache',
				nonce: jQuery('#opening-hours-settings').data('nonce')
			};

			jQuery.post(we_are_open_admin_ajax.url, data, function(response) {
				if (!response.success) {
					jQuery('#clear-cache-button').html('Retry Clear Cache');
					return;
				}

				jQuery('#clear-cache-button').html('Cleared');
				setTimeout(function() { document.location.href = document.location.href.replace(location.hash, ''); }, 300);
			}, 'json');
		});

		jQuery('#reset-button').on('click', function () {
			if (jQuery('#reset-confirm-text').is(':hidden')) {
				jQuery('#reset-confirm-text').slideDown(300);
				return;
			}
			
			if (!jQuery('#reset-confirm-text').is(':visible') || !jQuery('#reset-notifications').is(':checked') && !jQuery('#reset-opening-hours').is(':checked') && !jQuery('#reset-all').is(':checked')) {
				return;
			}

			data = {
				action: 'we_are_open_admin_ajax',
				type: 'reset',
				reset: {
					notifications: jQuery('#reset-notifications').is(':checked'),
					opening_hours: jQuery('#reset-opening-hours').is(':checked'),
					everything: jQuery('#reset-all').is(':checked')
				},
				nonce: jQuery('#opening-hours-settings').data('nonce')
			};

			jQuery.post(we_are_open_admin_ajax.url, data, function(response) {
				if (!response.success) {
					jQuery('#reset-notifications').prop('checked', false).removeAttr('checked');
					jQuery('#reset-opening-hours').prop('checked', false).removeAttr('checked');
					jQuery('#reset-all').prop('checked', false).removeAttr('checked');
					return;
				}
				
				if (jQuery('#reset-confirm-text').is(':visible')) {
					jQuery('#reset-confirm-text').slideUp(300);
				}
				
				if (data.everything) {
					document.location.href = document.location.href.replace(location.hash, '');
					return;
				}
			}, 'json');
		});

		jQuery('.nav-tab', jQuery('nav:eq(0)', '#wpbody-content')).each(function(tab_index) {
			jQuery(this).on('click', function (event) {
				event.preventDefault();
				open_settings_tab(this);
			});
		});

		setTimeout(function() {
			window.scrollTo(0, 0);
			setTimeout(function() {
				window.scrollTo(0, 0);
				}, 100);
			}, 10);
	}
	
	open_synchronization();
	open_media_image();
	open_syntax_highlight();
		
	return;
}

function open_regular(e) {
	if (!jQuery(e).is('a') && (!jQuery(e).is(':input') || jQuery(e).is(':input') && jQuery(e).attr('type') != 'time')) {
		return;
	}
	
	const notes = (document.getElementById('special-note-new') != null);

	let current_opening_hours = null,
		entry = null,
		open_special = false,
		label = null,
		note = null;

	if (jQuery(e).is(':input')) {
		jQuery(e).on('keyup change blur', function(event) {
			if (!notes && event.type == 'change' && jQuery('#open-notes').length) {
				jQuery('#open-notes').addClass('disabled').addClass('hide');
			}

			if (jQuery(':input[type=time]:eq(0)', jQuery(this).closest('li')).val() == '00:00' && (jQuery(':input[type=time]:eq(1)', jQuery(this).closest('li')).val() == '00:00' || jQuery(':input[type=time]:eq(1)', jQuery(this).closest('li')).val() == '23:59')) {
				jQuery(this).closest('td').addClass('hours-24');
				return;
			}
			
			if (jQuery(this).closest('td').hasClass('hours-24')) {
				jQuery(this).closest('td').removeClass('hours-24');
			}

			return;
		});

		return;
	}

	jQuery(e).on('click', function(event) {
		e = this;
		event.preventDefault();

		if (jQuery(e).hasClass('disabled')) {
			return false;
		}

		current_opening_hours = (typeof sessionStorage.getItem('we_are_open_hours') == 'string') ? JSON.parse(sessionStorage.getItem('we_are_open_hours')) : null;
		entry = jQuery(e).closest('tr');
		open_special = jQuery(entry).closest('form').is('#open-special');
		label = (open_special && jQuery('.label-column :input:eq(0)', entry).length && typeof jQuery('.label-column :input:eq(0)', entry).val() == 'string' && jQuery('.label-column :input:eq(0)', entry).val().length) ? jQuery('.label-column :input:eq(0)', entry).val() : null;
		note = (notes && open_special && jQuery(':input:eq(0)', jQuery('.note', jQuery('.hours-column', entry))).val().length) ? jQuery(':input:eq(0)', jQuery('.note', jQuery('.hours-column', entry))).val() : null;

		if (!notes && !jQuery(e).hasClass('copy') && jQuery('#open-notes').length) {
			jQuery('#open-notes').addClass('disabled').addClass('hide');
		}
		
		if (jQuery(e).hasClass('copy')) {
			if (label == null && note == null && jQuery(e).closest('.hours-column').hasClass('closed')) {
				current_opening_hours = null;
				sessionStorage.removeItem('we_are_open_hours');

				jQuery('.paste', '#opening-hours').each(function() {
					jQuery(this).addClass('disabled');
				});

				return false;
			}

			current_opening_hours = {
				label: label,
				note: note,
				closed: false,
				hours_24: (jQuery(e).closest('.hours-column').hasClass('hours-24')),
				hours: []
			};
			
			if (!jQuery(e).closest('.hours-column').hasClass('hours-24')) {
				if (!jQuery('.base:eq(0)', jQuery(e).closest('.hours-column')).is(':visible') || !jQuery('.base:eq(0) :input:eq(0)', jQuery(e).closest('.hours-column')).val().length || !jQuery('.base:eq(0) :input:eq(1)', jQuery(e).closest('.hours-column')).val().length) {
					current_opening_hours.closed = true;
				}
				else {
					current_opening_hours.hours[0] = [ jQuery('.base:eq(0) :input:eq(0)', jQuery(e).closest('.hours-column')).val(), jQuery('.base:eq(0) :input:eq(1)', jQuery(e).closest('.hours-column')).val() ];
					
					if (jQuery('.extended:eq(0)', jQuery(e).closest('.hours-column')).is(':visible') && jQuery('.extended:eq(0) :input:eq(0)', jQuery(e).closest('.hours-column')).val().length && jQuery('.extended:eq(0) :input:eq(1)', jQuery(e).closest('.hours-column')).val().length) {
						current_opening_hours.hours[1] = [ jQuery('.extended:eq(0) :input:eq(0)', jQuery(e).closest('.hours-column')).val(), jQuery('.extended:eq(0) :input:eq(1)', jQuery(e).closest('.hours-column')).val() ];

						if (jQuery('.extended-2:eq(0)', jQuery(e).closest('.hours-column')).is(':visible') && jQuery('.extended-2:eq(0) :input:eq(0)', jQuery(e).closest('.hours-column')).val().length && jQuery('.extended-2:eq(0) :input:eq(1)', jQuery(e).closest('.hours-column')).val().length) {
							current_opening_hours.hours[2] = [ jQuery('.extended-2:eq(0) :input:eq(0)', jQuery(e).closest('.hours-column')).val(), jQuery('.extended-2:eq(0) :input:eq(1)', jQuery(e).closest('.hours-column')).val() ];
						}
					}
				}	
			}

			jQuery('.paste', '#opening-hours').each(function() {
				if ((label != null || note != null) && jQuery(e).closest('.hours-column').hasClass('closed') && jQuery(this).closest('form').is('#open-regular')) {
					if (!jQuery(this).hasClass('disabled')) {
						jQuery(this).addClass('disabled');
					}

					return;
				}

				if (!jQuery(this).hasClass('disabled')) {
					return;
				}

				jQuery(this).removeClass('disabled');
			});
			
			sessionStorage.setItem('we_are_open_hours', JSON.stringify(current_opening_hours));
			return false;
		}
		
		if (jQuery(e).hasClass('paste')) {
			if (current_opening_hours == null) {
				return false;
			}

			e = jQuery(e).closest('.hours-column');

			if (open_special) {
				jQuery('.label-column :input:eq(0)', entry).val((typeof current_opening_hours.label == 'string' && current_opening_hours.label.length) ? current_opening_hours.label : '');

				if (notes) {
					jQuery(':input:eq(0)', jQuery('.note', jQuery('.hours-column', entry))).val((typeof current_opening_hours.note == 'string' && current_opening_hours.note.length) ? current_opening_hours.note : '');
				}
			}

			if (current_opening_hours.closed) {
				if (jQuery(e).hasClass('closed')) {
					return false;
				}

				jQuery('.dashicons-minus', e).removeClass('dashicons-minus').addClass('dashicons-plus');
				jQuery('.base:eq(0)', e).hide();
				jQuery('.extended:eq(0)', e).hide();
				jQuery('.extended-2:eq(0)', e).hide();
				jQuery('.closed:eq(0)', e).show();
				jQuery(e).removeClass('hours-24').addClass('closed');
				jQuery('.base:eq(0) :input:eq(0)', e).val('');
				jQuery('.base:eq(0) :input:eq(1)', e).val('');

				return false;
			}
			
			if (jQuery(e).hasClass('closed')) {
				jQuery(e).removeClass('closed');
				jQuery('.closed:eq(0)', e).hide();
			}

			if (current_opening_hours.hours_24) {
				if (jQuery('.hours', e).hasClass('hours-24')) {
					return false;
				}

				jQuery('.dashicons-minus', e).removeClass('dashicons-minus').addClass('dashicons-plus');
				jQuery('.closed:eq(0)', e).hide();
				jQuery('.extended:eq(0)', e).hide();
				jQuery('.extended-2:eq(0)', e).hide();

				if (jQuery('.base:eq(0)', e).is(':hidden')) {
					jQuery('.base:eq(0)', e).show();
				}

				jQuery(e).addClass('hours-24');

				jQuery('.base:eq(0) :input:eq(0)', e).val('00:00');
				jQuery('.base:eq(0) :input:eq(1)', e).val('00:00');
				
				return false;
			}
		
			jQuery('.dashicons-minus', jQuery('.closed:eq(0)', e)).removeClass('dashicons-minus').addClass('dashicons-plus');
			jQuery(e).removeClass('hours-24');
			
			if (current_opening_hours.hours.length < 3 && jQuery('.extended-2:eq(0)', e).is(':visible')) {
				jQuery('.extended-2:eq(0) :input:eq(0)', e).val('');
				jQuery('.extended-2:eq(0) :input:eq(1)', e).val('');
				jQuery('.extended-2:eq(0)', e).hide();
				jQuery('.dashicons-minus', jQuery('.extended:eq(0)', e)).removeClass('dashicons-minus').addClass('dashicons-plus');
			}
			
			if (current_opening_hours.hours.length < 2 && jQuery('.extended:eq(0)', e).is(':visible')) {
				jQuery('.extended:eq(0) :input:eq(0)', e).val('');
				jQuery('.extended:eq(0) :input:eq(1)', e).val('');
				jQuery('.extended:eq(0)', e).hide();
				jQuery('.dashicons-minus', e).removeClass('dashicons-minus').addClass('dashicons-plus');
			}

			if (jQuery('.base:eq(0)', e).is(':hidden')) {
				jQuery('.base:eq(0)', e).show();
			}
			
			if (current_opening_hours.hours.length >= 2 && jQuery('.extended:eq(0)', e).is(':hidden')) {
				jQuery('.extended:eq(0)', e).show();
				jQuery('.dashicons-minus', jQuery('.base:eq(0)', e)).removeClass('dashicons-plus').addClass('dashicons-minus');
			}

			if (current_opening_hours.hours.length >= 3 && jQuery('.extended-2:eq(0)', e).is(':hidden')) {
				jQuery('.extended-2:eq(0)', e).show();
				jQuery('.dashicons-minus', jQuery('.extended:eq(0)', e)).removeClass('dashicons-plus').addClass('dashicons-minus');
			}
			
			if (current_opening_hours.hours.length >= 1 && typeof current_opening_hours.hours[0] == 'object' && current_opening_hours.hours[0].length == 2) {
				jQuery('.base:eq(0) :input:eq(0)', e).val(current_opening_hours.hours[0][0]);
				jQuery('.base:eq(0) :input:eq(1)', e).val(current_opening_hours.hours[0][1]);
			}
			
			if (current_opening_hours.hours.length >= 2 && typeof current_opening_hours.hours[1] == 'object' && current_opening_hours.hours[1].length == 2) {
				jQuery('.extended:eq(0) :input:eq(0)', e).val(current_opening_hours.hours[1][0]);
				jQuery('.extended:eq(0) :input:eq(1)', e).val(current_opening_hours.hours[1][1]);
			}
			
			if (current_opening_hours.hours.length >= 3 && typeof current_opening_hours.hours[2] == 'object' && current_opening_hours.hours[2].length == 2) {
				jQuery('.extended-2:eq(0) :input:eq(0)', e).val(current_opening_hours.hours[2][0]);
				jQuery('.extended-2:eq(0) :input:eq(1)', e).val(current_opening_hours.hours[2][1]);
			}

			return false;
		}

		if (jQuery(e).hasClass('closed')) {
			jQuery(':input:eq(0)', jQuery(e).closest('li').siblings('.base')).val('');
			jQuery(':input:eq(1)', jQuery(e).closest('li').siblings('.base')).val('');
			jQuery('.base:visible, .extended:visible, .extended-2:visible', jQuery(e).closest('td')).slideUp(300, function() {
				jQuery('.add-subtract-toggle .dashicons-minus', jQuery('.base, .extended', jQuery(e).closest('td'))).removeClass('dashicons-minus').addClass('dashicons-plus');
				jQuery(':input:eq(0)', jQuery('.base, .extended, .extended-2', jQuery(e).closest('td'))).val('');
				jQuery(':input:eq(1)', jQuery('.base, .extended, .extended-2', jQuery(e).closest('td'))).val('');
			});
			jQuery('.closed:eq(0)', jQuery(this).closest('td')).slideDown(300);
			jQuery(this).closest('td').addClass('closed').removeClass('hours-24');

			return false;
		}

		jQuery(e).closest('td').removeClass('closed');

		if (jQuery('.closed:eq(0)', jQuery(e).closest('td')).is(':visible')) {
			jQuery('.closed:eq(0)', jQuery(e).closest('td')).slideUp(300);
		}
		
		if (!jQuery(e).closest('li').hasClass('extended-2')) {
			if ((jQuery(e).closest('li').hasClass('closed') || jQuery(e).hasClass('add-subtract-toggle')) && jQuery(e).closest('li').next().is(':hidden')) {
				jQuery(e).closest('li').next().slideDown(300, function() {
					if (jQuery(e).hasClass('closed-text') || jQuery(e).hasClass('add-subtract-toggle')) {
						jQuery(':input:eq(0)', this).trigger('focus');
					}
					if (!jQuery(e).closest('li').hasClass('closed')) {
						jQuery('.add-subtract-toggle .dashicons', jQuery(this).closest('li').prev()).removeClass('dashicons-plus').addClass('dashicons-minus');
					}
				});
			}
			else {
				if (jQuery(e).closest('li').hasClass('base') && jQuery(e).closest('li').siblings('.extended-2').is(':visible')) {
					jQuery(':input', jQuery(e).closest('li').siblings('.extended-2')).val('');
					jQuery(e).closest('li').siblings('.extended-2').slideUp(300, function() {
						jQuery('.add-subtract-toggle .dashicons', jQuery(this).prev()).removeClass('dashicons-minus').addClass('dashicons-plus');
						jQuery('.add-subtract-toggle .dashicons', this).removeClass('dashicons-minus').addClass('dashicons-plus');
					});
				}
				
				jQuery(':input', jQuery(e).closest('li').next()).val('');
				jQuery(e).closest('li').next().slideUp(300, function() {
						jQuery('.add-subtract-toggle .dashicons', jQuery(this).prev()).removeClass('dashicons-minus').addClass('dashicons-plus');
						jQuery('.add-subtract-toggle .dashicons', this).removeClass('dashicons-minus').addClass('dashicons-plus');
					});
					
				if (jQuery(e).hasClass('add-subtract-toggle')) {
					jQuery(':input:eq(0)', jQuery(e).closest('li')).trigger('focus');
				}
			}
		}
		
		if (jQuery(e).hasClass('hours-24') && !jQuery(e).closest('td').hasClass('hours-24') ) {
			jQuery('.base :input:eq(0)', jQuery(e).closest('td')).val('00:00');
			jQuery('.base :input:eq(1)', jQuery(e).closest('td')).val('00:00');
			jQuery(e).closest('td').addClass('hours-24');

			return false;
		}
		
		if (jQuery('.base :input:eq(0)', jQuery(e).closest('td')).val() == '00:00' && (jQuery('.base :input:eq(1)', jQuery(e).closest('td')).val() == '00:00' || jQuery('.base :input:eq(1)', jQuery(e).closest('td')).val() == '23:59')) {
			jQuery(':input:eq(0)', jQuery('.base, .extended, .extended-2', jQuery(e).closest('td'))).val('');
			jQuery(':input:eq(1)', jQuery('.base, .extended, .extended-2', jQuery(e).closest('td'))).val('');
			jQuery(e).closest('td').removeClass('hours-24');

			return false;
		}

		return false;
	});
	
	return;
}

function open_special(e, event) {
	if (jQuery(e).attr('type') != 'text' && jQuery(e).attr('type') != 'date' && !jQuery(e).is(':checkbox')) {
		return;
	}

	const notes = (document.getElementById('special-note-new') != null);

	let entry = jQuery(e).closest('tr'),
		special_delete = 0,
		html = '';
	
	if (typeof event == 'undefined' || !event) {
		if (jQuery(e).attr('type') == 'text' && (notes && jQuery(e).hasClass('note') || jQuery(e).parent().hasClass('label-column')) && typeof jQuery(e).val() == 'string' && jQuery(e).val().length) {
			jQuery('.copy.disabled', entry).removeClass('disabled');
		}

		jQuery(e).on('change blur', function(event) {
			if (!notes && !jQuery(event.target).is(':checkbox') && event.type == 'change' && jQuery('#open-notes').length) {
				jQuery('#open-notes').addClass('disabled').addClass('hide');
			}

			return open_special(this, true);
		});

		return;
	}

	if (jQuery(e).is(':checkbox')) {
		jQuery('.check-column > :checkbox', '#open-special').each(function() {
			if (jQuery(this).is(':disabled') || !jQuery(this).is(':checked')) {
				return;
			}

			entry = jQuery(this).closest('tr');

			if (typeof jQuery(entry).attr('id') == 'string' && jQuery(entry).attr('id').match(/^special-hours-(new|\d+)$/i) != null && jQuery(':input[type=date]', entry).length) {
				special_delete++;
			}
		});
		
		if (special_delete > 0 && jQuery('#open-delete').hasClass('disabled') && jQuery('tbody > tr', '#open-special').length > 1) {
			jQuery('#open-delete').removeClass('disabled');
			return;
		}
		
		if (special_delete == 0 && !jQuery('#open-delete').hasClass('disabled')) {
			jQuery('#open-delete').addClass('disabled');
		}
	
		return;
	}

	if (jQuery(e).attr('type') == 'text' && jQuery(e).parent().hasClass('label-column')) {
		if (typeof jQuery(e).val() != 'string' || !jQuery(e).val().length) {
			jQuery('.copy', entry).addClass('disabled');
			return;
		}

		jQuery('.copy.disabled', entry).removeClass('disabled');
		return;
	}

	if (jQuery(e).attr('type') == 'date') {
		if (jQuery(e).val().length && jQuery(e).val().match(/^\d{4}-\d{1,2}-\d{1,2}$/) != null && jQuery(e).closest('tr').index() == jQuery('tbody > tr', '#open-special').length - 1) {
			jQuery(':input[type=checkbox]:eq(0)', jQuery(e).closest('tr')).prop('disabled', false).removeAttr('disabled');
			html = jQuery('#special-hours-new')[0].outerHTML;
			html = html.replace(/(id="special-hours-new")/, '$1 style="display: none;"').replace(/\s+(?:hours-24|disabled(?:=["'][\w-]*["'])?)/g, '').replace(/value="[^"]*"/g, 'value=""').replace(/([\b_"\[-])new([\b_"\]-])/g, '$1' + (jQuery('tbody > tr', '#open-special').length - 1) + '$2');
			jQuery(e).closest('tr').after(html);
			e = jQuery('#special-hours-' + (jQuery('tbody > tr', '#open-special').length - 2));
			setTimeout(function(e) { jQuery(e).fadeIn(300, function() {
				jQuery(':input[type=checkbox]:eq(0)', e).prop('disabled', true);
				jQuery(e).removeAttr('style'); });
			}, 400, e);
			open_special(jQuery(':input[type=checkbox]:eq(0)', e));
			open_special(jQuery(':input[type=text]:eq(0)', e));
			open_special(jQuery(':input[type=date]:eq(0)', e));
			jQuery('.extended:eq(0)', e).hide();
			jQuery('.extended-2:eq(0)', e).hide();
			jQuery('a.closed-text, a.closed, a.hours-24, a.copy, a.paste, a.add-subtract-toggle', e).each(function() {
				open_regular(this);
			});
			return;
		}

		if ((!jQuery(e).val().length || jQuery(e).val().match(/^\d{4}-\d{1,2}-\d{1,2}$/) == null) && jQuery('tbody > tr', '#open-special').length > 1 && jQuery(e).closest('tr').index() == jQuery('tbody > tr', '#open-special').length - 2 && (!jQuery(':input[type=date]:eq(0)', jQuery('tbody > tr:last', '#open-special')).val().length || jQuery(':input[type=date]:eq(0)', jQuery('tbody > tr:last', '#open-special')).val().match(/^\d{4}-\d{1,2}-\d{1,2}$/) == null)) {
			jQuery(':input[type=checkbox]:eq(0)', jQuery(e).closest('tr')).prop('checked', false).removeAttr('checked').prop('disabled', true);
			jQuery('tbody > tr:eq(' + (jQuery('tbody > tr', '#open-special').length - ((jQuery('tbody > tr:last', '#open-special').is('#special-hours-new')) ? 2 : 1)) +')', '#open-special').fadeOut(300, function() {
				jQuery(this).remove();
			});
		}
		
		return;
	}
	
	return;
}

function open_synchronization(e) {
	if (typeof e == 'undefined') {
		let e = null;
	}

	if (e != null && jQuery('#structured-data', '#wpbody-content').is(':checked') && jQuery('#google-sync', '#wpbody-content').is(':checked')) {
		if (jQuery(e).is('#google-sync')) {
			jQuery('#structured-data', '#wpbody-content').prop('checked', false).removeAttr('checked');
		}
		else {
			jQuery('#google-sync', '#wpbody-content').prop('checked', false).removeAttr('checked');
		}
	}

	jQuery('.structured-data', '#wpbody-content').each(function() {
		if (jQuery('#structured-data', '#wpbody-content').is(':checked')) {
			jQuery(this).show();
			return;
		}

		jQuery(this).hide();
	});
	
	jQuery('tr.google-sync', '#wpbody-content').each(function() {
		if (jQuery('#google-sync', '#wpbody-content').is(':checked')) {
			jQuery(this).show();
			return;
		}

		jQuery(this).hide();
	});
	
	if (jQuery('#name', '#wpbody-content').length && !jQuery('#name', '#wpbody-content').val().length && jQuery('#place-name', '#wpbody-content').length && jQuery('#place-name', '#wpbody-content').val().length) {
		jQuery('#name', '#wpbody-content').val(jQuery('#place-name', '#wpbody-content').val());
	}

	if (jQuery('#structured-data', '#wpbody-content').is(':checked')) {
		jQuery('#name', '#wpbody-content').trigger('focus');
	}

	return;
}

function open_settings_tab(e) {
	let section = (typeof jQuery(e).attr('href') == 'string') ? jQuery(e).attr('href').replace(/#([\w-]+)/, '$1') : null,
		tab_index = jQuery(e).index('.nav-tab');
	
	if (jQuery('.is-dismissible', '#wpbody-content').length) {
		jQuery('.is-dismissible', '#wpbody-content').remove();
	}

	if (tab_index != jQuery('.nav-tab-active', jQuery('nav:eq(0)', '#wpbody-content')).index('.nav-tab')) {
		jQuery('.nav-tab:not(:eq(' + tab_index + '))', jQuery('nav:eq(0)', '#wpbody-content')).removeClass('nav-tab-active').removeProp('aria-current');
		jQuery('.nav-tab:eq(' + tab_index + ')', jQuery('nav:eq(0)', '#wpbody-content')).addClass('nav-tab-active').prop('aria-current', 'page');
	}
	
	jQuery('.section', '#wpbody-content').each(function(section_index) {
		if (section == null && section_index == 0 || section != null && section == jQuery(this).attr('id')) {
			if (jQuery(this).hasClass('hide')) {
				jQuery(this).removeClass('hide');
			}

			return;
		}

		if (!jQuery(this).hasClass('hide')) {
			jQuery(this).addClass('hide');
			return;
		}
	});
	
	data = {
		action: 'we_are_open_admin_ajax',
		type: 'section',
		section: (typeof section == 'string' && section.match(/^(?:general|setup)$/i) == null) ? section : null,
		nonce: jQuery('#opening-hours-settings').data('nonce')
	};

	jQuery.post(we_are_open_admin_ajax.url, data, function(response) {
		if (!response.success) {
			return;
		}
		
		if (document.getElementById('opening-hours-settings') != null && document.getElementById('opening-hours-settings').querySelector(':scope .plugin-notification') != null && document.querySelector('.nav-tab-wrapper .general') != null && (data.section != null) == document.getElementById('opening-hours-settings').querySelector(':scope .plugin-notification').classList.contains('active')) {
			document.getElementById('opening-hours-settings').querySelector(':scope .plugin-notification').classList.toggle('active');
		}

		if (window.history && window.history.pushState) {
			history.pushState(null, null, '#' + section);
			return;
		}

		location.hash = '#' + section;
		return;
	}, 'json');
			
	setTimeout(function(tab_index) {
		window.scrollTo(0, 0);

		setTimeout(function(tab_index) {
				window.scrollTo(0, 0);
				
				if (tab_index != jQuery('.nav-tab-active', jQuery('nav:eq(0)', '#wpbody-content')).index('.nav-tab')) {
					jQuery('.nav-tab:not(:eq(' + tab_index + '))', jQuery('nav:eq(0)', '#wpbody-content')).removeClass('nav-tab-active').removeProp('aria-current');
					jQuery('.nav-tab:eq(' + tab_index + ')', jQuery('nav:eq(0)', '#wpbody-content')).addClass('nav-tab-active').prop('aria-current', 'page');
				}
			}, 100, tab_index);
		}, 10, tab_index);

	return;
}

function open_message(message, type) {
	if (typeof message != 'string') {
		return;
	}
	
	if (typeof type != 'string') {
		let type = 'success';
	}

	if (message.match(/\b(?:refresh|reload|rafraîchir|d['’]actualiser|aktualisieren)\b/i) != null && message.match(/<a[^>]+>/i) == null) {
		message = message.replace(/\b(refresh|reload|rafraîchir|d['’]actualiser|aktualisieren)\b/gi, '<a href="' + document.location.href.replace(/#.*/i, '') + '">$1</a>');
	}
	
	let e = (jQuery('#open-settings').length) ? jQuery('#open-settings') : jQuery('#open'),
		html = '<div id="open-message" class="notice ' + type + ' notice-' + type + ' visible is-dismissible">\n'
			+ '<p><strong>' + message + '</strong></p>\n'
			+ '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>\n'
			+ '</div>';
	
	if (jQuery('#open-message').length) {
		jQuery('#open-message').remove();
	}
	
	jQuery('h1:eq(0)').after(html);
	jQuery('button.notice-dismiss:eq(0)', '#open-message').on('click', function () {
		jQuery('#open-message').remove();
	});
	
	setTimeout(function() {
		if (jQuery('#open-message').length) {
			jQuery('#open-message').slideUp(300, function() { jQuery(this).remove(); });
		}
	}, 15000);

	return;
}

function open_media_image() {
	let data = {},
		image_id = null
		image_frame = null,
		selection = null,
		gallery_ids = new Array(),
		my_index = 0;
	
	jQuery('#logo-image-delete').on('click', function(event) {
		data = {
			action: 'we_are_open_admin_ajax',
			type: 'logo_delete',
			nonce: jQuery('#opening-hours-settings').data('nonce')
		};
	
		jQuery.post(we_are_open_admin_ajax.url, data, function(response) {
			if (response.success) {
				jQuery('#logo-image-id').val('');
				jQuery('img', '#logo-image-preview').remove();
				jQuery('#logo-image-preview').html('');
				jQuery('#logo-image').html(jQuery('.dashicons', '#logo-image')[0].outerHTML + ' ' + jQuery('#logo-image').data('set-text'));
				jQuery('.logo-image:eq(0)').addClass('empty');
				jQuery('.delete', '.logo-image:eq(0)').hide();
				jQuery('#logo-image-row').addClass('empty');
			}
		}, 'json');
		
		return;		
	});

	jQuery('#logo-image, #logo-image-preview').on('click', function(event) {
		event.preventDefault();
		
		if (typeof wp == 'undefined') {
			return;
		}
				
		if (image_frame) {
			image_frame.open();
		}
		
		image_frame = wp.media({
			title: 'Select Media',
			multiple: false,
			library: {
				type: 'image',
			}
		});
		
		image_frame.on('select', function() {
			selection = image_frame.state().get('selection');
			gallery_ids = new Array();
			my_index = 0;
			
			selection.each(function(attachment) {
				gallery_ids[my_index] = attachment['id'];
				my_index++;
			});
			
			image_id = gallery_ids.join(",");
			jQuery('#logo-image-id').val(image_id);
			
			data = {
				action: 'we_are_open_admin_ajax',
				type: 'logo',
				id: image_id,
				nonce: jQuery('#opening-hours-settings').data('nonce')
			};
			
			jQuery.post(we_are_open_admin_ajax.url, data, function(response) {
				if (response.success) {
					jQuery('#logo-image-row').removeClass('empty');
					jQuery('.logo-image.empty').removeClass('empty');
					jQuery('#logo-image-preview')
						.html(response.image)
						.addClass('image');
					jQuery('#logo-image').html(jQuery('.dashicons', '#logo-image')[0].outerHTML + ' ' + jQuery('#logo-image').data('replace-text'));
					jQuery('.delete', '.logo-image:eq(0)').css('display', 'inline-block');
				}
			}, 'json');
		});
		
		image_frame.on('open', function() {
			let selection = image_frame.state()
				.get('selection'),
				ids = jQuery('#logo-image-id').val().split(',');
				
			ids.forEach(function(id) {
				let attachment = wp.media.attachment(id);
				attachment.fetch();
				selection.add(attachment ? [attachment] : []);
			});
		});
		
		image_frame.open();
	});
	
	return;
}

function open_syntax_highlight(e) {
	if (typeof e == 'undefined') {
		let e = jQuery('#open-data');
	}
	
	if (!jQuery(e).length || jQuery('span', jQuery(e)).length) {
		return;
	}
	
	let json = e.html().replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

	jQuery(e)
		.html(json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function(match) {
			let class_name = 'number';
			if (/^"/.test(match)) {
				if (/:$/.test(match)) {
					class_name = 'key';
				}
				else {
					class_name = 'string';
				}
			}
			else if (/true|false/.test(match)) {
				class_name = 'boolean';
			}
			else if (/null/.test(match)) {
				class_name = 'null';
			}

			return '<span class="' + class_name + '">' + match + '</span>';
		}));
		
	if (jQuery(e).attr('id').match(/structured[_-]?data/i) != null) {
		jQuery(e).html(jQuery(e).html().replace(/(<span\s+class="key">"image":<\/span>\s+<span\s+class="boolean)(">)(false)(<\/span>)/i, '$1 error$2$3 <span class="dashicons dashicons-warning" title="Required"></span>$4'));
	}
	
	return;
}

jQuery(document).ready(function($) {
	open_admin();
	if (window.history && window.history.pushState) {
		jQuery(window).on('popstate', function() {
			open_admin(true);
		});
	}
});

jQuery(window).on('keydown', function(event) {
	if (document.querySelector('.opening-hours.wrap') == null || document.getElementById('opening-hours-settings') != null && document.getElementById('opening-hours-settings').getAttribute('data-no-hover')) {
		return;
	}

	if (jQuery('.button-primary').is(':visible') && (event.ctrlKey || event.metaKey)) {
		if (String.fromCharCode(event.which).toLowerCase() == 's') {
			event.preventDefault();
			jQuery('.button-primary:visible:eq(0)').trigger('click');
			return false;
		}
	}
	
	if (document.activeElement.classList.contains('nav-tab-active')) {
		if ((event || window.event).keyCode != 37 && (event || window.event).keyCode != 39) {
			return;
		}
		
		if ((event || window.event).keyCode == 37 && document.activeElement.previousElementSibling == null || (event || window.event).keyCode == 39 && document.activeElement.nextElementSibling == null) {
			return;
		}
		
		if ((event || window.event).keyCode == 37) {
			open_settings_tab(document.activeElement.previousElementSibling);
			jQuery(document.activeElement.previousElementSibling).trigger('focus');
			return;
		}

		open_settings_tab(document.activeElement.nextElementSibling);
		jQuery(document.activeElement.nextElementSibling).trigger('focus');
		return;
	}

	return;
});
