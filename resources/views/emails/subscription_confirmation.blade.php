@component('mail::message')
 Hello **{{ $user->full_name }}**,

Thank you for submitting your subscription request.

Our team is currently reviewing your request to ensure everything is accurate.

Once your subscription is approved, you will receive an official invoice via email for payment.

We appreciate your interest and will get back to you shortly.

<br>

Warm regards, <br> 
**{{ config('app.name') }} Team**
@endcomponent
