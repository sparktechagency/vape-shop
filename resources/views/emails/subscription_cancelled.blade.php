<x-mail::message>
# Your Subscription Has Been Cancelled

Hello {{ $user->full_name }},

This is a confirmation that your subscription has been cancelled by our administration team.

### Cancelled Subscription Details:

<x-mail::table>
| Plan | Price |
| :--- | :---- |
@foreach ($subscription->plan_details as $plan)
| {{ $plan['name'] }} | ${{ number_format($plan['price'], 2) }} |
@endforeach
</x-mail::table>

If you believe this was a mistake or wish to re-subscribe, please contact our support team.

{{-- <x-mail::button :url="url('/contact-support')">
Contact Support
</x-mail::button> --}}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
