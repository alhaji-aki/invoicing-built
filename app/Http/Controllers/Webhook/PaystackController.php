<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Jobs\Invoice\UpdateInvoiceAmount;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class PaystackController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        $this->verify($request);

        $event = $request->get('event');

        /** @var array */
        $data = $request->get('data') ?? [];

        switch ($event) {
            case 'paymentrequest.success':
                UpdateInvoiceAmount::dispatch($data);
                break;
            default:
                break;
        }

        return response('Processing completed');
    }

    private function verify(Request $request): void
    {
        if (! $request->isMethod('post') || ! $request->header('x-paystack-signature', null)) {
            throw new AccessDeniedHttpException('Invalid Request');
        }

        // @phpstan-ignore-next-line
        if ($request->header('x-paystack-signature') !== hash_hmac('sha512', $request->getContent(), config('paystack.secret_key'))) {
            throw new AccessDeniedHttpException('Access Denied');
        }
    }
}
