@component('mail::message')
# Order Cancelled

Hello,

An order placed at your store has been cancelled by the customer.

**Order Details:**
- **Order ID:** #{{ $orderId }}
- **Customer Name:** {{ $customerName }}

No further action is required from your side for this order.

@component('mail::button', ['url' => $url])
View Order Details
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
