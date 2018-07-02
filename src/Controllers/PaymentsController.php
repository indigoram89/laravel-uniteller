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
        if ($this->uniteller->checkPaymentCompleted($request)) {
            $this->uniteller->dispatchPaymentCompleted($request);
        } else if ($this->uniteller->checkPaymentCancelled($request)) {
            $this->uniteller->dispatchPaymentCancelled($request);
        }

        return $this->uniteller->getPaymentResponse($request);
    }
}
