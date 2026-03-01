<x-mail::message>
# Order Confirmed!

Hi {{ $order->user->name }},

The body of your message.
Thank you for your order.

@component('mail::table')
| Product | Qty | Subtotal |
|:--------|:----|:---------|
@foreach($order->items as $item)
| {{ $item->product->name }} | {{ $item->quantity }} | ${{ $item->subtotal }} |
@endforeach
@endcomponent

**Total: ${{ $order->total_price }}**

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
