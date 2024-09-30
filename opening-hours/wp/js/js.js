// JavaScript Document

function we_are_open(action, index) {
	if (typeof action == 'undefined') {
		var action = 'init';
	}
	
	if (typeof index == 'object' && index != null) {
		var indexes = index;
		index = null;
	}
	else if (typeof index != 'number') {
		var index = null;
	}
	
	var data = {},
		ajax_data = {},
		html = '',
		e = null,
		indexes = (typeof indexes == 'object') ? indexes : [],
		elements = [],
		reload_check = false;
	
	if (index != null && jQuery('.opening-hours.update, .opening-hours-conditional.update').length) {
		e = jQuery('.opening-hours.update, .opening-hours-conditional.update').eq(index);
		data = (typeof e == 'object' && e != null && jQuery(e).length && typeof jQuery(e).data('data') == 'object' && jQuery(e).data('data') != null) ? jQuery(e).data('data') : {};
	}

	switch (action)
	{
	case 'init':
		if (!jQuery('.opening-hours.update, .opening-hours-conditional.update').length) {
			return;
		}
		
		indexes = [];
		
		jQuery('.opening-hours.update, .opening-hours-conditional.update').each(function(index) {
			indexes[index] = null;
			
			if (typeof jQuery(this).data('data') != 'object' || jQuery(this).data('data') == null) {
				return;
			}

			if (!reload_check && typeof jQuery(this).data('data') == 'object' && (jQuery(this).hasClass('reload') || typeof jQuery(this).data('data').reload == 'boolean' && jQuery(this).data('data').reload)) {
				reload_check = true;
				data = jQuery(this).data('data');
				setTimeout(function() { we_are_open('reload', index); }, data.change * 1000);
				return false;
			}
			
			data = jQuery(this).data('data');
			
			if (jQuery(this).hasClass('opening-hours-conditional')) {
				if (typeof data.immediate == 'boolean' && data.immediate) {
					indexes[index] = 1;
				}
				else if (typeof data.change == 'number' && data.change > 0 && data.change <= 129600) {
					indexes[index] = (data.change >= 3600) ? 3600000 : data.change * 1000;
				}
				
				return;
			}
			
			if (typeof data.immediate == 'boolean' && data.immediate) {
				indexes[index] = 1;
			}
			else if (typeof data.change == 'number' && data.change > 0 && data.change <= 86400) {
				indexes[index] = data.change * 1000;
			}

			return;
		});
		
		if (reload_check) {
			return;
		}
		
		for (index in indexes) {
			if (indexes[index] != null) {
				setTimeout(function() { we_are_open('update', indexes); }, Math.min.apply(null, indexes.filter(Boolean)));
				break;
			}
		}
		
		return;
	case 'reload':
		if (e == null || index == null || !jQuery('.opening-hours.update, .opening-hours-conditional.update').length || index > jQuery('.opening-hours.update, .opening-hours-conditional.update').length - 1 || typeof data != 'object' || (!jQuery(e).hasClass('reload') && (typeof data.reload != 'boolean' || typeof data.reload == 'boolean' && !data.reload))) {
			return;
		}
	
		document.location.reload();
		return;
	case 'update':
		if (typeof indexes != 'object' || indexes == null || !indexes.length || !jQuery('.opening-hours.update, .opening-hours-conditional.update').length || indexes.length != jQuery('.opening-hours.update, .opening-hours-conditional.update').length) {
			return;
		}

		jQuery('.opening-hours.update, .opening-hours-conditional.update').each(function(index) {
			if (!(index in indexes) || indexes[index] == null) {
				return;
			}
			
			data = (typeof jQuery(this).data('data') == 'object' && jQuery(this).data('data') != null) ? jQuery(this).data('data') : {};
			
			if (!reload_check && jQuery(this).hasClass('reload') || typeof data.reload == 'boolean' && data.reload) {
				reload_check = true;
				we_are_open('reload', index);
				return false;
			}

			if (jQuery(this).hasClass('opening-hours-conditional')) {
				elements[index] = {
					action: 'update',
					classes: (typeof data.classes == 'object') ? data.classes : [],
					parameters: null,
					content: null
				};
				return;
			}
			
			elements[index] = {
				action: 'refresh',
				classes: jQuery(this).attr('class').split(' '),
				parameters: (typeof data.parameters == 'object') ? data.parameters : {},
				content: (typeof data.content == 'string') ? data.content : null
			};
		});
		
		if (reload_check) {
			return;
		}
		
		ajax_data = {
			action: 'we_are_open_wp_ajax',
			type: action,
			elements: elements
		};
		 
		jQuery.post(we_are_open_wp_ajax.url, ajax_data, function(response) {
			data = {};
			html = '';
			
			if (response.success) {
				indexes = [];
				
				jQuery('.opening-hours.update, .opening-hours-conditional.update').each(function(index) {
					indexes[index] = null;
					
					if (!(index in response.elements)) {
						return;
					}
					
					if (jQuery(this).hasClass('opening-hours-conditional')) {
						if (typeof jQuery(this).data('data') != 'object' || typeof jQuery(this).data('data') == 'object' && jQuery(this).data('data') == null) {
							return;
						}
							
						data = jQuery(this).data('data');
						
						if (typeof data.html == 'undefined') {
							return;
						}
						
						html = (typeof data.html == 'string' && data.html.length) ? data.html : null;
		
						data.change = parseInt(response.elements[index].seconds_to_change);
						data.closed_now = response.closed_now;
						data.content = (typeof response.content == 'string') ? response.content : null;
						data.open_now = response.open_now;
						
						if ((data.closed != response.closed_now || data.open != response.open_now) && (html == null || !data.remove_html)) {
							if (data.remove_html) {
								data.html = jQuery(this).html();
								jQuery(this).html('');
							}
							
							if (data.hide && !jQuery(this).hasClass('hide')) {
								jQuery(this).removeClass('show');
								jQuery(this).addClass('hide');
							}
						}
						else if ((data.open == response.open_now || data.closed == response.closed_now) && (html != null || !data.remove_html)) {
							if (data.remove_html) {
								data.html = null;
								jQuery(this).html('');
							}
							
							jQuery(this).data('data', data);

							if (data.hide && jQuery(this).hasClass('hide')) {
								jQuery(this).removeClass('hide');
								jQuery(this).addClass('show');
							}
						}
						
						if (typeof data.change == 'number' && data.change > 0 && data.change <= 86400) {
							indexes[index] = (data.change >= 3600) ? 3600000 : data.change * 1000;
						}
						
						return;
					}
					
					if (jQuery(this).hasClass('opening-hours')) {
						data = (typeof response.elements[index] == 'object') ? response.elements[index] : {};
						jQuery(this)
							.data('data', {
								change: data.seconds_to_change,
								closed_now: !response.open_now,
								content: (typeof data.html == 'string') ? data.content.replace(/\\(\')/gi, '$1') : null,
								parameters: data.parameters,
								open_now: response.open_now,
								reload: data.reload
							})
							.html((typeof data.html == 'string') ? data.html.replace(/\\(\')/gi, '$1') : '');
							
							if (response.open_now) {
								jQuery(this).removeClass('closed-now').addClass('open-now');
							}
							else {
								jQuery(this).removeClass('open-now').addClass('closed-now');
							}
							
						if (typeof data.seconds_to_change == 'number' && data.seconds_to_change > 0 && data.seconds_to_change <= 86400) {
							indexes[index] = data.seconds_to_change * 1000;
						}
						
						return;
					}
						
					if (typeof jQuery(this).data('data') != 'object' || jQuery(this).data('data') == null) {
						return;
					}
			
					if (!reload_check && typeof jQuery(this).data('data') == 'object' && (jQuery(this).hasClass('reload') || typeof jQuery(this).data('data').reload == 'boolean' && jQuery(this).data('data').reload)) {
						reload_check = true;
						data = jQuery(this).data('data');
						setTimeout(function() { we_are_open('reload', index); }, data.change * 1000);
						return false;
					}
			
					return;
				});
				
				if (reload_check) {
					return;
				}

				for (index in indexes) {
					if (indexes[index] != null) {
						setTimeout(function() { we_are_open('update', indexes); }, Math.min.apply(null, indexes.filter(Boolean)));
						break;
					}
				}
				
			}
			
		}, 'json');

		return;
	}
	return;
}

jQuery(document).ready(function($){
	we_are_open();
	return;
});
