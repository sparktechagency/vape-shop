@component('mail::message')
# OTP Verification

Hello {{ $data['name'] }},

Your One-Time Password (OTP) is:

## **{{$data['otp']}}**

This OTP is valid for **{{$data['otp_expire_time']}}** minutes.

---

If you did not request this, please ignore this message.

Thank you, <br>
**{{ config('app.name') }}** Team

@endcomponent
