/**
 * @package      phpdev
 * @author       Cristian Trifan
 * @copyright    2012 Cristian Trifan
 * @license      Microsoft Public License (Ms-PL)  https://github.com/CrissDev/phpdev/blob/master/license.txt
 */
JSRPC_URL = 'jsrpc.php';
JSRPC_CONTENT_TYPE = 'text/json';
JSRPC_TIMEOUT = 30 * 1000;
JSRPC_ERROR_UNSPECIFIED = 0;
JSRPC_ERROR_INVALID_SERVER_RESPONSE = 1;
JSRPC_ERROR_REQUEST_FAILED = 2;


function doRpcRequest(method, params, context, onSuccess, onError, userData)
{
	(function(method, params, context, onSuccess, onError, userData)
	{
		var rid = Math.floor(Math.random() * 16485);
		var request = JSON.stringify({
			jsonrpc: 2.0,
			id: rid,
			method: method,
			params: params
		});

		jQuery.ajax({
			url: JSRPC_URL,
			type: 'POST',
			contentType: JSRPC_CONTENT_TYPE,
			cache: false,
			context: context,
			dataType: 'json',
			data: request,
			timeout: JSRPC_TIMEOUT,

			success: function(response)
			{
				if (typeof response === 'object' && typeof response.id !== 'undefined' && response.id === rid)
				{
					if (typeof response.result !== 'undefined')
					{
						if (typeof onSuccess === 'function')
							onSuccess.apply(context, [response.result, userData]);
						return;
					}

					if (typeof response.error === 'object' && typeof response.error.message !== 'undefined' && typeof response.error.code !== 'undefined')
					{
						if (typeof onError === 'function')
							onError.apply(this, [response.error.message, response.error.code, userData]);
						return;
					}
				}
				if (typeof onError === 'function')
					onError.apply(context, ['Invalid server response', JSRPC_ERROR_INVALID_SERVER_RESPONSE, userData]);
			},

			error: function()
			{
				if (typeof onError === 'function')
					onError.apply(context, ['Server could not serve the request.', JSRPC_ERROR_REQUEST_FAILED, userData]);
			}
		});
	})(method, params, context, onSuccess, onError, userData);
}

function reportError(message, code)
{
	alert(code + ': ' + message);
}