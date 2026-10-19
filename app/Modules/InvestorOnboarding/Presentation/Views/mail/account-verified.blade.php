<x-mail::message>
# Welcome to IOMP

Dear {{ $user->name }},

Welcome to the Investment Opportunities Mapping Platform (IOMP), managed by the Ghana Investment Promotion Authority (GIPA).

Your account has been created and successfully verified. Both your e-mail address and your phone number are confirmed.

Please create your business profile. That profile is how GIPA reviews your organisation. After it is saved, you can browse the opportunity list. When management approves the profile, you can express interest in an opportunity or send an inquiry.

<x-mail::button :url="$profileUrl">
Create your business profile
</x-mail::button>

If you need assistance, [contact GIPA]({{ $supportUrl }}).

Yours sincerely,<br>
Ghana Investment Promotion Authority
</x-mail::message>
