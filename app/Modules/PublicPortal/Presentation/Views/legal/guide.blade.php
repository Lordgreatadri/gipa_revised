<x-layouts.public :title="__('User guide')" :description="__('How a visitor registers, verifies an investor account, and reaches active opportunities on IOMP.')">
    <section class="border-b border-slate-200 bg-brand-900 py-12 text-white sm:py-16 dark:border-slate-800">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold text-brand-100" aria-label="{{ __('Breadcrumb') }}">
                <a href="{{ route('home') }}" class="hover:text-white">{{ __('Home') }}</a>
                <span aria-hidden="true">/</span>
                <span class="text-white" aria-current="page">{{ __('User guide') }}</span>
            </nav>
            <p class="mt-6 text-xs font-bold uppercase tracking-[0.18em] text-gold-400">{{ __('Public visit') }}</p>
            <h1 class="font-display mt-3 max-w-3xl text-3xl font-bold text-balance sm:text-4xl">{{ __('How to use IOMP as a visitor or investor') }}</h1>
            <p class="mt-4 max-w-3xl text-sm leading-relaxed text-pretty text-brand-100 sm:text-base">{{ __('This guide follows the journey that is available today. The public site introduces the platform. After you register and verify your e-mail and phone, the next required step is a business profile. Opportunity records open after that profile is saved. An inquiry waits until GIPA approves it. The assistant on this site answers from the same published information and does not list opportunity records to visitors.') }}</p>
        </div>
    </section>

    <div class="mx-auto grid max-w-6xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-[16rem_minmax(0,1fr)] lg:py-16">
        <nav class="h-fit rounded-2xl border border-slate-200 bg-white p-4 text-sm dark:border-slate-800 dark:bg-slate-900 lg:sticky lg:top-24" aria-label="{{ __('On this page') }}">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">{{ __('On this page') }}</p>
            <ol class="mt-3 space-y-2 text-slate-700 dark:text-slate-200">
                <li><a class="hover:text-brand-800 dark:hover:text-brand-200" href="#visit">{{ __('1. Visit the public site') }}</a></li>
                <li><a class="hover:text-brand-800 dark:hover:text-brand-200" href="#assistant">{{ __('Ask the assistant') }}</a></li>
                <li><a class="hover:text-brand-800 dark:hover:text-brand-200" href="#contact">{{ __('2. Contact GIPA') }}</a></li>
                <li><a class="hover:text-brand-800 dark:hover:text-brand-200" href="#register">{{ __('3. Register') }}</a></li>
                <li><a class="hover:text-brand-800 dark:hover:text-brand-200" href="#verify">{{ __('4. Verify e-mail and phone') }}</a></li>
                <li><a class="hover:text-brand-800 dark:hover:text-brand-200" href="#profile">{{ __('5. Business profile (required)') }}</a></li>
                <li><a class="hover:text-brand-800 dark:hover:text-brand-200" href="#browse">{{ __('6. Browse and map') }}</a></li>
                <li><a class="hover:text-brand-800 dark:hover:text-brand-200" href="#engage">{{ __('7. Inquire or express interest') }}</a></li>
                <li><a class="hover:text-brand-800 dark:hover:text-brand-200" href="#account">{{ __('8. Keep the account current') }}</a></li>
                <li><a class="hover:text-brand-800 dark:hover:text-brand-200" href="#staff">{{ __('9. Staff access') }}</a></li>
                <li><a class="hover:text-brand-800 dark:hover:text-brand-200" href="#not-yet">{{ __('What is not on the public site') }}</a></li>
            </ol>
        </nav>

        <article class="min-w-0 space-y-12">
            <section id="visit">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-brand-700 dark:text-brand-300">{{ __('01') }}</p>
                <h2 class="font-display mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ __('Visit the public site') }}</h2>
                <p class="mt-4 text-sm leading-7 text-slate-700 dark:text-slate-300">{{ __('The home page explains the platform, how investor access works, and where to register. It does not list investment opportunities. Anyone can read it without an account.') }}</p>
                <ul class="mt-4 list-disc space-y-2 pl-5 text-sm leading-7 text-slate-700 dark:text-slate-300">
                    <li>{{ __('The platform and How it works describe the purpose of the register.') }}</li>
                    <li>{{ __('Investor access explains the order: register, verify e-mail and phone, then save a business profile before any opportunity page opens.') }}</li>
                    <li>{{ __('Privacy, terms, and the data policy are published as placeholders until GIPA issues the approved text.') }}</li>
                    <li>{{ __('A light theme is the default. The sun and moon control switches to dark.') }}</li>
                </ul>
            </section>

            <section id="assistant">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-brand-700 dark:text-brand-300">{{ __('Assistant') }}</p>
                <h2 class="font-display mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ __('Ask the GIPA Assistant') }}</h2>
                <p class="mt-4 text-sm leading-7 text-slate-700 dark:text-slate-300">{{ __('The green button labelled Ask GIPA sits at the bottom right of the public site and of the investor workspace, including the dashboard. Open it to ask about registration, verification, the business profile, office hours, contact channels, and how opportunity lists are opened.') }}</p>
                <ul class="mt-4 list-disc space-y-2 pl-5 text-sm leading-7 text-slate-700 dark:text-slate-300">
                    <li>{{ __('A visitor is told how to register and is not given opportunity titles, officer phone numbers, or officer e-mail addresses.') }}</li>
                    <li>{{ __('After e-mail verification, phone verification, and a saved business profile, the same assistant can name approved and active opportunities.') }}</li>
                    <li>{{ __('Certificates, QR checks, matchmaking, readiness scores, and identity-document lists are not available yet. The assistant says so.') }}</li>
                    <li>{{ __('Answers come from published platform information. The assistant does not give personalised investment advice. Use Contact us when the answer is not enough.') }}</li>
                </ul>
            </section>

            <section id="contact">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-brand-700 dark:text-brand-300">{{ __('02') }}</p>
                <h2 class="font-display mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ __('Contact GIPA') }}</h2>
                <p class="mt-4 text-sm leading-7 text-slate-700 dark:text-slate-300">{{ __('Use Contact us for a general enquiry. You do not need an account. Do not put a password, a copy of an identity document, or bank details in the message.') }}</p>
                <ol class="mt-4 list-decimal space-y-2 pl-5 text-sm leading-7 text-slate-700 dark:text-slate-300">
                    <li>{{ __('Enter your name, e-mail, an optional phone number, and the message.') }}</li>
                    <li>{{ __('Leave the website field empty. It is there to catch automated posts.') }}</li>
                    <li>{{ __('Accept the acknowledgement and submit. You receive an e-mail that the enquiry was received.') }}</li>
                    <li>{{ __('GIPA staff read the enquiry in their workspace. A reply, when sent, arrives by e-mail.') }}</li>
                </ol>
            </section>

            <section id="register">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-brand-700 dark:text-brand-300">{{ __('03') }}</p>
                <h2 class="font-display mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ __('Register an investor account') }}</h2>
                <p class="mt-4 text-sm leading-7 text-slate-700 dark:text-slate-300">{{ __('Registration is for investors. A GIPA staff account cannot be created from this form.') }}</p>
                <ol class="mt-4 list-decimal space-y-2 pl-5 text-sm leading-7 text-slate-700 dark:text-slate-300">
                    <li>{{ __('Open Register as investor.') }}</li>
                    <li>{{ __('Provide your name, e-mail, mobile number in international form, country, and a password of at least 12 characters.') }}</li>
                    <li>{{ __('Accept the terms of use.') }}</li>
                    <li>{{ __('Submit. Registration alone does not open the opportunity register. Verification comes next, then a required business profile.') }}</li>
                </ol>
            </section>

            <section id="verify">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-brand-700 dark:text-brand-300">{{ __('04') }}</p>
                <h2 class="font-display mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ __('Verify e-mail and phone') }}</h2>
                <ol class="mt-4 list-decimal space-y-2 pl-5 text-sm leading-7 text-slate-700 dark:text-slate-300">
                    <li>{{ __('Open the signed link in the verification e-mail. The link expires.') }}</li>
                    <li>{{ __('Sign in if you are asked to.') }}</li>
                    <li>{{ __('Confirm the mobile number with the one-time code sent by text message to that phone.') }}</li>
                    <li>{{ __('When the phone code is accepted, IOMP opens the business profile form. That form is the next required step. Opportunity pages stay closed until it is saved.') }}</li>
                </ol>
                <p class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">{{ __('If you later change the e-mail, the e-mail must be verified again. If you change the phone, the phone must be verified again. Until that is done, the workspace stays closed.') }}</p>
            </section>

            <section id="profile">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-brand-700 dark:text-brand-300">{{ __('05') }}</p>
                <h2 class="font-display mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ __('Create a business profile') }}</h2>
                <p class="mt-4 text-sm leading-7 text-slate-700 dark:text-slate-300">{{ __('This step is required. After e-mail and phone verification, a new investor who has no business profile is taken to this form and kept there. The dashboard, opportunities, investment map, engagement, account, and notifications do not open until the profile is saved.') }}</p>
                <p class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-950 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-100">{{ __('Signing in again returns you to the same form if the profile is still missing. The menu shows only Business profile, and you can sign out. Removing a saved profile closes the other pages until you create one again.') }}</p>
                <p class="mt-4 text-sm leading-7 text-slate-700 dark:text-slate-300">{{ __('Save these details:') }}</p>
                <ul class="mt-4 list-disc space-y-2 pl-5 text-sm leading-7 text-slate-700 dark:text-slate-300">
                    <li>{{ __('Organization or business name, business e-mail, and contact.') }}</li>
                    <li>{{ __('Profile type. Individual is the default.') }}</li>
                    <li>{{ __('Nationality, country, currency (GHS or USD), and language (English or French).') }}</li>
                </ul>
                <p class="mt-4 text-sm leading-7 text-slate-700 dark:text-slate-300">{{ __('Saving the profile opens the rest of the workspace, including active opportunities and the map. GIPA approval is a separate later step. You can browse while a new or edited profile is waiting. You cannot send an inquiry or express interest until the profile is approved and active.') }}</p>
                <p class="mt-4 text-sm leading-7 text-slate-700 dark:text-slate-300">{{ __('Changing the organization name or the business e-mail on an approved profile sends it back for approval. Browsing stays open. Inquiries pause until GIPA approves it again.') }}</p>
                <p class="mt-4 text-sm leading-7 text-slate-700 dark:text-slate-300">{{ __('Identity documents are not part of this form. GIPA will add that KYC step later, after it confirms which documents to collect.') }}</p>
            </section>

            <section id="browse">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-brand-700 dark:text-brand-300">{{ __('06') }}</p>
                <h2 class="font-display mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ __('Browse active opportunities and the map') }}</h2>
                <p class="mt-4 text-sm leading-7 text-slate-700 dark:text-slate-300">{{ __('These pages open after the business profile is saved. The list shows only opportunities that GIPA has made active. Drafts, records waiting for approval, and closed records are not shown.') }}</p>
                <ul class="mt-4 list-disc space-y-2 pl-5 text-sm leading-7 text-slate-700 dark:text-slate-300">
                    <li>{{ __('Search by title and filter by sector, sub-sector, enterprise type, region, district, and amount.') }}</li>
                    <li>{{ __('Amounts can be typed or stepped with the arrows. GHS and USD are not added together.') }}</li>
                    <li>{{ __('The investment map plots active records on Ghana. Open a point to read the title, district, and amount, then follow the link to the record.') }}</li>
                    <li>{{ __('Use my location searches nearby records from the browser. That position is not saved on your account.') }}</li>
                    <li>{{ __('A map point is a discovery aid. It is not a surveyed site boundary, and district outlines are not drawn.') }}</li>
                    <li>{{ __('The record does not show the officer’s phone number or e-mail. Use an inquiry when you need to reach GIPA about that opportunity.') }}</li>
                </ul>
            </section>

            <section id="engage">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-brand-700 dark:text-brand-300">{{ __('07') }}</p>
                <h2 class="font-display mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ __('Inquire or express interest') }}</h2>
                <ol class="mt-4 list-decimal space-y-2 pl-5 text-sm leading-7 text-slate-700 dark:text-slate-300">
                    <li>{{ __('Save the business profile first, then wait until GIPA approves it. Open an active opportunity only after that approval.') }}</li>
                    <li>{{ __('Send an inquiry with a message of at least 20 characters, or express interest. Confirm consent.') }}</li>
                    <li>{{ __('You may send one inquiry and one expression of interest on the same opportunity. A repeat names the reference you already have.') }}</li>
                    <li>{{ __('Each submission receives a reference beginning with INQ- or INT- and stays in the submitted state.') }}</li>
                    <li>{{ __('Open My engagement to read that record and add a follow-up message. A message does not change the submitted state.') }}</li>
                </ol>
                <p class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">{{ __('Do not include passwords or copies of identity documents in an inquiry, an interest note, or a follow-up message.') }}</p>
            </section>

            <section id="account">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-brand-700 dark:text-brand-300">{{ __('08') }}</p>
                <h2 class="font-display mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ __('Keep the account current') }}</h2>
                <ul class="mt-4 list-disc space-y-2 pl-5 text-sm leading-7 text-slate-700 dark:text-slate-300">
                    <li>{{ __('Account and the notification bell appear after the business profile is saved. Account holds your name, e-mail, phone, and password. Business profile stays a separate page.') }}</li>
                    <li>{{ __('Changing the password asks for the current password and a new password of at least 12 characters.') }}</li>
                    <li>{{ __('The bell shows notices, including when a business profile is approved.') }}</li>
                    <li>{{ __('Sign out from the navigation when you finish, especially on a shared computer.') }}</li>
                </ul>
            </section>

            <section id="staff">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-brand-700 dark:text-brand-300">{{ __('09') }}</p>
                <h2 class="font-display mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ __('Staff access') }}</h2>
                <p class="mt-4 text-sm leading-7 text-slate-700 dark:text-slate-300">{{ __('GIPA staff do not register themselves. An authorised officer invites them, assigns a role, and where needed assigns districts. Staff sign in with the account from that invitation and use the staff workspace. Investor registration does not grant staff access.') }}</p>
            </section>

            <section id="not-yet">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-brand-700 dark:text-brand-300">{{ __('Limits') }}</p>
                <h2 class="font-display mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ __('What is not on the public site') }}</h2>
                <ul class="mt-4 list-disc space-y-2 pl-5 text-sm leading-7 text-slate-700 dark:text-slate-300">
                    <li>{{ __('There is no public catalogue of opportunities. Search engines and signed-out visitors do not receive the register.') }}</li>
                    <li>{{ __('The business profile does not ask for identity documents. That KYC upload will be added when GIPA confirms which documents it will accept.') }}</li>
                    <li>{{ __('Certificates, readiness scores, and job-creation figures are not part of this visit. The assistant will say those are not available.') }}</li>
                    <li>{{ __('An inquiry is recorded for GIPA to read. It is not an approval, a match, or a commitment to invest.') }}</li>
                </ul>
                <p class="mt-6 text-sm leading-7 text-slate-700 dark:text-slate-300">
                    <a class="font-semibold text-brand-800 underline dark:text-brand-200" href="{{ route('register') }}">{{ __('Register as an investor') }}</a>
                    <span aria-hidden="true"> · </span>
                    <a class="font-semibold text-brand-800 underline dark:text-brand-200" href="{{ route('contact.create') }}">{{ __('Contact us') }}</a>
                    <span aria-hidden="true"> · </span>
                    <a class="font-semibold text-brand-800 underline dark:text-brand-200" href="{{ route('api.documentation') }}">{{ __('API documentation') }}</a>
                </p>
            </section>
        </article>
    </div>
</x-layouts.public>
