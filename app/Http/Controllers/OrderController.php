<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    public function index()
    {
        $orders = auth()->user()
            ->orders()
            ->with('items.product')
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true, 
            'data' => $orders
        ]);
    }
    public function show($id)
    {
        $order = auth()->user()
            ->orders()
            ->with('items.product')
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $order]);
    }


    public function store(StoreOrderRequest $request)
    {
        try {
            $order = DB::transaction(function () use ($request) {
                $items      = $request->input('items');
                $totalPrice = 0;
                $orderItems = [];

                foreach ($items as $item) {
                    $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                    if ($product->inventory < $item['quantity']) {
                        throw new \Exception("Insufficient stock for [{$product->name}]. Available: {$product->stock}");
                    }

                    // Deduct stock
                    $product->decrement('inventory', $item['quantity']);

                    $subtotal    = $product->price * $item['quantity'];
                    $totalPrice += $subtotal;

                    $orderItems[] = [
                        'product_id' => $product->id,
                        'quantity'=> $item['quantity'],
                        'unit_price' => $product->price, 
                        'subtotal'=> $subtotal,
                    ];
                }

                $order = Order::create([
                    'user_id'=> auth()->id(),
                    'total_price' => $totalPrice,
                    'status'=> 'pending',
                ]);

                $order->items()->createMany($orderItems);

                return $order;
            });

            Mail::to($order->user->email)->send(new OrderConfirmationMail($order));

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully.',
                'data'    => $order->load('items.product'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(), // "Not enough stock for [Dog Food]. Only 2 left."
            ], 422);
        }
    }
}
