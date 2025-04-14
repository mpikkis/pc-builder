// PC Builder
function getURLVar(key) {
	var value = [];

	var query = String(document.location).split('?');

	if (query[1]) {
		var part = query[1].split('&');

		for (i = 0; i < part.length; i++) {
			var data = part[i].split('=');

			if (data[0] && data[1]) {
				value[data[0]] = data[1];
			}
		}

		if (value[key]) {
			return value[key];
		} else {
			return '';
		}
	}
}

// PC Builder product add remove functions
var pc_builder_product = {
	'add': function(pc_builder_component_id, product_id, quantity) {
		$.ajax({
			url: 'index.php?route=extension/pc_builder/pc_builder/add',
			type: 'post',
			data: 'pc_builder_component_id=' + pc_builder_component_id + '&product_id=' + product_id + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
			dataType: 'json',
			beforeSend: function() {
				$('#pc-builder-cart > button#pc-builder-cart-' + product_id).attr('disabled', 'disabled');
			},
			complete: function() {
				$('#pc-builder-cart > button#pc-builder-cart-' + product_id).removeAttr('disabled');
			},
			success: function(json) {
				$('.alert-dismissible, .text-danger').remove();

				if (json['redirect']) {
					location = json['redirect'];
				}

				if (json['success']) {
					location = 'index.php?route=extension/pc_builder/pc_builder&page_id=' + Math.random();
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'update': function(key, quantity) {
		
	},
	'remove': function(pc_builder_component_id, product_id, quantity) {
		$.ajax({
			url: 'index.php?route=extension/pc_builder/pc_builder/remove',
			type: 'post',
			data: 'pc_builder_component_id=' + pc_builder_component_id + '&product_id=' + product_id + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
			dataType: 'json',
			beforeSend: function() {
				$('.component-choose > button#pc-builder-product-remove-' + product_id).button('loading');
			},
			complete: function() {
				$('.component-choose > button#pc-builder-product-remove-' + product_id).button('reset');
			},
			success: function(json) {
				if (json['redirect']) {
					location = json['redirect'];
				}

				if (json['success']) {
					location = 'index.php?route=extension/pc_builder/pc_builder&page_id=' + Math.random();
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}
}

var pc_builder = {
	'cart': function(amount, weight, build) {
		console.log(amount,weight,build);
		$.ajax({
			url: 'index.php?route=extension/pc_builder/pc_builder/add_to_cart',
			type: 'post',
			data: 'amount=' + amount + '&weight=' + weight + '&build=' + build,
			dataType: 'json',
			beforeSend: function() {
				$('.pc-builder-container #menu-container > button#pc-builder-cart').button('loading');
			},
			complete: function() {
				$('.pc-builder-container #menu-container > button#pc-builder-cart').button('reset');
			},
			success: function(json) {
				console.log(json,'json');
				$('.alert-dismissible').remove();

				if (json['redirect']) {
					location = json['redirect'];
				}

				if (json['error']) {
					if (json['error']['build']) {
						$('#content').parent().before('<div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i> ' + json['error'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
					}

					if (json['error']['pc_builder_components']) {
						Object.keys(json['error']['pc_builder_components']).forEach(function (key) {
							$('#extension-pc-builder-pc-builder #pc-builder-component-container-' + key).addClass('required-component');
							//alert(key);
						});		

						$('#content').parent().before('<div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i> ' + json['error']['required_component'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
					}

					//alert(json['error']['pc_builder_components']);
				}

				if (json['success']) {
					console.log('success');
					
					location = 'index.php?route=checkout/cart';
					
					setTimeout(function () {
					$('#cart > button').html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + json['total'] + '</span>');
				}, 100);
                if (getURLVar('route') == 'cart' || getURLVar('route') == 'checkout') {
				//if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
					location = 'cart';
				} else {
					$('#cart > ul').load('index.php?route=common/cart/info ul li');
				}
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'save': function() {
		$.ajax({
			url: 'index.php?route=extension/pc_builder/pc_builder/save',
			type: 'post',
			dataType: 'json',
			beforeSend: function() {
				$('.pc-builder-container #menu-container > button#pc-builder-save').button('loading');
			},
			complete: function() {
				$('.pc-builder-container #menu-container > button#pc-builder-save').button('reset');
			},
			success: function(json) {
				$('.alert-dismissible, .text-danger').remove();

				if (json['redirect']) {
					location = json['redirect'];
				}

				if (json['error']) {
					$('#content').parent().before('<div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i> ' + json['error'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
				}

				if (json['success']) {
					location = 'index.php?route=extension/pc_builder/pc_builder&page_id=' + Math.random();
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'clear': function() {
		$.ajax({
			url: 'index.php?route=extension/pc_builder/pc_builder/clear',
			type: 'post',
			dataType: 'json',
			beforeSend: function() {
				$('.pc-builder-container #menu-container > button#pc-builder-clear').button('loading');
			},
			complete: function() {
				$('.pc-builder-container #menu-container > button#pc-builder-clear').button('reset');
			},
			success: function(json) {
				if (json['redirect']) {
					location = json['redirect'];
				}

				if (json['success']) {
					location = 'index.php?route=extension/pc_builder/pc_builder&page_id=' + Math.random();
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'remove': function(key) {
		$.ajax({
			url: 'index.php?route=extension/pc_builder/pc_builder/remove_from_cart',
			type: 'post',
			data: 'key=' + key,
			dataType: 'json',
			beforeSend: function() {
				//$('.pc-builder-container #menu-container > button#pc-builder-clear').button('loading');
			},
			complete: function() {
				//$('.pc-builder-container #menu-container > button#pc-builder-clear').button('reset');
			},
			success: function(json) {
				console.log(json,'jsondata');
				/*if (json['redirect']) {
					location = json['redirect'];
				}*/
				if (json['success']) {
					//location = 'index.php?route=extension/pc_builder/pc_builder&page_id=' + Math.random();
					location = 'cart';
				}

				// Need to set timeout otherwise it wont update the total
				setTimeout(function () {
					$('#cart > button').html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + json['total'] + '</span>');
				}, 100);
                if (getURLVar('route') == 'cart' || getURLVar('route') == 'checkout') {
				//if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
					location = 'index.php?route=cart';
				} else {
					$('#cart > ul').load('index.php?route=common/cart/info ul li');
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'build_delete': function() {
		$.ajax({
			url: 'index.php?route=extension/pc_builder/pc_builder/build_delete',
			type: 'post',
			dataType: 'json',
			beforeSend: function() {
				$('.pc-builder-container #menu-container > button#pc-builder-save').button('loading');
			},
			complete: function() {
				$('.pc-builder-container #menu-container > button#pc-builder-save').button('reset');
			},
			success: function(json) {
				if (json['redirect']) {
					location = json['redirect'];
				}

				if (json['success']) {
					location = 'index.php?route=extension/pc_builder/pc_builder&page_id=' + Math.random();
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}
}
