// JavaScript Document

const we_are_open_update_selector =
		'.opening-hours.update, .opening-hours-conditional.update',
	we_are_open_errors_selector =
		'.opening-hours.errors, .opening-hours-conditional.errors',
	we_are_open_element_data = new WeakMap();

const we_are_open_error = (error, e = null) => {
	if (
		(e == null &&
			document.querySelector(we_are_open_errors_selector) == null) ||
		(e != null && !e.classList.contains('errors'))
	) {
		return;
	}

	console.error(error);
};

const we_are_open = (action = 'init', index = null) => {
	let element_index = typeof index == 'number' ? index : null,
		indexes = Array.isArray(index) ? index : [],
		update_elements = document.querySelectorAll(
			we_are_open_update_selector,
		),
		elements = [],
		reload_check = false,
		current_element = null,
		data = {},
		e = null,
		i = 0;

	update_elements.forEach(e => {
		if (we_are_open_element_data.has(e)) {
			return;
		}

		let raw = e.dataset.data;

		if (raw == undefined || raw == '') {
			we_are_open_element_data.set(e, raw == undefined ? null : '');
			return;
		}

		try {
			we_are_open_element_data.set(e, JSON.parse(raw));
		} catch (error) {
			we_are_open_element_data.set(e, raw);
		}
	});

	if (element_index != null && update_elements.length) {
		current_element = update_elements[element_index] || null;

		if (current_element != null) {
			data =
				typeof we_are_open_element_data.get(current_element) ==
					'object' &&
				we_are_open_element_data.get(current_element) != null
					? we_are_open_element_data.get(current_element)
					: {};
		}
	}

	switch (action) {
		case 'init':
			if (!update_elements.length) {
				return;
			}

			indexes = [];

			for (i = 0; i < update_elements.length; i++) {
				e = update_elements[i];
				indexes[i] = null;
				data = we_are_open_element_data.get(e);

				if (typeof data != 'object' || data == null) {
					continue;
				}

				if (
					!reload_check &&
					(e.classList.contains('reload') ||
						(typeof data.reload == 'boolean' && data.reload))
				) {
					reload_check = true;
					setTimeout(
						() => we_are_open('reload', i),
						data.change * 1000,
					);
					break;
				}

				if (e.classList.contains('opening-hours-conditional')) {
					if (typeof data.immediate == 'boolean' && data.immediate) {
						indexes[i] = 1;
					} else if (
						typeof data.change == 'number' &&
						data.change > 0 &&
						data.change <= 129600
					) {
						indexes[i] =
							data.change >= 3600 ? 3600000 : data.change * 1000;
					}

					continue;
				}

				if (typeof data.immediate == 'boolean' && data.immediate) {
					indexes[i] = 1;
				} else if (
					typeof data.change == 'number' &&
					data.change > 0 &&
					data.change <= 86400
				) {
					indexes[i] = data.change * 1000;
				}
			}

			if (reload_check) {
				return;
			}

			if (indexes.some(interval => interval != null)) {
				setTimeout(
					() => we_are_open('update', indexes),
					Math.min(...indexes.filter(Boolean)),
				);
			}

			return;
		case 'reload':
			if (
				current_element == null ||
				element_index == null ||
				!update_elements.length ||
				element_index > update_elements.length - 1 ||
				typeof data != 'object' ||
				(!current_element.classList.contains('reload') &&
					(typeof data.reload != 'boolean' ||
						(typeof data.reload == 'boolean' && !data.reload)))
			) {
				return;
			}

			document.location.reload();
			return;
		case 'update':
			if (
				!Array.isArray(indexes) ||
				!indexes.length ||
				!update_elements.length ||
				indexes.length != update_elements.length
			) {
				return;
			}

			for (i = 0; i < update_elements.length; i++) {
				e = update_elements[i];

				if (!(i in indexes) || indexes[i] == null) {
					continue;
				}

				data = we_are_open_element_data.get(e);
				data = typeof data == 'object' && data != null ? data : {};

				if (
					(!reload_check && e.classList.contains('reload')) ||
					(typeof data.reload == 'boolean' && data.reload)
				) {
					reload_check = true;
					we_are_open('reload', i);
					break;
				}

				if (e.classList.contains('opening-hours-conditional')) {
					elements[i] = {
						action: 'update',
						classes:
							typeof data.classes == 'object' ? data.classes : [],
						parameters: null,
						content: null,
					};
					continue;
				}

				elements[i] = {
					action: 'refresh',
					classes: e.className.split(' '),
					parameters:
						typeof data.parameters == 'object'
							? data.parameters
							: {},
					content:
						typeof data.content == 'string' ? data.content : null,
				};
			}

			if (reload_check) {
				return;
			}

			fetch(
				`${we_are_open_wp_ajax.url}?action=${we_are_open_wp_ajax.action}`,
				{
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify({
						type: action,
						elements: elements,
					}),
				},
			)
				.then(response => response.json())
				.then(response => {
					if (!response.success) {
						return;
					}

					let elements = document.querySelectorAll(
							we_are_open_update_selector,
						),
						indexes = [],
						data = {},
						html = null,
						e = null,
						i = 0;

					elements.forEach(e => {
						if (we_are_open_element_data.has(e)) {
							return;
						}

						let raw = e.dataset.data;

						if (raw == undefined || raw == '') {
							we_are_open_element_data.set(
								e,
								raw == undefined ? null : '',
							);
							return;
						}

						try {
							we_are_open_element_data.set(e, JSON.parse(raw));
						} catch (error) {
							we_are_open_element_data.set(e, raw);
						}
					});

					for (i = 0; i < elements.length; i++) {
						e = elements[i];
						indexes[i] = null;

						if (!(i in response.elements)) {
							continue;
						}

						try {
							if (
								e.classList.contains(
									'opening-hours-conditional',
								)
							) {
								data = we_are_open_element_data.get(e);

								if (
									typeof data != 'object' ||
									data == null ||
									typeof data.html == 'undefined'
								) {
									continue;
								}

								html =
									typeof data.html == 'string' &&
									data.html.length
										? data.html
										: null;
								data.change = parseInt(
									response.elements[i].seconds_to_change,
								);
								data.closed_now = response.closed_now;
								data.content =
									typeof response.content == 'string'
										? response.content
										: null;
								data.open_now = response.open_now;

								if (
									(data.closed != response.closed_now ||
										data.open != response.open_now) &&
									(html == null || !data.remove_html)
								) {
									if (data.remove_html) {
										data.html = e.innerHTML;
										e.innerHTML = '';
									}

									if (
										data.hide &&
										!e.classList.contains('hide')
									) {
										e.classList.remove('show');
										e.classList.add('hide');
									}
								} else if (
									(data.open == response.open_now ||
										data.closed == response.closed_now) &&
									(html != null || !data.remove_html)
								) {
									if (data.remove_html) {
										data.html = null;
										e.innerHTML = '';
									}

									we_are_open_element_data.set(e, data);

									if (
										data.hide &&
										e.classList.contains('hide')
									) {
										e.classList.remove('hide');
										e.classList.add('show');
									}
								}

								if (
									typeof data.change == 'number' &&
									data.change > 0 &&
									data.change <= 86400
								) {
									indexes[i] =
										data.change >= 3600
											? 3600000
											: data.change * 1000;
								}

								continue;
							}

							if (e.classList.contains('opening-hours')) {
								data =
									typeof response.elements[i] == 'object'
										? response.elements[i]
										: {};

								we_are_open_element_data.set(e, {
									change: data.seconds_to_change,
									closed_now: !response.open_now,
									content:
										typeof data.content == 'string'
											? data.content.replace(
													/\\(')/gi,
													'$1',
												)
											: null,
									parameters: data.parameters,
									open_now: response.open_now,
									reload: data.reload,
								});
								e.innerHTML =
									typeof data.html == 'string'
										? data.html.replace(/\\(')/gi, '$1')
										: '';

								if (response.open_now) {
									e.classList.remove('closed-now');
									e.classList.add('open-now');
								} else {
									e.classList.remove('open-now');
									e.classList.add('closed-now');
								}

								if (
									typeof data.seconds_to_change == 'number' &&
									data.seconds_to_change > 0 &&
									data.seconds_to_change <= 86400
								) {
									indexes[i] = data.seconds_to_change * 1000;
								}

								continue;
							}

							data = we_are_open_element_data.get(e);

							if (typeof data != 'object' || data == null) {
								continue;
							}

							if (
								!reload_check &&
								(e.classList.contains('reload') ||
									(typeof data.reload == 'boolean' &&
										data.reload))
							) {
								reload_check = true;
								setTimeout(
									() => we_are_open('reload', i),
									data.change * 1000,
								);
								break;
							}
						} catch (error) {
							we_are_open_error(error, e);
							continue;
						}
					}

					if (reload_check) {
						return;
					}

					if (indexes.some(interval => interval != null)) {
						setTimeout(
							() => we_are_open('update', indexes),
							Math.min(...indexes.filter(Boolean)),
						);
					}
				})
				.catch(error => {
					we_are_open_error(error);
					setTimeout(() => we_are_open('init'), 3600000);
				});

			return;
	}

	return;
};

if (document.readyState == 'loading') {
	document.addEventListener('DOMContentLoaded', () => we_are_open());
} else {
	we_are_open();
}
