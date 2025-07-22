@component('mail::message')
# 📩 New Subscription Request Received

A new subscription request has been submitted and is awaiting your review.

---

### 👤 **Subscriber Name:** **{{ $subscription->user->full_name }}**

---

### 📦 **Subscription Details**

<x-mail::table>
| Plan Name         | Price (USD) |
|:------------------|------------:|
@foreach ($subscription->plan_details as $plan)
| {{ $plan['name'] }} | ${{ number_format($plan['price'], 2) }} |
@endforeach
</x-mail::table>

**🧾 Total Amount:**
**$ {{ number_format($subscription->total_cost, 2) }}**

---

Please log in to the admin panel to review and take necessary action.


Thanks & regards, <br>
**{{ config('app.name') }} Team**
@endcomponent
