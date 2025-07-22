<x-mail::message>
# 🧾 Subscription Invoice

Hello **{{ $user->full_name }}**,

Thank you for choosing **{{ config('app.name') }}**. We're excited to have you on board!

Here are the details of your subscription:

---

### 📦 Subscription Details

<x-mail::table>
| Plan Name | Price (USD) |
|:----------|------------:|
@foreach ($subscription->plan_details as $plan)
| {{ $plan['name'] }} | ${{ number_format($plan['price'], 2) }} |
@endforeach
</x-mail::table>

**🔹 Total Amount Due: ${{ number_format($subscription->total_cost, 2) }}**

---

### 💳 What’s Next?

Please proceed with your payment at your earliest convenience. Once your payment is confirmed, we will activate your subscription and send you a confirmation email.

If you have any questions or need assistance, feel free to reach out to our support team.

Thanks again for being with us!<br>
Warm regards,<br>
**{{ config('app.name') }} Team**
</x-mail::message>
