<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderInvoiceController extends Controller
{
    public function __invoke(Request $request, Order $order): View
    {
        $order->load(['customer', 'items', 'account']);

        return view('public.orders.invoice', compact('order'));
    }
}
