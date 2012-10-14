(function($)
{
	$.widget('ui.currentUser',
	{
		_init: function()
		{
			doRpcRequest('UserRpc.getCurrentUser', [], this, this._onUserInfoReceived, reportError);
		},

		_onUserInfoReceived: function(response)
		{
			var items = $('.dropdown-menu li', this.element).hide();

			if (response.auth)
			{
				$('.user-name', this.element).text(response.name);
				items.find('a[href="profile.php"],a[href="logout.php"]').parent().show();
				$('.dropdown-menu li.divider', this.element).eq(1).show();
			}
			else
			{
				$('.user-name', this.element).text('User');
				items.find('a[href="login.php"],a[href="register.php"]').parent().show();
				$('.dropdown-menu li.divider', this.element).eq(0).show();
			}
		}
	});
})
(jQuery);
