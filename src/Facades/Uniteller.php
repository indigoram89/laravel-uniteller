<?php

namespace Indigoram89\Laravel\Uniteller\Facades;

use Illuminate\Support\Facades\Facade;

class Uniteller extends Facade
{
	public static function getFacadeAccessor()
	{
		return 'uniteller';
	}
}