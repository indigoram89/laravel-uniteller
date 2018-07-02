<?php

namespace Indigoram89\Laravel\Uniteller\Contracts;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

interface Uniteller
{
	public function createPaymentForm(array $attributes, string $button = null) : string;
	
	public function checkPaymentCompleted(Request $request) : bool;
	
	public function dispatchPaymentCompleted(Request $request);
	
	public function getPaymentResponse(Request $request) : Response;

	public function getConfig(string $key, string $default = null);
	
}