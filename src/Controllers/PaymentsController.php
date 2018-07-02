<?php

namespace Indigoram89\Laravel\Uniteller\Controllers;

use Illuminate\Http\Request;
use Indigoram89\Laravel\Uniteller\Contracts\Uniteller;

class PaymentsController
{
    protected $uniteller;

    public function __construct(Uniteller $uniteller)
    {
        $this->uniteller = $uniteller;
    }

    public function status(Request $request)
    {
        info('request', $request->all());

        if ($this->uniteller->checkPaymentCompleted($request)) {
            info('completed');
            $this->uniteller->dispatchPaymentCompleted($request);
        } else if ($this->uniteller->checkPaymentCancelled($request)) {
            info('cancelled');
            $this->uniteller->dispatchPaymentCancelled($request);
        }

        return $this->uniteller->getPaymentResponse($request);
    }
}
