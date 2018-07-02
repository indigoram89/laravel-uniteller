<?php

namespace Indigoram89\Laravel\Uniteller;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Indigoram89\Laravel\Uniteller\Events\PaymentCompleted;
use Indigoram89\Laravel\Uniteller\Events\PaymentCancelled;
use Indigoram89\Laravel\Uniteller\Contracts\Uniteller as UnitellerContract;

class Uniteller implements UnitellerContract
{
	protected $config;

	public function __construct($app)
	{
		$this->config = $app['config']['uniteller'];
	}

	public function createPaymentForm(array $attributes, string $button = null) : string
	{
		$this->validateAttributes($attributes);

		$attributes = $this->prepareAttributes($attributes);

		$form = '<form action="https://wpay.uniteller.ru/pay" method="POST">';

		foreach ($attributes as $name => $value) {
			$form .= str_replace(
				['{name}', '{value}'], [e($name), e($value)],
				'<input type="text" name="{name}" value="{value}">'
			);
		}

		$form .= str_replace('{button}', $button, '<input type="submit" value="{button}">');
		
		$form .= '</form>';

		return $form;
	}

	protected function validateAttributes(array $attributes)
	{
		Validator::make($attributes, [
			'Order_IDP' => 'required|string|min:1|max:100',
			'Subtotal_P' => 'required|numeric|min:0.01',
			'Currency' => 'required|string|in:RUB,USD',
			
			'URL_RETURN_OK' => 'required|string|url',
			'URL_RETURN_NO' => 'required|string|url',

			'MeanType' => 'nullable|integer|0,1,2,3,4,5',
			'EMoneyType' => 'nullable|integer|0,1,13,18,19,29',
			
			'Language' => 'nullable|string|in:en,ru',
			'Comment' => 'nullable|string|min:1|max:1024',
			'FirstName' => 'nullable|string|min:1|max:64',
			'LastName' => 'nullable|string|min:1|max:64',
			'MiddleName' => 'nullable|string|min:1|max:64',
			'Phone' => 'nullable|string|min:1|max:64',
			'Email' => 'nullable|string|email',
		])->validate();
	}

	protected function prepareAttributes(array $attributes)
	{
		$attributes['Shop_IDP'] = $this->getConfig('shop_id');
		
		$signature = [];
		$signature[] = $attributes['Shop_IDP'];
		$signature[] = $attributes['Order_IDP'];
		$signature[] = $attributes['Subtotal_P'];
		$signature[] = $attributes['MeanType'] ?? '';
		$signature[] = $attributes['EMoneyType'] ?? '';
		$signature[] = $attributes['Lifetime'] ?? '';
		$signature[] = $attributes['Customer_IDP'] ?? '';
		$signature[] = $attributes['Card_IDP'] ?? '';
		$signature[] = $attributes['IData'] ?? '';
		$signature[] = $attributes['PT_Code'] ?? '';
		
		if (isset($attributes['OrderLifetime'])) {
			$signature[] = $attributes['OrderLifetime'] ?? '';
		}
		
		$signature[] = $this->getConfig('password');

		$attributes['Signature'] = strtoupper(md5(implode('&', array_map(function ($value) {
			return md5($value);
		}, $signature))));

		return $attributes;
	}

	public function checkPaymentCompleted(Request $request) : bool
	{
		if ($this->checkPaymentSignature($request)) {
			return in_array($request->input('Status'), ['paid', 'authorized']);
		}

		return false;
	}

	public function checkPaymentCancelled(Request $request) : bool
	{
		if ($this->checkPaymentSignature($request)) {
			return ($request->input('Status') === 'cancelled');
		}

		return false;
	}

	protected function checkPaymentSignature(Request $request)
	{
		$attributes = $request->input('Order_ID');
		$attributes .= $request->input('Status');
		$attributes .= $this->getConfig('password');

		$signature = strtoupper(md5($attributes));

		return ($request->input('Signature') === $signature);
	}

	public function dispatchPaymentCompleted(Request $request)
	{
		PaymentCompleted::dispatch($request);
	}

	public function dispatchPaymentCancelled(Request $request)
	{
		PaymentCancelled::dispatch($request);
	}

	public function getPaymentResponse(Request $request) : Response
	{
		return new Response('', 200);
	}

	public function getConfig(string $key, string $default = null)
	{
		return array_get($this->config, $key, $default);
	}

	public static function routes()
	{
		Route::prefix('uniteller')->namespace('Indigoram89\Laravel\Uniteller\Controllers')->group(function ($routes) {
			$routes->post('payments/status', 'PaymentsController@status')->name('uniteller.payments.status');
		});
	}
}