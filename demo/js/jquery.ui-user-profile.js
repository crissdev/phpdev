/**
 * @package      phpdev
 * @author       Cristian Trifan
 * @copyright    2012 Cristian Trifan
 * @license      Microsoft Public License (Ms-PL)  https://github.com/CrissDev/phpdev/blob/master/license.txt
 */
(function($)
{
	$.widget('ui.userProfile',
	{
		_create: function()
		{
			var self = this;

			$(this.element).validate({
				submitHandler: function(form) { return false; },
				showErrors: function(errorMap, errorList)
				{
					if (errorList.length == 0)
					{
						$('.help-block label.error', self.element).html('').parent().hide().closest('.control-group.error').removeClass('error');
						return;
					}
					for (var i in errorList)
					{
						$(errorList[i].element).closest('.control-group').addClass('error')
							.find('.help-block label.error').html(errorList[i].message).parent().show();
					}
				}
			});

			$('.btn-primary', this.element).bind('click', function() {
				var isValid = $(self.element).valid();
				if (isValid) {
					doRpcRequest('UserRpc.updateUserProfile', [
						$('#user-email').val(),
						$('#user-first-name').val(),
						$('#user-last-name').val(),
						$('#user-password').val()
					],
					this, function() { window.location = 'index.php'; }, reportError);
				}
			});
		},

		_init: function()
		{
			doRpcRequest('UserRpc.getUserProfile', [], this, this._onUserProfileReceived, reportError);
		},

		_onUserProfileReceived: function(response)
		{
			$('#user-name').val(response.userName);
			$('#user-email').val(response.email);
			$('#user-first-name').val(response.firstName);
			$('#user-last-name').val(response.lastName);
			$('#user-password').val('************');
		}
	});
})
(jQuery);
