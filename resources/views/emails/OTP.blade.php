@component('mail::message')
# OTP Verification

Hello {{ $data['name'] }},

Your One-Time Password (OTP) is:

## **{{ $data['otp'] }}**

This OTP is valid for **{{ $data['otp_expire_time'] }} minutes**.

@component('mail::button', ['url' => url('/verify-otp?email=' . urlencode($data['email']) . '&otp=' . urlencode($data['otp']))])
Verify OTP Now
@endcomponent

---

If the OTP has expired, you can request a new one by clicking the link below:

[Resend OTP]({{ url('/verify-otp?email=' . urlencode($data['email'])) }})

If you did not request this, please ignore this message.

Thanks,<br>
**{{ config('app.name') }}** Team
@endcomponent
