<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('user', 'items.product')
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true, 
            'data' => $orders
        ]);
    }

    public function show($id)
    {
        $order = Order::with('user', 'items.product')->findOrFail($id);
        return response()->json([
            'success' => true, 
            'data' => $order
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,ongoing,delivered,cancelled',
        ]);
        $order = Order::findOrFail($id);
        $order->update([
            'status' => $request->status
        ]);
        return response()->json([
            'success' => true, 
            'data' => $order
        ]);
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();
        return response()->json([
            'success' => true, 
            'message' => 'Order soft deleted'
        ]);
    }

    public function restore($id)
    {
        $order = Order::withTrashed()->findOrFail($id);
        $order->restore();
        return response()->json([
            'success' => true, 
            'message' => 'Order restored'
        ]);
    }
}
