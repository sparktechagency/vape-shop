<x-mail::message>
# Your Subscription is Active!

Hello {{ $user->name }},

Congratulations! Your payment has been confirmed and your subscription is now active.

### Subscription Details:

<x-mail::table>
| Plan | Price |
| :--- | :---- |
@foreach ($subscription->plan_details as $plan)
| {{ $plan['name'] }} | ${{ number_format($plan['price'], 2) }} |
@endforeach
</x-mail::table>

**Total Paid: ${{ number_format($subscription->total_cost, 2) }}**

Your subscription is valid from **{{ $subscription->starts_at->format('F j, Y') }}** to **{{ $subscription->ends_at->format('F j, Y') }}**.

<x-mail::button :url="url('/subscription')">
Go to Your Dashboard
</x-mail::button>

Thank you for being a valuable member of our community.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
